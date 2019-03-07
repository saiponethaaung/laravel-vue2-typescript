<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return view('spa');
});
Route::get('/testplugin', function () {
    // return view('welcome');
    return '
    -a
<div class="fb-send-to-messenger" 
    messenger_app_id="1155102521322007" 
    page_id="386294088519239" 
    data-ref="8f14e45fceea167a5a36dedd4bea2543-386294088519239-343922309521024" 
    color="blue" 
    size="xlarge">
    Test this bot
</div>
a
<script>
window.fbAsyncInit = () => {
    FB.init({
        appId      : \'1155102521322007\',
        cookie     : true,
        xfbml      : true,
        version    : \'v3.2\'
    });

    console.log("Wet");
    
    // FB.AppEvents.logPageView(); 
    FB.Event.subscribe(\'send_to_messenger\', function(e) {
        console.log("event send to messenger", e);
    });
    
};

(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement(\'script\');
    js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));
</script>';
});

Route::get('/mid', 'TestController@getPSID');

Route::any('{any}/{all?}', function() {
    return view('spa');
})->where('all', '.+');