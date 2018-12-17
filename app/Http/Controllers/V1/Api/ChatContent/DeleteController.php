<?php

namespace App\Http\Controllers\V1\Api\ChatContent;

use DB;
use Auth;
use File;
use Image;
use Storage;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ChatBlockSectionContent as CBSC;
use App\Models\ChatGallery;
use App\Models\ChatAttribute;
use App\Models\ChatQuickReply;
use App\Models\ChatUserInput;
use App\Models\ChatButton;
use App\Models\ChatButtonBlock;

class DeleteController extends Controller
{
    public function deleteButtonBlock(Request $request)
    {
        $buttonBlock = ChatButtonBlock::where('button_id', $request->buttonid)->first();

        if(empty($buttonBlock)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'There is no block connected with this button!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $buttonBlock->delete();
        } catch(\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete a block!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ]);
    }

    public function deleteButton(Request $request)
    {
        $button = ChatButton::find($request->buttonid);

        if(empty($button)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'There is no button!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $button->delete();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete a button!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ]);
    }
}
