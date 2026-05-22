<?php

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;

class TwilioWhatsAppProvider implements WhatsAppProviderInterface
{
    protected $sid;
    protected $authToken;
    protected $sender;

    public function __construct(string $sid, string $authToken, string $sender)
    {
        $this->sid = $sid;
        $this->authToken = $authToken;
        $this->sender = $sender;
    }

    public function sendMessage(string $to, string $templateName, string $languageCode, array $parameters): array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
        
        // Twilio handles WhatsApp templates by sending a message body that perfectly matches 
        // the approved template structure. For robustness, if using Twilio Content API, it requires ContentSid.
        // Assuming standard Twilio Programmable Messaging for WhatsApp where Body must match the approved template:
        $body = "Template: {$templateName}. Params: " . implode(', ', $parameters);

        $payload = [
            'From' => 'whatsapp:' . $this->sender,
            'To' => 'whatsapp:' . $to,
            'Body' => $body
        ];

        $response = Http::withBasicAuth($this->sid, $this->authToken)
            ->asForm()
            ->post($url, $payload);

        return [
            'success' => $response->successful(),
            'payload' => $payload,
            'response' => $response->json(),
            'error_message' => $response->successful() ? null : ($response->json()['message'] ?? 'Twilio API error')
        ];
    }

    /**
     * Test the validity of the credentials against the Twilio REST API.
     */
    public function testConnection(): array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}.json";
        try {
            $response = Http::withBasicAuth($this->sid, $this->authToken)->get($url);
            return [
                'success' => $response->successful(),
                'error_message' => $response->successful() ? null : ($response->json()['message'] ?? 'Twilio SID or Auth Token is invalid.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }
}
