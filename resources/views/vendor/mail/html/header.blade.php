@props(['url'])
@php
    $logoPath = \App\Models\AppSetting::get('email_logo');
    $logoUrl = $logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ config('app.name') }}">
@else
<span class="logo-badge">{{ \Illuminate\Support\Str::of(config('app.name'))->substr(0, 1) }}</span>
{{ $slot }}
@endif
</a>
</td>
</tr>
