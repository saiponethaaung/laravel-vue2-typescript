<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ProjectPageUserAttribute;

class ValidateChatUserAttribute
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
        $attribute = ProjectPageUserAttribute::where('project_page_user_id', $request->attributes->get('project_page_user')->id)->find($request->attributeId);

        if(empty($attribute)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Attribute did\'t exists or might have been deleted!'
            ], 422);
        }

        $request->attributes->set('project_page_user_attribute', $attribute);

        return $next($request);
    }
}
