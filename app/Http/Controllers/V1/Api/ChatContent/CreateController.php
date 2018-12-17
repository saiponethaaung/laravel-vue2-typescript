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

class CreateController extends Controller
{
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

                case(3):
                    $content = $this->createQuickReply($request);
                    break;

                case(4):
                    $content = $this->createUserInput($request);
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
                'project' => md5($request->attributes->get('project')->id),
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
                'project' => md5($request->attributes->get('project')->id),
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
                'project' => md5($request->attributes->get('project')->id),
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
                'project' => md5($request->attributes->get('project')->id),
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

    public function createQuickReply(Request $request)
    {
        $quickReply = null;
        $create = null;

        DB::beginTransaction();

        try {
            $create = CBSC::create([
                'section_id' => $request->attributes->get('chatBlockSection')->id,
                'order' => CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->count()+1,
                'type' => 3,
                'text' => '',
                'content' => '',
                'image' => '',
                'duration' => 1
            ]);

            $quickReply = ChatQuickReply::create([
                'title' => '',
                'attribute_id' => null,
                'content_id' => $create->id,
                'value' => ''
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create quick reply!',
                'debugMesg' => $e->getMessage()
            ];
        }

        DB::commit();

        return [
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => (int) $create->id,
                'type' => (int) $create->type,
                'section_id' => (int) $request->attributes->get('chatBlockSection')->id,
                'block_id' => (int) $request->attributes->get('chatBlock')->id,
                'project' => md5($request->attributes->get('project')->id),
                'content' => [
                    [
                        'id' => $quickReply->id,
                        'title' => '',
                        'attribute' => [
                            'id' => 0,
                            'title' => '',
                            'value' => ''
                        ],
                        'content_id' => $create->id,
                        'block' => []
                    ]
                ]
            ]
        ];
    }

    public function createNewQuickReply(Request $request)
    {
        $quickReply = null;

        DB::beginTransaction();

        try {
            $quickReply = ChatQuickReply::create([
                'title' => '',
                'attribute_id' => null,
                'content_id' => $request->contentId,
                'value' => ''
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create quick reply!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => $quickReply->id,
                'title' => '',
                'attribute' => [
                    'id' => 0,
                    'title' => '',
                    'value' => ''
                ],
                'content_id' => $request->contentId,
                'block' => []
            ]
        ]);
    }

    public function createUserInput(Request $request)
    {
        $userInput = null;
        $create = null;

        DB::beginTransaction();

        try {
            $create = CBSC::create([
                'section_id' => $request->attributes->get('chatBlockSection')->id,
                'order' => CBSC::where('section_id', $request->attributes->get('chatBlockSection')->id)->count()+1,
                'type' => 4,
                'text' => '',
                'content' => '',
                'image' => '',
                'duration' => 1
            ]);

            $userInput = ChatUserInput::create([
                'question' => '',
                'attribute_id' => null,
                'content_id' => $create->id,
                'validation' => null
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create user input!',
                'debugMesg' => $e->getMessage()
            ];
        }

        DB::commit();

        return [
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => (int) $create->id,
                'type' => (int) $create->type,
                'section_id' => (int) $request->attributes->get('chatBlockSection')->id,
                'block_id' => (int) $request->attributes->get('chatBlock')->id,
                'project' => md5($request->attributes->get('project')->id),
                'content' => [
                    [
                        'id' => $userInput->id,
                        'question' => '',
                        'attribute' => [
                            'id' => 0,
                            'title' => ''
                        ],
                        'content_id' => $create->id,
                        'validation' => 0
                    ]
                ]
            ]
        ];
    }

    
    public function createNewUserInput(Request $request)
    {
        $userInput = null;

        DB::beginTransaction();

        try {
            $userInput = ChatUserInput::create([
                'question' => '',
                'attribute_id' => null,
                'content_id' => $request->contentId,
                'validation' => null
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create user input!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => $userInput->id,
                'question' => '',
                'attribute' => [
                    'id' => 0,
                    'title' => ''
                ],
                'content_id' => $request->contentId,
                'validation' => 0
            ]
        ]);
    }

    public function createTextButton(Request $request)
    {
        $total = ChatButton::where('content_id', $request->attributes->get('chatBlockSectionContent')->id)->count();
        if($total>2) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Text button at it\'s limit!'
            ], 422);
        }

        $res = $this->createButton($request, $total, $request->attributes->get('chatBlockSectionContent')->id);

        if($res['status']===false) {
            return response()->json($res, $res['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 201,
            'button' => $res['button']
        ], 201);
    }

    public function createGalleryButton(Request $request)
    {
        $total = ChatButton::where('gallery_id', $request->galleryid)->count();
        if($total>2) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Gallery button at it\'s limit!'
            ], 422);
        }

        $res = $this->createButton($request, $total, null, $request->galleryid);

        if($res['status']===false) {
            return response()->json($res, $res['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 201,
            'button' => $res['button']
        ], 201);
    }

    public function createListButton(Request $request)
    {
        $total = ChatButton::where('content_id', $request->attributes->get('chatBlockSectionContent')->id)->count();
        if($total>=1) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'List button at it\'s limit!'
            ], 422);
        }

        $res = $this->createButton($request, 1, $request->attributes->get('chatBlockSectionContent')->id);

        if($res['status']===false) {
            return response()->json($res, $res['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 201,
            'button' => $res['button']
        ], 201);
    }

    public function createListItemButton(Request $request)
    {
        $total = ChatButton::where('gallery_id', $request->listid)->count();
        if($total>=1) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'List item button at it\'s limit!'
            ], 422);
        }

        $res = $this->createButton($request, 1, null, $request->listid);

        if($res['status']===false) {
            return response()->json($res, $res['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 201,
            'button' => $res['button']
        ], 201);
    }

    public function createButton(Request $request, $order=0, $content=null, $gallery=null)
    {
        $button = null;

        DB::beginTransaction();
        try {
            $button = ChatButton::create([
                'title' => '',
                'text' => '',
                'phone' => '',
                'url' => '',
                'content_id' => $content,
                'gallery_id' => $gallery,
                'action_type' => 0,
                'order' => $order+1
            ]);
        } catch(\Exceptin $e) {
            DB::rollback();
            return [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create a button!',
                'debugMesg' => $e->getMessage()
            ];
        }

        DB::commit();

        return [
            'status' => true,
            'code' => 201,
            'button' => [
                'id' => $button->id,
                'type' => $button->action_type,
                'title' => $button->title,
                'block' => [],
                'url' => $button->url,
                'phone' => [
                    'countryCode' => 95,
                    'number' => null
                ]
            ]
        ];
    }
}
