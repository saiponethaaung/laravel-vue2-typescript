@component('mail::message')
Hi,<br/>

You are invited to colleborate on <b>{{ $project }}</b> bot.<br/>
Register with the link below to start.

@component('mail::button', ['url' => url('/register')])
Register
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent