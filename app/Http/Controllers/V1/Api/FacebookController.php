<?php

namespace App\Http\Controllers\V1\Api;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Facebook\Facebook;

class FacebookController extends Controller
{
    private $fb;

    public function __construct($token=false)
    {
        $this->reconstruct($token);
    }
    
    public function reconstruct($token=false)
    {
        $this->fb = new Facebook([
            'app_id' => config('facebook.appId'),
            'app_secret' => config('facebook.appSecret'),
            'default_graph_version' => config('facebook.graphVersion'),
            'default_access_token' => $token ? $token : config('facebook.appId').'|'.config('facebook.appSecret')
        ]);
    }

    public function expire()
    {
        $graphObject = null;
        try {
            $graphObject = $this->fb->get('/me?fields=id,name,picture{url}')->getGraphObject();
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
            'publish_pages' => 'Publish pages(publish_pages) permission is need to be granted in order to send private message to a comment!',
            'read_page_mailboxes' => 'Read page mailboxes (read_page_mailboxes) permission is need to be granted in order to link chat bot to your page and retrive message history in our applicatin to perform live chat!'
        ];

        foreach ($permissions as $key => $value) {

            try {
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

    public function getPageList($token)
    {
        $res = [];

        try {
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
}