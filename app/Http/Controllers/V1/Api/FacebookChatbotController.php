<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\FacebookRequestLogs;

use App\Models\ProjectPage;
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
            'data' => 'success'
        ]);
        
        return;
    }

    public function processWebHook($input)
    {
        if($input['object']!=='page') {
            return null;
        }

        $projectPage = ProjectPage::where('page_id', $input['entry'][0]['id'])->first();

        if(empty($projectPage) || is_null($projectPage->project_id)) {
            return null;
        }
        
        $this->token = $projectPage->token;
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
        
        // $log = FacebookRequestLogs::create([
        //     'data' => json_encode([
        //         'raw' => $input,
        //         'get' => $_GET,
        //         'post' => $_POST,
        //     ])
        // ]);

        // Read payload
        if(isset($input['entry'][0]['messaging'][0]['read'])) {
            // $log->is_read = true;
            // $log->save();
            return;
        }
        
        // deliver payload
        if(isset($input['entry'][0]['messaging'][0]['delivery'])) {
            // $log->is_deliver = true;
            // $log->save();
            return;
        }

        if (isset($input['entry'][0]['messaging'][0]['sender']['id'])) {
            if(
                isset($input['entry'][0]['messaging'][0]['message']['text'])
                // isset($input['entry'][0]['messaging'][0]['postback']['title'])
            ) {

                // echo payload
                if(isset($input['entry'][0]['messaging'][0]['message']['is_echo'])) {
                    // $log->is_echo = true;
                    // $log->save();
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
                    $messages = $project->process($input);
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
                    foreach($messages['data'] as $mesg) {

                        $jsonData = [
                            "recipient" => [
                                "id" => $sender,
                            ]
                        ];

                        $sleep = -1;

                        if($mesg['type']==2) {
                            continue;
                        }

                        switch($mesg['type']) {
                            case(1):
                            case(5):
                                $jsonData['message'] = $mesg['data'];
                                break;

                            case(2):
                                $jsonData['sender_action'] = 'typing_on';
                                $sleep = $mesg['duration'];
                                break;
                        }

                        FacebookRequestLogs::create([
                            'fb_request' => true,
                            'data' => json_encode($jsonData)
                        ]);
                        
                        $this->execResponse($jsonData);

                        if($sleep>-1) {
                            sleep((int) $sleep);
                            $jsonData['sender_action'] = 'typing_off';
                            $this->execResponse($jsonData);
                        } else {
                            sleep(1);
                        }
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
        }
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
