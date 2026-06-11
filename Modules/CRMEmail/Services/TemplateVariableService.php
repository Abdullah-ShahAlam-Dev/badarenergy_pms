<?php

namespace Modules\CRMEmail\Services;

use App\Models\User;
use App\Models\Lead;
use App\Models\ClientContact;

class TemplateVariableService
{
    /**
     * Get all available variables for email templates.
     *
     * @return array
     */
    public function getAvailableVariables(): array
    {
        return [
            '{{name}}' => 'Recipient Name',
            '{{company}}' => 'Recipient Company Name',
            '{{email}}' => 'Recipient Email Address',
            '{{city}}' => 'Recipient City',
            '{{country}}' => 'Recipient Country',
            '{{designation}}' => 'Recipient Designation / Title',
        ];
    }

    /**
     * Replace placeholders in template text with exact array data.
     *
     * @param string $content
     * @param array $data
     * @return string
     */
    public function render(string $content, array $data): string
    {
        $placeholders = [
            '{{name}}' => $data['name'] ?? '',
            '{{company}}' => $data['company'] ?? '',
            '{{email}}' => $data['email'] ?? '',
            '{{city}}' => $data['city'] ?? '',
            '{{country}}' => $data['country'] ?? '',
            '{{designation}}' => $data['designation'] ?? '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }

    /**
     * Render the template placeholders dynamically for a specific recipient model.
     *
     * @param string $content
     * @param mixed $recipient (User, Lead, or ClientContact)
     * @return string
     */
    public function renderForRecipient(string $content, $recipient): string
    {
        $data = [
            'name' => '',
            'email' => '',
            'company' => '',
            'city' => '',
            'country' => '',
            'designation' => '',
        ];

        if ($recipient instanceof User) {
            $data['name'] = $recipient->name;
            $data['email'] = $recipient->email;
            $data['country'] = $recipient->country->name ?? '';

            if ($recipient->clientDetails) {
                // Client user
                $data['company'] = $recipient->clientDetails->company_name ?? '';
                $data['city'] = $recipient->clientDetails->city ?? '';
                $data['designation'] = 'Client';
            } else {
                // Internal staff user
                $data['company'] = $recipient->company->company_name ?? '';
                $data['city'] = $recipient->employeeDetail->city ?? '';
                $data['designation'] = $recipient->employeeDetail->designation->name ?? '';
            }
        } elseif ($recipient instanceof Lead) {
            $data['name'] = $recipient->client_name;
            $data['email'] = $recipient->client_email;
            $data['company'] = $recipient->company_name ?? '';
            $data['city'] = $recipient->city ?? '';
            $data['country'] = $recipient->country ?? '';
            $data['designation'] = 'Lead';
        } elseif ($recipient instanceof ClientContact) {
            $data['name'] = $recipient->contact_name;
            $data['email'] = $recipient->email;
            $data['designation'] = $recipient->title ?? 'Contact';

            // Resolve company details from the client user they belong to
            if ($recipient->client) {
                $data['country'] = $recipient->client->country->name ?? '';
                if ($recipient->client->clientDetails) {
                    $data['company'] = $recipient->client->clientDetails->company_name ?? '';
                    $data['city'] = $recipient->client->clientDetails->city ?? '';
                }
            }
        }

        return $this->render($content, $data);
    }
}
