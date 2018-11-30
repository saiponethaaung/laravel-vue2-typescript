<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\ChatBlock;

class ValidateChatBlock
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
        $block = ChatBlock::find($request->blockId);

        if(empty($block)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid block!'
            ], 422);
        }

        $request->attributes->add([
            'chatBlock' => $block
        ]);
        
        return $next($request);
    }
}
