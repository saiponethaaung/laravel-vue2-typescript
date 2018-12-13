<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\FacebookRequestLogs;

use App\Models\ProjectPage;

class FacebookChatbotController extends Controller
{
    private $token = "EAAQaj0N2ahcBAK1DRSng7KgrBZAuLk1KZAioCAGcxd8YNZCTqg7LD4U9N30b9sVJRDexEXZCjlVhHwGpgBt6lIHjHUk0ToNQiZAR9GRlBo08SPtbepyUsW3iBJyfoPg0fMnYBJIJfxptN0hAPWxmKEyri7LrF9nYsQ8HujrISeClZAQoBDro8s";
    public function index(Request $request) {
        $input = json_decode(file_get_contents('php://input'), true);

        if($input['object']!=='page') {
            return null;
        }

        $projectPage = ProjectPage::where('page_id', $input['entry'][0]['id'])->first();

        if(empty($projectPage) || is_null($projectPage->project_id)) {
            return null;
        }
        
        $this->token = $projectPage->token;

        FacebookRequestLogs::create([
            'data' => json_encode([
                'raw' => $input,
                'get' => $_GET,
                'post' => $_POST,
                'header' => $_SERVER
            ])
        ]);

        if (isset($_GET['hub_verify_token'])) { 
            if ($_GET['hub_verify_token'] === '$2y$12$uyP735FKW7vuSYmlAEhF/OOoo1vCaWZN7zIEeFEhYbAw2qv8X4ffe') {
                echo $_GET['hub_challenge'];
                return;
            } else {
                echo 'Invalid Verify Token';
                return;
            }
        }

        $this->sampleBot($input);
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
                        "text" => "You said, $message"
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
                        $result = 'from facebook '. json_encode($header); // user will get the message
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
