<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\KeywordFilterGroup;

class AIController extends Controller
{
    public function getList(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => KeywordFilterGroup::select('id', 'name')
                        ->where('project_id', $request->attributes->get('project')->id)
                        ->get()
        ]);
    }
}
