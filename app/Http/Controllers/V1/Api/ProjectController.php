<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Mail;
use Auth;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ProjectPage;
use App\Models\ProjectInvite as ProjectInviteModel;
use App\Models\ProjectInviteEmail;
use App\Models\ChatBlock;
use App\Models\ChatBlockSection;

use App\Mail\MemberInviteWithProject;
use App\Notifications\ProjectInvite;

class ProjectController extends Controller
{
    public function create(Request $request)
    {
        $input = $request->only('name');

        if(empty($input['name'])) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Project name is required!'
            ], 422);
        }

        $project = null;

        DB::beginTransaction();
 
        try {
            $project = Project::create([
                'name' => $input['name'],
                'timezone' => '',
                'user_id' => Auth::guard('api')->user()->id
            ]);
        }
        // @codeCoverageIgnoreStart
        catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'debugMesg' => $e->getMessage(),
                'mesg' => 'Failed to create new project!'
            ], 422);
        }
        // @codeCoverageIgnoreEnd

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'mesg' => 'success',
            'data' => [
                'id' => md5($project->id),
                'name' => $project->name
            ]
        ], 201);
    }

    public function list(Request $request)
    {
        $projects = ProjectUser::with('project', 'project.page')->where('user_id', Auth::guard('api')->user()->id)->get();

        $res = [];

        foreach($projects as $project) {
            $parsed = [
                'id' => md5($project->project_id),
                'name' => $project->project->name,
                'image' => $project->project->page && $project->project->page->page_icon ? $project->project->page->page_icon : '',
                'isOwner' => $project->user_type!==0 ? false : true,
                'role' => $project->user_type,
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
        $project = ProjectUser::with(['project', 'project.page'])
                    ->where('user_id', Auth::guard('api')->user()->id)
                    ->where('project_id', $request->attributes->get('project')->id)
                    ->first();

        $res = [
            'id' => md5($project->project_id),
            'name' => $project->project->name,
            'image' => '',
            'isOwner' => $project->user_type==0 ? true : false,
            'role' => $project->user_type,
            'pageId' => config('facebook.defaultPageId'),
            'testingPageId' => config('facebook.defaultPageId'),
            'pageConnected' => false,
            'publish' => false
        ];

        if(is_null($project->project->page)==false) {
            // @codeCoverageIgnoreStart
            $fbc = new FacebookController($project->project->page->token);
            $pageInfo = $fbc->expire();
        
            // Response error if page check response error
            if($pageInfo['status']===false) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => $pageInfo['mesg']
                ], 422);
            }
            
            $res['pageId'] = $project->project->page->page_id;
            $res['pageConnected'] = true;
            $res['publish'] = $project->project->page->publish===1 ? true : false;
            $res['image'] = $pageInfo['data']['picture']['url'];
            ProjectPage::where('id', $project->project->page->id)->update([
                'page_icon' => $pageInfo['data']['picture']['url']
            ]);
            // @codeCoverageIgnoreEnd
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ]);
    }
    
    // @codeCoverageIgnoreStart
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
            ], 422);
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
            $projectPage->page_icon = $pageInfo['data']['picture']['url'];
            $projectPage->publish = 0;
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
            'mesg' => 'Success',
            'data' => [
                'id' => md5($projectPage->project_id),
                'name' => $request->attributes->get('project')->name,
                'image' => $pageInfo['data']['picture']['url'],
                'isOwner' => true,
                'role' => 0,
                'pageId' => $projectPage->id,
                'testingPageId' => config('facebook.defaultPageId'),
                'pageConnected' => true,
                'publish' => false
            ]
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
            ], 422);
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
            $projectPage->publish = 0;
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
            'mesg' => 'Success',
            'data' => [
                'id' => md5($projectPage->project_id),
                'name' => $request->attributes->get('project')->name,
                'image' => '',
                'isOwner' => true,
                'role' => 0,
                'pageId' => config('facebook.defaultPageId'),
                'testingPageId' => config('facebook.defaultPageId'),
                'pageConnected' => false,
                'punblish' => false
            ]
        ]);
    }
    // @codeCoverageIgnoreEnd

    public function changePublishStatusPage(Request $request)
    {
        $projectPage = ProjectPage::where('project_id', $request->attributes->get('project')->id)->first();

        // Check if the project have a page linked or not
        if(empty($projectPage)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'This project didn\'t have a page linked! Refresh a page to get up to date data!',
            ], 422);
        }

        // Change project page status
        DB::beginTransaction();
        try {
            $projectPage->publish = $projectPage->publish==1 ? 0 : 1;
            $projectPage->save();
        }
        // @codeCoverageIgnoreStart
        catch(\Exception $e) {
            // Rollback and send error on failed
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to change publish status!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }
        // @codeCoverageIgnoreEnd

        // commit changes
        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'publishStatus' => $projectPage->publish===1
        ]);
    }

    public function inviteMember(Request $request)
    {
        $input = $request->only('email', 'role');

        $validator = Validator::make($input, [
            'email' => 'required|email',
            'role' => 'required|in:1,2'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::where('email', $input['email'])->first();
        $inviteType = 0;
        $userInfo = '';

        if(empty($user)) {
            $isInvited = ProjectInviteModel::where('email', $input['email'])
                ->where('project_id', $request->attributes->get('project')->id)
                ->first();

            if(!empty($isInvited)) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => $input['email'].' is already invited!'
                ], 422);
            }
        } else {
            $isInvited = ProjectUser::where('user_id', $user->id)
                ->where('project_id', $request->attributes->get('project')->id)
                ->first();
            if(!empty($isInvited)) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => $input['email'].' is already a member!'
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $projectInviteStatus = 0;
            $projectInvite = ProjectInviteModel::create([
                'email' => $input['email'],
                'user_id' => empty($user) ? null : $user->id,
                'role' => $input['role'],
                'project_id' => $request->attributes->get('project')->id,
                'status' => 0
            ]);

            ProjectInviteEmail::create([
                'status' => 1,
                'project_invite_id' => $projectInvite->id,
                'project_user_id' => $request->attributes->get('project_user')->id
            ]);

            if(empty($user)) {
                Mail::to($input['email'])->send(new MemberInviteWithProject($input['email'], $request->attributes->get('project')->name));
            } else {
                ProjectUser::create([
                    'project_id' => $request->attributes->get('project')->id,
                    'user_id' => $user->id,
                    'user_type' => $input['role']
                ]);
                
                $user->notify(new ProjectInvite($user, $request->attributes->get('project')->name));
                $projectInviteStatus = 1;
            }

            $projectInvite->status = $projectInviteStatus;
            $projectInvite->save();
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to invite new member!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'data' => [
                'type' => 1,
                'info' => [],
            ],
        ]);
    }
}
