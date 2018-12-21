<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Storage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

class ChatBotProjectController extends Controller
{
    protected $projectid;
    protected $projectPage;
    protected $user;

    public function __construct($projectid)
    {
        $this->projectid = $projectid;
        $this->projectPage = ProjectPage::where('project_id', $projectid)->first();
    }

    // process input from messenger
    public function process($input=null, $payload=false)
    {
        $userid = $input['entry'][0]['messaging'][0]['sender']['id']!==$this->projectPage->page_id ? $input['entry'][0]['messaging'][0]['sender']['id']: $input['entry'][0]['messaging'][0]['recipient']['id'];

        $recordUser = $this->recordChatUser($userid);
    
        if(!$recordUser['status']) {
            return $recordUser;
        }
        
        DB::beginTransaction();

        $log;
        $mesgText = isset($input['entry'][0]['messaging'][0]['message']['text']) ? $input['entry'][0]['messaging'][0]['message']['text'] : '';
        $postback = '';
        $response = [];

        if($payload) {
            $mesgText = (String) $input['entry'][0]['messaging'][0]['postback']['title'];
            $postback = $input['entry'][0]['messaging'][0]['postback']['payload'];
            $py = explode('-', $input['entry'][0]['messaging'][0]['postback']['payload']);
            $button = ChatButton::with('blocks')->find($py[1]);

            if(!empty($button)) {

                if(!empty($button->blocks) && !is_null($button->blocks[0]->section_id)) {

                    $resContent = $this->getSection($button->blocks[0]->section_id);

                    if($resContent['status']) {
                        $response = $resContent;
                    }

                }

            }

        } else if(isset($input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'])) {

            $postback = $input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'];
            $qr = explode('-', $input['entry'][0]['messaging'][0]['message']['quick_reply']['payload']);
            $content = ChatBlockSectionContent::with('section')->find($qr[1]);

            if(!empty($content)) {
                $chatQuickReply = ChatQuickReply::with('blocks')->find($qr[2]);
                if(!empty($chatQuickReply)) {
                    if(!empty($chatQuickReply->blocks) && !is_null($chatQuickReply->blocks[0]->section_id)) {
                        $resContent = $this->getSection($chatQuickReply->blocks[0]->section_id);
                        if($resContent['status']) {
                            $response = $resContent;
                        }
                    }
                }
            }
            
        }

        try {
            $log = ProjectPageUserChat::create([
                'content_id' => null,
                'post_back' => $postback,
                'from_platform' => false,
                'mesg' => $mesgText,
                'mesg_id' => isset($input['entry'][0]['messaging'][0]['message']['mid']) ? $input['entry'][0]['messaging'][0]['message']['mid'] : '',
                'is_send' => false,
            ]);
        } catch(\Exception $e) {
            DB::rollback();
            return [
                'status' => false,
                'type' => 'um-record',
                'mesg' => 'Failed to record mesg!'
            ];
        }

        DB::commit();

        if(empty($response)) {
            $response = $this->getDefault();
        }
        return $response;
    }

    // public function setAttribute()
    // {

    // }

    // ai validation
    public function aiValidation($keyword='')
    {
        
    }

    public function getSection($section)
    {
        $section = ChatBlockSection::with([
            'contents',
            'contents.galleryList',
            'contents.galleryList.buttons',
            'contents.buttons',
            'contents.quickReply',
            'contents.userInput'
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

    public function getDefault()
    {
        $block = ChatBlock::where('project_id', $this->projectid)->where('is_lock', true)->first();
        $section = ChatBlockSection::with([
            'contents',
            'contents.galleryList',
            'contents.galleryList.buttons',
            'contents.buttons',
            'contents.quickReply',
            'contents.userInput'
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
            switch ($content->type) {
                case 1:
                    $res[] = $this->parseText($content);
                    break;
                
                case 2:
                    $res[] = $this->parseTyping($content);
                    break;
                
                case 3:
                    $break = true;
                    $res[] = $this->parseQuickReply($content);
                    break;
                
                case 4:

                    break;
                
                case 5:
                    $res[] = $this->parseList($content);
                    break;
                
                case 6:
                    $res[] = $this->parseGallery($content);
                    break;
                
                case 7:
                    $res[] = $this->parseImage($content);
                    break;
            }

            if($break) break;
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
    public function recordChatUser($userid)
    {
        $res = [
            'status' => true,
            'type' => '',
            'mesg' => 'success'
        ];

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

        return $res;
    }
}
