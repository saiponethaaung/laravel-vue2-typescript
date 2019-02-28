<?php

namespace App\Http\Middleware\Project;

use Auth;

use Closure;
use App\Models\ProjectUser;

class IsProjectMember
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
        $projectUser = ProjectUser::where('project_id', $request->attributes->get('project')->id)->where('user_id', Auth::guard('api')->user()->id)->first();
        
        if(empty($projectUser)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'You are not a memeber of the project!',
                'redirectHome' => true
            ], 422);
        }

        $request->attributes->set('project_user', $projectUser);
        
        return $next($request);
    }
}
