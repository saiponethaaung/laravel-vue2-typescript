<?php

namespace App\Http\Middleware\AI;

use Closure;
use App\Models\KeywordFilterGroup;

class ValidateGroupId
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
        $group = KeywordFilterGroup::find($request->kfGroupId);

        if(empty($group)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid group id!'
            ], 422);
        }

        if($group->project_id!==$request->attributes->get('project')->id) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid project ai rule group!'
            ], 422);
        }

        $request->attributes->add([
            'project_ai_group' => $group
        ]);

        return $next($request);
    }
}
