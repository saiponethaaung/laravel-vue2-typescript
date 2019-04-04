<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="/images/icons/favicon.png">

        <title>{{ env('APP_NAME') }}</title>
        <link href="{{ mix('css/app.css') }}" rel="stylesheet"/>
    </head>
    <body>
        <div id="app">
            <app ref="rootApp"></app>
        </div>
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
