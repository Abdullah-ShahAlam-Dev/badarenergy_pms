<?php

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;

class MetaCloudProvider implements WhatsAppProviderInterface
{
    protected $token;
    protected $phoneNumberId;

    public function __construct(string $token, string $phoneNumberId)
    {
        $this->token = $token;
        $this->phoneNumberId = $phoneNumberId;
    }

    public function sendMessage(string $to, string $templateName, string $languageCode, array $parameters): array
    {
        $url = "https://graph.facebook.com/v17.0/{$this->phoneNumberId}/messages";
        
        $formattedParams = [];
        foreach ($parameters as $value) {
            $formattedParams[] = ['type' => 'text', 'text' => $value];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => []
            ]
        ];

        // Meta requires components array only if there are parameters
        if (!empty($formattedParams)) {
            $payload['template']['components'][] = [
                'type' => 'body',
                'parameters' => $formattedParams
            ];
        }

        $response = Http::withToken($this->token)->post($url, $payload);
        
        return [
            'success' => $response->successful(),
            'payload' => $payload,
            'response' => $response->json(),
            'error_message' => $response->successful() ? null : ($response->json()['error']['message'] ?? 'Meta API error')
        ];
    }

    /**
     * Test the validity of the credentials against the Meta Graph API.
     */
    public function testConnection(): array
    {
        $url = "https://graph.facebook.com/v17.0/{$this->phoneNumberId}";
        try {
            $response = Http::withToken($this->token)->get($url);
            return [
                'success' => $response->successful(),
                'error_message' => $response->successful() ? null : ($response->json()['error']['message'] ?? 'Meta API credential verification failed.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }
}
