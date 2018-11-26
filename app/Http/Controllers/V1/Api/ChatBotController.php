<?php

namespace App\Http\Controllers\V1\Api;

use Auth;
use File;
// use Image;
use Storage;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ChatBlock;
use App\Models\ChatBlockSection;

class ChatBotController extends Controller
{
    public function createBlock(Request $request)
    {
        $block = ChatBlock::create([
            'title' => 'Untitle block',
            'is_lock' => false,
            'type' => 1
        ]);

        return response()->json([
            'status' => true,
            'code' => 201,
            'data' => [
                'id' => $block->id,
                'title' => $block->title,
                'lock' => $block->is_lock
            ]
        ]);
    }

    public function getBlocks(Request $request)
    {
        $blocks = ChatBlock::query();
        $blocks->with(['sections']);
        $blocks->orderBy('order', 'asc');
        $blocks = $blocks->get();

        $result = $this->parseBlock($blocks);

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $result
        ]);
    }

    private function parseBlock($blocks)
    {
        $res = [];
        
        foreach($blocks as $block) {
            $parsed = [
                'block' => [
                    'id' => $block->id,
                    'title' => $block->title,
                    'lock' => $block->type===0 || $block->is_lock ? true : false
                ],
                'sections' => $this->parseSection($block->sections),
            ];

            $res[] = $parsed;
        }
        
        return $res;
    }

    private function parseSection($sections)
    {
        $res = [];
        foreach($sections as $section) {
            $parsed = [
                'id' => $section->id,
                'title' => $section->title
            ];
            $res[] = $parsed;
        }

        return $res;
    }
}
