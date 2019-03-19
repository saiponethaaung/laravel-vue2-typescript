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
use App\Models\KeywordFilterGroupRule;
use App\Models\KeywordFilter;
use App\Models\KeywordFilterResponse;

use App\Http\Controllers\V1\Api\FacebookController;

// @codeCoverageIgnoreStart

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
        // get user id
        $userid = isset($input['entry'][0]['messaging'][0]['sender']['id']) ? $input['entry'][0]['messaging'][0]['sender']['id'] : '';
        // check the section is welcome section
        if($welcome===false) {
            $userid = $userid!==$this->projectPage->page_id ? $input['entry'][0]['messaging'][0]['sender']['id']: $input['entry'][0]['messaging'][0]['recipient']['id'];
        }

        // Reocord user
        $recordUser = $this->recordChatUser($userid, config('facebook.defaultPageId')===$input['entry'][0]['id']);
    
        // Response an error if record user is failed
        if(!$recordUser['status']) {
            FacebookRequestLogs::create([
                'data' => 'break on failed to record'
            ]);
            return $recordUser;
        }

        // update user last active status
        $this->user->updated_at = date("Y-m-d H:i:s");
        $this->user->save();

        $log;
        $postback = '';
        // get message body from user
        $mesgText = isset($input['entry'][0]['messaging'][0]['message']['text']) ? $input['entry'][0]['messaging'][0]['message']['text'] : '';

        // Check if the request payload
        if($payload) {
            $mesgText = (String) $input['entry'][0]['messaging'][0]['postback']['title'];
            $postback = $input['entry'][0]['messaging'][0]['postback']['payload'];
        } else if(isset($input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'])) {
            // check if the request is payload from quick reply
            $postback = $input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'];
        }

        if(!empty('$mesgText')) {
            // open transaction
            DB::beginTransaction();
            try {
                // Record user chat
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
                FacebookRequestLogs::create([
                    'data' => 'break on failed to record mesg'
                ]);
                // rollback if recording user chat is failed
                DB::rollback();
                return [
                    'status' => false,
                    'type' => 'um-record',
                    'mesg' => 'Failed to record mesg!',
                    'debugMesg' => $e->getMessage()
                ];
            }
            DB::commit();
        }

        // if user is on live chat or it's function to stop with record stop the process
        if($this->user->live_chat || $justRecord) {
            FacebookRequestLogs::create([
                'data' => 'break because live chat is open'
            ]);
            return [
                'status' => true,
                'type' => '',
                'data' => [],
                'userid' => $this->user->id
            ];
        }
        
        if($welcome===false) {
            // if it's button payload
            if($payload) {
                $py = explode('-', $input['entry'][0]['messaging'][0]['postback']['payload']);

                if(isset($py[1])) {
                    $button = ChatButton::with('blocks')->find($py[1]);

                    if(!empty($button)) {

                        if(!empty($button->blocks) && isset($button->blocks[0]) && !is_null($button->blocks[0]->section_id)) {

                            if(!is_null($button->blocks[0]->attribute_id) && $button->blocks[0]->value) {
                                $this->setUserAttribute($button->blocks[0]->attribute_id, $this->user->id, $button->blocks[0]->value);
                            }

                            $resContent = $this->getSection($button->blocks[0]->section_id);

                            if($resContent['status']) {
                                $response = $resContent;
                            }

                        }

                    }
                }
            } else if(isset($input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'])) {
                // if it's quick reply payload
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
                // Check the last action from platform
                $lastRecord = ProjectPageUserChat::where('project_page_user_id', $this->user->id)
                                ->where('is_send', true)
                                ->where('from_platform', true)
                                ->where('ignore', false)
                                ->orderBy('created_at', 'desc')
                                ->first();
                
                // if last record is not empty and it's user input proceed
                if(!empty($lastRecord) && !is_null($lastRecord->user_input_id) && !$lastRecord->input_complete) {
                    // extract user input and if user input still exists
                    $userInput = ChatUserInput::find($lastRecord->user_input_id);
                    if(!empty($userInput)) {
                        $breakUserInputProcess = false;

                        // validate input type
                        $isValidInput = true;
                        $errorMesg = '';
                        switch($userInput->validation) {
                            // validate phone number
                            case(1):
                                if (preg_match('/^[0-9 ()+-]+$/', $mesgText)===0) {
                                    $isValidInput = false;
                                    $errorMesg = 'Invalid phone number value!';
                                }
                                break;
                            
                            // validate email
                            case(2):
                                if (!filter_var($mesgText, FILTER_VALIDATE_EMAIL)) {
                                    $isValidInput = false;
                                    $errorMesg = 'Invalid email address!';
                                }
                                break;

                            // validate number only
                            case(3):
                                if (preg_match('/^[0-9]+$/', $mesgText)===0) {
                                    $isValidInput = false;
                                    $errorMesg = 'Enter only numeric value!';
                                }
                                break;
                        }

                        if(!$isValidInput) {
                            $response = [
                                'status' => true,
                                'type' => '',
                                'data' => [
                                    [
                                        'status' => true,
                                        'mesg' => '',
                                        'type' => 1,
                                        'content_id' => null,
                                        'data' => [
                                            'text' => $errorMesg
                                        ],
                                        'ignore' => true
                                    ]
                                ]
                            ];
                            $breakUserInputProcess = true;
                        } else {
                            // if user input have attribute
                            if(!is_null($userInput->attribute_id)) {
                                $attribute = $this->setUserAttribute($userInput->attribute_id, $this->user->id, $mesgText);
                                
                            }
                        }
                        
                        if(!$breakUserInputProcess) {
                            $this->inputOrder = $userInput->order;
            
                            $resContent = $this->getResumeSection($userInput->content->section_id, ($userInput->content->order-1));
                            if($resContent['status']) {
                                $response = $resContent;
                            }

                            $lastRecord->input_complete = true;
                            $lastRecord->save();
                        }
                    }
                }
            }
        } else {
            $response = $this->getWelcome();
        }
        
        if(empty($response)) {
            // if response is empty validate user mesg text with ai rule
            if(!empty($mesgText)) {
                $response = $this->aiValidation($mesgText);
            }
            // if there is no ai rule get default response
            if(empty($response)) {
                $response = $this->getDefault();
            }
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
        $searchKeyword = KeywordFilter::where("value", $keyword)
        ->whereHas('rule', function($query) {
            $query->whereHas('group', function($query) {
                $query->where('project_id', $this->projectid);
            });
        })
        ->first();
        // $searchKeyword = KeywordFilter::selectRaw("*, MATCH (value) AGAINST ('$keyword') as score")
        // ->whereRaw("MATCH (value) AGAINST ('$keyword')")
        // ->whereHas('rule', function($query) {
        //     $query->whereHas('group', function($query) {
        //         $query->where('project_id', $this->projectid);
        //     });
        // })
        // ->orderBy('score', 'desc')
        // ->first();

        FacebookRequestLogs::create([
            'data' => json_encode([
                'section' => 'ai',
                'data' => $searchKeyword
            ])
        ]);

        if(empty($searchKeyword)) {
            return '';
        }

        $result = null;

        $response = KeywordFilterResponse::where(function($query) {
            $query->where('reply_text', '!=', '');
            $query->orWhereNotNull('chat_block_section_id');
        })->where('keywords_filters_group_rule_id', $searchKeyword->keywords_filters_group_rule_id)->inRandomOrder()->first();

        if($response->type===1) {
            $result = [
                'status' => true,
                'type' => '',
                'data' => [
                    [
                        'status' => true,
                        'mesg' => '',
                        'type' => 1,
                        'content_id' => null,
                        'data' => [
                            'text' => $response->reply_text
                        ]
                    ]
                ]
            ];
        } else {
            $result = $this->getSection($response->chat_block_section_id);
        }

        return $result;
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
        $index = -1;
        foreach($contents as $content) {
            $index++;
            $parsed = [];

            switch ($content->type) {
                case 1:
                    $parsed = $this->parseText($content);
                    break;
                
                case 2:
                    $parsed = $this->parseTyping($content);
                    break;
                
                case 3:
                    if($index>0) {
                        $parsed = $this->parseQuickReply($content);
                        $break = !$parsed['status'] || in_array($contents[$index-1]['type'], [1,5,6,7]);
                    } else {
                        $parsed['status'] = false;
                    }
                    break;
                
                case 4:
                    $parsed = $this->parseUserInput($content);
                    $break = $parsed['status'];
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

        if(!empty($buttons)) {
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

        if(count($content->galleryList)>=2) {
            foreach($content->galleryList as $list) {
                if(
                    empty($list->title) || 
                    (
                        empty($list->sub) &&
                        (empty($list->image) || (!empty($list->image) && !Storage::disk('public')->exists('images/list/'.$list->image))) &&
                        empty($list->buttons)
                    )
                )  continue;
    
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
            if(
                empty($list->title) || 
                (
                    empty($list->sub) &&
                    (empty($list->image) || (!empty($list->image) && !Storage::disk('public')->exists('images/gallery/'.$list->image))) &&
                    count($list->buttons)===0
                )
            ) continue;

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
            'type' => 6
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
                if(!empty($button->url)) {
                    $res['data'] = [
                        'type' => 'web_url',
                        'url' => $button->url,
                        'title' => $button->title,
                    ];
                } else {
                    $res['status'] = false;
                    $res['mesg'] = 'URL value is empty!';
                }
                break;

            case(2):
                if(!empty($button->phone)) {
                    $res['data'] = [
                        'type' => 'phone_number',
                        'payload' => $button->phone,
                        'title' => $button->title
                    ];
                } else {
                    $res['status'] = false;
                    $res['mesg'] = 'Phone number value is empty!';
                }
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
                        'fb_user_id' => $userid,
                        'seen_at' => date("Y-m-d H:i:s")
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
            
            DB::beginTransaction();

            try {
                $fbc = new FacebookController($this->projectPage->token);
                $profile = $fbc->getMessengerProfile($this->user->fb_user_id);
                if($profile['status']) {
                    $this->user->first_name = $profile['data']['first_name'];
                    $this->user->last_name = $profile['data']['last_name'];
                    $this->user->gender = isset($profile['data']['gender']) ? $profile['data']['gender'] : null;
                    $this->user->locale = isset($profile['data']['locale']) ? $profile['data']['locale'] : null;
                    $this->user->timezone = isset($profile['data']['timezone']) ? $profile['data']['timezone'] : null;
                    $this->user->image = isset($profile['data']['profile_pic']) ? $profile['data']['profile_pic'] : null;
                    $this->user->save();
                }
            } catch (\Exception $e) {
                DB::rollback();
            }

            DB::commit();
        }
        
        return $res;
    }
}

// @codeCoverageIgnoreEnd
