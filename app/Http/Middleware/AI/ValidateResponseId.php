<?php

namespace App\Http\Middleware\AI;

use Closure;

use App\Models\KeywordFilterResponse;

class ValidateResponseId
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
        $response = KeywordFilterResponse::find($request->responseid);

        if(empty($response)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid response id!'
            ], 422);
        }

        if($response->keywords_filters_group_rule_id!==$request->attributes->get('project_ai_group_rule')->id) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid rule for response!'
            ], 422);
        }

        $request->attributes->add([
            'project_ai_group_rule_response' => $response
        ]);

        return $next($request);
    }
}
