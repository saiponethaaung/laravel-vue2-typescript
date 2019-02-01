<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Validator;
use App\Models\AdminNote;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminNoteController extends Controller
{

    public function getNote(Request $request) 
    {
        
    }

    public function createNote(Request $request) 
    {
        $input = $request->only('note');

        $validator = Validator::make($input, [
            'note' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $note = null;

        DB::beginTransaction();

        try {
            $note = AdminNote::create([
                'note' => $input['note'],
                'project_page_user_id' => $request->attributes->get('project_page_user')->id,
                'project_user_id' => $request->attributes->get('project_user')->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create note!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'data' => $this->formatNote(AdminNote::with([
                'projectUser',
                'projectUser.user'
            ])->find($note->id))
        ], 201);

    }

    public function formatNote(\App\Models\AdminNote $note)
    {
        return [
            'id' => $note->id,
            'name' => $note->projectUser->user->name,
            'note' => $note->note,
            'image' => '',
            'time' => ''
        ];
    }

}
