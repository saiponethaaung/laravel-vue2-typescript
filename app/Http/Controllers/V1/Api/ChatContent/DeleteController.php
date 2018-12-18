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
use App\Models\ChatQuickReplyBlock;
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

    public function deleteQuickReplyBlock(Request $request)
    {
        $qrBlock = ChatQuickReplyBlock::where('quick_reply_id', $request->qrId)->first();

        if(empty($qrBlock)) {
            return response()->json([
                'status' => true,
                'code' => 422,
                'type' => 'Block didn\'t exists for selected quick reply!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $qrBlock->delete();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete a block from selected quick reply!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
        ]);
    }

    public function deleteGalleryImage(Request $request)
    {
        DB::beginTransaction();

        $image = '';
        
        $gallery = ChatGallery::find($request->galleId);

        if(is_null($gallery->image) || $gallery->image==='' || Storage::disk('public')->exists('/images/gallery/'.$gallery->image)==false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Gallery image didn\'t exists!'
            ], 422);
        }

        try {
            Storage::disk('public')->delete('/images/gallery/'.$gallery->image);
            $gallery->image = null;
            $gallery->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete an image!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'sucess'
        ], 200);
    }

    public function deleteListImage(Request $request)
    {
        DB::beginTransaction();

        $image = '';
        
        $list = ChatGallery::find($request->listId);

        if(is_null($list->image) || $list->image==='' || Storage::disk('public')->exists('/images/list/'.$list->image)==false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'List image didn\'t exists!'
            ], 422);
        }

        try {
            Storage::disk('public')->delete('/images/list/'.$list->image);
            $list->image = null;
            $list->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete an image!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'sucess'
        ], 200);
    }

    public function deleteGalleryItem(Request $request)
    {
        $gallery = ChatGallery::find($request->galleId);

        DB::beginTransaction();

        try {
            $gallery->delete();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete a gallery!',
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

    public function deleteListItem(Request $request)
    {
        $list = ChatGallery::find($request->listId);

        DB::beginTransaction();

        try {
            $list->delete();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete a list!',
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

    public function deleteQuickReplyItem(Request $request)
    {
        $qr = ChatQuickReply::find($request->qrId);

        DB::beginTransaction();

        try {
            $qr->delete();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete quick reply!',
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

    public function deleteUserInputItem(Request $request)
    {
        $userInput = ChatUserInput::find($request->uiId);

        DB::beginTransaction();

        try {
            $userInput->delete();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete user input!',
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

    public function deleteImageImage(Request $request)
    {
        DB::beginTransaction();

        $image = '';
        
        $image = CBSC::find($request->attributes->get('chatBlockSectionContent')->id);

        if(is_null($image->image) || $image->image==='' || Storage::disk('public')->exists('/images/photos/'.$image->image)==false) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Image didn\'t exists!'
            ], 422);
        }

        try {
            Storage::disk('public')->delete('/images/photos/'.$image->image);
            $image->image = null;
            $image->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete an image!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'sucess'
        ], 200);
    }
}
