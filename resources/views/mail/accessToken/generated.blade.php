<x-mail::message>
# Hi {{ $accessToken['name'] }}

Your access token is: {{ $accessToken['token'] }} and it will expire on {{ $accessToken['expires_at'] }}.

You can use this token to access the Digital Assistant Chatbot between now and the expiration date.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
