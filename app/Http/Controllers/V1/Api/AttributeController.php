<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ChatAttribute;

class AttributeController extends Controller
{
    public function getAttributeSuggestion(Request $request)
    {
        $keyword = $request->input('keyword');

        if(is_null($keyword) || empty($keyword)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Keyword is required!'
            ], 422);
        }

        $attr = ChatAttribute::where('attribute', 'LIKE', $keyword.'%')
        ->where('project_id', '=', $request->attributes->get('project')->id)
        ->get();

        if(empty($attr)) {
            return response()->json([
                'status' => false,
                'code' => 204,
                'mesg' => 'There is no matced keywords!',
                'data' => []
            ], 204);
        }

        return response()->json([
            'status' => false,
            'code' => 200,
            'mesg' => 'success',
            'data' => $attr->pluck('attribute')
        ]);
    }

    public function getAttributeValueSuggestion(Request $request)
    {
        $input = $request->only('attr', 'keyword');

        $attr = ChatAttribute::with(['chatValue' => function($query) use ($request, $input) {
            $query->where('value', 'LIKE', $input['keyword'].'%');
            $query->whereHas('user', function($query) use ($request){
                $query->whereHas('projectPage', function($query) use ($request) {
                    $query->where('project_id', $request->attributes->get('project')->id);
                });
            });
        }])->where(DB::raw('attribute COLLATE utf8mb4_bin'), $input['attr'])
        ->where('project_id', '=', $request->attributes->get('project')->id)
        ->first();

        if(empty($attr)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Selected attribute name is invalid!'
            ], 422);
        }

        if($attr->type==1) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'mesg' => 'Success',
                'data' => [
                    'Yes',
                    'No'
                ]
            ]);
        }

        if(empty($attr->chatValue)) {
            return response()->json([
                'status' => false,
                'code' => 204,
                'mesg' => 'There is no matced keywords!',
                'data' => []
            ], 204);
        }

        return response()->json([
            'status' => false,
            'code' => 200,
            'mesg' => 'success',
            'data' => $attr->chatValue->pluck('value')->all()
        ]);
    }
}
