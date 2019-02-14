<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\KeywordFilterGroup;
use App\Models\KeywordFilterGroupRule;

class AIController extends Controller
{
    public function getList(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => KeywordFilterGroup::select('id', 'name')
                        ->where('project_id', $request->attributes->get('project')->id)
                        ->get()
        ]);
    }

    public function create(Request $request)
    {
        $group = null;

        DB::beginTransaction();
        
        try {
            $group = KeywordFilterGroup::create([
                'name' => 'New Group',
                'project_id' => $request->attributes->get('project')->id,
                'created_by' => $request->attributes->get('project_user')->id,
                'updated_by' => $request->attributes->get('project_user')->id
            ]);
        }
        // @codeCoverageIgnoreStart
        catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'debugMesg' => $e->getMessage(),
                'mesg' => 'Failed to create new group!'
            ], 422);
        }
        // @codeCoverageIgnoreEnd

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'mesg' => 'success',
            'data' => [
                'id' => $group->id,
                'name' => $group->name
            ]
        ], 201);
    }

    public function updateGroupName(Request $request)
    {
        $input = $request->only('name');

        $validator = Validator::make($input, [
            'name' => 'required'
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
            $request->attributes->get('project_ai_group')->name = $input['name'];
            $request->attributes->get('project_ai_group')->updated_by = $request->attributes->get('project_user')->id;
            $request->attributes->get('project_ai_group')->save();
        }
        // @codeCoverageIgnoreStart
        catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'debugMesg' => $e->getMessage(),
                'mesg' => 'Failed to update group name!'
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

    public function deleteGroup(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->attributes->get('project_ai_group')->delete();
        }
        // @codeCoverageIgnoreStart
        catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'debugMesg' => $e->getMessage(),
                'mesg' => 'Failed to delete group!'
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

    public function getRules(Request $request)
    {
        $rules = KeywordFilterGroupRule::with('filters', 'response', 'response.section')
                    ->where('keywords_filters_group_id', $request->attributes->get('project_ai_group')->id)
                    ->get();

        $res = [];

        foreach($rules as $rule) {
            $parsed = [
                'id' => $rule->id,
                'filters' => [],
                'response' => []
            ];

            foreach($rule->filters as $filter) {
                $parsed['filters'][] = [
                    'id' => $filter->id,
                    'keyword' => $filter->value
                ];
            }

            foreach($rule->response as $response) {
                $parsed['response'][] = [
                    'id' => $response->id,
                    'type' => $response->type,
                    'content' => $response->reply_text,
                    'segmentId' => is_null($response->chat_block_section_id) ? 0 : $response->chat_block_section_id,
                    'segmentName' => is_null($response->chat_block_section_id) ? 0 : $response->section->title,
                ];
            }

            $res[] = $parsed;
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'data' => $res
        ]);
    }

    public function createRule(Request $request)
    {
        $rule = null;

        DB::beginTransaction();

        try {
            $rule = KeywordFilterGroupRule::create([
                'keywords_filters_group_id' => $request->attributes->get('project_ai_group')->id,
                'created_by' => $request->attributes->get('project_user')->id,
                'updated_by' => $request->attributes->get('project_user')->id
            ]);
        }
        // @codeCoverageIgnoreStart
        catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'debugMesg' => $e->getMessage(),
                'mesg' => 'Failed to create new rule!'
            ], 422);
        }
        // @codeCoverageIgnoreEnd

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'data' => [
                'id' => $rule->id,
                'filters' => [],
                'response' => []
            ]
        ]);
    }
}
