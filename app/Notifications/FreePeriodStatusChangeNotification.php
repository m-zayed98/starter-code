<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class FreePeriodStatusChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly bool $isEnabled,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $reasonAr = null,
        public readonly ?string $reasonEn = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        if ($this->isEnabled) {
            return [
                'title' => [
                    'ar' => 'بدء فترة مجانية',
                    'en' => 'Free Period Started',
                ],
                'body' => [
                    'ar' => $this->reasonAr ?? 'تم تفعيل فترة مجانية للتطبيق',
                    'en' => $this->reasonEn ?? 'A free period has been activated for the application',
                ],
                'start_date' => $this->startDate,
                'end_date'   => $this->endDate,
                'type'       => NotificationType::FREE_PERIOD_ENABLED->value,
            ];
        }

        return [
            'title' => [
                'ar' => 'انتهاء الفترة المجانية',
                'en' => 'Free Period Ended',
            ],
            'body' => [
                'ar' => 'تم إيقاف الفترة المجانية للتطبيق',
                'en' => 'The free period for the application has been disabled',
            ],
            'type' => NotificationType::FREE_PERIOD_DISABLED->value,
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        if ($this->isEnabled) {
            $title = $locale === 'ar' ? 'بدء فترة مجانية' : 'Free Period Started';
            $body  = $locale === 'ar'
                ? ($this->reasonAr ?? 'تم تفعيل فترة مجانية للتطبيق')
                : ($this->reasonEn ?? 'A free period has been activated for the application');

            return (new FcmMessage(notification: new FcmNotification(
                title: $title,
                body: $body,
            )))->data([
                'title'      => $title,
                'body'       => $body,
                'start_date' => (string) ($this->startDate ?? ''),
                'end_date'   => (string) ($this->endDate ?? ''),
                'type'       => NotificationType::FREE_PERIOD_ENABLED->value,
            ])->custom([
                'android' => [
                    'priority'     => 'high',
                    'notification' => [
                        'sound' => 'default',
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'free_period',
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
                        'analytics_label' => 'free_period',
                    ],
                ],
            ]);
        }

        $title = $locale === 'ar' ? 'انتهاء الفترة المجانية' : 'Free Period Ended';
        $body  = $locale === 'ar'
            ? 'تم إيقاف الفترة المجانية للتطبيق'
            : 'The free period for the application has been disabled';

        return (new FcmMessage(notification: new FcmNotification(
            title: $title,
            body: $body,
        )))->data([
            'title' => $title,
            'body'  => $body,
            'type'  => NotificationType::FREE_PERIOD_DISABLED->value,
        ])->custom([
            'android' => [
                'priority'     => 'high',
                'notification' => [
                    'sound' => 'default',
                ],
                'fcm_options' => [
                    'analytics_label' => 'free_period',
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
                    'analytics_label' => 'free_period',
                ],
            ],
        ]);
    }
}
