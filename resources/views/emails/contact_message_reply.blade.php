<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Reply to your message') }}</title>
</head>
<body>
<p>{{ __('Hello') }} {{ $contactMessage->name }},</p>

<p>{{ __('We received your message:') }}</p>
<blockquote>
    {{ $contactMessage->message }}
</blockquote>

<p>{{ __('Our reply:') }}</p>
<blockquote>
    {{ $contactMessage->reply }}
</blockquote>

<p>{{ __('Thank you') }}</p>
</body>
</html>

