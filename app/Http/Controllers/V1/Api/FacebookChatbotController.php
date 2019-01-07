<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ProjectPage;
use App\Models\ProjectPageUser;
use App\Models\ProjectPageUserChat;
use App\Models\FacebookRequestLogs;

use App\Http\Controllers\V1\Api\ChatBotProjectController;

use App\Jobs\Facebook\Webhook\ProcessWebhook;

class FacebookChatbotController extends Controller
{
    private $token = "EAAQaj0N2ahcBAK1DRSng7KgrBZAuLk1KZAioCAGcxd8YNZCTqg7LD4U9N30b9sVJRDexEXZCjlVhHwGpgBt6lIHjHUk0ToNQiZAR9GRlBo08SPtbepyUsW3iBJyfoPg0fMnYBJIJfxptN0hAPWxmKEyri7LrF9nYsQ8HujrISeClZAQoBDro8s";
    private $url = '';
    
    public function index(Request $request)
    {
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

        if(!empty($projectPage) && is_null($projectPage->project_id)==false) {
            ProcessWebhook::dispatch($input);
        } else {
            $this->sampleBot($input);
        }

        FacebookRequestLogs::create([
            'data' => json_encode([
                'status' => 'success',
                'data' => $input
            ])
        ]);
        
        return;
    }

    public function processWebHook($input)
    {
        if($input['object']!=='page') {
            return null;
        }

        $projectPage = null;

        if($input['entry'][0]['id']===config('facebook.defaultPageId')) {
            // if(ProjectPageUser::)
            $projectPage = ProjectPage::where('page_id', $input['entry'][0]['id'])->first();
        } else {
            $projectPage = ProjectPage::where('page_id', $input['entry'][0]['id'])->first();
        }

        if(empty($projectPage) || is_null($projectPage->project_id)) {
            return null;
        }
        
        $this->token = $projectPage->token ? $projectPage->token : config('facebook.defaultPageToken');
        $this->url = 'https://graph.facebook.com/v3.2/me/messages?access_token='.$this->token;

        try {
            if(is_null($projectPage->project_id)) {
                $this->sampleBot($input);
            } else {
                $this->parseMessage($projectPage->project_id, $input);
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

    public function parseMessage($projectId, $input) {
        
        $log = FacebookRequestLogs::create([
            'data' => json_encode([
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

        if (isset($input['entry'][0]['messaging'][0]['sender']['id'])) {
            if(
                isset($input['entry'][0]['messaging'][0]['message']['text']) ||
                isset($input['entry'][0]['messaging'][0]['postback'])
            ) {
                
                $isPostBack = isset($input['entry'][0]['messaging'][0]['postback']);

                // echo payload
                if(isset($input['entry'][0]['messaging'][0]['message']['is_echo'])) {
                    $log->is_echo = true;
                    $log->save();
                    return;
                }

                $log = FacebookRequestLogs::create([
                    'data' => json_encode([
                        'raw' => $input,
                        'get' => $_GET,
                        'post' => $_POST,
                    ])
                ]);

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
                    $messages = $project->process($input, $isPostBack);

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
                    
                    foreach($messages['data'] as $mesg) {

                        if($mesg['status']===false) continue;

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
                            'mesg' => '',
                            'mesg_id' => '',
                            'project_page_user_id' => $messages['userid'],
                            'is_send' => true,
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
                            'raw' => $e
                        ])
                    ]);
                }
            }
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
