<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ProjectPageUser;

use App\Http\Controllers\V1\Api\FacebookController;

class InboxController extends Controller
{
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
            return response()->json($send, $send['status']);
        }
        
        return response()->json($send);
    }
}
