<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use \Facebook\Facebook;

class FacebookController extends Controller
{
    private $fb;

    public function __construct()
    {
        
    }
}

//   $app_id = "YOUR_APP_ID";
//   $app_secret = "YOUR_APP_SECRET"; 
//   $my_url = "YOUR_POST_LOGIN_URL";
     
//   // known valid access token stored in a database 
//   $access_token = "YOUR_STORED_ACCESS_TOKEN";

//   $code = $_REQUEST["code"];
    
//   // If we get a code, it means that we have re-authed the user 
//   //and can get a valid access_token. 
//   if (isset($code)) {
//     $token_url="https://graph.facebook.com/oauth/access_token?client_id="
//       . $app_id . "&redirect_uri=" . urlencode($my_url) 
//       . "&client_secret=" . $app_secret 
//       . "&code=" . $code . "&display=popup";
//     $response = file_get_contents($token_url);
//     $params = null;
//     parse_str($response, $params);
//     $access_token = $params['access_token'];
//   }

        
//   // Attempt to query the graph:
//   $graph_url = "https://graph.facebook.com/me?"
//     . "access_token=" . $access_token;
//   $response = curl_get_file_contents($graph_url);
//   $decoded_response = json_decode($response);
    
//   //Check for errors 
//   if ($decoded_response->error) {
//   // check to see if this is an oAuth error:
//     if ($decoded_response->error->type== "OAuthException") {
//       // Retrieving a valid access token. 
//       $dialog_url= "https://www.facebook.com/dialog/oauth?"
//         . "client_id=" . $app_id 
//         . "&redirect_uri=" . urlencode($my_url);
//       echo("&lt;script> top.location.href='" . $dialog_url 
//       . "'&lt;/script>");
//     }
//     else {
//       echo "other error has happened";
//     }
//   } 
//   else {
//   // success
//     echo("success" . $decoded_response->name);
//     echo($access_token);
//   }

//   // note this wrapper function exists in order to circumvent PHP’s 
//   //strict obeying of HTTP error codes.  In this case, Facebook 
//   //returns error code 400 which PHP obeys and wipes out 
//   //the response.
//   function curl_get_file_contents($URL) {
//     $c = curl_init();
//     curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($c, CURLOPT_URL, $URL);
//     $contents = curl_exec($c);
//     $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
//     curl_close($c);
//     if ($contents) return $contents;
//     else return FALSE;
//   }