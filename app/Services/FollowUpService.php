<?php

namespace App\Services;

use App\Models\Booking;

class FollowUpService
{
    public function schedule(Booking $booking): array
    {
        $slot = $booking->slot_datetime;

        return [
            'booking_ref' => $booking->booking_ref,
            'schedule' => [
                [
                    'event' => 'reminder_1h',
                    'trigger_at' => $slot->copy()->subHour()->toDateTimeString(),
                    'type' => 'reminder',
                    'payload' => $this->reminderPayload($booking, '1 hour'),
                ],
                [
                    'event' => 'en_route_notification',
                    'trigger_at' => $slot->copy()->subMinutes(15)->toDateTimeString(),
                    'type' => 'en_route',
                    'payload' => $this->enRoutePayload($booking),
                ],
                [
                    'event' => 'completion_checklist',
                    'trigger_at' => $slot->copy()->addHours(2)->toDateTimeString(),
                    'type' => 'completion',
                    'payload' => $this->completionPayload($booking),
                ],
            ],
        ];
    }

    private function reminderPayload(Booking $booking, string $timeLabel): array
    {
        $booking->loadMissing(['provider', 'serviceRequest']);

        return [
            'english' => "Reminder: Your {$booking->serviceRequest?->service_type} appointment with {$booking->provider?->name} is in {$timeLabel}. Ref: {$booking->booking_ref}",
            'urdu' => "یاد دہانی: آپ کی {$booking->serviceRequest?->service_type} اپائنٹمنٹ {$timeLabel} میں ہے۔ حوالہ: {$booking->booking_ref}",
        ];
    }

    private function enRoutePayload(Booking $booking): array
    {
        $booking->loadMissing(['provider']);

        return [
            'english' => "Your provider {$booking->provider?->name} is on the way! They should arrive in ~15 minutes.",
            'urdu' => "آپ کے {$booking->provider?->name} راستے میں ہیں! تقریباً 15 منٹ میں پہنچیں گے۔",
        ];
    }

    private function completionPayload(Booking $booking): array
    {
        return [
            'checklist' => [
                'service_completed' => false,
                'customer_satisfied' => false,
                'payment_collected' => false,
                'photos_uploaded' => false,
            ],
            'english' => "Service completed? Please confirm and rate your experience.",
            'urdu' => "کیا سروس مکمل ہو گئی؟ براہ کرم تصدیق کریں اور اپنا تجربہ ریٹ کریں۔",
        ];
    }
}
