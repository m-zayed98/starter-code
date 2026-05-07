<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\NotificationGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class AdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly NotificationGroup $group
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_group_id' => $this->group->id,
            'title'                 => $this->group->getTranslations('title'),
            'body'                  => $this->group->getTranslations('body'),
            'type'                  => NotificationType::ADMIN_NOTIFICATION->value,
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        $title = $this->group->getTranslation('title', $locale);
        $body  = $this->group->getTranslation('body', $locale);

        return (new FcmMessage(notification: new FcmNotification(
            title: $title,
            body: $body,
        )))->data([
            'notification_group_id' => (string) $this->group->id,
            'title'                 => $title,
            'body'                  => $body,
            'type'                  => NotificationType::ADMIN_NOTIFICATION->value,
        ])->custom([
            'android' => [
                'priority'     => 'high',
                'notification' => [
                    'sound' => 'default',
                ],
                'fcm_options' => [
                    'analytics_label' => 'admin_notification',
                ],
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
                'fcm_options' => [
                    'analytics_label' => 'admin_notification',
                ],
            ],
        ]);
    }
}
