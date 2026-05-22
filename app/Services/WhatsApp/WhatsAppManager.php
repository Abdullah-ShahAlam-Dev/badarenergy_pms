<?php

namespace App\Services\WhatsApp;

use App\Models\NotificationIntegration;
use App\Services\WhatsApp\Providers\MetaCloudProvider;
use App\Services\WhatsApp\Providers\TwilioWhatsAppProvider;
use Exception;

class WhatsAppManager
{
    /**
     * Resolves the active WhatsApp provider based on company settings.
     *
     * @param NotificationIntegration $setting
     * @return WhatsAppProviderInterface
     * @throws Exception
     */
    public static function resolveProvider(NotificationIntegration $setting): WhatsAppProviderInterface
    {
        $credentials = $setting->credentials;

        if ($setting->provider === 'meta_cloud') {
            if (empty($credentials['access_token']) || empty($credentials['phone_number_id'])) {
                throw new Exception("Meta Cloud credentials incomplete for company #{$setting->company_id}");
            }
            return new MetaCloudProvider($credentials['access_token'], $credentials['phone_number_id']);
        }

        if ($setting->provider === 'twilio') {
            if (empty($credentials['sid']) || empty($credentials['auth_token']) || empty($credentials['sender'])) {
                throw new Exception("Twilio credentials incomplete for company #{$setting->company_id}");
            }
            return new TwilioWhatsAppProvider($credentials['sid'], $credentials['auth_token'], $credentials['sender']);
        }

        throw new Exception("Unsupported WhatsApp provider: {$setting->provider}");
    }
}
