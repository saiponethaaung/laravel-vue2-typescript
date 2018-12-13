<?php

namespace App\Http\Middleware\Project;

use DB;
use Closure;

use App\Models\Project;

class IsProjectValid
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
        $project = Project::where(DB::raw('md5(id)'), $request->projectId)->first();

        if(empty($project)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid project id!'
            ], 422);
        }

        $request->attributes->add([
            'project' => $project
        ]);

        return $next($request);
    }
}
