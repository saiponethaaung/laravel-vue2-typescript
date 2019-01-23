<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ChatAttribute;
use App\Models\ProjectPageUser;
use App\Models\ProjectPageUserAttribute;
use App\Models\SegmentFilter;

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
        
        $attributes->whereHas('chatValue', function($query) use ($request) {
            $query->whereHas('user', function($query) use ($request){
                $query->whereHas('projectPage', function($query) use ($request) {
                    $query->where('id', $request->attributes->get('project_page')->id);
                    $query->where('project_id', $request->attributes->get('project')->id);
                });
            });
        });

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

        $users = $users->paginate(50);

        $res = [];

        $fbc = new FacebookController($request->attributes->get('project_page')->token);
        
        foreach($users as $user) {
            $res[] = $this->buildUser($user);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
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

    public function getDurationOffset($duration) {
        $date = strtotime(date("Y-m-d H:i:s"));
        $offset = null;
        switch($duration) {
            case("1"):
                $offset = "-1 day";
                break;

            case("2"):
                $offset = "-7 day";
                break;

            case("3"):
                $offset = "-1 month";
                break;
           
            case("4"):
                $offset = "-3 month";
                break;
        }

        return is_null($offset) ? date("Y-m-d H:i:s", $date) : date("Y-m-d H:i:s", strtotime($offset, $date));
    }

    public function getUserAttributes(Request $request)
    {
        $attributes = ProjectPageUserAttribute::with('attrValue')
                        ->where('project_page_user_id', $request->attributes->get('project_page_user')->id)
                        ->get();

        $res = [];

        foreach($attributes as $attribute) {
            $res[] = [
                'id' => $attribute->id,
                'name' => is_null($attribute->attrValue) ? '' : $attribute->attrValue->attribute,
                'value' => $attribute->value
            ];
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ]);
    }

    public function createUserAttributes(Request $request)
    {
        $attribute = null;
        DB::beginTransaction();

        try {
            $attribute = ProjectPageUserAttribute::create([
                'value' => '',
                'project_page_user_id' => $request->attributes->get('project_page_user')->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create user attribute!',
                'debugMesg' => $e->getMessage()
            ], 422);
        }

        DB::commit();

        $res= [
            'id' => $attribute->id,
            'name' => '',
            'value' => ''
        ];

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $res
        ]);
    }

    public function deleteUserAttribute(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->attributes->get('project_page_user_attribute')->delete();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete user attribute!',
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

    public function updateUserAttributeName(Request $request)
    {
        if(is_null($request->input('name')) || empty($request->input('name'))) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Attribute name cannot be empty!'
            ], 422);
        }

        $chatAttribute = ChatAttribute::where(
            DB::raw('attribute COLLATE utf8mb4_bin'), 'LIKE', $request->input('name').'%'
        )->first();

        if(empty($chatAttribute)) {
            DB::beginTransaction();

            try {
                $chatAttribute = ChatAttribute::create([
                    'attribute' => $request->input('name')
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Failed to update attribute name!',
                    'debugMesg' => $e->getMessage()
                ], 422);
            }

            DB::commit();
        }
        
        if($request->attributes->get('project_page_user_attribute')->attribute_id!==$chatAttribute->id) {
            DB::beginTransaction();

            try {
                $request->attributes->get('project_page_user_attribute')->attribute_id = $chatAttribute->id;
                $request->attributes->get('project_page_user_attribute')->save();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Failed to update attribute name!',
                    'debugMesg' => $e->getMessage()
                ], 422);
            }

            DB::commit();
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ]);
    }
    
    public function updateUserAttributeValue(Request $request)
    {
        if(is_null($request->input('value')) || empty($request->input('value'))) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Attribute value cannot be empty!'
            ], 422);
        }

       
        DB::beginTransaction();

        try {
            $request->attributes->get('project_page_user_attribute')->value = $request->input('value');
            $request->attributes->get('project_page_user_attribute')->save();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to update attribute value!',
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

    public function getUsersBySegment(Request $request)
    {
        $filterList = [];
        $users = ProjectPageUser::query();

        if(isset($request->input('user')['gender']['value'])) {
            $users->whereIn('gender', $request->input('user')['gender']['value']);
        }

        $users->whereHas('projectPage', function($query) use ($request) {
            $query->where('id', $request->attributes->get('project_page')->id);
            $query->where('project_id', $request->attributes->get('project')->id);
        });
        
        $segmentFilters = SegmentFilter::with('attribute')->where('project_user_segments_id', $request->attributes->get('segment')->id)->get();

        if(!empty($segmentFilters)) {
            foreach($segmentFilters as $key => $value) {
                $whereCondi = 'where';
                
                if($key>0 && $segmentFilters[$key-1]->chain_condition==2) {
                    $whereCondi = 'orWhere';
                }

                $condi = $value['condition']===2 ? '=' : '!=';

                
                switch($value['filter_type']) {
                    case(1):
                        switch($value['user_attribute_type']) {
                            case(1):
                                $fValue = $value['user_attribute_value']==1 ? 'male' : 'female';
                                $filterList[] = [
                                    'key' => 'Gender',
                                    'value' => ucfirst($fValue)
                                ];
                                $users->$whereCondi('gender', $condi, $fValue);
                                break;
                        }
                        break;
                    
                    case(2):
                        $whereCondi = 'whereHas';
                        
                        if($key>0 && $segmentFilters[$key-1]->chain_condition==2) {
                            $whereCondi = 'orWhereHas';
                        }

                        if(empty($value->chat_attribute_value)) continue;
                        
                        $filterList[] = [
                            'key' => $value->attribute->attribute,
                            'value' => $value->chat_attribute_value
                        ];

                        $users->$whereCondi('attributes', function($query) use ($value) {
                            $query->where('attribute_id', $value->chat_attribute_id);
                            $query->where('value', $value->chat_attribute_value);
                        });
                        break;
                    
                    case(3):
                        $condi = $value['condition']===2 ? '>=' : '<';
                        $section = 'created_at';
                        $fValueKey = 'Signed up';
                        
                        switch($value->system_attribute_type) {
                            case(2):
                                $fValueKey = 'Last Seen';
                                $section = 'seen_at';
                                break;
                                
                                case(3):
                                $fValueKey = 'Last Engaged';
                                $section = 'updated_at';
                                break;
                        }

                        switch($value->system_attribute_value) {
                            case("1"):
                                $fValue = '24 hrs ago';
                                break;

                            case("2"):
                                $fValue = '1 week ago';
                                break;

                            case("3"):
                                $fValue = '1 month ago';
                                break;

                            case("4"):
                                $fValue = '3 months ago';
                                break;
                        }

                        $filterList[] = [
                            'key' => $fValueKey,
                            'value' => $fValue
                        ];

                        $users->$whereCondi($section, $condi, $this->getDurationOffset($value->system_attribute_value));
                        break;
                }
            }
        }

        $users = $users->paginate(50);

        $res = [];

        $fbc = new FacebookController($request->attributes->get('project_page')->token);
        
        foreach($users as $user) {
            $res[] = $this->buildUser($user);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'user' => $res,
                'filters' => $filterList
            ]
        ]);
    }
}
