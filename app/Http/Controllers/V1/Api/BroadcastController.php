<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Broadcast;
use App\Models\BroadcastWeekday;
use App\Models\BroadcastFilters;
use App\Models\BroadcastTriggerAttribute;
use App\Models\ProjectMessageTag;
use App\Models\ChatBlockSection;
use App\Models\ChatAttribute;

use App\Jobs\Facebook\BroadcastJob;

class BroadcastController extends Controller
{
    public function getSendNow(Request $request) {
        $sendNow = Broadcast::where('broadcast_type', 1)->where('complete', 0)->first();

        if(empty($sendNow)) {
            $createSendNow = $this->createSendNow($request);
            
            if(!$createSendNow['status']) {
                return response()->json($createSendNow, $createSendNow['code']);
            }

            $sendNow = $createSendNow['data'];
        }
        
        $res = [];
        $res['id'] = $sendNow->id;
        $res['status'] = $sendNow->status===1 ? true : false;
        $res['tag'] = $sendNow->project_message_tag_id;
        $res['project'] = md5($sendNow->project_id);
        $res['type'] = $sendNow->broadcast_type;
        $res['section'] = [
            'id' => $sendNow->chatBlockSection->id,
            'broadcast' => $sendNow->id
        ];

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ], 200);
    }

    public function createSendNow(Request $request)
    {
        $broadcast = new Broadcast();
        $tag = ProjectMessageTag::where('tag_format', 'NON_​PROMOTIONAL_​SUBSCRIPTION')->first();

        if(empty($tag)) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => 'Message tag not found!'
            ];
        }

        $res = null;
        DB::beginTransaction();

        try {
            $broadcast->project_id = $request->attributes->get('project')->id;
            $broadcast->project_message_tag_id = $tag->id;
            $broadcast->broadcast_type = 1;
            $broadcast->interval_type = 1;
            $broadcast->status = false;
            $broadcast->complete = false;
            $broadcast->save();

            $broadcast->ChatBlockSection = ChatBlockSection::create([
                'broadcast_id' => $broadcast->id,
                'title' => '',
                'type' => 3,
                'order' => 1
            ]);

            $this->createSingleFilter($broadcast->id);
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create new broadcast!',
                'debugMesg' => $e->getMessage()
            ];
        }

        DB::commit();

        return [
            'status' => true,
            'data' => $broadcast
        ];
    }

    public function create(Request $request)
    {
        $section = $request->input('section');

        if(is_null($section) || !in_array($section, ['schedule', 'trigger'])) {
            return response()->josn([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invaid section!'
            ], 422);
        }

        $broadcast = new Broadcast();
        $tag = ProjectMessageTag::where('tag_format', 'NON_​PROMOTIONAL_​SUBSCRIPTION')->first();

        if(empty($tag)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Message tag not found!'
            ], 422);
        }

        $res = null;
        DB::beginTransaction();

        try {
            $broadcast->project_id = $request->attributes->get('project')->id;
            $broadcast->project_message_tag_id = $tag->id;
            $broadcast->broadcast_type = $section==='trigger' ? 2 : 3;
            
            if($section==='schedule') {
                $broadcast->day = date("d");
                $broadcast->month = date("m");
                $broadcast->year = date("Y");
                $broadcast->time = date("Hi");
            }
            
            if($section==='trigger') {
                $broadcast->duration = 15;
                $broadcast->duration_type = 1;
                $broadcast->trigger_type = 1;
                $broadcast->time = date("Hi");
            }

            $broadcast->interval_type = 1;
            $broadcast->status = false;
            $broadcast->complete = false;
            $broadcast->save();

            ChatBlockSection::create([
                'broadcast_id' => $broadcast->id,
                'title' => '',
                'type' => 3,
                'order' => 1
            ]);

            if($section==='schedule') {
                for($i=0; $i<7; $i++) {
                    BroadcastWeekday::create([
                        'project_broadcast_id' => $broadcast->id,
                        'days' => $i+1,
                        'status' => true
                    ]);
                }

                $res = $this->buildScheduleList($broadcast);
            }
            
            if($section==='trigger') {
                BroadcastTriggerAttribute::create([
                    'project_broadcast_id' => $broadcast->id,
                    'chat_attribute_id' => null,
                    'condition' => 1,
                    'value' => ''
                ]);

                $res = $this->buildTriggerList($broadcast);
            }

            $this->createSingleFilter($broadcast->id);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create new broadcast!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'mesg' => 'sucess',
            'data' => $res
        ], 201);
    }

    public function getSchedule(Request $request)
    {
        $schedules = Broadcast::where('broadcast_type', 3)->get();
        
        $res = [];

        foreach($schedules as $schedule) {
            $res[] = $this->buildScheduleList($schedule);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ], 200);
    }

    public function buildScheduleList(\App\Models\Broadcast $schedule)
    {
        return [
            'id' => $schedule->id,
            'day' => is_null($schedule->day) ? 0 : $schedule->day,
            'month' => is_null($schedule->month) ? 0 : $schedule->month,
            'year' => is_null($schedule->year) ? 0 : $schedule->year,
            'time' => is_null($schedule->time) ? 0 : $schedule->time,
            'interval_type' => is_null($schedule->interval_type) ? 1 : $schedule->interval_type
        ];
    }
    
    public function getTrigger(Request $request)
    {
        $triggers = Broadcast::where('broadcast_type', 2)->get();
        
        $res = [];

        foreach($triggers as $trigger) {
            $res[] = $this->buildTriggerList($trigger);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ], 200);
    }

    public function buildTriggerList(\App\Models\Broadcast $trigger)
    {
        return [
            'id' => $trigger->id,
            'duration' => $trigger->duration,
            'duration_type' => $trigger->duration_type,
            'trigger_type' => $trigger->trigger_type,
        ];
    }

    public function getScheduleDetail(Request $request)
    {
        $schedule = Broadcast::with([
            'weekday' => function($query) {
                $query->orderBy('days', 'ASC');
            },
            'chatBlockSection'
        ])->find($request->broadcastId);

        $hour = substr($schedule->time, 0, 2);
        $min = substr($schedule->time, 2, 4);
        $hour = (int) $hour>12 ? (int) $hour-12 : (int) $hour;
        $min = $min>59 ? '59' : $min;

        $res = [];
        $res['id'] = $schedule->id;
        $res['status'] = $schedule->status===1 ? true : false;
        $res['date'] = $schedule->year.'-'.($schedule->month<10 ? '0'.$schedule->month : $schedule->month).'-'.($schedule->day<10 ? '0'.$schedule->day : $schedule->day);
        $res['period'] = substr($schedule->time, 0, 2)>12 ? 2 : 1;
        $res['time'] = ($hour<10 ? '0'.$hour : $hour).':'.$min;
        $res['repeat'] = $schedule->interval_type;
        $res['tag'] = $schedule->project_message_tag_id;
        $res['project'] = md5($schedule->project_id);
        $res['type'] = $schedule->broadcast_type;
        $res['section'] = [
            'id' => $schedule->chatBlockSection->id,
            'broadcast' => $schedule->id
        ];

        foreach($schedule->weekday as $weekday) 
        {
            $res['days'][] = [
                'day' => $weekday->days,
                'status' => $weekday->status===1 ? true : false
            ];
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ], 200);
    }

    public function updateSchedule(Request $request)
    {
        $request->attributes->get('broadcast')->day = date("d", strtotime($request->input('date')));
        $request->attributes->get('broadcast')->month = date("m", strtotime($request->input('date')));
        $request->attributes->get('broadcast')->year = date("Y", strtotime($request->input('date')));
        $request->attributes->get('broadcast')->time = $request->input('time');
        $request->attributes->get('broadcast')->interval_type = $request->input('repeat');

        DB::beginTransaction();

        try {
            $request->attributes->get('broadcast')->save();

            foreach($request->input('day') as $day) {
                BroadcastWeekday::where('project_broadcast_id', $request->attributes->get('broadcast')->id)
                    ->where('days', $day['key'])
                    ->update([
                        'status' => $day['value']=='true' ? 1 : 0
                    ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update schedule!',
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

    public function getTriggerDetail(Request $request)
    {
        $trigger = Broadcast::with([
            'weekday' => function($query) {
                $query->orderBy('days', 'ASC');
            },
            'chatBlockSection',
            'attribute',
            'attribute.attrValue'
        ])->find($request->broadcastId);

        $hour = substr($trigger->time, 0, 2);
        $min = substr($trigger->time, 2, 4);
        $hour = (int) $hour>12 ? (int) $hour-12 : (int) $hour;
        $min = $min>59 ? '59' : $min;

        $res = [];
        $res['id'] = $trigger->id;
        $res['status'] = $trigger->status===1 ? true : false;
        $res['period'] = substr($trigger->time, 0, 2)>12 ? 2 : 1;
        $res['time'] = ($hour<10 ? '0'.$hour : $hour).':'.$min;
        $res['tag'] = $trigger->project_message_tag_id;
        $res['project'] = md5($trigger->project_id);
        $res['duration'] = $trigger->duration;
        $res['durationType'] = $trigger->duration_type;
        $res['triggerType'] = $trigger->trigger_type;
        $res['attributeName'] = is_null($trigger->attribute->attrValue) ? '' : $trigger->attribute->attrValue->attribute;
        $res['attributeValue'] = $trigger->attribute->value;
        $res['attributeCondi'] = $trigger->attribute->condition;
        $res['project'] = md5($trigger->project_id);
        $res['type'] = $trigger->broadcast_type;
        $res['section'] = [
            'id' => $trigger->chatBlockSection->id,
            'broadcast' => $trigger->id
        ];

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ], 200);
    }

    public function updateTrigger(Request $request)
    {
        $request->attributes->get('broadcast')->duration = $request->input('duration');
        $request->attributes->get('broadcast')->duration_type = $request->input('durationType');
        $request->attributes->get('broadcast')->trigger_type = $request->input('triggerType');
        $request->attributes->get('broadcast')->time = $request->input('time');

        DB::beginTransaction();

        try {
            $request->attributes->get('broadcast')->save();

            $attribute = BroadcastTriggerAttribute::where('project_broadcast_id', $request->attributes->get('broadcast')->id)
                            ->first();
            $attribute->chat_attribute_id = $this->getChatAttribute($request->input('attribute'));
            $attribute->condition = $request->input('condi');
            $attribute->value = (String) $request->input('value');
            $attribute->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update schedule!',
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

    public function updateMessageTag(Request $request)
    {
        $tag = ProjectMessageTag::find($request->input('tag'));

        if(empty($tag)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid message tag!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $request->attributes->get('broadcast')->project_message_tag_id = $tag->id;
            $request->attributes->get('broadcast')->save();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update message tag!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }

    public function updateStatus(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->attributes->get('broadcast')->status = $request->input('status')=='true' ? 1 : 0;
            $request->attributes->get('broadcast')->save();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update broadcast status!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }
    
    public function deleteBroadcast(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->attributes->get('broadcast')->delete();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete broadcast!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }
    
    public function publishBroadcast(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->attributes->get('broadcast')->complete = 1;
            $request->attributes->get('broadcast')->save();
            BroadcastJob::dispatch($request->attributes->get('broadcast')->id);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to publish broadcast!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
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

    public function createSingleFilter($id)
    {
        return BroadcastFilters::create([
            'project_broadcast_id' => $id,
            'filter_type' => 2,
            'conditin' => 1,
            'chain_conditin' => 1
        ]);
    }

    public function getFilters(Request $request)
    {
        $filters = BroadcastFilters::with([
            'attribute',
            'segment'
        ])->where('project_broadcast_id', $request->attributes->get('broadcast')->id)->get();

        $res = [];

        foreach($filters as $filter) {
            $res[] = [
                'filters' => [
                    'id' => (int) $filter->id,
                    'option' => (int) $filter->filter_type,
                    'type' => (int) $filter->condition,
                    'name' => is_null($filter->attribute) ? (string) '' : (string) $filter->attribute->attribute,
                    'value' => is_null($filter->chat_attribute_value) ? (string) '' : (string) $filter->chat_attribute_value,
                    'condi' => (int) $filter->chain_condition,
                    'systemAttribute' => is_null($filter->system_attribute_type) ? (int) 1 : (int) $filter->system_attribute_type,
                    'systemAttributeValue' => is_null($filter->system_attribute_value) ? (int) 1 : (int) $filter->system_attribute_value,
                    'userAttribute' => is_null($filter->user_attribute_type) ? (int) 1 : (int) $filter->user_attribute_type,
                    'userAttributeValue' => is_null($filter->user_attribute_value) ? (int) 1 : (int) $filter->user_attribute_value,
                ],
                'segment' => [
                    'id' => $filter->project_user_segment_id ? $filter->project_user_segment_id : -1,
                    'name' => is_null($filter->project_user_segment_id) ? '' : $filter->segment->name
                ]
            ];
        }

        return response()->json([
            'status' => false,
            'code' => 200,
            'data' => $res
        ]);
    }

    public function createFilters(Request $request)
    {
        $filter = '';

        DB::beginTransaction();

        try {
            $filter = $this->createSingleFilter($request->attributes->get('broadcast')->id);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create new attribute',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'data' => [
                'filters' => [
                    'id' => (int) $filter->id,
                    'option' => (int) $filter->filter_type,
                    'type' => (int) $filter->condition,
                    'name' => is_null($filter->attribute) ? (string) '' : (string) $filter->attribute->attribute,
                    'value' => is_null($filter->chat_attribute_value) ? (string) '' : (string) $filter->chat_attribute_value,
                    'condi' => (int) $filter->chain_condition,
                    'systemAttribute' => is_null($filter->system_attribute_type) ? (int) 1 : (int) $filter->system_attribute_type,
                    'systemAttributeValue' => is_null($filter->system_attribute_value) ? (int) 1 : (int) $filter->system_attribute_value,
                    'userAttribute' => is_null($filter->user_attribute_type) ? (int) 1 : (int) $filter->user_attribute_type,
                    'userAttributeValue' => is_null($filter->user_attribute_value) ? (int) 1 : (int) $filter->user_attribute_value,
                ],
                'segment' => [
                    'id' => 0,
                    'name' => ''
                ]
            ]
        ], 201);
    }
    
    public function deleteFilters(Request $request)
    {
        $filter = '';

        DB::beginTransaction();

        try {
            BroadcastFilters::where('id', $request->filterId)->delete();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create new attribute',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }

    public function updateFilters(Request $request)
    {
        $filter = BroadcastFilters::find($request->filterId);

        try {
            $attributeId = null;
            $filter->filter_type = $request->input('option');
            $filter->condition = $request->input('type');
            $filter->chain_condition = $request->input('condi');
            $filter->user_attribute_type = null;
            $filter->user_attribute_value = null;
            $filter->chat_attribute_id = null;
            $filter->chat_attribute_value = null;
            $filter->system_attribute_type = null;
            $filter->system_attribute_value = null;
            $filter->project_user_segments_id = null;
            
            switch($request->input('option')) {
                case("1"):
                    $filter->user_attribute_type = $request->input('userAttribute');
                    $filter->user_attribute_value = $request->input('userAttributeValue');
                    break;

                case("2"):
                    $attributeId = $this->getChatAttribute($request->input('name'));
                    $filter->chat_attribute_id = $attributeId;
                    $filter->chat_attribute_value = $request->input('value');
                    break;
                    
                case("3"):
                    $filter->system_attribute_type = $request->input('systemAttribute');
                    $filter->system_attribute_value = $request->input('systemAttributeValue');
                    break;
                    
                case("4"):
                    $filter->project_user_segments_id = $request->input('segmentId')<0 ? null : $request->input('segmentId');
                    break;
            }
            
            $filter->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update attribute',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }
}
