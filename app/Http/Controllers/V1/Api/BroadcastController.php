<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Broadcast;
use App\Models\ProjectMessageTag;
use App\Models\ChatBlockSection;

class BroadcastController extends Controller
{
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
                $res = $this->buildScheduleList($broadcast);
            }
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
            'status' => false,
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
            'day' => is_null($schedule->day) ? 0 : $schedule->day,
            'interval_type' => is_null($schedule->interval_type) ? 1 : $schedule->interval_type
        ];
    }

    public function getScheduleDetail(Request $request)
    {
        
    }
}
