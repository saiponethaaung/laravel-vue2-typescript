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
use App\Models\ProjectPageUser;
use App\Models\ProjectPageUserChat;
use App\Models\ProjectInvite as ProjectInviteModel;
use App\Models\ProjectInviteEmail;
use App\Models\ChatBlock;
use App\Models\ChatAttribute;
use App\Models\ChatBlockSection;

use App\Models\PersistentFirstMenu;
use App\Models\PersistentSecondMenu;
use App\Models\PersistentThirdMenu;

use App\Mail\MemberInviteWithProject;
use App\Mail\ProjectInviteCancelNonMember;
use App\Notifications\ProjectInvite;
use App\Notifications\ProjectInviteCancel;

class ProjectController extends Controller
{
    public function create(Request $request)
    {
        $input = $request->only('name');

        if (empty($input['name'])) {
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

        foreach ($projects as $project) {
            if ($project->user_type !== 0 && $project->project->status == 0) {
                continue;
            }

            $parsed = [
                'id' => md5($project->project_id),
                'name' => $project->project->name,
                'status' => $project->project->status,
                'image' => $project->project->page && $project->project->page->page_icon ? $project->project->page->page_icon : '',
                'isOwner' => $project->user_type !== 0 ? false : true,
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
            'isOwner' => $project->user_type == 0 ? true : false,
            'role' => $project->user_type,
            'inputDisabled' => $project->project->is_input_disabled == 1,
            'pageId' => config('facebook.defaultPageId'),
            'testingPageId' => config('facebook.defaultPageId'),
            'pageConnected' => false,
            'publish' => false,
            'haveLiveChat' => false,
        ];

        $reAuth = false;
        $pageInfo = null;
        $liveChat = 0;

        if (is_null($project->project->page) == false) {
            // @codeCoverageIgnoreStart
            $fbc = new FacebookController($project->project->page->token);
            $pageInfo = $fbc->expire();

            // Response error if page check response error
            if ($pageInfo['status'] === false && $pageInfo['fbCode'] !== 190) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => $pageInfo['mesg'],
                    'reAuthenticate' => false
                ], 422);
            }

            if (!isset($pageInfo['fbCode']) || $pageInfo['fbCode'] !== 190) {
                $res['pageId'] = $project->project->page->page_id;
                $res['pageConnected'] = true;
                $res['publish'] = $project->project->page->publish === 1 ? true : false;
                $res['image'] = $pageInfo['data']['picture']['url'];
                ProjectPage::where('id', $project->project->page->id)->update([
                    'page_icon' => $pageInfo['data']['picture']['url']
                ]);
                $liveChat = ProjectPageUser::where('project_page_id', $project->project->page->id)->where('live_chat', 1)->count();
                if ($liveChat > 0) {
                    $res['haveLiveChat'] = true;
                } else {
                    $res['haveLiveChat'] = false;
                }
            } else {
                $reAuth = true;
            }
            // @codeCoverageIgnoreEnd
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res,
            'reAuthenticate' => $reAuth,
            'raw' => $pageInfo,
        ]);
    }

    // @codeCoverageIgnoreStart
    public function getPage(Request $request)
    {
        $fbc = new FacebookController(Auth::guard('api')->user()->facebook_token);
        $pageList = $fbc->getPageList();

        if ($pageList['status'] === false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $pageList['mesg']
            ], 422);
        }

        $res = [];

        foreach ($pageList['list'] as $list) {
            $parsed = [
                'id' => $list['id'],
                'access_token' => $list['access_token'],
                'name' => $list['name'],
                'image' => $list['picture']['url'],
                'connected' => false,
                'currentProject' => false
            ];

            $project = ProjectPage::whereNotNull('project_id')->where('page_id', $parsed['id'])->first();

            if (!empty($project)) {
                $parsed['connected'] = true;
                $parsed['currentProject'] = $project->project_id === $request->attributes->get('project')->id;
            }

            $res[] = $parsed;
        }

        usort($res, function ($a, $b) {
            return $a['currentProject'] < $b['currentProject'];
        });

        return response()->json([
            'status' => true,
            'code' => 210,
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
        if ($validator->fails()) {
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
        if ($pageInfo['status'] === false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $pageInfo['mesg']
            ], 422);
        } else {
            // Response error if page id and provided id from post are not matched
            if ($pageInfo['data']['id'] != $input['id']) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Invalid page id!'
                ], 422);
            }
        }

        // Check project page
        $projectPage = ProjectPage::where('page_id', $input['id'])->first();

        if (!empty($projectPage) && is_null($projectPage->project_id) === false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Page is already used in another bot!'
            ], 422);
        }

        //Begin transaction
        DB::beginTransaction();
        try {
            $addGetStarted = $fbc->addGetStarted();
            if (!$addGetStarted['status']) {
                throw (new \Exception("Failed to add get started!"));
            }
            // Create new project page if project page didn't exists
            if (empty($projectPage)) {
                // Create new project page
                $projectPage = ProjectPage::create([
                    'project_id' => null,
                    'page_id' => $input['id'],
                    'token' => $input['access_token']
                ]);
            }

            $attributes = ChatAttribute::with([
                'chatValue',
                'chatValue.user'
            ])->whereHas('chatValue', function ($query) use ($projectPage) {
                $query->whereHas('user', function ($query) use ($projectPage) {
                    $query->where('project_page_id', $projectPage->id);
                });
            })->get();

            foreach ($attributes as $attribute) {
                foreach ($attribute->chatValue as $cv) {
                    $validate = $this->chatAttributeHandler($attribute, $request->attributes->get('project')->id);
                    $cv->attribute_id = $validate;
                    $cv->save();
                }
            }
        } catch (\Exception $e) {
            // Rollback and send error
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to link project and page!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        // Subscribe facebook page to bot
        $subscribe = $fbc->subscribeApp($input['id']);

        // Response error if subscribe failed
        if ($subscribe['status'] === false) {
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
            $this->updatePersistentMenu($request->attributes->get('project')->id, $request->attributes->get('project')->is_input_disabled);
        } catch (\Exception $e) {
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

    private function chatAttributeHandler($attribute, $targetProject)
    {
        $attributeId = $attribute->id;
        // check project id is null on chat attribute
        if (is_null($attribute->project_id)) {
            // set target project id
            $attribute->project_id = $targetProject;
            $attribute->save();
        } else {
            // check target project id and attribute project id are matched
            if ($attribute->project_id !== $targetProject) {
                // if it is not match check chat attribute with target project id already exists
                $attr = ChatAttribute::where(DB::raw('attribute COLLATE utf8mb4_bin'), 'LIKE', $attribute->attribute . '%')
                    ->where('project_id', $targetProject)
                    ->first();
                // create new attribute if it's not yet exists
                if (empty($attr)) {
                    $attr = ChatAttribute::create([
                        'attribute' => $attribute->attribute,
                        'type' => $attribute->type,
                        'is_system' => 0,
                        'project_id' => $targetProject
                    ]);
                }
                $attributeId = $attr->id;
            }
        }
        return $attributeId;
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
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $projectPage = ProjectPage::where('page_id', $input['page_id'])->first();

        // Check if the page have a project linked or not
        if (is_null($projectPage->project_id)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'This page didn\'t have a project! Refresh a page to get up to date data!',
            ], 422);
        }

        if ($projectPage->project_id !== $request->attributes->get('project')->id) {
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
        if ($pageInfo['status'] === false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $pageInfo['mesg']
            ], 422);
        } else {
            // Response error if page id and provided id from post are not matched
            if ($pageInfo['data']['id'] != $projectPage['page_id']) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Invalid page id!'
                ], 422);
            }
        }

        $checkIsSub = $fbc->issubscribeApp($projectPage['page_id']);

        if ($checkIsSub['status'] === false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to check facebook page subscribtion!'
            ], 422);
        }

        if ($checkIsSub['isSubscribe']) {
            $deletePersistentMenu = $fbc->deletePersistentMenu($projectPage['page_id']);
            if (!$deletePersistentMenu['status']) {
                return response()->json($deletePersistentMenu, $deletePersistentMenu['code']);
            }
            // Subscribe facebook page to bot
            $unsubscribe = $fbc->unsubscribeApp($projectPage['page_id']);

            // Response error if subscribe failed
            if ($unsubscribe['status'] === false) {
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
        } catch (\Exception $e) {
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
        if (empty($projectPage)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'This project didn\'t have a page linked! Refresh a page to get up to date data!',
            ], 422);
        }

        // Change project page status
        DB::beginTransaction();
        try {
            $projectPage->publish = $projectPage->publish == 1 ? 0 : 1;
            $projectPage->save();

            if ($projectPage->publish === 1) {
                $this->updatePersistentMenu($request->attributes->get('project')->id, $request->attributes->get('project')->is_input_disabled);
            } else {
                $fbc = new FacebookController($projectPage['token']);
                $deletePersistentMenu = $fbc->deletePersistentMenu($projectPage['page_id']);
            }
        }
        // @codeCoverageIgnoreStart
        catch (\Exception $e) {
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
            'publishStatus' => $projectPage->publish === 1
        ]);
    }

    public function inviteMember(Request $request)
    {
        $input = $request->only('email', 'role');

        $validator = Validator::make($input, [
            'email' => 'required|email',
            'role' => 'required|in:1,2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::where('email', $input['email'])->first();
        $inviteType = 0;
        $userInfo = '';

        if (empty($user)) {
            $isInvited = ProjectInviteModel::where('email', $input['email'])
                ->where('project_id', $request->attributes->get('project')->id)
                ->first();

            if (!empty($isInvited)) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => $input['email'] . ' is already invited!'
                ], 422);
            }
        } else {
            $isInvited = ProjectUser::where('user_id', $user->id)
                ->where('project_id', $request->attributes->get('project')->id)
                ->first();
            if (!empty($isInvited)) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => $input['email'] . ' is already a member!'
                ], 422);
            }
        }

        DB::beginTransaction();

        $info = [];
        $type = 1;

        try {
            $projectInviteStatus = 1;
            $projectInvite = ProjectInviteModel::create([
                'email' => $input['email'],
                'user_id' => empty($user) ? null : $user->id,
                'role' => $input['role'],
                'project_id' => $request->attributes->get('project')->id,
                'status' => 1
            ]);

            ProjectInviteEmail::create([
                'status' => 1,
                'project_invite_id' => $projectInvite->id,
                'project_user_id' => $request->attributes->get('project_user')->id
            ]);

            if (empty($user)) {
                Mail::to($input['email'])->send(new MemberInviteWithProject($input['email'], $request->attributes->get('project')->name));
                $info = [
                    'id' => $projectInvite->id,
                    'email' => $input['email'],
                    'role' => $input['role'],
                    'invited_on' => date('M d, Y')
                ];
            } else {
                $projectUser = ProjectUser::create([
                    'project_id' => $request->attributes->get('project')->id,
                    'user_id' => $user->id,
                    'user_type' => $input['role']
                ]);

                $user->notify(new ProjectInvite($user, $request->attributes->get('project')->name));
                $projectInviteStatus = 0;
                $info = [
                    'id' => $projectUser->id,
                    'name' => $user->name,
                    'email' => $input['email'],
                    'role' => $input['role'],
                    'invited_on' => date('M d, Y')
                ];
                $type = 2;
            }

            $projectInvite->status = $projectInviteStatus;
            $projectInvite->save();
        } catch (\Exception $e) {
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
                'type' => $type,
                'info' => $info,
            ],
        ]);
    }

    public function getAllMembers(Request $request)
    {
        $projectUsers = ProjectUser::with('user')
            ->where('project_id', $request->attributes->get('project')->id)
            ->where('user_type', '!=', 0)
            ->get();

        $res = [];

        foreach ($projectUsers as $projectUser) {
            $res[] = [
                'id' => $projectUser->id,
                'name' => $projectUser->user->name,
                'email' => $projectUser->user->email,
                'role' => $projectUser->user_type,
                'invited_on' => date('M d, Y', strtotime($projectUser->created_at))
            ];
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'data' => $res
        ]);
    }

    public function getAllInvite(Request $request)
    {
        $projectInvites = ProjectInviteModel::where('project_id', $request->attributes->get('project')->id)
            ->where('status', 1)
            ->get();

        $res = [];

        foreach ($projectInvites as $projectInvite) {
            $res[] = [
                'id' => $projectInvite->id,
                'email' => $projectInvite->email,
                'role' => $projectInvite->role,
                'invited_on' => date('M d, Y', strtotime($projectInvite->created_at))
            ];
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'data' => $res
        ]);
    }

    public function cancelInvite(Request $request)
    {
        $inviteId = $request->inviteId;

        if (is_null($inviteId)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invitation id is required!'
            ], 422);
        }

        $invite = ProjectInviteModel::where('status', 1)->find($inviteId);

        if (empty($invite)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid invitation id!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $email = $invite->email;
            $invite->delete();
            Mail::to($email)->send(new ProjectInviteCancelNonMember($email, $request->attributes->get('project')->name));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to canceled invite!',
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

    public function deleteMember(Request $request)
    {
        $projectuserid = $request->projectUserId;

        if (is_null($projectuserid)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Member id is required!'
            ], 422);
        }

        $projectUser = ProjectUser::where('user_type', '!=', 0)->find($projectuserid);

        if (empty($projectUser)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid member id!'
            ], 422);
        }

        $user = User::find($projectUser->user_id);

        DB::beginTransaction();

        try {
            $projectUser->delete();
            $user->notify(new ProjectInviteCancel($user, $request->attributes->get('project')->name));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete member!',
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

    public function updateUserInput(Request $request)
    {
        $projectPage = ProjectPage::where('project_id', $request->attributes->get('project')->id)->first();

        DB::beginTransaction();

        try {
            $request->attributes->get('project')->is_input_disabled = $request->attributes->get('project')->is_input_disabled == 1 ? 0 : 1;
            $request->attributes->get('project')->save();
            if (!empty($projectPage)) {
                $this->updatePersistentMenu($request->attributes->get('project')->id, $request->attributes->get('project')->is_input_disabled);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update messenger user input!',
                'data' => []
            ];

            if (env('APP_DEBUG')) {
                $response['debugMesg'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
            }

            return response()->json($response, $response['code']);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Composer status updated!',
            'data' => []
        ], 200);
    }

    public function updatePersistentMenu($projectId, $input = false)
    {
        $projectPage = ProjectPage::where('project_id', $projectId)->first()->toArray();

        // Check if the page have a project linked or not
        if (empty($projectPage)) {
            return [
                'status' => true,
                'code' => 200,
                'mesg' => 'This page didn\'t have a project!',
            ];
        }

        // Check if the page exists and token valid or not
        $fbc = new FacebookController($projectPage['token']);
        $pageInfo = $fbc->expire();

        // Response error if page check response error
        if ($pageInfo['status'] === false) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => $pageInfo['mesg']
            ];
        } else {
            // Response error if page id and provided id from post are not matched
            if ($pageInfo['data']['id'] != $projectPage['page_id']) {
                return [
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Invalid page id!'
                ];
            }
        }

        $addGetStarted = $fbc->addGetStarted();
        if (!$addGetStarted['status']) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => $addGetStarted['mesg']
            ];
        }

        $deletePersistentMenu = $fbc->deletePersistentMenu($projectPage['page_id']);

        if ($deletePersistentMenu['status'] === false) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => $deletePersistentMenu['mesg']
            ];
        }

        $menu = PersistentFirstMenu::with(
            'secondRelation',
            'secondRelation.thirdRelation'
        )->where('project_id', $projectId)->get();

        $menuRes = [];

        // Loop first persistent menu
        foreach ($menu as $m) {
            // ignore if title is empty
            if (empty($m->title)) continue;
            $res = [];
            //check menu type
            switch ($m->type) {
                    // if menu is payload type
                case (0):
                    // check block is is null and ignore if it's
                    if (!is_null($m->block_id)) {
                        $res = [
                            'title' => $m->title,
                            'type' => 'postback',
                            'payload' => 'persistentMenu-' . $m->id
                        ];
                    }
                    break;

                    // if menu is url
                case (1):
                    // check url empty and ignore if it's
                    if (!is_null($m->url)) {
                        $res = [
                            'title' => $m->title,
                            'type' => 'web_url',
                            'url' => $m->url
                        ];
                    }
                    break;

                    // if menu is nested
                case (2):
                    $secondRes = [];
                    // loop second persistent menu
                    foreach ($m->secondRelation as $s) {
                        // ignore if title is empty
                        if (empty($s->title)) continue;
                        $sRes = [];
                        //check menu type
                        switch ($s->type) {
                                // if menu is payload type
                            case (0):
                                // check block is is null and ignore if it's
                                if (!is_null($s->block_id)) {
                                    $sRes = [
                                        'title' => $s->title,
                                        'type' => 'postback',
                                        'payload' => 'persistentMenu-' . $m->id . '-' . $s->id
                                    ];
                                }
                                break;

                                // if menu is url
                            case (1):
                                // check url empty and ignore if it's
                                if (!is_null($s->url)) {
                                    $sRes = [
                                        'title' => $s->title,
                                        'type' => 'web_url',
                                        'url' => $s->url
                                    ];
                                }
                                break;

                                // if menu is nested
                            case (2):
                                $thirdRes = [];
                                // loop third persistent menu
                                foreach ($s->thirdRelation as $t) {
                                    // ignore if title is empty
                                    if (empty($t->title)) continue;
                                    $tRes = [];
                                    //check menu type
                                    switch ($t->type) {
                                            // if menu is payload type
                                        case (0):
                                            // check block is is null and ignore if it's
                                            if (!is_null($t->block_id)) {
                                                $tRes = [
                                                    'title' => $t->title,
                                                    'type' => 'postback',
                                                    'payload' => 'persistentMenu-' . $m->id . '-' . $s->id . '-' . $t->id
                                                ];
                                            }
                                            break;

                                            // if menu is url
                                        case (1):
                                            // check url empty and ignore if it's
                                            if (!is_null($t->url)) {
                                                $tRes = [
                                                    'title' => $t->title,
                                                    'type' => 'web_url',
                                                    'url' => $t->url
                                                ];
                                            }
                                            break;
                                    }

                                    if (!empty($tRes)) {
                                        $thirdRes[] = $tRes;
                                    }
                                }

                                if (!empty($thirdRes)) {
                                    $sRes = [
                                        'title' => $s->title,
                                        'type' => 'nested',
                                        'call_to_actions' => $thirdRes
                                    ];
                                }
                                break;
                        }

                        if (!empty($sRes)) {
                            $secondRes[] = $sRes;
                        }
                    }

                    if (!empty($secondRes)) {
                        $res = [
                            'title' => $m->title,
                            'type' => 'nested',
                            'call_to_actions' => $secondRes
                        ];
                    }
                    break;
            }

            if (!empty($res)) {
                $menuRes[] = $res;
            }
        }

        $addPersistentMenu = null;

        if (!empty($menuRes)) {
            $persistentMenu = [
                'persistent_menu' => [
                    [
                        'locale' => 'default',
                        'composer_input_disabled' => $input,
                        'call_to_actions' => $menuRes
                    ]
                ]
            ];

            $addPersistentMenu = $fbc->addPersistentMenu($projectPage['page_id'], $persistentMenu);

            if ($addPersistentMenu['status'] === false) {
                return [
                    'status' => false,
                    'code' => 422,
                    'mesg' => $addPersistentMenu['mesg']
                ];
            }
        }

        return [
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'del' => $deletePersistentMenu,
            'add' => $addPersistentMenu
        ];
    }

    public function deactivateProject(Request $request)
    {
        $project = ProjectUser::with(['project', 'project.page'])
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('project_id', $request->attributes->get('project')->id)
            ->first();

        DB::beginTransaction();

        try {
            $project->project->status = 0;
            $project->project->save();

            if ($project->project->page) {
                $project->project->page->publish = 0;
                $project->project->page->save();

                $fbc = new FacebookController($project->project->page->token);
                $deletePersistentMenu = $fbc->deletePersistentMenu($project->project->page->page_id);
            }
        } catch (\Exception $e) {
            DB::rollback();

            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to deactivate project!',
                'data' => []
            ];

            if (env('APP_DEBUG')) {
                $response['debugMesg'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
            }

            return response()->json($response, $response['code']);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'data' => []
        ], 200);
    }

    public function activateProject(Request $request)
    {
        $project = ProjectUser::with(['project', 'project.page'])
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('project_id', $request->attributes->get('project')->id)
            ->first();

        DB::beginTransaction();

        try {
            $project->project->status = 1;
            $project->project->save();
        } catch (\Exception $e) {
            DB::rollback();

            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to activate project!',
                'data' => []
            ];

            if (env('APP_DEBUG')) {
                $response['debugMesg'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
            }

            return response()->json($response, $response['code']);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'data' => []
        ], 200);
    }

    public function deleteProject(Request $request)
    {
        $project = ProjectUser::with(['project', 'project.page'])
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('project_id', $request->attributes->get('project')->id)
            ->first();

        if ($project->project->status == 1) {
            return response()->json([
                'status' => false,
                'mesg' => 'Deactivate a project before deleting.',
                'data' => [],
                'code' => 422
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($project->project->page) {
                $fbc = new FacebookController($project->project->page->token);
                $deletePersistentMenu = $fbc->deletePersistentMenu($project->project->page->page_id);
            }

            $project->project->delete();
        } catch (\Exception $e) {
            DB::rollback();

            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete project!',
                'data' => []
            ];

            if (env('APP_DEBUG')) {
                $response['debugMesg'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
            }

            return response()->json($response, $response['code']);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'data' => []
        ], 200);
    }
}
