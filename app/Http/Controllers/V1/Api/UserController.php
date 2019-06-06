<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Hash;
use Auth;
use Image;
use Storage;
use Validator;

use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Api\FacebookController;

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

// @codeCoverageIgnoreStart

class UserController extends Controller
{
    public function getProfile(Request $request)
    {
        $request->attributes->get('sessionInfo')->last_login = gmdate('Y-m-d H:i:s');
        $request->attributes->get('sessionInfo')->save();

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

        $user['image'] = !empty($user['image']) && Storage::disk('public')->exists('images/users/'.$user['image']) ? Storage::disk('public')->url('images/users/'.$user['image']) : '';

        // unset($user['facebook']);
        unset($user['facebook_token']);

        return [
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [
                'profile' => $user,
                'passwordVerify' => $request->attributes->get('sessionInfo')->is_verify==0 ? false : true
            ]
        ];
    }

    public function connectFacebook(Request $request)
    {
        $input = $request->only('access_token', 'userID');

        $validator = Validator::make($input, [
            'access_token' => 'required',
            'userID' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
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

    public function getQrCode(Request $request)
    {
        $input = $request->only('password');

        $validator = Validator::make($input, [
            'password' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        if(!Hash::check($input['password'], Auth::guard('api')->user()->password)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Wrong password!'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'url' => route('api.qr.generate', ['userid' => md5(Auth::guard('api')->user()->id)])
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $input = $request->only('name', 'email');

        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required|unique:users,email,'.Auth::guard('api')->user()->id.',id'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => true,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::find(Auth::guard('api')->user()->id);
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }

    public function validateOtp(Request $request)
    {
        $input = $request->only('otp');

        $validator = Validator::make($input, [
            'otp' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        // initialize Google 2 factor authentication lib
        $google2Fa = new Google2FA();
        
        // Verify otp code
        if(!$google2Fa->verifyKey(Auth::guard('api')->user()->auth_code, $input['otp'])) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid otp code!'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $input = $request->only('password');

        $validator = Validator::make($input, [
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => true,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::find(Auth::guard('api')->user()->id);
        $user->password = bcrypt($input['password']);
        $user->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }

    public function uploadImage(Request $request)
    {
        $input = $request->only('image');

        $validator = Validator::make($input, [
            'image' => 'required|image'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::find(Auth::guard('api')->user()->id);

        if(!empty($user->image) && Storage::disk('public')->exists('images/users/'.$user->image)) {
            Storage::disk('public')->delete('images/users/'.$user->image);
        }

        $name = str_random(20)."-".date("YmdHis");
        $ext = $input['image']->getClientOriginalExtension();
        $upload = Storage::disk('public')->putFileAs('/images/users/', $input['image'], $name.'.'.$ext);
        $file = Image::make(Storage::disk('public')->get($upload));
        $file->fit(1000, 1000, function ($constraint) {
            $constraint->upSize();
        })->encode('jpg')->save(public_path('storage/images/users/'.$name.'.jpg'));
        
        $file->resize(200, 200, function ($constraint) {
            $constraint->upSize();
        })->encode('jpg')->save(public_path('storage/images/users/'.$name.'.jpg'));

        if($ext!=="jpg"){
            Storage::disk('public')->delete($upload);
            $ext = "jpg";
        }

        $user->image = $name.'.'.$ext;
        $user->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success.',
            'data' => [
                'image' => Storage::disk('public')->url('images/users/'.$user->image)
            ]
        ]);
    }
}

// @codeCoverageIgnoreEnd
