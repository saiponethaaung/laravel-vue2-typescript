<?php

namespace App\Http\Controllers\V1\Api;

use DB;
use Auth;
use Hash;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\RegisterToken;

use App\Http\Controllers\V1\Api\FacebookController;

use App\Notifications\SendEmailVerificationToken;

class UserAuthController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');

        $validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $user = User::where('email', $input['email'])->first();

        if(empty($user)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid email or username'
            ], 422);
        }

        $passwordCheck = Hash::check($input['password'], $user->password);

        if(!$passwordCheck) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid email or username'
            ], 422);
        }

        if(is_null($user->email_verified_at)) {
            return response()->json([
                'status' => false,
                'code' => 200,
                'isVerify' => false
            ]);
        }

        $token = $user->createToken('dashboard token');

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => "Login Success",
            'token' => $token->accessToken,
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
            'mesg' => 'success'
        ]);
    }
}
