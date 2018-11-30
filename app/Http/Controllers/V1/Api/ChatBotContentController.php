<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Auth;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ChatBlockSectionContent as CBSC;

class ChatBotContentController extends Controller
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
                'content' => null
            ];

            switch($parsed['type']) {
                case(1):
                    $parsed['content'] = $this->parseText($content);
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

    public function parseTyping()
    {

    }

    public function createContents(Request $request)
    {
        $input = $request->only('type');

        $content = null;

        DB::beginTransaction();

        try {
            switch((int) $input['type']) {
                case(1):
                    $content = $this->createText($request);
                    break;
            }
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $e->getMessage(),
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json($content, $content['code']);
    }

    public function createText(Request $request)
    {
        $create = CBSC::create([
            'section_id' => $request->attributes->get('chatBlockSection')->id,
            'order' => CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->count()+1,
            'type' => 1,
            'text' => '',
            'content' => 'Text Content',
            'image' => '',
            'duration' => 0
        ]);

        $res = [
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => (int) $create->id,
                'type' => (int) $create->type,
                'section_id' => (int) $request->attributes->get('chatBlockSection')->id,
                'block_id' => (int) $request->attributes->get('chatBlock')->id,
                'content' => [
                    'text' => $create->content,
                    'button' => []
                ]
            ]
        ];

        return $res;
    }

    public function createTyping()
    {

    }

    public function updateContent(Request $request)
    {
        $res = null;
        switch((int) $request->input('type')) {
            case(1):
                $res = $this->updateText($request, true);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Invalid type!'
                ], 422);
                break;
        }
        
        return response()->json($res, $res['code']);
    }

    public function updateText(Request $request, $return=false)
    {
        $input = $request->only('content');
        $validator = Validator::make($input, [
            'content' => 'required'
        ]);

        if($validator->fails()) {
            $res = [
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ];
            return $return ? $res : response()->json($res, $res['code']);
        }

        $content = CBSC::find($request->contentId);
        $content->content = $input['content'];
        
        DB::beginTransaction();

        try {
            $content->save();
        } catch (\Exception $e) {
            DB::rollback();
            $res = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update!',
                'debugMesg' => $e->getMessage()
            ];
            return $return ? $res : response()->json($res, $res['code']);
        }

        DB::commit();

        $res = [
            'status' => true,
            'code' => 200,
            'mesg' => 'Content updated.'
        ];

        return $return ? $res : response()->json($res, $res['code']);
    }

}
