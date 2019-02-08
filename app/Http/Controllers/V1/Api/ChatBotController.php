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
                'project_id' => $request->attributes->get('project')->id,
                'is_lock' => false,
                'type' => 1
            ]);
        }
        // @codeCoverageIgnoreStart
        catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create block!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }
        // @codeCoverageIgnoreEnd

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'data' => [
                'id' => $block->id,
                'project' => md5($block->project_id),
                'title' => $block->title,
                'lock' => $block->is_lock
            ]
        ], 201);
    }

    public function getBlocks(Request $request)
    {
        $blocks = ChatBlock::query();
        $blocks->with(['sections' => function($query) {
            $query->orderBy('order', 'asc');
        }]);
        $blocks->where('project_id', $request->attributes->get('project')->id);
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
                    'project' => md5($block->project_id),
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
        }
        // @codeCoverageIgnoreStart
        catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete block!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }
        // @codeCoverageIgnoreEnd

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
        }
        // @codeCoverageIgnoreStart
        catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create section!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }
        // @codeCoverageIgnoreEnd

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

    public function updateSection(Request $request)
    {
        $input = $request->only('title');

        $validator = Validator::make($input, [
            'title' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $section = ChatBlockSection::find($request->attributes->get('chatBlockSection')->id);

        DB::beginTransaction();

        try {
            $section->title = $input['title'];
            $section->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update block title!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ]);
    }

    public function deleteSection(Request $request)
    {
        if($request->attributes->get('chatBlock')->is_lock==1) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Cannot delete section that is locked by parent!'
            ], 422);
        }

        $section = ChatBlockSection::find($request->attributes->get('chatBlockSection')->id);

        DB::beginTransaction();

        try {
            $section->delete();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete a block!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ]);
    }

    public function searchSection(Request $request)
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
        $list->where(DB::raw('md5(project_id)'), $request->projectId);
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
            'code' => 200,
            'data' => $res
        ]);
    }
}
