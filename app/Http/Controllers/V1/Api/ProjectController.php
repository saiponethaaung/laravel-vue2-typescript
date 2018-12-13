<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Auth;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ProjectUser;
use App\Models\ProjectPage;

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
                'isOwner' => $project->user_type!==0 ? false : true,
                'pageConnected' => false
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
        $project = ProjectUser::with(['project', 'project.page'])->where('user_id', Auth::guard('api')->user()->id)->where(DB::raw('md5(project_id)'), $request->projectId)->first();

        $res = [
            'id' => md5($project->project_id),
            'name' => $project->project->name,
            'image' => '',
            'isOwner' => $project->user_type!==0 ? false : true,
            'pageConnected' => false
        ];

        if(is_null($project->project->page)==false) {
            $fbc = new FacebookController($project->project->page->token);
            $pageInfo = $fbc->expire();
        
            // Response error if page check response error
            if($pageInfo['status']===false) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => $pageInfo['mesg']
                ]);
            }
            
            $res['pageConnected'] = true;
            $res['image'] = $pageInfo['data']['picture']['url'];
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ]);
    }

    public function getPage(Request $request)
    {
        $fbc = new FacebookController(Auth::guard('api')->user()->facebook_token);
        $pageList = $fbc->getPageList();

        if($pageList['status']===false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $pageList['mesg']
            ], 422);
        }

        $res = [];

        foreach($pageList['list'] as $list) {
            $parsed = [
                'id' => $list['id'],
                'access_token' => $list['access_token'],
                'name' => $list['name'],
                'image' => $list['picture']['url'],
                'connected' => false,
                'currentProject' => false
            ];

            $project = ProjectPage::whereNotNull('project_id')->where('page_id', $parsed['id'])->first();

            if(!empty($project)) {
                $parsed['connected'] = true;
                $parsed['currentProject'] = $project->project_id===$request->attributes->get('project')->id;
            }

            $res[] = $parsed;
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ], 200);
    }

    public function linkProject(Request $request)
    {
        // Collect id and access_token from post
        $input = $request->only('id', 'access_token');

        // Set validation rule and make validation
        $validator = Validator::make($input, [
            'id' => 'required',
            'access_token' => 'required'
        ]);
        
        // Response error if validation failed
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        // Check if the page exists and token valid or not
        $fbc = new FacebookController($input['access_token']);
        $pageInfo = $fbc->expire();
        
        // Response error if page check response error
        if($pageInfo['status']===false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $pageInfo['mesg']
            ]);
        } else {
            // Response error if page id and provided id from post are not matched
            if($pageInfo['data']['id']!=$input['id']) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Invalid page id!'
                ], 422);
            }
        }

        // Check project page
        $projectPage = ProjectPage::where('page_id', $input['id'])->first();

        // Create new project page if project page didn't exists
        if(empty($projectPage)) {
            //Begin transaction
            DB::beginTransaction();

            // Create new project page
            try {
                $projectPage = ProjectPage::create([
                    'project_id' => null,
                    'page_id' => $input['id'],
                    'token' => $input['access_token']
                ]);
            } catch (\Exception $e) {
                // Rollback and send error
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Failed to link project and page!'
                ], 422);
            }
            
            // Commit page
            DB::commit();
        } else {
            // if project id from project page is not null send an error
            if(is_null($projectPage->project_id)===false) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Page is already used in another bot!'
                ], 422);
            }
        }

        // Subscribe facebook page to bot
        $subscribe = $fbc->subscribeApp($input['id']);

        // Response error if subscribe failed
        if($subscribe['status']===false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to installed bot to a page!'
            ], 422);
        }

        // Assign project id to project page
        DB::beginTransaction();
        try {
            $projectPage->project_id = $request->attributes->get('project')->id;
            $projectPage->save();
        } catch(\Exception $e) {
            // Rollback and send error on failed
            DB::rollback();
            return repsonse()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to link project and page!'
            ], 422);
        }

        // commit changes
        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }

    public function unlinkProject(Request $request)
    {
        // Collect id and access_token from post
        $input = $request->only('page_id');

        // Set validation rule and make validation
        $validator = Validator::make($input, [
            'page_id' => 'required|exists:project_page,page_id'
        ]);
        
        // Response error if validation failed
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $projectPage = ProjectPage::where('page_id', $input['page_id'])->first();

        // Check if the page have a project linked or not
        if(is_null($projectPage->project_id)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'This page didn\'t have a project! Refresh a page to get up to date data!',
            ], 422);
        }

        if($projectPage->project_id!==$request->attributes->get('project')->id) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Selected project and connected page mismatch!'
            ], 422);
        }

        // Check if the page exists and token valid or not
        $fbc = new FacebookController($projectPage['token']);
        $pageInfo = $fbc->expire();
        
        // Response error if page check response error
        if($pageInfo['status']===false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $pageInfo['mesg']
            ]);
        } else {
            // Response error if page id and provided id from post are not matched
            if($pageInfo['data']['id']!=$projectPage['page_id']) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Invalid page id!'
                ], 422);
            }
        }

        $checkIsSub = $fbc->issubscribeApp($projectPage['page_id']);

        if($checkIsSub['status']===false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to check facebook page subscribtion!'
            ], 422);
        }

        if($checkIsSub['isSubscribe']) {
            // Subscribe facebook page to bot
            $unsubscribe = $fbc->unsubscribeApp($projectPage['page_id']);

            // Response error if subscribe failed
            if($unsubscribe['status']===false) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Failed to uninstalled bot from a page!',
                ], 422);
            }
        }

        // Remove project id from project page
        DB::beginTransaction();
        try {
            $projectPage->project_id = null;
            $projectPage->save();
        } catch(\Exception $e) {
            // Rollback and send error on failed
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to unlink project and page!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        // commit changes
        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }
}
