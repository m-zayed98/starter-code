<?php

use App\Models\NafathVerificationRequest;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Private channel for Nafath verification events.
 *
 * Only the user who owns the verification request may subscribe.
 * Channel: private-nafath.{nafath_request_id}
 */
Broadcast::channel('nafath.{nafathRequestId}', function ($user, int $nafathRequestId) {
    $request = NafathVerificationRequest::find($nafathRequestId);

    return $request !== null && (int) $request->user_id === (int) $user->id;
});
