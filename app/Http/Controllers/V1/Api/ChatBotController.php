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
        $res1 = [];
        foreach($sections as $section) {
            $contents = app('App\Http\Controllers\V1\Api\ChatContent\GetController')->loadContentForValidation($section->id);
            
            $parsed = [
                'id' => $section->id,
                'title' => $section->title,
                'isValid' => true,
                'contents' => null
            ];

            foreach($contents as $content) {
                $break = false;
                $button = true;
                switch($content['type']) {
                    case(1):
                    $button = $this->validateButton($content['content']['button']);
                    if(empty($content['content']['text']) || !$button) {
                        $parsed['isValid'] = false;
                        $break = true;
                    }
                    break;

                    case(3):
                    foreach($content['content'] as $content) {
                        if(empty($content['title'])) {
                            $parsed['isValid'] = false;
                            $break = true;
                        }
                    }
                    break;
                    
                    case(4):
                    foreach($content['content'] as $content) {
                        if(empty($content['question']) || empty($content['attribute']['title'])) {
                            $parsed['isValid'] = false;
                            $break = true;
                        }
                    }
                    break;

                    case(6):
                    foreach($content['content'] as $content) {
                        if(empty($content['title']) || (empty($content['sub']) || empty($content['url']))) {
                            $parsed['isValid'] = false;
                            $break = true;
                        }
                    }
                    $parsed['contents'] = $content;
                    break;

                    // if(empty($content['content']['question'])) {
                    //     $parsed['isValid'] = false;
                    //     $break = true;
                    // }
                    // // $parsed['contents'] = $content['content']->$parsed['content']['question'];
                    //     break;
                       
                        // if($btn = false) {
                        //     $parsed['isValid'] = false;
                        //     $break = true;
                        // }
                        // break;
        
                        // $parsed['isValid'] = $this->validateButton($content['content']['button']);
        
                        // if($parsed['isValid'] = false) break;

                }
                
                
                
                if($break) break;
            }
            
            
            // if($section->contents[0]->type == 1 && $section->contents[0]->) {
            //     $parsed['isValid'] = false;
            // }

            $res[] = $parsed;
        }

        return $res;
    }

    private function validateButton($buttons) {
        
        foreach($buttons as $button) {
            if(empty($button['title'])) {
                return false;
            }
        }

        return true;
    }

    private function parseContent($contents) 
    {
        $res = [];
        foreach($contents as $content) {
            $parsed = [
                'id' => $content->id,
                'content' => $content->content,
                'galleryList' => $this->parseGallery($content->galleryList)
            ];

            $res[] = $parsed;
        }

        return $res;
    }

    private function parseGallery($galleryList) 
    {
        $res = [
            'count' => 0,
            'isError' => false
        ];

        foreach($galleryList as $gallery) {
            $parsed = [
                'id' => $gallery->id,
                'title' => $gallery->title,
                'sub' => $gallery->sub
            ];
            $res['count']++;
        } 

        if($res['count'] < 3) {
            $res['isError'] = true;
        }

        return $res;
    }
    
    public function deleteBlock(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->attributes->get('chatBlock')->delete();
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

    public function updateBlock(Request $request)
    {
        if($request->attributes->get('chatBlock')->is_lock) { 
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'This block cannot be delete!'
            ], 422);
        }

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

        DB::beginTransaction();

        try {
            $request->attributes->get('chatBlock')->title = $input['title'];
            $request->attributes->get('chatBlock')->save();
        }
        // @codeCoverageIgnoreStart
        catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update block title!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }
        // @codeCoverageIgnoreEnd

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ]);
    }

    public function updateBlockOrder(Request $request)
    {
        $blockid = $request->input('blocks');

        if(!is_null($blockid) && !empty($blockid)) {
            DB::beginTransaction();

            try {
                $order = 1;
                foreach($blockid as $id) {
                    $block = ChatBlock::find($id);
                    if(!empty($block)) {
                        $block->order = $order++;
                        $block->save();
                    }
                }
            } catch(\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Failed to update block order!',
                    'debugMesg' => $e->getMessage()
                ], 422);
            }

            DB::commit();
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Order updated!'
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
        ], 201);
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
        }
        // @codeCoverageIgnoreStart
        catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update block title!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }
        // @codeCoverageIgnoreEnd

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ]);
    }

    public function updateSectionOrder(Request $request)
    {
        $sectionid = $request->input('sections');

        if(!is_null($sectionid) && !empty($sectionid)) {
            DB::beginTransaction();

            try {
                $order = 1;
                foreach($sectionid as $id) {
                    $section = ChatBlockSection::find($id);
                    if(!empty($section)) {
                        $section->order = $order++;
                        $section->save();
                    }
                }
            } catch(\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Failed to update section order!',
                    'debugMesg' => $e->getMessage()
                ], 422);
            }

            DB::commit();
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Order updated!'
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
        }
        // @codeCoverageIgnoreStart
        catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete a block!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }
        // @codeCoverageIgnoreEnd

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
        $list->where('project_id', $request->attributes->get('project')->id);
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
