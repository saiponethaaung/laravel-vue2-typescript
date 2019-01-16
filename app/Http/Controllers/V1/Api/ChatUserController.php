<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ChatAttribute;
use App\Models\ProjectPageUser;
use App\Models\ProjectPageUserAttribute;

class ChatUserController extends Controller
{
    public function getFilterAttributes(Request $request)
    {
        $attributes = ChatAttribute::query();
        $attributes->with(['chatValue' => function($query) use ($request) {
            $query->whereHas('user', function($query) use ($request){
                $query->whereHas('projectPage', function($query) use ($request) {
                    $query->where('id', $request->attributes->get('project_page')->id);
                    $query->where('project_id', $request->attributes->get('project')->id);
                });
            });
        }]);

        $attributes = $attributes->get();
    
        $res = [
            [
                "name" => "User Attributes",
                "single" => false,
                "child" => [
                    [
                        "id" => 0,
                        "name" => "Gender",
                        "key" => "gender",
                        "value" => [
                            [
                                "value" => "Male",
                            ],
                            [
                                "value" => "Female"
                            ],
                        ]
                    ]
                ]
            ],
            [
                "name" => "Custom Attributes",
                "single" => false,
                "child" => []
            ],
            [
                "name" => "System Attributes",
                "single" => true,
                "child" => [
                    [
                        "id" => 0,
                        "name" => "Signed up",
                        "key" => "signup",
                        "value" => [
                            [
                                "value" => "24 hrs ago",
                            ],
                            [
                                "value" => "1 week ago",
                            ],
                            [
                                "value" => "1 month ago",
                            ],
                            [
                                "value" => "3 months ago",
                            ],
                        ]
                    ],
                    [
                        "id" => 0,
                        "name" => "Last Seen",
                        "key" => "lastseen",
                        "value" => [
                            [
                                "value" => "24 hrs ago",
                            ],
                            [
                                "value" => "1 week ago",
                            ],
                            [
                                "value" => "1 month ago",
                            ],
                            [
                                "value" => "3 months ago",
                            ],
                        ]
                    ],
                    [
                        "id" => 0,
                        "name" => "Last Engaged",
                        "key" => "lastengaged",
                        "value" => [
                            [
                                "value" => "24 hrs ago",
                            ],
                            [
                                "value" => "1 week ago",
                            ],
                            [
                                "value" => "1 month ago",
                            ],
                            [
                                "value" => "3 months ago",
                            ],
                        ]
                    ],
                ]
            ]
        ];
        
        foreach($attributes as $attr) {
            $parsed = [
                'id' => $attr->id,
                'name' => $attr->attribute,
                'key' => $attr->id,
                'value' => []
            ];

            $values = [];

            foreach($attr->chatValue as $chat) {
                if(in_array($chat->value, $values)) continue;
                $values[] = $chat->value;
                $parsed['value'][]['value'] = $chat->value;
            }

            $res[1]['child'][] = $parsed;
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ]);
    }

    public function getUserList(Request $request)
    {
        $users = ProjectPageUser::query();

        if(isset($request->input('user')['gender']['value'])) {
            $users->whereIn('gender', $request->input('user')['gender']['value']);
        }

        $users->whereHas('projectPage', function($query) use ($request) {
            $query->where('id', $request->attributes->get('project_page')->id);
            $query->where('project_id', $request->attributes->get('project')->id);
        });

        if(!is_null($request->input('custom'))) {
            foreach($request->input('custom') as $custom) {
                if(!isset($custom['value'])) continue;
                $users->whereHas('attributes', function($query) use ($custom) {
                    $query->where('attribute_id', $custom['key']);
                    $query->whereIn('value', $custom['value']);
                });
            }
        }

        if(isset($request->input('system')['signup']['value'])) {
            $users->where('created_at', '>=', $this->durationOffsetParser($request->input('system')['signup']['value']));
        }

        if(isset($request->input('system')['lastengaged']['value'])) {
            $users->where('updated_at', '>=', $this->durationOffsetParser($request->input('system')['lastengaged']['value']));
        }

        if(isset($request->input('system')['lastseen']['value'])) {
            $users->where('seen_at', '>=', $this->durationOffsetParser($request->input('system')['lastseen']['value']));
        }

        // $users->whereHas('attributes', function($query) use ($request, $uc) {
        //     foreach($request->input('custom') as $custom) {
        //         if(!isset($custom['value'])) continue;
        //         $uc[] = $custom;
        //         $query->where(function($query) use ($custom) {
        //             $query->where('attribute_id', $custom['key']);
        //             $query->whereIn('value', $custom['value']);
        //         });
        //     }
        // });
        $users = $users->paginate(50);

        $res = [];

        $fbc = new FacebookController($request->attributes->get('project_page')->token);
        
        foreach($users as $user) {
            $res[] = $this->buildUser($user);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'input' => $request->input(),
            'data' => $res,
        ]);
    }

    public function buildUser(ProjectPageUser $user)
    {
        $dateOffset = strtotime(date("Y-m-d H:i:s"));
        $dateOffset = strtotime("-30 day", $dateOffset);
        return [
            'id' => $user->id,
            'name' => $user->first_name.' '.$user->last_name,
            'gender' => is_null($user->gender) ? '-': $user->gender,
            'age' => 0,
            'fbid' => $user->fb_user_id,
            'lastSeen' => $dateOffset<strtotime($user->seen_at)  ? \Carbon\Carbon::createFromTimeStamp(strtotime($user->seen_at))->diffForHumans() : date("M d, Y", strtotime($user->seen_at)),
            'lastEngaged' => $dateOffset<strtotime($user->updated_at)  ? \Carbon\Carbon::createFromTimeStamp(strtotime($user->updated_at))->diffForHumans() : date("M d, Y", strtotime($user->updated_at)),
            'signup' => $dateOffset<strtotime($user->created_at) ? \Carbon\Carbon::createFromTimeStamp(strtotime($user->created_at))->diffForHumans() : date("M d, Y", strtotime($user->created_at))
        ];
    }

    public function durationOffsetParser($duration) {
        $date = strtotime(date("Y-m-d H:i:s"));
        $offset = null;
        switch($duration) {
            case("24 hrs ago"):
                $offset = "-1 day";
                break;

            case("1 week ago"):
                $offset = "-7 day";
                break;

            case("1 month ago"):
                $offset = "-1 month";
                break;
           
            case("3 months ago"):
                $offset = "-3 month";
                break;
        }

        return is_null($offset) ? date("Y-m-d H:i:s", $date) : date("Y-m-d H:i:s", strtotime($offset, $date));
    }
}
