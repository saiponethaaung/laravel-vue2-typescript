<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\ProjectPageUser;

class ValidatePageUser
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
        $projectPageUser = ProjectPageUser::where('project_page_id', $request->attributes->get('project_page')->id)->find($request->pageUserId);

        if(empty($projectPageUser)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid user!'
            ], 422);
        }

        $request->attributes->set('project_page_user', $projectPageUser);

        return $next($request);
    }
}
