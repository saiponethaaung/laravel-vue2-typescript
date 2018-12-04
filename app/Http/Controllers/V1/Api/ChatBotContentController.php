<?php

namespace App\Http\Controllers\V1\Api;

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

class ChatBotContentController extends Controller
{
    public function getContents(Request $request)
    {
        $contents = CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->orderBy('id', 'asc')->get();

        $res = [];

        foreach($contents as $content) {
            $parsed = [
                'id' => (int) $content->id,
                'type' => (int) $content->type,
                'section_id' => (int) $content->section->id,
                'block_id' => (int) $content->section->block_id,
                'content' => null
            ];

            switch($parsed['type']) {
                case(1):
                    $parsed['content'] = $this->parseText($content);
                    break;

                case(2):
                    $parsed['content'] = $this->parseTyping($content);
                    break;

                case(5):
                    $parsed['content'] = $this->parseList($content);
                    break;

                case(6):
                    $parsed['content'] = $this->parseGallery($content);
                    break;
            }

            $res[] = $parsed;
        }

        return response()->json($res);
    }

    public function parseText($content)
    {
        return [
            'text' => $content->content,
            'button' => []
        ];
    }

    public function parseTyping($content)
    {
        return [
            'duration' => $content->duration,
        ];
    }

    public function parseList($content)
    {
        $list = ChatGallery::where('content_id', $content->id)->get();

        $res = [
            'content' => [],
            'button' => null
        ];
        
        foreach($list as $l) {
            $res['content'][] = [
                'id' => $l->id,
                'image' => $l->image && Storage::disk('public')->exists('images/list/'.$l->image) ? Storage::disk('public')->url('images/list/'.$l->image) : '',
                'title' => $l->title,
                'sub' => $l->sub,
                'url' => $l->url,
                'content_id' => $content->id,
                'button' => null
            ];
        }

        return $res;
    }

    public function parseGallery($content)
    {
        $list = ChatGallery::where('content_id', $content->id)->get();

        $res = [];
        
        foreach($list as $l) {
            $res[] = [
                'id' => $l->id,
                'image' => $l->image && Storage::disk('public')->exists('images/gallery/'.$l->image) ? Storage::disk('public')->url('images/gallery/'.$l->image) : '',
                'title' => $l->title,
                'sub' => $l->sub,
                'url' => $l->url,
                'content_id' => $content->id,
                'button' => []
            ];
        }

        return $res;
    }

    public function createContents(Request $request)
    {
        $input = $request->only('type');

        $content = null;

        DB::beginTransaction();

        try {
            switch((int) $input['type']) {
                case(1):
                    $content = $this->createText($request);
                    break;

                case(2):
                    $content = $this->createTyping($request);
                    break;

                case(5):
                    $content = $this->createList($request);
                    break;

                case(6):
                    $content = $this->createGallery($request);
                    break;
            }
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => $e->getMessage(),
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json($content, $content['code']);
    }

    public function createText(Request $request)
    {
        $create = CBSC::create([
            'section_id' => $request->attributes->get('chatBlockSection')->id,
            'order' => CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->count()+1,
            'type' => 1,
            'text' => '',
            'content' => 'Text Content',
            'image' => '',
            'duration' => 0
        ]);

        $res = [
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => (int) $create->id,
                'type' => (int) $create->type,
                'section_id' => (int) $request->attributes->get('chatBlockSection')->id,
                'block_id' => (int) $request->attributes->get('chatBlock')->id,
                'content' => [
                    'text' => $create->content,
                    'button' => []
                ]
            ]
        ];

        return $res;
    }

    public function createTyping(Request $request)
    {
        $create = CBSC::create([
            'section_id' => $request->attributes->get('chatBlockSection')->id,
            'order' => CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->count()+1,
            'type' => 2,
            'text' => '',
            'content' => '',
            'image' => '',
            'duration' => 1
        ]);

        $res = [
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => (int) $create->id,
                'type' => (int) $create->type,
                'section_id' => (int) $request->attributes->get('chatBlockSection')->id,
                'block_id' => (int) $request->attributes->get('chatBlock')->id,
                'content' => [
                    'duration' => $create->duration,
                ]
            ]
        ];

        return $res;
    }

    public function createList(Request $request)
    {
        $create = CBSC::create([
            'section_id' => $request->attributes->get('chatBlockSection')->id,
            'order' => CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->count()+1,
            'type' => 5,
            'text' => '',
            'content' => '',
            'image' => '',
            'duration' => 1
        ]);

        $list = ChatGallery::create([
            'title' => '',
            'sub' => '',
            'image' => '',
            'url' => '',
            'type' => 1,
            'order' => 1,
            'content_id' => $create->id
        ]);

        $res = [
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => (int) $create->id,
                'type' => (int) $create->type,
                'section_id' => (int) $request->attributes->get('chatBlockSection')->id,
                'block_id' => (int) $request->attributes->get('chatBlock')->id,
                'content' => [
                    'content' => [
                        [
                            'id' => $list->id,
                            'title' => '',
                            'sub' => '',
                            'image' => '',
                            'url' => '',
                            'content_id' => $create->id
                        ]
                    ]
                ]
            ]
        ];

        return $res;
    }

    public function createGallery(Request $request)
    {
        $create = CBSC::create([
            'section_id' => $request->attributes->get('chatBlockSection')->id,
            'order' => CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->count()+1,
            'type' => 6,
            'text' => '',
            'content' => '',
            'image' => '',
            'duration' => 1
        ]);

        $gallery = ChatGallery::create([
            'title' => '',
            'sub' => '',
            'image' => '',
            'url' => '',
            'type' => 2,
            'order' => 1,
            'content_id' => $create->id
        ]);

        $res = [
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => (int) $create->id,
                'type' => (int) $create->type,
                'section_id' => (int) $request->attributes->get('chatBlockSection')->id,
                'block_id' => (int) $request->attributes->get('chatBlock')->id,
                'content' => [
                    [
                        'id' => $gallery->id,
                        'title' => '',
                        'sub' => '',
                        'image' => '',
                        'url' => '',
                        'content_id' => $create->id,
                        'button' => []
                    ]
                ]
            ]
        ];

        return $res;
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
    
    public function createNewList(Request $request)
    {
        $list = null;

        DB::beginTransaction();
        try {
            $list = ChatGallery::create([
                'title' => '',
                'sub' => '',
                'image' => '',
                'url' => '',
                'type' => 1,
                'order' => ChatGallery::where('content_id', $request->contentId)->count()+1,
                'content_id' => $request->contentId
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create new list!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'content' => [
                'id' => $list->id,
                'title' => '',
                'sub' => '',
                'image' => '',
                'url' => '',
                'content_id' => $list->id
            ]
        ], 201);
    }
    
    public function createNewGallery(Request $request)
    {
        $list = null;

        DB::beginTransaction();
        try {
            $list = ChatGallery::create([
                'title' => '',
                'sub' => '',
                'image' => '',
                'url' => '',
                'type' => 1,
                'order' => ChatGallery::where('content_id', $request->contentId)->count()+1,
                'content_id' => $request->contentId
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create new list!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'content' => [
                'id' => $list->id,
                'title' => '',
                'sub' => '',
                'image' => '',
                'url' => '',
                'content_id' => $list->id,
                'button' => []
            ]
        ], 201);
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
}
