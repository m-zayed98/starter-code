<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Reply to your ad report') }}</title>
</head>
<body>
<p>{{ __('Hello') }} {{ $report->user->name }},</p>

<p>{{ __('We received your report about the ad:') }} <strong>{{ $report->ad->title }}</strong></p>

<p>{{ __('Your report reason:') }}</p>
<blockquote>
    {{ $report->reason }}
</blockquote>

<p>{{ __('Our reply:') }}</p>
<blockquote>
    {{ $report->reply }}
</blockquote>

<p>{{ __('Thank you') }}</p>
</body>
</html>
