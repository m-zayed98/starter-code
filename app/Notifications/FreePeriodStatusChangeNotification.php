<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

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
        return ['database'];
        // TODO: Add fcm channel when ready
        // return ['database', FcmChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        if ($this->isEnabled) {
            $titleAr = 'بدء فترة مجانية';
            $titleEn = 'Free Period Started';
            $bodyAr = $this->reasonAr ?? 'تم تفعيل فترة مجانية للتطبيق';
            $bodyEn = $this->reasonEn ?? 'A free period has been activated for the application';

            return [
                'title' => [
                    'ar' => $titleAr,
                    'en' => $titleEn,
                ],
                'body' => [
                    'ar' => $bodyAr,
                    'en' => $bodyEn,
                ],
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'type' => NotificationType::FREE_PERIOD_ENABLED->value,
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

    // Uncomment when FCM is ready
    /*
    public function toFcm(object $notifiable): FcmMessage
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        if ($this->isEnabled) {
            $title = $locale === 'ar' ? 'بدء فترة مجانية' : 'Free Period Started';
            $body = $locale === 'ar' 
                ? ($this->reasonAr ?? 'تم تفعيل فترة مجانية للتطبيق')
                : ($this->reasonEn ?? 'A free period has been activated for the application');

            return FcmMessage::create()
                ->setData([
                    'title' => $title,
                    'body' => $body,
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'type' => NotificationType::FREE_PERIOD_ENABLED->value,
                ]);
        }

        $title = $locale === 'ar' ? 'انتهاء الفترة المجانية' : 'Free Period Ended';
        $body = $locale === 'ar' 
            ? 'تم إيقاف الفترة المجانية للتطبيق'
            : 'The free period for the application has been disabled';

        return FcmMessage::create()
            ->setData([
                'title' => $title,
                'body' => $body,
                'type' => NotificationType::FREE_PERIOD_DISABLED->value,
            ]);
    }
    */
}
