@component('vendor.mail.html.message')

Hello,</br></br>

Scan the qr code below with google authenticator or other 3rd party OTP QR scanner to use OTP to access an account.</br></br></br>
<p style="text-align: center">
    <img src="{{ $qrImage }}"/>
</p>
Click <a href="{{ url('/') }}">here</a> to login<br/>
Click the link below to download Google Authenticator for android or ios.<br/>
<a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=e">Android</a>
<a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8">IOS</a>
<br/><br/>
Thanks,<br>
{{ config('app.name') }}
@endcomponent