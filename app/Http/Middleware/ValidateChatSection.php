<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\ChatBlockSection;

class ValidateChatSection
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
        $section = ChatBlockSection::find($request->sectionId);

        if(empty($section)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid section!'
            ], 422);
        }

        if((int) $section->block_id!==(int) $request->blockId) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid section block!'
            ], 422);
        }

        $request->attributes->add([
            'chatBlockSection' => $section
        ]);

        return $next($request);
    }
}
