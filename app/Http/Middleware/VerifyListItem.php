<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\ChatGallery;

class VerifyListItem
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
        $list = ChatGallery::find($request->listId);

        if(!empty($list)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid list!'
            ], 422);
        }

        if($list->content_id!==$request->contentId) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid list id!'
            ], 422);
        }

        $request->attributes->add([
            'contentList' => $list
        ]);

        return $next($request);
    }
}
