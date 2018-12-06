<?php

namespace App\Http\Controllers\V1\Api;

use DB;
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
        $block = null;
        DB::beginTransaction();
        try {
            $block = ChatBlock::create([
                'title' => 'Untitle block',
                'is_lock' => false,
                'type' => 1
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create block!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

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
    
    public function deleteBlock(Request $request)
    {
        DB::beginTransaction();
        try {
            ChatBlock::findOrFail($request->blockId)->delete();
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete block!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();
        
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success delete'
        ]);
    }

    public function createSection(Request $request)
    {
        $section = null;

        DB::beginTransaction();
        
        try {
            $section = ChatBlockSection::create([
                'title' => 'New Section',
                'block_id' => $request->blockId,
                'order' => ChatBlockSection::where('block_id', $request->blockId)->count()+1
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create section!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'data' => [
                'id' => $section->id,
                'title' => $section->title,
            ]
        ]);
    }

    public function serachSection(Request $request)
    {
        $keyword = "";
        if(is_null($request->input('keyword'))==false && $request->input('keyword')) {
            $keyword = $request->input('keyword');
        }
        $list = ChatBlock::query();
        $list->with([
            'sections' => function($query) use ($keyword) {
                if($keyword) {
                    $query->where('title', 'like', '%'.$keyword.'%');
                }
            }
        ]);
        $list->whereHas('sections',  function($query) use ($keyword)  {
            if($keyword) {
                $query->where('title', 'like', '%'.$keyword.'%');
            }
        });
        $list = $list->get();

        $res = [];

        foreach($list as $l) {
            $parsed = [
                'id' => $l->id,
                'title' => $l->title,
                'contents' => []
            ];

            foreach($l->sections as $sec) {
                $parsed['contents'][] = [
                    'id' => $sec->id,
                    'title' => $sec->title
                ];
            }

            $res[] = $parsed;
        }

        return response()->json([
            'status' => true,
            'code' => 422,
            'data' => $res
        ]);
    }
}
