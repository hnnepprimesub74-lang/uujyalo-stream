@php
    $whatsappNumber = \App\Models\AppSetting::get('whatsapp_number');
    $whatsappLink = $whatsappNumber ? 'https://wa.me/'.preg_replace('/\D/', '', $whatsappNumber) : null;
@endphp
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
@if ($whatsappLink)
<tr>
<td class="content-cell" align="center">
<a href="{{ $whatsappLink }}" class="whatsapp-link" target="_blank" rel="noopener">
&#9993; For any enquiry, contact us on WhatsApp
</a>
</td>
</tr>
@endif
<tr>
<td class="content-cell" align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
