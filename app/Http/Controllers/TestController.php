<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\Facebook\Webhook\ProcessWebhook;

use Facebook\Facebook;

class TestController extends Controller
{
    public function startQueue(Request $request)
    {
        $input = ['awef'];
        ProcessWebhook::dispatch($input);
    }

    public function getPSID(Request $request)
    {
        $token = config('facebook.defaultPageToken');
        $token = 'EAAQaj0N2ahcBAKju20PCbWB2MdZCOOmglIGZAew942X4tFyu3zus2wxFA8XZC0I9ca1WMOSWRFZBPOaiamWAGnk0qzgFjIpZAeumVvvpJ3adocG7OcTTK1ESQNnrMGKZC1ybaJnaMNHGkNu4IFi2NV9rVlpnWlAT7JfjIZAmgoKk94AOdoCUoAV';
        // $token = 'EAAQaj0N2ahcBAKtFYmhfadkadZCmL0lqMp9uFivGGD14nnAem15lGkBeLG6jGMO382p19ghrTRodyZCrOo4QZBdPLjrZC8tQD1bgaplABo47ZChIh02rMj7nW292A95dJFmZCz93hkWMgiZB2KjCK5Iwby1ZBZA8u08gN07vIIXZAAZA6OoNNItXJQn';
        // $token = config('facebook.appId').'|'.config('facebook.appSecret');
        $fb = new Facebook([
            'app_id' => config('facebook.appId'),
            'app_secret' => config('facebook.appSecret'),
            'default_graph_version' => config('facebook.graphVersion'),
            'default_access_token' => $token
        ]);

        $proof = hash_hmac('sha256', $token, config('facebook.appSecret'));

        $res = null;

        try {
            $res = $fb->post('/pages_id_mapping', [
                'user_ids' => '343922309521024',
                'appsecret_proof' => $proof,
                'access_token' => $token
            ])->getGraphObject()->asArray();
            // $res = $fb->get('/me')->getGraphObject()->asArray();
            // $res = $fb->get('/1945421455506191?fields=id,name,gender,ids_for_apps,ids_for_pages,ids_for_business&debug=all&page=298170577572248&appsecret_proof='.$proof, $token)->getGraphObject()->asArray();;
            // $res = $fb->get('/343922309521024/ids_for_pages?page=527626654371234&debug=all&appsecret_proof='.$proof)->getGraphEdge()->asArray();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        return response()->json([
            'success' => 'yes',
            'res' => $res
        ]);
    }
}
