<?php

namespace App\Services\WhatsApp;

interface WhatsAppProviderInterface
{
    /**
     * Send a template-based WhatsApp message.
     *
     * @param string $to E.164 formatted phone number.
     * @param string $templateName Name or ID of the template.
     * @param string $languageCode Language code for the template (e.g., 'en').
     * @param array $parameters Dynamic parameters to inject into the template.
     * @return array Response payload including 'success', 'response', 'payload', 'error_message'
     */
    public function sendMessage(string $to, string $templateName, string $languageCode, array $parameters): array;

    /**
     * Test the validity of the credentials against the provider's API.
     *
     * @return array Includes 'success' and 'error_message'
     */
    public function testConnection(): array;
}

