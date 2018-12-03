<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\ChatBlockSectionContent;

class ValidateChatContent
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
        $content = ChatBlockSectionContent::find($request->contentId);

        if(empty($content)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid content!'
            ], 422);
        }

        if((int) $content->section_id!==(int) $request->sectionId) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid content section!'
            ], 422);
        }

        $request->attributes->add([
            'chatBlockSectionContent' => $content
        ]);

        return $next($request);
    }
}
