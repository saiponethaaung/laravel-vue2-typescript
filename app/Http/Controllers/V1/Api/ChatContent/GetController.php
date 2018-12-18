<?php

namespace App\Http\Controllers\V1\Api\ChatContent;

use DB;
use Auth;
use File;
use Image;
use Storage;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ChatBlockSectionContent as CBSC;
use App\Models\ChatGallery;
use App\Models\ChatAttribute;
use App\Models\ChatQuickReply;
use App\Models\ChatUserInput;
use App\Models\ChatButton;

class GetController extends Controller
{
    public function getContents(Request $request)
    {
        $contents = CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->orderBy('id', 'asc')->get();

        $res = [];

        foreach($contents as $content) {
            $parsed = [
                'id' => (int) $content->id,
                'type' => (int) $content->type,
                'section_id' => (int) $content->section->id,
                'block_id' => (int) $content->section->block_id,
                'project' => md5($request->attributes->get('project')->id),
                'content' => null
            ];

            switch($parsed['type']) {
                case(1):
                    $parsed['content'] = $this->parseText($content);
                    break;

                case(2):
                    $parsed['content'] = $this->parseTyping($content);
                    break;

                case(3):
                    $parsed['content'] = $this->parseQuickReply($content);
                    break;

                case(4):
                    $parsed['content'] = $this->parseUserInput($content);
                    break;

                case(5):
                    $parsed['content'] = $this->parseList($content);
                    break;

                case(6):
                    $parsed['content'] = $this->parseGallery($content);
                    break;

                case(7):
                    $parsed['content'] = $this->parseImage($content);
                    break;
            }

            $res[] = $parsed;
        }

        return response()->json($res);
    }

    public function parseText($content)
    {
        $button = ChatButton::with('blocks', 'blocks.value')->where('content_id', $content->id)->get();

        $buttonList = [];

        foreach($button as $btn) {
            $blocks = [];

            foreach($btn->blocks as $block) {
                $blocks[] = [
                    'id' => $block->value->id,
                    'title' => $block->value->title
                ];
            }

            $buttonList[] = [
                'id' => $btn->id,
                'type' => $btn->action_type,
                'title' => $btn->title,
                'block' => $blocks,
                'url' => $btn->url,
                'phone' => [
                    'countryCode' => 95,
                    'number' => $btn->phone
                ]
            ];
        }

        return [
            'text' => $content->content,
            'button' => $buttonList
        ];
    }

    public function parseTyping($content)
    {
        return [
            'duration' => $content->duration,
        ];
    }

    public function parseList($content)
    {
        $list = ChatGallery::where('content_id', $content->id)->get();

        $listButton = null;
        $button = ChatButton::with('blocks', 'blocks.value')->where('content_id', $content->id)->first();

        if(!empty($button)) {
            $blocks = [];

            foreach($button->blocks as $block) {
                $blocks[] = [
                    'id' => $block->value->id,
                    'title' => $block->value->title
                ];
            }

            $listButton = [
                'id' => $button->id,
                'type' => $button->action_type,
                'title' => $button->title,
                'block' => $blocks,
                'url' => $button->url,
                'phone' => [
                    'countryCode' => 95,
                    'number' => $button->phone
                ]
            ];
        }

        $res = [
            'content' => [],
            'button' => $listButton
        ];
        
        foreach($list as $l) {

            $listItemButton = null;
            $button = ChatButton::with('blocks', 'blocks.value')->where('gallery_id', $l->id)->first();

            if(!empty($button)) {
                $blocks = [];

                foreach($button->blocks as $block) {
                    $blocks[] = [
                        'id' => $block->value->id,
                        'title' => $block->value->title
                    ];
                }

                $listItemButton = [
                    'id' => $button->id,
                    'type' => $button->action_type,
                    'title' => $button->title,
                    'block' => $blocks,
                    'url' => $button->url,
                    'phone' => [
                        'countryCode' => 95,
                        'number' => $button->phone
                    ]
                ];
            }
            
            $res['content'][] = [
                'id' => $l->id,
                'image' => $l->image && Storage::disk('public')->exists('images/list/'.$l->image) ? Storage::disk('public')->url('images/list/'.$l->image) : '',
                'title' => $l->title,
                'sub' => $l->sub,
                'url' => $l->url,
                'content_id' => $content->id,
                'button' => $listItemButton
            ];
        }

        return $res;
    }

    public function parseGallery($content)
    {
        $list = ChatGallery::where('content_id', $content->id)->get();

        $res = [];
        
        foreach($list as $l) {

            $button = ChatButton::with('blocks', 'blocks.value')->where('gallery_id', $l->id)->get();

            $buttonList = [];

            foreach($button as $btn) {
                $blocks = [];

                foreach($btn->blocks as $block) {
                    $blocks[] = [
                        'id' => $block->value->id,
                        'title' => $block->value->title
                    ];
                }

                $buttonList[] = [
                    'id' => $btn->id,
                    'type' => $btn->action_type,
                    'title' => $btn->title,
                    'block' => $blocks,
                    'url' => $btn->url,
                    'phone' => [
                        'countryCode' => 95,
                        'number' => $btn->phone
                    ]
                ];
            }
            
            $res[] = [
                'id' => $l->id,
                'image' => $l->image && Storage::disk('public')->exists('images/gallery/'.$l->image) ? Storage::disk('public')->url('images/gallery/'.$l->image) : '',
                'title' => $l->title,
                'sub' => $l->sub,
                'url' => $l->url,
                'content_id' => $content->id,
                'button' => $buttonList
            ];
        }

        return $res;
    }

    public function parseQuickReply($content)
    {
        $list = ChatQuickReply::with([
            'attribute',
            'blocks',
            'blocks.value'
        ])->where('content_id', $content->id)->get();
        $res = [];

        foreach($list as $l) {
            $blocks = [];

            foreach($l->blocks as $b) {
                $blocks[] = [
                    'id' => $b->value->id,
                    'title' => $b->value->title
                ];
            }

            $res[] = [
                'id' => $l->id,
                'title' => $l->title,
                'attribute' => [
                    'id' => $l->attribute ? $l->attribute->id : 0,
                    'title' => $l->attribute ? $l->attribute->attribute : '',
                    'value' => $l->value
                ],
                'content_id' => $content->id,
                'block' => $blocks
            ];
        }

        return $res;
    }

    public function parseUserInput($content)
    {
        $list = ChatUserInput::with([
            'attribute'
        ])->where('content_id', $content->id)->get();
        $res = [];

        foreach($list as $l) {
            $res[] = [
                'id' => $l->id,
                'question' => (String) $l->question,
                'attribute' => [
                    'id' => $l->attribute ? $l->attribute->id : 0,
                    'title' => $l->attribute ? $l->attribute->attribute : '',
                ],
                'content_id' => $content->id,
                'validation' => (int) $l->validation
            ];
        }

        return $res;
    }

    public function parseImage($content)
    {
        return [
            'image' => $content->image && Storage::disk('public')->exists('images/photos/'.$content->image) ? Storage::disk('public')->url('images/photos/'.$content->image) : ''
        ];
    }
}
