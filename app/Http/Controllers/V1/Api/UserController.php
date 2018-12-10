<?php

namespace App\Http\Controllers\V1\Api;

use Auth;
use Validator;

use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $user['facebook_connected'] = false;
        unset($user['facebook']);
        unset($user['facebook_token']);

        return $user;
    }

    public function connectFacebook(Request $request)
    {
        
    }
}
