<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Facebook\Facebook;
use App\Models\ProjectPage;
use App\Models\FacebookRequestCounter;

// @codeCoverageIgnoreStart

class FacebookController extends Controller
{
    private $fb;
    private $token = null;

    public function __construct($token=false)
    {
        $this->reconstruct($token);
    }
    
    public function reconstruct($token=false)
    {
        $this->token = $token ? $token : config('facebook.appId').'|'.config('facebook.appSecret');
        $this->fb = new Facebook([
            'app_id' => config('facebook.appId'),
            'app_secret' => config('facebook.appSecret'),
            'default_graph_version' => config('facebook.graphVersion'),
            'default_access_token' => $this->token
        ]);
    }

    public function expire()
    {
        $graphObject = null;
        try {
            FacebookRequestCounter::create([
                'section' => 'check_expire',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => '/me?fields=id,name,picture{url}'
                ])
            ]);
            $graphObject = $this->fb->get('/me?fields=id,name,picture{url}')->getGraphObject();
        }catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'code' => 422,
                'type' => 'expire',
                'raw' => $e,
                'fbCode' => $e->getCode(),
                'mesg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'code' => 422,
                'type' => 'sdkerror',
                'raw' => $e,
                'fbCode' => $e->getCode(),
                'mesg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        return [
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => $graphObject->asArray()
        ];
    }

    public function auth($accessToken=false)
    {
        if($accessToken===false) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => 'Access token is required!'
            ];
        }

        $helper = $this->fb->getRedirectLoginHelper();
        $oAuth2Client = $this->fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId(config('facebook.appId')); 
        $tokenMetadata->validateExpiration();
        
        $token = "";
        // Exchanges a short-lived access token for a long-lived one
        try {
            FacebookRequestCounter::create([
                'section' => 'make_long_live_token',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => ''
                ])
            ]);
            $lt = $oAuth2Client->getLongLivedAccessToken($accessToken);
            $token = $lt->getValue();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                "status" => false,
                "code" => 422,
                "mesg" => "Error getting long-lived access token:". $helper->getMessage(),
                "raw" => [
                    "exception" => $e->getMessage(),
                    "helper" => $helper->getError()
                ]
            ];
        }

        $this->reconstruct($token);

        $permission = $this->getPermissions($token);
        
        if($permission['status']===false) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => $permission['mesg']
            ];
        }

        $pages = $this->getPageList();

        if(!$pages['status']) {
            return [
                'status' => false,
                'code' => 422,
                'mesg' => $pages['mesg']
            ];
        }

        if(!empty($pages['list'])) {
            DB::beginTransaction();

            try {
                foreach($pages['list'] as $page) {
                    $projectPage = ProjectPage::where('page_id', $page['id'])->first();
                    if(!empty($projectPage)) {
                        $projectPage->token = $page['access_token'];
                        $projectPage->save();
                    }
                }
            } catch (\Exception $e) {
                DB::rollback();
                return [
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Failed to update page access!',
                    'debugMesg' => $e->getMessage(),
                ];
            }

            DB::commit();
        }
        
        return [
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'token' => $token
        ];
    }

    public function getPermissions()
    {
        $res = [];

        $permissions = [
            'public_profile' => 'Public information.',
            'email' => 'Permission to check user email (email) is need to be granted!',
            'pages_messaging' => 'Pages messaging (pages_messaging) permission is need to be granted in order to send message from page on your behalf!',
            'pages_messaging_subscriptions' => 'Pages messaging subscriptions (pages_messaging_subscriptions) permission is need to be granted in order to send message from page on your behalf!',
            'manage_pages' => 'Manage pages (manage_pages) permission is need to be granted in order to link chat bot to your page and retrive message history in our applicatin to perform live chat!',
            'pages_show_list' => 'Pages show list (pages_show_list) permission is need to be granted in order to show list of available to link with chatbot!',
            // 'publish_pages' => 'Publish pages(publish_pages) permission is need to be granted in order to send private message to a comment!',
            'read_page_mailboxes' => 'Read page mailboxes (read_page_mailboxes) permission is need to be granted in order to link chat bot to your page and retrive message history in our applicatin to perform live chat!',
        ];

        foreach ($permissions as $key => $value) {

            try {
                FacebookRequestCounter::create([
                    'section' => 'check_permission',
                    'request' => json_encode([
                        'token' => $this->token,
                        'data' => '/me/permissions/'.$key
                    ])
                ]);
                $status = $this->fb->get('/me/permissions/'.$key)->getGraphEdge()->asArray();
                if($status[0]['status']!=='granted') {
                    return [
                        'status' => false,
                        'code' => 422,
                        'mesg' => $value
                    ];
                } 
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                return [
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Graph returned an error: ' . $e->getMessage()
                ];
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                return [
                    'status' => false,
                    'code' => 422,
                    'mesg' => 'Facebook SDK returned an error: ' . $e->getMessage()
                ];
            } catch(\Exception $e) {
                return [
                    'status' => false,
                    'code' => 422,
                    'mesg' => $e->getMesesage()
                ];
            }

        }

        return [
            'status' => true,
            'code' => 200,
            'mesg' => 'All permission is granted!'
        ];
    }

    public function getPageList()
    {
        $res = [];

        try {
            FacebookRequestCounter::create([
                'section' => 'get_page_list',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => '/me/accounts?fields=id,access_token,name,picture.width(500)&limit(100)'
                ])
            ]);
            $me = $this->fb->get('/me/accounts?fields=id,access_token,name,picture.width(500)&limit(100)')->getGraphEdge()->asArray();
            $res = $me;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'mesg' => 'Graph returned an error: ' . $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'mesg' => 'Facebook SDK returned an error: ' . $e->getMessage()
            ];
        } catch(\Exception $e) {
            return [
                'status' => false,
                'mesg' => $e->getMesesage()
            ];
        }

        return [
            'status' => true,
            'list' => $res
        ];
    }

    public function subscribeApp($pageid)
    {
        $res = [];

        try {
            FacebookRequestCounter::create([
                'section' => 'app_subscribe',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => '/'.$pageid.'/subscribed_apps',
                ])
            ]);
            $me = $this->fb->post(
                '/'.$pageid.'/subscribed_apps',
                [
                    'subscribed_fields' => [
                        'messages',
                        'messaging_postbacks',
                        'messaging_optins',
                        'message_deliveries',
                        'message_reads',
                        'messaging_payments',
                        'messaging_pre_checkouts',
                        'messaging_checkout_updates',
                        'messaging_referrals',
                        'message_echoes',
                        'standby',
                        'messaging_handovers',
                        'messaging_policy_enforcement'
                    ]
                ]
            )->getGraphObject()->asArray();
            $res = $me;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'mesg' => 'Graph returned an error: ' . $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'mesg' => 'Facebook SDK returned an error: ' . $e->getMessage()
            ];
        } catch(\Exception $e) {
            return [
                'status' => false,
                'mesg' => $e->getMesesage()
            ];
        }

        if(!$res['success']) {
            return [
                'status' => false,
                'mesg' => 'Failed to subscribe an app!',
                'debug' => $res
            ];
        }

        return [
            'status' => true,
            'list' => $res
        ];
    }

    public function issubscribeApp($pageid)
    {
        $res = [];

        try {
            FacebookRequestCounter::create([
                'section' => 'check_is_subscribe',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => '/'.$pageid.'/subscribed_apps',
                ])
            ]);
            $me = $this->fb->get(
                '/'.$pageid.'/subscribed_apps',
                $this->token
            )->getGraphEdge()->asArray();
            $res = $me;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'mesg' => 'Graph returned an error: ' . $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'mesg' => 'Facebook SDK returned an error: ' . $e->getMessage()
            ];
        } catch(\Exception $e) {
            return [
                'status' => false,
                'mesg' => $e->getMesesage()
            ];
        }

        return [
            'status' => true,
            'isSubscribe' => !empty($res),
            'raw' => $res
        ];
    }

    public function unsubscribeApp($pageid)
    {
        $res = [];

        try {
            FacebookRequestCounter::create([
                'section' => 'unsubscribe_app',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => '/'.$pageid.'/subscribed_apps'
                ])
            ]);
            $me = $this->fb->delete(
                '/'.$pageid.'/subscribed_apps'
            )->getGraphNode()->asArray();
            $res = $me;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'mesg' => 'Graph returned an error: ' . $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'mesg' => 'Facebook SDK returned an error: ' . $e->getMessage()
            ];
        } catch(\Exception $e) {
            return [
                'status' => false,
                'mesg' => $e->getMesesage()
            ];
        }

        if(!$res['success']) {
            return [
                'status' => false,
                'mesg' => 'Failed to unsubscribe an app!',
                'debug' => $res
            ];
        }
        
        return [
            'status' => true,
            'list' => $res
        ];
    }

    public function deletePersistentMenu($pageid)
    {
        $res = [];

        try {
            FacebookRequestCounter::create([
                'section' => 'delete_persistent_menu',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => ''
                ])
            ]);
            $me = $this->fb->delete(
                '/me/messenger_profile',
                [
                    'fields' => [
                        'persistent_menu'
                    ]
                ]
            )->getGraphNode()->asArray();
            $res = $me;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'mesg' => 'Graph returned an error: ' . $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'mesg' => 'Facebook SDK returned an error: ' . $e->getMessage()
            ];
        } catch(\Exception $e) {
            return [
                'status' => false,
                'mesg' => $e->getMesesage()
            ];
        }

        // if(!$res['success']) {
        //     return [
        //         'status' => false,
        //         'mesg' => 'Failed to delete persistent menu!',
        //         'debug' => $res
        //     ];
        // }
        
        return [
            'status' => true,
            'list' => $res
        ];
    }

    public function addPersistentMenu($pageid, $menu)
    {
        $res = [];

        try {
            FacebookRequestCounter::create([
                'section' => 'add_persistent_menu',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => ''
                ])
            ]);
            $me = $this->fb->post(
                '/me/messenger_profile', $menu
            )->getGraphNode()->asArray();
            $res = $me;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'mesg' => 'Graph returned an error: ' . $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'mesg' => 'Facebook SDK returned an error: ' . $e->getMessage()
            ];
        } catch(\Exception $e) {
            return [
                'status' => false,
                'mesg' => $e->getMesesage()
            ];
        }

        // if(!$res['success']) {
        //     return [
        //         'status' => false,
        //         'mesg' => 'Failed to delete persistent menu!',
        //         'debug' => $res
        //     ];
        // }
        
        return [
            'status' => true,
            'list' => $res
        ];
    }

    public function addGetStarted()
    {
        $res = [];

        try {
            FacebookRequestCounter::create([
                'section' => 'add_get_started',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => ''
                ])
            ]);
            $me = $this->fb->post(
                '/me/messenger_profile', [
                    "get_started" => [
                        "payload" => "get_started"
                    ]
                ]
            )->getGraphNode()->asArray();
            $res = $me;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'mesg' => 'Graph returned an error: ' . $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'mesg' => 'Facebook SDK returned an error: ' . $e->getMessage()
            ];
        } catch(\Exception $e) {
            return [
                'status' => false,
                'mesg' => $e->getMesesage()
            ];
        }

        // if(!$res['success']) {
        //     return [
        //         'status' => false,
        //         'mesg' => 'Failed to delete persistent menu!',
        //         'debug' => $res
        //     ];
        // }
        
        return [
            'status' => true,
            'mesg' => 'Success'
        ];
    }

    public function getMessengerProfile($psid)
    {
        $graphObject = null;
        try {
            FacebookRequestCounter::create([
                'section' => 'get_messenger_user_profile',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => '/'.$psid.'?fields=first_name,last_name,profile_pic,locale,timezone,gender'
                ])
            ]);
            $graphObject = $this->fb->get('/'.$psid.'?fields=first_name,last_name,profile_pic,locale,timezone,gender')->getGraphObject();
        }catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'code' => 422,
                'type' => 'expire',
                'mesg' => $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'code' => 422,
                'type' => 'sdkerror',
                'mesg' => $e->getMessage()
            ];
        }

        return [
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => $graphObject->asArray()
        ];
    }

    public function sendMessage($mesg)
    {
        $graphObject = null;
        try {
            FacebookRequestCounter::create([
                'section' => 'send_mesage',
                'request' => json_encode([
                    'token' => $this->token,
                    'data' => ''
                ])
            ]);
            $graphObject = $this->fb->post('/me/messages', $mesg)->getGraphObject();
        }catch(\Facebook\Exceptions\FacebookResponseException $e) {
            return [
                'status' => false,
                'code' => 422,
                'type' => 'expire',
                'mesg' => $e->getMessage()
            ];
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [
                'status' => false,
                'code' => 422,
                'type' => 'sdkerror',
                'mesg' => $e->getMessage()
            ];
        }

        return [
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => $graphObject->asArray()
        ];
    }
}

// @codeCoverageIgnoreEnd
