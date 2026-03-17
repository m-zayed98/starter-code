<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status === 'inactive' ? __('Account disabled') : __('Account activated') }}</title>
</head>
<body>
<p>{{ __('Hello') }} {{ $user->name }},</p>

@if ($status === 'inactive')
    <p>{{ __('Your account has been disabled. Please contact the administration.') }}</p>
    @if (!empty($reason))
        <p>{{ __('Reason:') }} {{ $reason }}</p>
    @endif
@else
    <p>{{ __('Your account has been activated. You can login now.') }}</p>
@endif

<p>{{ __('Thank you') }}</p>
</body>
</html>

