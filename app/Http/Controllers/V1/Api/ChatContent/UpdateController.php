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

class UpdateController extends Controller
{
    public function updateContent(Request $request)
    {
        $res = null;
        switch((int) $request->input('type')) {
            case(1):
                $res = $this->updateText($request, true);
                break;

            case(2):
                $res = $this->updateTyping($request, true);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Invalid type!'
                ], 422);
                break;
        }
        
        return response()->json($res, $res['code']);
    }

    public function updateText(Request $request, $return=false)
    {
        $input = $request->only('content');
        $validator = Validator::make($input, [
            'content' => 'required'
        ]);

        if($validator->fails()) {
            $res = [
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ];
            return $return ? $res : response()->json($res, $res['code']);
        }

        $content = CBSC::find($request->contentId);
        $content->content = $input['content'];
        
        DB::beginTransaction();

        try {
            $content->save();
        } catch (\Exception $e) {
            DB::rollback();
            $res = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update!',
                'debugMesg' => $e->getMessage()
            ];
            return $return ? $res : response()->json($res, $res['code']);
        }

        DB::commit();

        $res = [
            'status' => true,
            'code' => 200,
            'mesg' => 'Content updated.'
        ];

        return $return ? $res : response()->json($res, $res['code']);
    }

    public function updateTyping(Request $request, $return=false)
    {
        $input = $request->only('duration');
        $validator = Validator::make($input, [
            'duration' => 'required|integer'
        ]);

        if($validator->fails()) {
            $res = [
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ];
            return $return ? $res : response()->json($res, $res['code']);
        }

        $content = CBSC::find($request->contentId);
        $content->duration = $input['duration'];
        
        DB::beginTransaction();

        try {
            $content->save();
        } catch (\Exception $e) {
            DB::rollback();
            $res = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update!',
                'debugMesg' => $e->getMessage()
            ];
            return $return ? $res : response()->json($res, $res['code']);
        }

        DB::commit();

        $res = [
            'status' => true,
            'code' => 200,
            'mesg' => 'Content updated.'
        ];

        return $return ? $res : response()->json($res, $res['code']);
    }

    public function updateList(Request $request)
    {
        $input = $request->only('title', 'sub', 'url');

        $validator = Validator::make($input, [
            'title' => 'required|string',
            'sub' => 'nullable|string',
            'url' => 'nullable|url'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $list = ChatGallery::find($request->listId);

        if(empty($list)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid list!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $list->title = $input['title'];
            $list->sub = (String) $input['sub'];
            $list->url = (String) $input['url'];
            $list->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update!',
                'debugMesg' => $e->getMessage()
            ], 422);
        } 

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }

    public function updateGallery(Request $request)
    {
        $input = $request->only('title', 'sub', 'url');

        $validator = Validator::make($input, [
            'title' => 'required|string',
            'sub' => 'nullable|string',
            'url' => 'nullable|url'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $galle = ChatGallery::find($request->galleId);

        if(empty($galle)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid gallery!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $galle->title = $input['title'];
            $galle->sub = (String) $input['sub'];
            $galle->url = (String) $input['url'];
            $galle->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update!',
                'debugMesg' => $e->getMessage()
            ], 422);
        } 

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success'
        ]);
    }

    public function uploadListImage(Request $request)
    {
        $input = $request->only('image');

        $validator = Validator::make($input, [
            'image' => 'required|image|mimes:jpeg,png'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        DB::beginTransaction();

        $image = '';
        $oldImage = '';
        
        $list = ChatGallery::find($request->listId);

        if($list->image) {
            $oldImage = $list->image;
        }

        try {
            $name = str_random(20)."-".date("YmdHis");
            $ext = strtolower($input['image']->getClientOriginalExtension());
            
            $upload = Storage::disk('public')->putFileAs('/images/list/', $input['image'], $name.'.'.$ext);

            $file = Image::make(Storage::disk('public')->get($upload));
                
            $file->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upSize();
            })->encode('jpg')->save(public_path('storage/images/list/'.$name.'.'.$ext));

            if($ext!=="jpg"){
                Storage::disk('public')->delete($upload);
                $ext = "jpg";
            }

            $list->image = $name.'.'.$ext;
            $image = Storage::disk('public')->url('images/list/'.$name.'.'.$ext);
            if($list->save()) {
                if(!empty($oldImage)) {
                    Storage::disk('public')->delete('/images/list/'.$oldImage);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to upload image',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'mesg' => 'sucess',
            'image' => $image
        ], 201);
    }

    public function uploadGalleryImage(Request $request)
    {
        $input = $request->only('image');

        $validator = Validator::make($input, [
            'image' => 'required|image|mimes:jpeg,png'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        DB::beginTransaction();

        $image = '';
        $oldImage = '';
        
        $gallery = ChatGallery::find($request->galleId);

        if($gallery->image) {
            $oldImage = $gallery->image;
        }

        try {
            $name = str_random(20)."-".date("YmdHis");
            $ext = strtolower($input['image']->getClientOriginalExtension());
            
            $upload = Storage::disk('public')->putFileAs('/images/gallery/', $input['image'], $name.'.'.$ext);

            $file = Image::make(Storage::disk('public')->get($upload));
                
            $file->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upSize();
            })->encode('jpg')->save(public_path('storage/images/gallery/'.$name.'.'.$ext));

            if($ext!=="jpg"){
                Storage::disk('public')->delete($upload);
                $ext = "jpg";
            }

            $gallery->image = $name.'.'.$ext;
            $image = Storage::disk('public')->url('images/gallery/'.$name.'.'.$ext);
            if($gallery->save()) {
                if(!empty($oldImage)) {
                    Storage::disk('public')->delete('/images/gallery/'.$oldImage);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to upload image',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 201,
            'mesg' => 'sucess',
            'image' => $image
        ], 201);
    }

    public function updateQuickReply(Request $request)
    {
        $input = $request->only('title', 'attribute', 'value');

        $qr = ChatQuickReply::find($request->qrId);
        $attrId = null;
        
        DB::beginTransaction();

        try {
            
            if($input['attribute']) {
                $attr = ChatAttribute::where('attribute', $input['attribute'])->first();

                if(empty($attr)) {
                    $attr = ChatAttribute::create([
                        'attribute' => $input['attribute']
                    ]);
                }

                $attrId = $attr->id;
            }

            $qr->title = $input['title'] ? $input['title'] : "";
            $qr->value = $input['value'] ? $input['value'] : "";
            $qr->attribute_id = $attrId;
            $qr->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update quick reply!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Sucecss',
            'data' => [
                'attribute' => is_null($attrId) ? 0 : $attrId
            ]
        ]);
    }

    public function addQuickReplyBlock(Request $request)
    {
        $input = $request->only('section');

        $validator = Validator::make($input, [
            'section' => 'required|exists:chat_block_section,id'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $exists = ChatQuickReplyBlock::where('quick_reply_id', $request->qrId)->where('section_id', $input['section'])->first();

        if(!empty($exists)) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'type' => 'exists'
            ]);
        }

        DB::beginTransaction();

        try {
            ChatQuickReplyBlock::create([
                'quick_reply_id' => $request->qrId,
                'section_id' => $input['section']
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to add a block!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'type' => 'new'
        ]);
    }

    public function updateUserInput(Request $request)
    {
        $input = $request->only('question', 'attribute', 'validation');

        $ui = ChatUserInput::find($request->uiId);
        $attrId = null;
        
        DB::beginTransaction();

        try {
            
            if($input['attribute']) {
                $attr = ChatAttribute::where('attribute', $input['attribute'])->first();

                if(empty($attr)) {
                    $attr = ChatAttribute::create([
                        'attribute' => $input['attribute']
                    ]);
                }

                $attrId = $attr->id;
            }

            $ui->question = $input['question'] ? $input['question'] : "";
            $ui->attribute_id = $attrId;
            $ui->validation = $input['validation'];
            $ui->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update user input!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Sucecss',
            'data' => [
                'attribute' => is_null($attrId) ? 0 : $attrId
            ]
        ]);
    }
}
