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
                "child" => []
            ],
            [
                "name" => "System Attributes",
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

            foreach($attr->chatValue as $chat) {
                if(in_array($chat->value, $parsed['value'])) continue;
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
        $users->whereHas('projectPage', function($query) use ($request) {
            $query->where('id', $request->attributes->get('project_page')->id);
            $query->where('project_id', $request->attributes->get('project')->id);
        });
        $users = $users->paginate(50);

        $res = [];

        $fbc = new FacebookController($request->attributes->get('project_page')->token);
        
        foreach($users as $user) {
            $profile = $fbc->getMessengerProfile($user->fb_user_id);
            
            if(!$profile['status']) continue;

            $res[] = $this->buildUser($user, $profile['data']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'input' => $request->input(),
            'data' => $res
        ]);
    }

    public function buildUser(ProjectPageUser $user, $facebookData)
    {
        $dateOffset = strtotime(date("Y-m-d H:i:s"));
        $dateOffset = strtotime("-30 day", $dateOffset);
        return [
            'fbraw' => $facebookData,
            'id' => $user->id,
            'name' => $facebookData['first_name'].' '.$facebookData['last_name'],
            'gender' => isset($facebook['gender']) ? $facebook['gender'] : '-',
            'age' => 0,
            'lastSeen' => $dateOffset<strtotime($user->updated_at)  ? \Carbon\Carbon::createFromTimeStamp(strtotime($user->updated_at))->diffForHumans() : date("M d, Y", strtotime($user->updated_at)),
            'signup' => $dateOffset<strtotime($user->created_at) ? \Carbon\Carbon::createFromTimeStamp(strtotime($user->created_at))->diffForHumans() : date("M d, Y", strtotime($user->created_at))
        ];
    }
}
