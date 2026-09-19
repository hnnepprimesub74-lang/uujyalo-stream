@component('mail::message')
# {{ $campaign->subject }}

{!! Illuminate\Mail\Markdown::parse($campaign->email_body) !!}

@component('mail::subcopy')
Don't want these emails? [Unsubscribe]({{ $unsubscribeUrl }}).
@endcomponent
@endcomponent
