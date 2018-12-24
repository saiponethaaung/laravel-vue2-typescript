<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
        <link href="{{ mix('css/app.css') }}" rel="stylesheet"/>
    </head>
    <body>
        <div id="app">
            <app ref="rootApp"></app>
        </div>
        <script src="{{ mix('js/app.js') }}"></script>
        {{-- <script>
            window.fbAsyncInit = function() {
                FB.init({
                    appId      : '1155102521322007',
                    cookie     : true,
                    xfbml      : true,
                    version    : 'v3.2'
                });
                window.fbSdkLoaded = true;
                console.log('facebook loaded');
                
                FB.AppEvents.logPageView();   
            };
            
            (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
        </script>  --}}
    </body>
</html>
