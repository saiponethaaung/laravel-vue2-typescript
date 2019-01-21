<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Segment;
use App\Models\ChatAttribute;
use App\Models\SegmentFilter;

class SegmentController extends Controller
{
    public function getList(Request $request)
    {
        $segments = Segment::where('project_id', $request->attributes->get('project')->id)->get();

        $res = [];
        
        foreach($segments as $segment) {
            // $filters = [];

            // foreach($segment->filters as $filter) {
            //     $filters[] = [
            //         'id' => (int) $filter->id,
            //         'option' => (int) $filter->filter_type,
            //         'type' => (int) $filter->condition,
            //         'name' => is_null($filter->attribute) ? (string) '' : (string) $filter->attribute->attribute,
            //         'value' => is_null($filter->chat_attribute_value) ? (string) '' : (string) $filter->chat_filter_value,
            //         'condi' => (int) $filter->chain_condition,
            //         'systemAttribute' => is_null($filter->system_attribute_type) ? (int) 1 : (int) $filter->system_attribute_type,
            //         'systemAttributeValue' => is_null($filter->system_attribute_value) ? (int) 1 : (int) $filter->system_attribute_value,
            //         'userAttribute' => is_null($filter->user_attribute_type) ? (int) 1 : (int) $filter->user_attribute_type,
            //         'userAttributeValue' => is_null($filter->user_attribute_value) ? (int) 1 : (int) $filter->user_attribute_value,
            //     ];
            // }
            
            $res[] = [
                'id' => $segment->id,
                'name' => $segment->name,
                // 'filters' => $filters
            ];
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ]);
    }

    public function createSegment(Request $request)
    {
        $input = $request->only('name', 'filters');

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
            $segment = Segment::create([
                'name' => $input['name'],
                'project_id' => $request->attributes->get('project')->id
            ]);
            if(is_array($input['filters']) && !empty($input['filters'])) {
                foreach($input['filters'] as $filter) {
                    $attributeId = null;
                    $check = SegmentFilter::query();
                    $check->where('project_user_segments_id', $segment->id);
                    $check->where('filter_type', $filter['option']);

                    switch($filter['option']) {
                        case("1"):
                            if(!in_array($filter['userAttribute'], [1])) continue;
                            if(!in_array($filter['userAttributeValue'], [1,2])) continue;
                            $check->where('user_attribute_type', $filter['userAttribute']);
                            $check->where('user_attribute_value', $filter['userAttributeValue']);
                            break;
                            
                        case("2"):
                            if(empty($filter['name']) || empty($filter['value'])) continue;
                            $attributeId = $this->getChatAttribute($filter['name']);
                            $check->where('chat_attribute_id', $attributeId);
                            $check->where('chat_attribute_value', $filter['value']);
                            $check->where('condition', $filter['type']);
                            break;
                            
                        case("3"):
                            if(!in_array($filter['systemAttribute'], [1,2,3])) continue;
                            if(!in_array($filter['systemAttributeValue'], [1,2,3,4])) continue;
                            $check->where('system_attribute_type', $filter['systemAttribute']);
                            break;
                    }

                    $check = $check->count();

                    if($check>0) continue;

                    $newFilter = new SegmentFilter();
                    $newFilter->filter_type = $filter['option'];
                    $newFilter->condition = $filter['type'];
                    $newFilter->chain_condition = $filter['condi'];
                    
                    switch($filter['option']) {
                        case("1"):
                            $newFilter->user_attribute_type = $filter['userAttribute'];
                            $newFilter->user_attribute_value = $filter['userAttributeValue'];
                            break;

                        case("2"):
                            $attributeId = $this->getChatAttribute($filter['name']);
                            $newFilter->chat_attribute_id = $attributeId;
                            $newFilter->chat_attribute_value = $filter['value'];
                            break;
                            
                        case("3"):
                            $newFilter->system_attribute_type = $filter['systemAttribute'];
                            $newFilter->system_attribute_value = $filter['systemAttributeValue'];
                            break;
                    }
                    
                    $newFilter->project_user_segments_id = $segment->id;
                    $newFilter->save();
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create segment!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'mesg' => 'success'
        ], 201);
    }

    public function getChatAttribute($name)
    {
        $chatAttribute = ChatAttribute::where(
            DB::raw('attribute COLLATE utf8mb4_bin'), 'LIKE', $name.'%'
        )->first();

        if(empty($chatAttribute)) {
            $chatAttribute = ChatAttribute::create([
                'attribute' => $name
            ]);
        }

        return $chatAttribute->id;
    }
}
