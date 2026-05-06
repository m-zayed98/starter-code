<?php

namespace App\Services;

use App\Http\Requests\Admin\UpdateGeneralSettingRequest;
use App\Models\User;
use App\Notifications\FreePeriodStatusChangeNotification;
use App\Settings\GeneralSetting;

class GeneralSettingService
{
    public function __construct(
        private GeneralSetting $generalSetting
    ) {}

    public function getSettings(): array
    {
        return [
            'is_free_period_enabled' => $this->generalSetting->is_free_period_enabled,
            'free_period_start_date' => $this->generalSetting->free_period_start_date,
            'free_period_end_date' => $this->generalSetting->free_period_end_date,
            'free_period_reason_ar' => $this->generalSetting->free_period_reason_ar,
            'free_period_reason_en' => $this->generalSetting->free_period_reason_en,
        ];
    }

    public function getPublicSettings(): array
    {
        $locale = app()->getLocale();

        $reason = $locale === 'ar'
            ? $this->generalSetting->free_period_reason_ar
            : $this->generalSetting->free_period_reason_en;

        return [
            'is_free_period_enabled' => $this->generalSetting->is_free_period_enabled,
            'free_period_start_date' => $this->generalSetting->free_period_start_date,
            'free_period_end_date' => $this->generalSetting->free_period_end_date,
            'free_period_reason' => $reason,
        ];
    }

    public function updateSettings(UpdateGeneralSettingRequest $request): void
    {
        $previousStatus = $this->generalSetting->is_free_period_enabled;
        $newStatus = $request->boolean('is_free_period_enabled');

        $this->generalSetting->is_free_period_enabled = $newStatus;
        $this->generalSetting->free_period_start_date = $request->input('free_period_start_date');
        $this->generalSetting->free_period_end_date = $request->input('free_period_end_date');
        $this->generalSetting->free_period_reason_ar = $request->input('free_period_reason_ar');
        $this->generalSetting->free_period_reason_en = $request->input('free_period_reason_en');

        $this->generalSetting->save();

        // Send notification to all users if status changed
        if ($previousStatus !== $newStatus) {
            $this->notifyUsers(
                $newStatus,
                $request->input('free_period_start_date'),
                $request->input('free_period_end_date'),
                $request->input('free_period_reason_ar'),
                $request->input('free_period_reason_en')
            );
        }
    }

    private function notifyUsers(
        bool $isEnabled,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $reasonAr = null,
        ?string $reasonEn = null
    ): void {
        $notification = new FreePeriodStatusChangeNotification(
            $isEnabled,
            $startDate,
            $endDate,
            $reasonAr,
            $reasonEn
        );

        User::query()
            ->where('status', 'active')
            ->chunk(100, function ($users) use ($notification) {
                foreach ($users as $user) {
                    $user->notify($notification);
                }
            });
    }
}
