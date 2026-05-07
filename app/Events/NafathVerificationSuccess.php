<?php

namespace App\Events;

use App\Models\NafathVerificationRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NafathVerificationSuccess implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly NafathVerificationRequest $verificationRequest,
    ) {}

    /**
     * Broadcast on a private channel scoped to the verification request.
     * Channel name: private-nafath.{nafath_request_id}
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('nafath.' . $this->verificationRequest->id);
    }

    public function broadcastAs(): string
    {
        return 'VerificationSuccess';
    }

    public function broadcastWith(): array
    {
        return [
            'nafath_request_id' => $this->verificationRequest->id,
            'status'            => $this->verificationRequest->status->value,
        ];
    }
}
