<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\SegmentFilter;

class ValidateSegmentFilter
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
        $filter = SegmentFilter::find($request->filterId);

        if(empty($filter) || $filter->project_user_segments_id!==$request->attributes->get('segment')->id) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid attribute filter!'
            ], 422);
        }

        $request->attributes->set('segment_filter', $filter);

        return $next($request);
    }
}
