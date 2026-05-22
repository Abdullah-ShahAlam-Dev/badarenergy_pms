<?php

namespace App\Services;

use App\Models\Company;
use App\Models\NotificationIntegration;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Cache;

class NotificationChannelResolver
{
    /**
     * Resolves and caches whether WhatsApp should be enabled for a given event and company.
     * Cache duration is 60 minutes to prevent N+1 queries during bulk notifications.
     */
    public static function isWhatsAppEnabled(Company $company, string $eventName): bool
    {
        $cacheKey = "whatsapp_enabled_company_{$company->id}_{$eventName}";

        return Cache::remember($cacheKey, 60, function () use ($company, $eventName) {
            $setting = NotificationIntegration::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->first();

            if (!$setting) {
                return false;
            }

            $template = NotificationTemplate::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('event_name', $eventName)
                ->where('is_active', true)
                ->first();

            return $template !== null;
        });
    }
}
