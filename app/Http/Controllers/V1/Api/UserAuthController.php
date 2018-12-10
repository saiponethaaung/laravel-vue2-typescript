<?php

namespace App\Http\Controllers\V1\Api;

use Auth;
use Hash;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;

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

        $token = $user->createToken('dashboard token');

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => "Login Success",
            'token' => $token->accessToken
        ]);
    }
}
