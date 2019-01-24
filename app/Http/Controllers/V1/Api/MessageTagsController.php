<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ProjectMessageTag;

class MessageTagsController extends Controller
{
    public function getList()
    {
        $list = ProjectMessageTag::get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $list
        ]);
    }
}
