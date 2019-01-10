<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Storage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectPage;
use App\Models\ChatBlock;
use App\Models\ChatBlockSection;
use App\Models\ChatBlockSectionContent;
use App\Models\ChatButton;
use App\Models\ChatButtonBlock;
use App\Models\ChatGallery;
use App\Models\ChatQuickReply;
use App\Models\ChatQuickReplyBlock;
use App\Models\ChatUserInput;

use App\Models\FacebookRequestLogs;
use App\Models\ProjectPageUser;
use App\Models\ProjectPageUserChat;
use App\Models\ProjectPageUserAttribute;

class ChatBotProjectController extends Controller
{
    protected $projectid;
    protected $projectPage;
    protected $user;
    protected $inputOrder = -1;

    public function __construct($projectid)
    {
        $this->projectid = $projectid;
        $this->projectPage = ProjectPage::where('project_id', $projectid)->first();
    }

    // process input from messenger
    public function process($input=null, $payload=false, $welcome=false, $userid=null, $ignore=false, $justRecord=false)
    {
        $response = [];
        $userid = isset($input['entry'][0]['messaging'][0]['sender']['id']) ? $input['entry'][0]['messaging'][0]['sender']['id'] : '';
        if($welcome===false) {
            $userid = $userid!==$this->projectPage->page_id ? $input['entry'][0]['messaging'][0]['sender']['id']: $input['entry'][0]['messaging'][0]['recipient']['id'];
        }

        $recordUser = $this->recordChatUser($userid, config('facebook.defaultPageId')===$input['entry'][0]['id']);
    
        if(!$recordUser['status']) {
            return $recordUser;
        }

        $this->user->updated_at = date("Y-m-d H:i:s");
        $this->user->save();

        $log;
        $postback = '';
        $mesgText = isset($input['entry'][0]['messaging'][0]['message']['text']) ? $input['entry'][0]['messaging'][0]['message']['text'] : '';

        if($payload) {
            $mesgText = (String) $input['entry'][0]['messaging'][0]['postback']['title'];
            $postback = $input['entry'][0]['messaging'][0]['postback']['payload'];
        } else if(isset($input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'])) {
            $postback = $input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'];
        }

        DB::beginTransaction();
        try {
            $log = ProjectPageUserChat::create([
                'content_id' => null,
                'post_back' => $postback,
                'from_platform' => false,
                'mesg' => $mesgText,
                'mesg_id' => isset($input['entry'][0]['messaging'][0]['message']['mid']) ? $input['entry'][0]['messaging'][0]['message']['mid'] : '',
                'project_page_user_id' => $this->user->id,
                'is_send' => false,
                'ignore' => $ignore
            ]);
        } catch(\Exception $e) {
            DB::rollback();
            return [
                'status' => false,
                'type' => 'um-record',
                'mesg' => 'Failed to record mesg!',
                'debugMesg' => $e->getMessage()
            ];
        }
        DB::commit();

        if($this->user->live_chat || $justRecord) {
            return [
                'status' => true,
                'type' => '',
                'data' => []
            ];
        }
        
        if($welcome===false) {
            if($payload) {
                $py = explode('-', $input['entry'][0]['messaging'][0]['postback']['payload']);

                if(isset($py[1])) {
                    $button = ChatButton::with('blocks')->find($py[1]);

                    if(!empty($button)) {

                        if(!empty($button->blocks) && isset($button->blocks[0]) && !is_null($button->blocks[0]->section_id)) {

                            $resContent = $this->getSection($button->blocks[0]->section_id);

                            if($resContent['status']) {
                                $response = $resContent;
                            }

                        }

                    }
                }
            } else if(isset($input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'])) {
                $qr = explode('-', $input['entry'][0]['messaging'][0]['message']['quick_reply']['payload']);
                $content = ChatBlockSectionContent::with('section')->find($qr[1]);

                if(!empty($content)) {
                    $chatQuickReply = ChatQuickReply::with(['content', 'blocks'])->find($qr[2]);
                    if(!empty($chatQuickReply)) {

                        if(!is_null($chatQuickReply->attribute_id) && $chatQuickReply->value) {
                            $this->setUserAttribute($chatQuickReply->attribute_id, $this->user->id, $chatQuickReply->value);
                        }

                        if(!empty($chatQuickReply->blocks) && isset($chatQuickReply->blocks[0]) && !is_null($chatQuickReply->blocks[0]->section_id)) {
                            $resContent = $this->getSection($chatQuickReply->blocks[0]->section_id);
                            if($resContent['status']) {
                                $response = $resContent;
                            }
                        } else {
                            $resContent = $this->getResumeSection($chatQuickReply->content->section_id, $chatQuickReply->content->order);
                            if($resContent['status']) {
                                $response = $resContent;
                            }
                        }
                    }
                }
                
            } else {
                $lastRecord = ProjectPageUserChat::where('project_page_user_id', $this->user->id)
                                ->where('is_send', true)
                                ->where('from_platform', true)
                                ->orderBy('created_at', 'desc')
                                ->first();
                
                if(!empty($lastRecord) && !is_null($lastRecord->user_input_id)) {
                    $userInput = ChatUserInput::find($lastRecord->user_input_id);
                    if(!empty($userInput)) {
                        if(!is_null($userInput->attribute_id)) {
                            $this->setUserAttribute($userInput->attribute_id, $this->user->id, $mesgText);
                        }

                        $this->inputOrder = $userInput->order;
        
                        $resContent = $this->getResumeSection($userInput->content->section_id, ($userInput->content->order-1));
                        if($resContent['status']) {
                            $response = $resContent;
                        }
                    }
                }
            }
        } else {
            $response = $this->getWelcome();
        }
        
        if(empty($response)) {
            $response = $this->getDefault();
        }

        $response['userid'] = $this->user->id;

        return $response;
    }

    public function setUserAttribute($attributeid, $userid, $value)
    {
        $attr = ProjectPageUserAttribute::where('attribute_id', $attributeid)->where('project_page_user_id', $userid)->first();

        if(empty($attr)) {
            DB::beginTransaction();
            try {
                $attr = ProjectPageUserAttribute::create([
                    'attribute_id' => $attributeid,
                    'project_page_user_id' => $userid,
                    'value' => ''
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw(new \Exception("Failed to create user attribute!"));
            }
            DB::commit();
        }

        DB::beginTransaction();
        try {
            $attr->value = $value;
            $attr->save();
        } catch (\Exception $e) {
            DB::rollback();
            throw(new \Exception("Failed to update user attribute!"));
        }
        DB::commit();
    }

    // ai validation
    public function aiValidation($keyword='')
    {
        
    }

    public function getSection($section)
    {
        $section = ChatBlockSection::with([
            'contents' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.galleryList' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.galleryList.buttons' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.buttons' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.quickReply' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.userInput' => function($query) {
                $query->limit(1);
                $query->orderBy('order', 'ASC');
            }
        ])->find($section);
        

        FacebookRequestLogs::create([
            'section' => 'section: ',
            'data' => json_encode($section)
        ]);

        return [
            'status' => true,
            'type' => '',
            'data' => $this->contentParser($section->contents)
        ];
    }

    public function getResumeSection($section, $order) {
        // $inputOrder = $this->inputOrder;
        $section = ChatBlockSection::with([
            'contents' => function($query) use ($order) {
                $query->where('order', '>', $order);
                $query->orderBy('order', 'ASC');
            },
            'contents.galleryList' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.galleryList.buttons' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.buttons' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.quickReply' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.userInput' => function($query) {
                $query->limit(1);
                if($this->inputOrder>-1) {
                    $query->where('order', '>', $this->inputOrder);
                }
                $query->orderBy('order', 'ASC');
            }
        ])->whereHas('contents', function($query) use ($order) {
            $query->where('order', '>', $order);
        })->find($section);

        FacebookRequestLogs::create([
            'data' => json_encode([
                'section' => 'section: ',
                'data' => $section
            ])
        ]);

        return [
            'status' => !empty($section),
            'type' => '',
            'data' => !empty($section) ? $this->contentParser($section->contents) : []
        ];
    }

    public function getWelcome()
    {
        $block = ChatBlock::where('project_id', $this->projectid)->where('is_lock', true)->first();
        $section = ChatBlockSection::with([
            'contents' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.galleryList' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.galleryList.buttons' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.buttons' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.quickReply' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.userInput' => function($query) {
                $query->orderBy('order', 'ASC');
                $query->limit(1);
            }
        ])->where('block_id', $block->id)->where('type', 1)->first();
        

        FacebookRequestLogs::create([
            'section' => 'section: ',
            'data' => json_encode($section)
        ]);

        return [
            'status' => true,
            'type' => '',
            'data' => $this->contentParser($section->contents)
        ];
    }

    public function getDefault()
    {
        $block = ChatBlock::where('project_id', $this->projectid)->where('is_lock', true)->first();
        $section = ChatBlockSection::with([
            'contents' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.galleryList' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.galleryList.buttons' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.buttons' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.quickReply' => function($query) {
                $query->orderBy('order', 'ASC');
            },
            'contents.userInput' => function($query) {
                $query->orderBy('order', 'ASC');
                $query->limit(1);
            }
        ])->where('block_id', $block->id)->where('type', 2)->first();
        

        FacebookRequestLogs::create([
            'section' => 'section: ',
            'data' => json_encode($section)
        ]);

        return [
            'status' => true,
            'type' => '',
            'data' => $this->contentParser($section->contents)
        ];
    }
    
    public function getGreeting()
    {
        
    }

    public function contentParser($contents)
    {
        $res = [];

        $break = false;

        // parse content based on their content type
        foreach($contents as $content) {
            $parsed = [];

            switch ($content->type) {
                case 1:
                    $parsed = $this->parseText($content);
                    break;
                
                case 2:
                    $parsed = $this->parseTyping($content);
                    break;
                
                case 3:
                    $break = true;
                    $parsed = $this->parseQuickReply($content);
                    break;
                
                case 4:
                    $break = true;
                    $parsed = $this->parseUserInput($content);
                    break;
                
                case 5:
                    $parsed = $this->parseList($content);
                    break;
                
                case 6:
                    $parsed = $this->parseGallery($content);
                    break;
                
                case 7:
                    $parsed = $this->parseImage($content);
                    break;
            }

            $parsed['content_id'] = $content->id;
            if($parsed['status']) {
                $res[] = $parsed;
                if($break) break;
            } else {
                $break = false;
            }
        }

        return $res;
    }

    // parse text to messenger support format
    public function parseText($content)
    {
        $buttons = [];

        foreach($content->buttons as $button) {
            $parsed = $this->parseButton($button);
            if($parsed['status'] === true) {
                $buttons[] = $parsed['data'];
            }
        }

        $res = [
            "text" => $content->content
        ];

        if(!empty($button)) {
            $res = [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'button',
                        'text' => $content->content,
                        'buttons' => $buttons
                    ]
                ]
            ];
        }

        return [
            'status' => true,
            'mesg' => '',
            'type' => 1,
            'data' => $res
        ];
    }

    // get duration to show typing action
    public function parseTyping($content)
    {
        return [
            'status' => true,
            'mesg' => '',
            'data' => [],
            'type' => 2,
            'duration' => $content->duration
        ];
    }

    // parse quick reply to messenger support format
    public function parseQuickReply($content)
    {
        $qr = [];
        $res = [
            'status' => true,
            'mesg' => '',
            'type' => 3,
            'data' => [
                'text' => 'Select an option',
                'quick_replies' => []
            ]
        ];

        foreach($content->quickReply as $quickReply) {
            $qr[] = [
                'content_type' => 'text',
                'title' => $quickReply->title,
                'payload' => 'qr-'.$content->id.'-'.$quickReply->id
            ];
        }

        $res['data']['quick_replies'] = $qr;

        if(empty($qr)) {
            $res['status'] = false;
        }

        return $res;
    }

    public function parseUserInput($content)
    {
        $err = [
            'status' => false,
            'mesg' => 'There is no userinput!',
            'type' => 4,
            'data' => []
        ];
        if(empty($content->userInput) || is_null($content->userInput)) {
            return $err;
        }
        
        if(empty($content->userInput[0]->question) || is_null($content->userInput[0]->attribute_id)) {
            return $err;
        }
        
        return [
            'status' => true,
            'mesg' => '',
            'type' => 4,
            'data' => [
                "text" => $content->userInput[0]->question
            ],
            'input_id' => $content->userInput[0]->id
        ];
    }

    // parse list to messenger support format
    public function parseList($content)
    {
        $res = [];

        foreach($content->galleryList as $list) {
            if(empty($list->title)) continue;

            $buttons = [];

            foreach($list->buttons as $button) {
                $btParsed = $this->parseButton($button);
                if($btParsed['status']) {
                    $buttons[] = $btParsed['data'];
                }
            }

            $image = "";

            if(!empty($list->image) && Storage::disk('public')->exists('images/list/'.$list->image)) {
                $image = Storage::disk('public')->url('images/list/'.$list->image);
            }
            
            $parsed = [
                'title' => (string) $list->title,
                'subtitle' => (string) $list->sub
            ];

            if($image) {
                $parsed['image_url'] = $image;
            }

            if($list->url) {
                $parsed['default_action'] = [
                    'type' => 'web_url',
                    'url' => $list->url,
                ];
            }

            if(!empty($buttons)) {
                $parsed['buttons'] = $buttons;
            }

            $res[] = $parsed;
        }

        // $but[]
        $result = [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => 'list',
                    'top_element_style' => 'compact',
                    'elements' => $res
                ]
            ],
        ];

        if(!empty($content->buttons)) {
            $buttons = [];
            foreach($content->buttons as $button) {
                $btParsed = $this->parseButton($button);
                if($btParsed['status']) {
                    $buttons[] = $btParsed['data'];
                }
            }

            if(!empty($buttons)) {
                $result['attachment']['payload']['buttons'] = $buttons;
            }
        }

        if(empty($res)) {
            return [
                'status' => false,
                'mesg' => 'There is no list!',
                'data' => [],
                'type' => 5
            ];
        }

        return [
            'status' => true,
            'mesg' => '',
            'data' => $result,
            'type' => 5
        ];
    }

    // parse gallery (generic template) to messenger support format
    public function parseGallery($content)
    {
        $res = [];

        foreach($content->galleryList as $list) {
            if(empty($list->title)) continue;

            $buttons = [];

            foreach($list->buttons as $button) {
                $btParsed = $this->parseButton($button);
                if($btParsed['status']) {
                    $buttons[] = $btParsed['data'];
                }
            }

            $image = "";

            if(!empty($list->image) && Storage::disk('public')->exists('images/gallery/'.$list->image)) {
                $image = Storage::disk('public')->url('images/gallery/'.$list->image);
            }
            
            $parsed = [
                'title' => (string) $list->title,
                'subtitle' => (string) $list->sub
            ];

            if($image) {
                $parsed['image_url'] = $image;
            }

            if($list->url) {
                $parsed['default_action'] = [
                    'type' => 'web_url',
                    'url' => $list->url,
                ];
            }

            if(!empty($buttons)) {
                $parsed['buttons'] = $buttons;
            }

            $res[] = $parsed;
        }
        
        $result = [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => 'generic',
                    'elements' => $res
                ]
            ],
        ];

        if(!empty($content->buttons)) {
            $buttons = [];
            foreach($content->buttons as $button) {
                $btParsed = $this->parseButton($button);
                if($btParsed['status']) {
                    $buttons[] = $btParsed['data'];
                }
            }

            if(!empty($buttons)) {
                $result['attachment']['payload']['buttons'] = $buttons;
            }
        }

        if(empty($res)) {
            return [
                'status' => false,
                'mesg' => 'There is no list!',
                'data' => [],
                'type' => 5
            ];
        }

        return [
            'status' => true,
            'mesg' => '',
            'data' => $result,
            'type' => 5
        ];
    }

    // parse image
    public function parseImage($content)
    {
        $res = [
            'status' => false,
            'mesg' => '',
            'data' => [
                'image' => ''
            ],
            'type' => 7
        ];
        
        if(!empty($content->image) && Storage::disk('public')->exists('images/photos/'.$content->image)) {
            $res['status'] = true;
            $res['data']['image'] = Storage::disk('public')->url('images/photos/'.$content->image);
        }

        return $res;
    }

    // Parse buttont to messenger support format
    public function parseButton($button)
    {
        $res = [
            'status' => true,
            'mesg' => '',
            'data' => []
        ];

        if(empty($button->title)) {
            $res['status'] = false;
            $res['mesg'] = 'Button title is empty!';
            return $res;
        }

        switch((int) $button->action_type) {
            case(0):
                $res['data'] = [
                    'type' => 'postback',
                    'title' => $button->title,
                    'payload' => 'button-'.$button->id
                ];
                break;

            case(1):
                $res['data'] = [
                    'type' => 'web_url',
                    'url' => $button->url,
                    'title' => $button->title,
                ];
                break;

            case(2):
                $res['data'] = [
                    'type' => 'phone_number',
                    'payload' => $button->phone,
                    'title' => $button->title
                ];
                break;

            default:
                $res['status'] = false;
                break;
        }

        return $res;
    }

    // record chat user
    public function recordChatUser($userid, $isTestPage=false)
    {
        $res = [
            'status' => true,
            'type' => '',
            'mesg' => 'success'
        ];

        if($isTestPage) {
            $this->user = ProjectPageUser::whereNull('project_page_id')->where('fb_user_id', $userid)->first();
            if(empty($this->user)) {
                $res = [
                    'status' => false,
                    'type' => 'rcu-create',
                    'mesg' => $userid.' did\'t exists!'
                ];
            }
        } else {
            $this->user = ProjectPageUser::where('project_page_id', $this->projectPage->id)->where('fb_user_id', $userid)->first();
            if(empty($this->user)) {
                DB::beginTransaction();
    
                try {
                    $this->user = ProjectPageUser::create([
                        'project_page_id' => $this->projectPage->id,
                        'fb_user_id' => $userid
                    ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    $res = [
                        'status' => false,
                        'type' => 'rcu-create',
                        'mesg' => 'Failed to record chat user!'
                    ];
                }
    
                DB::commit();
            }
        }
        
        return $res;
    }
}
