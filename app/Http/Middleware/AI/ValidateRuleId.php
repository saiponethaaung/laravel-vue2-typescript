<?php

namespace App\Http\Middleware\AI;

use Closure;

use App\Models\KeywordFilterGroupRule;

class ValidateRuleId
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
        $rule = KeywordFilterGroupRule::find($request->ruleid);

        if(empty($rule)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid rule id!'
            ], 422);
        }

        if($rule->keywords_filters_group_id!==$request->attributes->get('project_ai_group')->id) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid group for rule!'
            ], 422);
        }

        $request->attributes->add([
            'project_ai_group_rule' => $rule
        ]);

        return $next($request);
    }
}
