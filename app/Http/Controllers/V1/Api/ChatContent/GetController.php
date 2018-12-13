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
            }

            $res[] = $parsed;
        }

        return response()->json($res);
    }

    public function parseText($content)
    {
        return [
            'text' => $content->content,
            'button' => []
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

        $res = [
            'content' => [],
            'button' => null
        ];
        
        foreach($list as $l) {
            $res['content'][] = [
                'id' => $l->id,
                'image' => $l->image && Storage::disk('public')->exists('images/list/'.$l->image) ? Storage::disk('public')->url('images/list/'.$l->image) : '',
                'title' => $l->title,
                'sub' => $l->sub,
                'url' => $l->url,
                'content_id' => $content->id,
                'button' => null
            ];
        }

        return $res;
    }

    public function parseGallery($content)
    {
        $list = ChatGallery::where('content_id', $content->id)->get();

        $res = [];
        
        foreach($list as $l) {
            $res[] = [
                'id' => $l->id,
                'image' => $l->image && Storage::disk('public')->exists('images/gallery/'.$l->image) ? Storage::disk('public')->url('images/gallery/'.$l->image) : '',
                'title' => $l->title,
                'sub' => $l->sub,
                'url' => $l->url,
                'content_id' => $content->id,
                'button' => []
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
}
