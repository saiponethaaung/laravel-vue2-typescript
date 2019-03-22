<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Auth;
use Hash;
use Agent;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\UserSession;
use App\Models\RegisterToken;
use PragmaRX\Google2FA\Google2FA;

use App\Http\Controllers\V1\Api\FacebookController;

use App\Notifications\SendQrCode;
use App\Notifications\SendEmailVerificationToken;

use App\Traits\Common\GenerateUniqueOTPCodeTrait;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class UserAuthController extends Controller
{
    use GenerateUniqueOTPCodeTrait;

    public function login(Request $request)
    {
        $input = $request->only('email', 'otp');

        $validator = Validator::make($input, [
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::where('email', $input['email'])->first();

        // If user didn't exists
        if(empty($user)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid email or otp!'
            ], 422);
        }

        // if user is deactivated
        if($user->status==0) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Your account is deactivated!'
            ], 422);
        }

        // $passwordCheck = Hash::check($input['password'], $user->password);

        // if(!$passwordCheck) {
        //     return response()->json([
        //         'status' => false,
        //         'code' => 422,
        //         'mesg' => 'Invalid email or username'
        //     ], 422);
        // }

        // If user email is not yet verified
        if(is_null($user->email_verified_at)) {
            return response()->json([
                'status' => false,
                'code' => 200,
                'isVerify' => false
            ]);
        }

        // initialize Google 2 factor authentication lib
        $google2Fa = new Google2FA();
        
        // Verify otp code
        if(!$google2Fa->verifyKey($user->auth_code, $input['otp'])) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid otp code!'
            ], 422);
        }

        $identifier = "";

        // get unique identifier
        do {
            $identifier = str_random(247).date("Ymd");
        } while (UserSession::where('identifier', $identifier)->count()!==0);

        // create user session
        $userSession = UserSession::create([
            'parent_id' => $user->id,
            'identifier' => $identifier,
            'browser' => Agent::browser(),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'os' => Agent::platform(),
            'last_login' => date("Y-m-d H:i:s")
        ]);

        // activate user status if it's pending
        if($user->status===2) {
            $user->status = 1;
            $user->activated_at = gmdate("Y-m-d H:i:s");
            $user->save();
        }

        // generate token
        $token = $user->createToken('dashboard token');

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => "Login Success",
            'token' => $token->accessToken,
            'sessionIdentifier' => $userSession->identifier,
            'isVerify' => true
        ]);
    }

    public function register(Request $request)
    {
        $input = $request->only('name', 'email', 'password');

        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
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
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => bcrypt($input['password']),
                'image' => ''
            ]);
            
            $token = null;

            do {
                $token = str_random(8).'-'.date('YmdHis');
            } while (RegisterToken::where('token', $token)->where('status', 1)->count()>0);

            RegisterToken::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            $user->notify(new SendEmailVerificationToken($user, $token));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to register!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'status' => 'Success'
        ]);
    }

    public function verifyToken(Request $request)
    {
        $input = $request->only('code', 'email');

        $validator = Validator::make($input, [
            'email' => 'required|email|exists:users,email',
            'code' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::where('email', $input['email'])->first();

        $token = RegisterToken::where('user_id', $user->id)->where('token', $input['code'])->where('status', 1)->first();

        if(empty($token)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid verification code!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user->email_verified_at = gmdate('Y-m-d H:i:s');
            $user->save();
            $token->status = 0;
            $token->save();

            // Otp to user
            $user->auth_code = $this->generateUniqueCode();
            $user->save();
            $user->notify(new SendQrCode($this->getImageQr($user->id)));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to verify!'
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Qrcode is send to your email.'
        ]);
    }

    public function verifyPassword(Request $request) {
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
            if($request->attributes->get('sessionInfo')->wrong_attempted<3) {
                $request->attributes->get('sessionInfo')->wrong_attempted++;
                $request->attributes->get('sessionInfo')->save();
            } else {
                $request->attributes->get('sessionInfo')->is_valid = 0;
                $request->attributes->get('sessionInfo')->save();
                return response()->json([
                    'status' => false,
                    'code' => 401,
                    'mesg' => 'You have type wrong password 3 times please login with email again!'
                ], 401);
            }

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Wrong password!'
            ], 422);
        }

        $request->attributes->get('sessionInfo')->is_verify = 1;
        $request->attributes->get('sessionInfo')->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Password verification success!'
        ]);
    }

    public function showQrCode(Request $request)
    {
        $userid = $request->input('userid');

        if(is_null($userid)) {
            abort(404);
        }
        
        $user = User::where(DB::raw('md5(id)'), $userid)->first();

        if(empty($user)) {
            abort(404);
        }
        
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );
        
        $google2Fa = new Google2FA();
        $qrCodeUrl = $google2Fa->getQRCodeUrl(
            env('APP_NAME'),
            $user->email,
            $user->auth_code
        );

        $writer = new Writer($renderer);
        return $writer->writeString($qrCodeUrl);
    }

    public function resendOtp(Request $request)
    {
        $input = $request->only('email');

        $validator = Validator::make($input, [
            'email' => 'required|email|exists:users,email'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::where('email', $input['email'])->first();

        try {
            // Otp to user
            $user->notify(new SendQrCode($this->getImageQr($user->id)));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to verify!'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Qrcode is send to your email.'
        ]);
    }

    public function resendPassword(Request $request)
    {
        
    }
}
