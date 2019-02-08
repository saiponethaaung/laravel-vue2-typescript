<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Auth;
use Validator;

use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\V1\Api\FacebookController;

// @codeCoverageIgnoreStart

class UserController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $user['facebook_connected'] = true;

        if(!empty($user['facebook']) && !empty($user['facebook_token'])) {
            $fbc = new FacebookController($user['facebook_token']);
            $userInfo = $fbc->expire();
        
            if($userInfo['status']===false) {
                $user['facebook_connected'] = false;
            } else {
                $permission = $fbc->getPermissions();
                if($permission['status']===false) {
                    $user['facebook_connected'] = false;
                }
            }
        } else {
            $user['facebook_connected'] = false;
        }

        // unset($user['facebook']);
        unset($user['facebook_token']);

        return $user;
    }

    public function connectFacebook(Request $request)
    {
        $input = $request->only('access_token', 'userID');

        $validator = Validator::make($input, [
            'access_token' => 'required',
            'userID' => 'required'
        ]);

        if($validator->fails()) {
            return reponse()->json([
                'status' => true,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $fbc = new FacebookController();
        $token = $fbc->auth($request->input('access_token'));

        if($token['status']===false) {
            return response()->json($token, $token['code']);
        }
        
        $user = User::find(Auth::guard('api')->user()->id);
        
        $userInfo = $fbc->expire();
        
        if($userInfo['status']===false) {
            return response()->json($userInfo, $userInfo['code']);
        }

        DB::beginTransaction();

        try {
            $user->facebook = $userInfo['data']['id'];
            $user->facebook_token = $token['token'];
            $user->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to verify facebook acount!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        $user['facebook_connected'] = true;
        unset($user['facebook']);
        unset($user['facebook_token']);

        return response()->json([
            'status' => true,
            'code' => 422,
            'mesg' => 'success',
            'user' => $user
        ]);
    }
}

// @codeCoverageIgnoreEnd
