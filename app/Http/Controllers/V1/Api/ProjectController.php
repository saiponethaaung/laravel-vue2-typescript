<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ProjectUser;

class ProjectController extends Controller
{
    public function list(Request $request)
    {
        $projects = ProjectUser::with('project')->where('user_id', Auth::guard('api')->user()->id)->get();

        $res = [];

        foreach($projects as $project) {
            $parsed = [
                'id' => md5($project->project_id),
                'name' => $project->project->name,
                'page_image' => '',
                'isOwner' => $project->user_type!==0 ? false : true
            ];

            $res[] = $parsed;
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ]);
    }

    public function projectInfo(Request $request)
    {
        $project = ProjectUser::with('project')->where('user_id', Auth::guard('api')->user()->id)->where(DB::raw('md5(project_id)'), $request->projectId)->first();

        $res = [
            'id' => md5($project->project_id),
            'name' => $project->project->name,
            'page_image' => '',
            'isOwner' => $project->user_type!==0 ? false : true
        ];

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ]);
    }

    public function getPage()
    {
        $fbc = new FacebookController(Auth::guard('api')->user()->facebook_token);

        return response()->json([
            'mesg' => 'facebook pages goes here'
        ], 422);
    }
}
