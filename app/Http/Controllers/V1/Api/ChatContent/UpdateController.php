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

use App\Models\ChatButton;
use App\Models\ChatGallery;
use App\Models\ChatAttribute;
use App\Models\ChatUserInput;
use App\Models\ChatQuickReply;
use App\Models\ChatButtonBlock;
use App\Models\ChatQuickReplyBlock;
use App\Models\ChatBlockSectionContent as CBSC;

class UpdateController extends Controller
{
    public function updateContentsOrder(Request $request)
    {
        $contentid = $request->input('contents');

        if(!is_null($contentid) && !empty($contentid)) {
            DB::beginTransaction();

            try {
                $order = 1;
                foreach($contentid as $id) {
                    $content = CBSC::find($id);
                    if(!empty($content)) {
                        $content->order = $order++;
                        $content->save();
                    }
                }
            } catch(\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Failed to update content order!',
                    'debugMesg' => $e->getMessage()
                ], 422);
            }

            DB::commit();
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Order updated!'
        ]);
    }

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
            'content' => 'nullable'
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
            'title' => 'nullable|string',
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
            $list->title = (String) $input['title'];
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
            'title' => 'nullable|string',
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
            $galle->title = (String) substr($input['title'], 0, 80);
            $galle->sub = (String) substr($input['sub'], 0, 80);
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
            })->encode('jpg')->save(public_path('storage/images/list/'.$name.'.jpg'));

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
                
            $file->fit(1300, 750, function ($constraint) {
                $constraint->upSize();
            })->encode('jpg')->save(public_path('storage/images/gallery/'.$name.'.jpg'));

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
                $attr = ChatAttribute::where(
                    DB::raw('attribute COLLATE utf8mb4_bin'), '=', $input['attribute']
                )
                ->where('project_id', '=', $request->attributes->get('project')->id)
                ->first();

                if(empty($attr)) {
                    $attr = ChatAttribute::create([
                        'attribute' => $input['attribute'],
                        'project_id' => $request->attributes->get('project')->id
                    ]);
                }

                if($attr->type===1) {
                    return response()->json([
                        'status' => false,
                        'code' => 422,
                        'mesg' => 'Attribute `'.$input['attribute'].'` can only be used in user input!'
                    ], 422);
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
                $attr = ChatAttribute::where(
                    DB::raw('attribute COLLATE utf8mb4_bin'), '=', $input['attribute']
                )
                ->where('project_id', $request->attributes->get('project')->id)
                ->first();

                if(empty($attr)) {
                    $attr = ChatAttribute::create([
                        'attribute' => $input['attribute'],
                        'project_id' => $request->attributes->get('project')->id,
                        'type' => 1
                    ]);
                }

                if($attr->type!==1) {
                    return response()->json([
                        'status' => false,
                        'code' => 422,
                        'mesg' => 'Attribute `'.$input['attribute'].'` is already used in quick reply attribute or button attribute!'
                    ], 422);
                }

                $attr->type = 1;
                $attr->save();

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

    public function updateTextButtonBlock(Request $request)
    {
        $update = $this->updateButtonBlock($request, $request->buttonid);
        return response()->json($update, $update['code']);
    }

    private function updateButtonBlock(Request $request, $buttonid=null)
    {
        if($buttonid===null) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => 'Button id required!'
            ];
        }

        $validator = Validator::make($request->only('section'), [
            'section' => 'required|exists:chat_block_section,id'
        ]);

        if($validator->fails()) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ];
        }

        $exists = ChatButtonBlock::where('button_id', $buttonid)->where('section_id', $request->input('section'))->first();

        if(!empty($exists)) {
            return [
                'status' => true,
                'code' => 200,
                'type' => 'exists'
            ];
        }

        DB::beginTransaction();

        try {
            ChatButtonBlock::create([
                'button_id' => $buttonid,
                'section_id' => $request->input('section')
            ]);

            $chatButton = ChatButton::find($buttonid);
            $chatButton->url = '';
            $chatButton->phone = null;
            $chatButton->action_type = 0;
            $chatButton->save();
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to add a block!',
                'debugMesg' => $e->getMessage()
            ];
        }

        DB::commit();

        return [
            'status' => true,
            'code' => 200,
            'mesg' => 'success',
            'type' => 'new'
        ];
    }

    public function updateButtonInfo(Request $request)
    {
        $input = $request->only('title', 'url', 'number', 'type', 'attrTitle', 'attrValue');

        $validator = Validator::make($input, [
            'title' => 'nullable',
            'url' => 'nullable',
            'number' => 'nullable',
            'type' => 'required|in:0,1,2',
            'attrTitle' => 'nullable',
            'attrValue' => 'nullable',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        $button = ChatButton::find($request->buttonid);

        DB::beginTransaction();

        try {
            $button->title = $input['title'] ? $input['title'] : '';
            $button->url = $request->type==1 ? $input['url'] : '';
            $button->phone = $request->type==2 ? $input['number'] : null;
            $button->action_type = $input['type'];
            $button->save();

            $block = ChatButtonBlock::where('button_id', $request->buttonid)->first();
            if($button->type!=0 && !empty($block)) {
                $block->delete();
            } else {
                if(!empty($input['attrTitle'])) {
                    $attr = ChatAttribute::where(
                        DB::raw('attribute COLLATE utf8mb4_bin'), '=', $input['attrTitle']
                    )
                    ->where('project_id', $request->attributes->get('project')->id)
                    ->first();

                    if(empty($attr)) {
                        $attr = ChatAttribute::create([
                            'attribute' => $input['attrTitle'],
                            'project_id' => $request->attributes->get('project')->id,
                            'type' => 2
                        ]);
                    }

                    if($attr->type==1) {
                        return response()->json([
                            'status' => false,
                            'code' => 422,
                            'mesg' => 'Attribute `'.$input['attrTitle'].'` can only be used in user input!'
                        ], 422);
                    }

                    if(empty($block)) {
                        $block = ChatButtonBlock::create([
                            'button_id' => $button->id,
                        ]);
                    }

                    $block->attribute_id = $attr->id;
                    $block->value = $input['attrValue'];
                    $block->save();
                }
            }
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update button info!',
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

    public function uploadImageImage(Request $request)
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
        
        $content = CBSC::find($request->attributes->get('chatBlockSectionContent')->id);

        if($content->image) {
            $oldImage = $list->image;
        }

        try {
            $name = str_random(20)."-".date("YmdHis");
            $ext = strtolower($input['image']->getClientOriginalExtension());
            
            $upload = Storage::disk('public')->putFileAs('/images/photos/', $input['image'], $name.'.'.$ext);

            $file = Image::make(Storage::disk('public')->get($upload));
                
            $file->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upSize();
            })->encode('jpg')->save(public_path('storage/images/photos/'.$name.'.jpg'));

            if($ext!=="jpg"){
                Storage::disk('public')->delete($upload);
                $ext = "jpg";
            }

            $content->image = $name.'.'.$ext;
            $image = Storage::disk('public')->url('images/photos/'.$name.'.'.$ext);
            if($content->save()) {
                if(!empty($oldImage)) {
                    Storage::disk('public')->delete('/images/photos/'.$oldImage);
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

    public function updateQuickReplyOrder(Request $request)
    {
        $quickReplies = $request->only('order');

        $validator = Validator::make($quickReplies, [
            'order' => 'required',
            'order.*' => 'required|exists:chat_quick_reply,id'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $order = 1;
            foreach($quickReplies['order'] as $quickReply) {
                $qr = ChatQuickReply::find($quickReply);
                $qr->order = $order++;
                $qr->save();
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update quick reply order!',
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

    public function updateGalleryOrder(Request $request)
    {
        $chatGalleries = $request->only('order');

        $validator = Validator::make($chatGalleries, [
            'order' => 'required',
            'order.*' => 'required|exists:chat_gallery,id'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $validator->errors()->all()[0]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $order = 1;
            foreach($chatGalleries['order'] as $chatGallery) {
                $cg = ChatGallery::find($chatGallery);
                $cg->order = $order++;
                $cg->save();
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update gallery order!',
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
}
