<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Segment;

class ValidateSegment
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
        $segment = Segment::find($request->segmentId);

        if(empty($segment) || $segment->project_id !== $request->attributes->get('project')->id) {
            return response()->json([
                'status' => false,
                'code' => '422',
                'mesg' => 'Invalid segment!'
            ], 422);
        }

        $request->attributes->set('segment', $segment);

        return $next($request);
    }
}
