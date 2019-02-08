<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Validator;
use App\Models\SavedReply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SavedReplyController extends Controller
{

    public function getReply(Request $request) 
    {

        $replyList = SavedReply::query();

        if($request->input('keyword')) {
            $keyword = $request->input('keyword'); 

            $replyList->where('title', 'like', '%'.$keyword.'%');
            $replyList = $replyList->get();
        } else {
            $replyList->where('project_id', $request->attributes->get('project')->id);
            $replyList = $replyList->get();
        }

        $res = [];

        foreach($replyList as $reply) {
            $res[] = $this->formatReply($reply);
        }
        return response()->json([
            'status' => true,
            'code' => true,
            'data' => $res
        ]);   
    }

    public function createReply(Request $request) {
        $input = $request->only('title', 'message');

        $validator = Validator::make($input, [
            'title' => 'required',
            'message' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $reply = null;

        DB::beginTransaction();

        try {
            $reply = SavedReply::create([
                'title' => $input['title'],
                'message' => $input['message'],
                'project_id' => $request->attributes->get('project')->id,
                'created_by' => $request->attributes->get('project_user')->user_id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create reply!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'data' => $this->formatReply(SavedReply::with([
                'projectUser'
            ])->find($reply->id))
        ], 201);
    }

    public function formatReply(\App\Models\SavedReply $reply)
    {
        return [
            'id' => $reply->id,
            'title' => $reply->title,
            'message' => $reply->message,
            'time' => ''
        ];
    }
}