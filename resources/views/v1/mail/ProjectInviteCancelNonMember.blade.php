@component('mail::message')
Hi,<br/>
Your invitation to colleborate on <b>{{ $project }}</b> bot has been cancelled.<br/>
<br/>
Thanks,<br>
{{ config('app.name') }}
@endcomponent