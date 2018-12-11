<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\FacebookRequestLogs;

class FacebookChatbotController extends Controller
{
    public function index(Request $request) {
        FacebookRequestLogs::create([
            'data' => json_encode([
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

    }
}
