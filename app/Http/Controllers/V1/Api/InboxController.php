<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ProjectPageUser;
use App\Models\ProjectPageUserChat;

use App\Http\Controllers\V1\Api\FacebookController;

use App\Traits\Format\ChatHistoryFormat;

class InboxController extends Controller
{
    use ChatHistoryFormat;

    public function getInboxList(Request $request)
    {
        $list = ProjectPageUser::with([
            'attributes',
            'attributes.attrValue'
        ])->where('project_page_id', $request->attributes->get('project_page')->id)->paginate(20);
        
        $fbc = new FacebookController($request->attributes->get('project_page')->token);
        
        $res = [];
        
        foreach($list as $d) {
            $profile = $fbc->getMessengerProfile($d->fb_user_id);
            
            if(!$profile['status']) continue;
            
            unset($profile['data']['id']);
            $attributes = [];
            
            foreach($d->attributes as $attr) {
                $attributes[] = [
                    'id' => $attr->id,
                    'name' => $attr->attrValue->attribute,
                    'value' => $attr->value
                ];
            }
            
            $profile['data']['custom_attribute'] = $attributes;
            
            // $res[] = $profile['data'];
            $res[] = array_merge($profile['data'], $d->toArray());
        }
        
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res,
            // 'raw' => $list
        ]);
    }

    public function sendReply(Request $request)
    {
        if(is_null($request->input('mesg')) || empty($request->input('mesg'))) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Message content cannot be empty!'
            ], 422);
        }

        $fbc = new FacebookController($request->attributes->get('project_page')->token);
        $send = $fbc->sendMessage([
            "recipient" => [
                "id" => $request->attributes->get('project_page_user')->fb_user_id,
            ],
            "message" => [
                "text" => $request->input('mesg')
            ]
        ]);

        if($send['status'] === false) {
            return response()->json($send, $send['code']);
        }

        $response = ProjectPageUserChat::create([
            'content_id' => null,
            'post_back' => false,
            'from_platform' => true,
            'mesg' => $request->input('mesg'),
            'mesg_id' => $send['data']['message_id'],
            'project_page_user_id' => $request->attributes->get('project_page_user')->id,
            'is_send' => true,
            'is_live' => true,
            'ignore' => false,
            'content_type' => 0
        ]);
        
        return response()->json([
            $send,
            'data' => $this->formatChat($response)
        ]);
    }

    public function getMesg(Request $request)
    {
        $list = ProjectPageUserChat::where('project_page_user_id', $request->attributes->get('project_page_user')->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        $res = [];

        foreach($list as $chat) {
            $res[] = $this->formatChat($chat);
        }
        return response()->json([
            'data' => $res
        ]);
    }

    public function getNewMesg(Request $request)
    {
        $list = ProjectPageUserChat::where('project_page_user_id', $request->attributes->get('project_page_user')->id)
            ->where('id', '>', $request->input('last_id'))
            ->orderBy('created_at', 'desc')
            ->get();
        $res = [];

        foreach($list as $chat) {
            $res[] = $this->formatChat($chat);
        }

        return response()->json([
            'data' => $res
        ]);
    }

    public function changeLiveChatStatus(Request $request)
    {
        if(is_null($request->input('status')) || empty($request->input('status')) || !in_array($request->input('status'), ['true', 'false'])) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Action status is required!'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $request->attributes->get('project_page_user')->live_chat = $request->input('status')==='true' ? 1 : 0;
            $request->attributes->get('project_page_user')->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to change live chat status!'
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
