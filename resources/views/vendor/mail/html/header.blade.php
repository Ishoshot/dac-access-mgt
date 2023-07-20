@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@elseif (trim($slot) === 'Digital Assistant Chatbot')
<img src="https://www.dipolediamond.com/wp-content/uploads/2019/04/ddlogo.png" width="150" class=""
alt="DAC Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
