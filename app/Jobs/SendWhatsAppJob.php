<?php

namespace App\Jobs;

use App\Models\NotificationDelivery;
use App\Models\NotificationIntegration;
use App\Services\WhatsApp\WhatsAppManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    
    protected $deliveryId;
    protected $settingId;
    protected $to;
    protected $templateName;
    protected $languageCode;
    protected $parameters;

    public function __construct(
        int $deliveryId,
        int $settingId,
        string $to,
        string $templateName,
        string $languageCode,
        array $parameters
    ) {
        $this->deliveryId = $deliveryId;
        $this->settingId = $settingId;
        $this->to = $to;
        $this->templateName = $templateName;
        $this->languageCode = $languageCode;
        $this->parameters = $parameters;
        
        // Use separate queue for WhatsApp
        $this->onConnection('redis')->onQueue('notifications_whatsapp');
    }

    public function backoff()
    {
        return [60, 300, 900];
    }

    public function handle()
    {
        $delivery = NotificationDelivery::find($this->deliveryId);
        $setting = NotificationIntegration::find($this->settingId);

        if (!$delivery || !$setting || $setting->status !== 'active') {
            return;
        }

        try {
            $provider = WhatsAppManager::resolveProvider($setting);
            $result = $provider->sendMessage($this->to, $this->templateName, $this->languageCode, $this->parameters);

            $delivery->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'response' => json_encode($result['response'])
            ]);

            if (!$result['success']) {
                Log::warning("WhatsApp Delivery Failed for Task #{$delivery->task_id} to {$this->to}");
                // If it fails, throw exception to trigger retries
                throw new \Exception($result['error_message'] ?? 'Unknown WhatsApp API Error');
            }

        } catch (Throwable $e) {
            $delivery->update([
                'status' => 'failed',
                'response' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
