<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Broadcast;

class ValidateBroadcast
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
        $broadcast = Broadcast::find($request->broadcastId);

        if(empty($broadcast)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid broadcast!'
            ], 422);
        }

        if((int) $broadcast->project_id!==(int) $request->attributes->get('project')->id) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid project broadcast!'
            ], 422);
        }

        $request->attributes->add([
            'broadcast' => $broadcast
        ]);

        return $next($request);
    }
}
