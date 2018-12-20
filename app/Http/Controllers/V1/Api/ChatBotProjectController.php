<?php

namespace App\Http\Controllers\V1\Api;

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

class ChatBotProjectController extends Controller
{
    protected $projectid = null;

    public function __construct($projectid)
    {
        $this->projectid = $projectid;
    }

    public function process($input=null, $payload=true)
    {
        return $this->getDefault();
    }

    public function aiValidation($keyword='')
    {

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
            'data' => $this->contentParser($section->contents)
        ];
    }
    
    public function getGreeting()
    {
        
    }

    public function contentParser($contents)
    {
        $res = [];

        foreach($contents as $content) {
            switch ($content->type) {
                case 1:
                    $res[] = $this->parseText($content);
                    break;
                
                case 2:
                    $res[] = $this->parseTyping($content);
                    break;
                
                case 3:
                    // $res[] = $this->parseQuickReply($content);
                    break;
                
                case 4:

                    break;
                
                case 5:
                    $res[] = $this->parseList($content);
                    break;
                
                case 6:

                    break;
                
                case 7:

                    break;
            }
        }

        return $res;
    }

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

    public function parseQuickReply($content)
    {
        return [
            'status' => true,
            'mesg' => '',
            'type' => 3,
            'data' => [
                'text' => 'qc',
                'quick_replies' => [
                    [
                        'content_type' => 'text',
                        'title' => 'Hello',
                        'payload' => 'test payload'
                    ],
                    [
                        'content_type' => 'text',
                        'title' => '5',
                        'payload' => 'test payload'
                    ],
                    [
                        'content_type' => 'text',
                        'title' => '4',
                        'payload' => 'test payload'
                    ],
                    [
                        'content_type' => 'text',
                        'title' => '3',
                        'payload' => 'test payload'
                    ],
                    [
                        'content_type' => 'text',
                        'title' => '2',
                        'payload' => 'test payload'
                    ],
                    [
                        'content_type' => 'text',
                        'title' => '1',
                        'payload' => 'test payload'
                    ],
                ]
            ]
        ];
    }

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

    public function parseGallery()
    {

    }

    public function parseImage()
    {

    }

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
                    'payload' => 'payload'
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
}
