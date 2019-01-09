<?php

namespace App\Http\Middleware\Project;

use Closure;

use App\Models\ProjectPage;

class IsPageConnected
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
        $projectPage = ProjectPage::where('project_id', $request->attributes->get('project')->id)->first();

        if(empty($projectPage)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Project is not connected to a facebook page!'
            ], 422);
        }

        $request->attributes->set('project_page', $projectPage);

        return $next($request);
    }
}
