<?php

namespace App\Http\Middleware;

use Auth;
use Agent;
use Closure;

use App\Models\UserSession;

class VerifyUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::guard('api')->user()->status===0) {
            return response()->json([
                'status' => false,
                'code' => 401,
                'mesg' => 'Account is deactivated!'
            ], 401);
        }

        $identifier = $request->header('sessionIdentifier');

        if(is_null($identifier) || $identifier==='') {
            return response()->json([
                'status' => false,
                'code' => 401,
                'mesg' => 'Invalid request!'
            ], 401);
        }

        $session = UserSession::where('identifier', $identifier)->first();

        if(empty($session) || $session->is_valid!=1) {
            return response()->json([
                'status' => false,
                'code' => 401,
                'mesg' => 'Invalid identifier!'
            ], 401);
        }

        if(
            // $session->ip !== $_SERVER['REMOTE_ADDR'] ||
            $session->browser !== Agent::browser() ||
            $session->os !== Agent::platform()
        ) {
            $session->reject_ip = $_SERVER['REMOTE_ADDR'];
            $session->reject_browser = Agent::browser();
            $session->reject_os = Agent::platform();
            $session->is_valid = 0;
            $session->save();

            return response()->json([
                'status' => false,
                'code' => 401,
                'mesg' => 'Invalid request!'
            ], 401);
        }

        $request->attributes->set('sessionInfo', $session);
        return $next($request);
    }
}
