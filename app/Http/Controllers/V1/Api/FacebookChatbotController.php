<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectPage;
use App\Models\ProjectPageUser;
use App\Models\ProjectPageUserChat;
use App\Models\FacebookRequestLogs;
use App\Models\Broadcast;

use App\Http\Controllers\V1\Api\ChatBotProjectController;

use App\Jobs\Facebook\Webhook\ProcessWebhook;

// @codeCoverageIgnoreStart

class FacebookChatbotController extends Controller
{
    private $token = '';
    private $url = '';
    
    public function __construct()
    {
        $this->token = config('facebook.defaultPageToken');
    }

    public function index(Request $request)
    {
        // Retrive raw input from messenger webhook
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($_GET['hub_verify_token'])) { 
            if ($_GET['hub_verify_token'] === '$2y$12$uyP735FKW7vuSYmlAEhF/OOoo1vCaWZN7zIEeFEhYbAw2qv8X4ffe') {
                echo $_GET['hub_challenge'];
                return;
            } else {
                echo 'Invalid Verify Token';
                return;
            }
        }

        $projectPage = ProjectPage::where('page_id', $input['entry'][0]['id'])->first();
        $dispatch = false;

        // If page id is the default testing page or page from project page proceed to dispatching job
        if($input['entry'][0]['id']==config('facebook.defaultPageId') || (!empty($projectPage) && is_null($projectPage->project_id)==false)) {
            ProcessWebhook::dispatch($input);
            $dispatch = true;
        } else {
            $this->sampleBot($input);
        }

        FacebookRequestLogs::create([
            'data' => json_encode([
                'status' => 'success',
                'dispatch' => $dispatch,
                'data' => $input
            ])
        ]);
        
        return;
    }

    public function processWebHook($input)
    {
        FacebookRequestLogs::create([
            'data' => json_encode([
                'status' => 'working on dispatch',
                'data' => $input
            ])
        ]);
        // Check is the request is from page or not
        if($input['object']!=='page') {
            return null;
        }

        $welcome = false;
        $dev = false;
        $projectId = null;
        $pageId = null;
        $userId = null;
        $projectPage = null;

        $clientid = $input['entry'][0]['id']!==$input['entry'][0]['messaging'][0]['sender']['id'] ? $input['entry'][0]['messaging'][0]['sender']['id'] : $input['entry'][0]['messaging'][0]['recipient']['id'];

        // if message come from dashboard "Test this bot" button
        if(isset($input['entry'][0]['messaging'][0]['optin'])) {

            // Enable welcome status and dev status
            $welcome = true;
            $dev = true;

            // Retrive project id, page id and user id
            list($projectId, $pageId, $userId) = explode('-', $input['entry'][0]['messaging'][0]['optin']['ref']);

            $user = User::where('facebook', $userId)->first();

            if(empty($user)) {
                // Log error if there is no project
                FacebookRequestLogs::create([
                    'data' => json_encode([
                        'error' => true,
                        'data' => 'User with facebook id ('.$userId.') is not a user of platform!'
                    ])
                ]);
                return null;
            }

            // Find Project
            $project = Project::where(DB::raw('md5(id)'), $projectId)->whereHas('user', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();
            
            if(empty($project)) {
                // Log error if there is no project
                FacebookRequestLogs::create([
                    'data' => json_encode([
                        'error' => true,
                        'data' => 'Project ('.$projectId.') didn\'t exists!'
                    ])
                ]);
                return null;
            }

            try {
                $user->project_id = $project->id;
                $user->save();

                $projectPageUser = ProjectPageUser::where('user_id', $user->id)->first();

                if(empty($projectPageUser)) {
                    ProjectPageUser::create([
                        'project_page_id' => null,
                        'fb_user_id' => $clientid,
                        'user_id' => $user->id
                    ]);
                }
            } catch (\Exception $e) {
                // Log error if there is no project
                FacebookRequestLogs::create([
                    'data' => json_encode([
                        'error' => true,
                        'mesg' => 'Failed to assigned project to user!',
                        'data' => $e->getMessage()
                    ])
                ]);
                return null;
            }

            $projectId = $project->id;

            // If page is not from default testing page check project page
            if($input['entry'][0]['id']!==config('facebook.defaultPageId')) {
                $projectPage = ProjectPage::where('page_id', $pageId)->first();
                
                // Stop the process if project page didn't exists or project page is not linked with project
                if(empty($projectPage) || is_null($projectPage->project_id)) {
                    return null;
                }
                
                // Change default token with page token
                $this->token = $projectPage->token ? $projectPage->token : $this->token;
            }
        } else {

            // If page is not from default testing page check project page
            if($input['entry'][0]['id']!==config('facebook.defaultPageId')) {
                $projectPage = ProjectPage::where('page_id', $input['entry'][0]['id'])->first();
                if(empty($projectPage) || is_null($projectPage->project_id) || $projectPage->publish!==1) {
                    return null;
                }
                // Change default token with page token
                $this->token = $projectPage->token ? $projectPage->token : $this->token;
                $projectId = $projectPage->project_id;
            } else {
                $projectPageUser = ProjectPageUser::with('user')->where('fb_user_id', $clientid)->first();
                if(empty($projectPageUser) || is_null($projectPageUser->user->project_id)) {
                    return null;
                }
               
                $projectId = $projectPageUser->user->project_id;
            }
        }
        
        $this->url = 'https://graph.facebook.com/v3.2/me/messages?access_token='.$this->token;

        try {
            // If project id is null send default mesg or proceed on parsing
            if(is_null($projectId)) {
                $this->sampleBot($input);
            } else {
                $this->parseMessage($projectId, $input, $welcome, $dev);
            }
        } catch (\Exception $e) {
            FacebookRequestLogs::create([
                'data' => json_encode([
                    'error' => true,
                    'data' => $e->getMessage()
                ])
            ]);
        }
    }

    public function parseMessage($projectId, $input, $welcome=false, $dev=false)
    {
        $log = FacebookRequestLogs::create([
            'data' => json_encode([
                'isWelcome' => $welcome,
                'isDev' => $dev,
                'raw' => $input,
                'get' => $_GET,
                'post' => $_POST,
            ])
        ]);

        // Read payload
        if(isset($input['entry'][0]['messaging'][0]['read'])) {
            $log->is_read = true;
            $log->save();
            return;
        }
        
        // deliver payload
        if(isset($input['entry'][0]['messaging'][0]['delivery'])) {
            $log->is_deliver = true;
            $log->save();
            return;
        }

        if (isset($input['entry'][0]['messaging'][0]['sender']['id']) || isset($input['entry'][0]['messaging'][0]['optin'])) {
            if(
                isset($input['entry'][0]['messaging'][0]['message']['text']) ||
                isset($input['entry'][0]['messaging'][0]['postback']) ||
                isset($input['entry'][0]['messaging'][0]['optin'])
            ) {

                if(!isset($input['entry'][0]['messaging'][0]['optin']) && $input['entry'][0]['id']==config('facebook.defaultPageId')) {

                }
                
                $isPostBack = isset($input['entry'][0]['messaging'][0]['postback']);

                // echo payload
                if(isset($input['entry'][0]['messaging'][0]['message']['is_echo'])) {
                    $log->is_echo = true;
                    $log->save();
                    return;
                }

                if(isset($input['entry'][0]['messaging'][0]['postback'])) {
                    $log->is_payload = true;
                    $log->save();
                } else {
                    $log->is_income = true;
                    $log->save();
                }
                
                $sender = $input['entry'][0]['messaging'][0]['sender']['id'];

                try {
                    $project = new ChatBotProjectController($projectId);
                    $messages = $project->process($input, $isPostBack, $welcome);

                    if($messages['status']===false) {
                        FacebookRequestLogs::create([
                            'data' => json_encode([
                                'data' => $messages
                            ])
                        ]);
                        return;
                    }
                    unset($project);
    
                    FacebookRequestLogs::create([
                        'data' => json_encode([
                            'aclist' => 'action list: ',
                            'data' => $messages
                        ])
                    ]);
    
                    $this->execResponse([
                        "recipient" => [
                            "id" => $sender,
                        ],
                        "sender_action" => "mark_seen"
                    ]);

                    $break = false;

                    if($dev) {
                        array_unshift($messages['data'], [
                            'status' => true,
                            'mesg' => '',
                            'type' => 1,
                            'content_id' => null,
                            'data' => [
                                'text' => 'New Dev section started'
                            ]
                        ]);
                    }

                    $nextSkip = false;
                    $index = -1;
                    
                    foreach($messages['data'] as $mesg) {
                        $index++;

                        if($nextSkip) {
                            $nextSkip = false;
                            continue;
                        }

                        // If status false or first element is quick reply
                        if($mesg['status']===false || ($index===0 && $mesg['type']===3)) continue;

                        $jsonData = [
                            "recipient" => [
                                "id" => $sender,
                            ]
                        ];

                        $sleep = -1;
                        $skip = false;

                        $recordChat = [
                            'content_id' => $mesg['content_id'],
                            'post_back' => '',
                            'from_platform' => true,
                            'mesg' => json_encode($mesg['data']),
                            'mesg_id' => '',
                            'project_page_user_id' => $messages['userid'],
                            'is_send' => true,
                            'is_live' => false,
                            'content_type' => $mesg['type']
                        ];

                        $type = $mesg['type'];
                        
                        // if next element is quick reply
                        if(isset($messages['data'][$index+1]) && $messages['data'][$index+1]['type']===3) {
                            $isValid = true;
                            $data = $messages['data'][$index+1]['data'];
                            unset($data['text']);
                            
                            // If current element is text, list, gallery or image
                            switch($type) {
                                case(1):
                                    if(isset($mesg['data']['text'])) {
                                        $data['text'] = $mesg['data']['text'];
                                    } else {
                                        $data['attachment'] = $mesg['data']['attachment'];
                                    }
                                    break;
                                    
                                case(5):
                                case(6):
                                    $data['attachment'] = $mesg['data']['attachment'];
                                    break;

                                case(7):
                                    $attach = $this->getImage($mesg['data']['image']);
                                    if($attach['status']) {
                                        $data['attachment'] = [
                                            'type' => 'template',
                                            'payload' => [
                                                'template_type' => 'media',
                                                'elements' => [
                                                    [
                                                        'media_type' => 'image',
                                                        'attachment_id' => $attach['atid'],
                                                    ]
                                                ]
                                            ]
                                        ];
                                    } else {
                                        continue;
                                    }
                                    break;
                                
                                // Ignore quick reply on next turn
                                default:
                                    $isValid = false;
                                    break;
                            }

                            if($isValid) {
                                $type = 3;
                                $mesg['data'] = $data;
                            }

                            $nextSkip = true;
                        }

                        switch($type) {
                            case(1):
                            case(5):
                            case(6):
                                $jsonData['message'] = $mesg['data'];
                                break;

                            case(2):
                                $jsonData['sender_action'] = 'typing_on';
                                $sleep = $mesg['duration'];
                                break;

                            case(3):
                                $break = true;
                                $jsonData['message'] = $mesg['data'];
                                break;

                            case(4):
                                $break = true;
                                $recordChat['user_input_id'] = $mesg['input_id'];
                                $jsonData['message'] = $mesg['data'];
                                break;

                            case(7):
                                $attach = $this->getImage($mesg['data']['image']);
                                if($attach['status']) {
                                    $jsonData['message'] = [
                                        'attachment' => [
                                            'type' => 'template',
                                            'payload' => [
                                                'template_type' => 'media',
                                                'elements' => [
                                                    [
                                                        'media_type' => 'image',
                                                        'attachment_id' => $attach['atid'],
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ];
                                } else {
                                    $skip = true;
                                }
                                break;
                        }

                        if($skip) continue;

                        FacebookRequestLogs::create([
                            'fb_request' => true,
                            'data' => json_encode($jsonData)
                        ]);
                        
                        if(isset($mesg['ignore']) && $mesg['ignore']) {
                            $recordChat['ignore'] = true;
                        }
                        $recordChat = ProjectPageUserChat::create($recordChat);
                        
                        $this->execResponse($jsonData);

                        if($sleep>-1) {
                            sleep((int) $sleep);
                            $jsonData['sender_action'] = 'typing_off';
                            $this->execResponse($jsonData);
                        } else {
                            sleep(1);
                        }
                        
                        if($break) break;
                    }
                } catch (\Exception $e) {
                    FacebookRequestLogs::create([
                        'data' => json_encode([
                            'error' => true,
                            'data' => $e->getMessage(),
                            'raw' => $e->getTraceAsString()
                        ])
                    ]);
                }
            }
        }
    }

    public function sendBroadcast($id)
    {
        FacebookRequestLogs::create([
            'data' => 'suppose to handle this broadcast section '. $id
        ]);

        $broadcast = Broadcast::with('chatBlockSection')->find($id);

        if(empty($broadcast)) {
            FacebookRequestLogs::create([
                'data' => "there is no data for broadcast $id"
            ]);
            return;
        }

        $page = ProjectPage::where('project_id', $broadcast->project_id)->first();
        if(!$page->publish) {
            FacebookRequestLogs::create([
                'data' => "The page is not publish $id"
            ]);
            return;
        }

        $users = ProjectPageUser::where('project_page_id', $page->id)->get();

        if(empty($users)) {
            FacebookRequestLogs::create([
                'data' => 'The page has no user to broadcast'
            ]);
            return;
        }
        try {
            $project = new ChatBotProjectController($broadcast->project_id);
            $messages = $project->getSection($broadcast->chatBlockSection->id);

            if($messages['status']===false) {
                FacebookRequestLogs::create([
                    'data' => json_encode([
                        'data' => $messages
                    ])
                ]);
                return;
            }
            unset($project);

            FacebookRequestLogs::create([
                'data' => json_encode([
                    'aclist' => 'action list: ',
                    'data' => $messages
                ])
            ]);

            $this->token = $page->token;
            $this->url = 'https://graph.facebook.com/v3.2/me/messages?access_token='.$this->token;
                
            foreach($messages['data'] as $mesg) {
                foreach($users as $user) {
                    if($mesg['status']===false) continue;

                    $jsonData = [
                        "recipient" => [
                            "id" => $user->fb_user_id,
                        ]
                    ];

                    $sleep = -1;
                    $skip = false;

                    $recordChat = [
                        'content_id' => $mesg['content_id'],
                        'post_back' => '',
                        'from_platform' => true,
                        'mesg' => json_encode($mesg['data']),
                        'mesg_id' => '',
                        'project_page_user_id' => $user->id,
                        'is_send' => true,
                        'is_live' => false,
                        'content_type' => $mesg['type']
                    ];

                    switch($mesg['type']) {
                        case(1):
                        case(5):
                        case(6):
                            $jsonData['message'] = $mesg['data'];
                            break;

                        case(2):
                            $jsonData['sender_action'] = 'typing_on';
                            $sleep = $mesg['duration'];
                            break;

                        case(3):
                            $break = true;
                            $jsonData['message'] = $mesg['data'];
                            break;

                        case(7):
                            $attach = $this->getImage($mesg['data']['image']);
                            if($attach['status']) {
                                $jsonData['message'] = [
                                    'attachment' => [
                                        'type' => 'template',
                                        'payload' => [
                                            'template_type' => 'media',
                                            'elements' => [
                                                [
                                                    'media_type' => 'image',
                                                    'attachment_id' => $attach['atid'],
                                                ]
                                            ]
                                        ]
                                    ]
                                ];
                            } else {
                                $skip = true;
                            }
                            break;
                    }

                    if($skip) continue;

                    FacebookRequestLogs::create([
                        'fb_request' => true,
                        'data' => json_encode($jsonData)
                    ]);

                    $recordChat = ProjectPageUserChat::create($recordChat);
                    
                    $this->execResponse($jsonData);

                    if($sleep>-1) {
                        sleep((int) $sleep);
                        $jsonData['sender_action'] = 'typing_off';
                        $this->execResponse($jsonData);
                    } else {
                        sleep(1);
                    }
                }
            }
        } catch (\Exception $e) {
            FacebookRequestLogs::create([
                'data' => json_encode([
                    'error' => true,
                    'data' => $e->getMessage(),
                    'raw' => $e
                ])
            ]);
        }
    }

    public function getImage($img)
    {
        $jsonData = [
            "message" => [
                "attachment" => [
                    "type" => "image",
                    "payload" => [
                        "is_reusable" => true,
                        "url" => $img
                    ]
                ]
            ]
        ];

        $ch = curl_init('https://graph.facebook.com/v3.2/me/message_attachments?access_token='.$this->token);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        $result = "failed";

        $data = [
            'status' => true,
            'atid' => null
        ];
        
        try {
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $result = json_decode($response, true); // user will get the message
            $data['atid'] = $result['attachment_id'];
        } catch(\Exception $e) {
            $data['status'] = false;
            $result = 'error: '. $e->getMessage();
            FacebookRequestLogs::create([
                'data' => json_encode([
                    'error' => true,
                    'data' => $e->getMessage()
                ])
            ]);
        }

        return $data;
    }

    public function execResponse($jsonData)
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        $result = "failed";
        
        try {
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $result = json_encode($header); // user will get the message
        } catch(\Exception $e) {
            $result = 'error: '. $e->getMessage();
        }

        
        FacebookRequestLogs::create([
            'fb_response' => true,
            'data' => $result
        ]);

        return $result;
    }

    public function sampleBot($input)
    {
        if (isset($input['entry'][0]['messaging'][0]['sender']['id'])) {
            $sender = $input['entry'][0]['messaging'][0]['sender']['id']; //sender facebook id
            $url = 'https://graph.facebook.com/v3.2/me/messages?access_token='.$this->token;
            $ch = curl_init($url);
            $myFile = "response";
        
            if(isset($input['entry'][0]['messaging'][0]['message']['text']) || isset($input['entry'][0]['messaging'][0]['postback']['title'])) {
                $message = isset($input['entry'][0]['messaging'][0]['postback']['title']) ? $input['entry'][0]['messaging'][0]['postback']['title'] :$input['entry'][0]['messaging'][0]['message']['text']; //text that user sent
        
                /*initialize curl*/
                /*prepare response*/
                $jsonData = [
                    "recipient" => [
                        "id" => $sender,
                    ],
                    "message" => [
                        "text" => "Hello from Pixy Bots. Your page setup with our bot is not complete otherwise you will get the default answer. By the way you said, $message"
                    ]
                ];
        
                /* curl setting to send a json post data */
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                if (!empty($message)) {
                    $result = "failed";
                    try {
                        $response = curl_exec($ch);
                        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $header = substr($response, 0, $header_size);
                        $result = json_encode($header); // user will get the message
                    } catch(\Exception $e) {
                        $result = 'error: '. $e->getMessage();
                    }
                    FacebookRequestLogs::create([
                        'data' => json_encode($result)
                    ]);
                }
            }
        }
    }
}
// @codeCoverageIgnoreEnd
