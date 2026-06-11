<?php

namespace Modules\CRMEmail\Jobs;

use App\Models\ClientContact;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\CRMEmail\Entities\Campaign;
use Modules\CRMEmail\Entities\CampaignEmail;
use Modules\CRMEmail\Entities\InvalidEmail;
use Modules\CRMEmail\Mail\CampaignMailable;
use Modules\CRMEmail\Services\TemplateVariableService;
use Throwable;

class SendCampaignEmailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum delivery attempts before the job is considered permanently failed.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Backoff delay in seconds between retry attempts.
     *
     * @var array
     */
    public $backoff = [30, 60, 120];

    /**
     * The ID of the crm_campaign_emails record to process.
     *
     * @var int
     */
    protected int $campaignEmailId;

    /**
     * Create a new job instance.
     *
     * @param int $campaignEmailId The primary key of the crm_campaign_emails row
     */
    public function __construct(int $campaignEmailId)
    {
        $this->campaignEmailId = $campaignEmailId;
    }

    /**
     * Execute the job.
     *
     * Resolves the CampaignEmail record, renders placeholders for the specific
     * recipient, and dispatches the Mailable. Updates the log record accordingly.
     *
     * @param TemplateVariableService $variableService
     * @return void
     */
    public function handle(TemplateVariableService $variableService): void
    {
        // If the batch was cancelled, exit early without processing.
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        /** @var CampaignEmail|null $log */
        $log = CampaignEmail::find($this->campaignEmailId);

        if (!$log) {
            Log::warning("CRMEmail: CampaignEmail record #{$this->campaignEmailId} not found. Skipping.");
            return;
        }

        // Skip if already sent (idempotency guard against retries causing duplicates).
        if ($log->status === 'sent') {
            return;
        }

        $log->increment('attempts');

        /** @var Campaign $campaign */
        $campaign = Campaign::find($log->campaign_id);

        if (!$campaign || !in_array($campaign->status, ['sending', 'scheduled'])) {
            Log::info("CRMEmail: Campaign #{$log->campaign_id} is no longer active. Aborting email to {$log->email}.");
            return;
        }

        // Resolve the recipient model for placeholder rendering.
        $recipient = $this->resolveRecipient($log->recipient_type, $log->recipient_id);

        // Resolve template details: subject, body, from overrides.
        $template = $campaign->template;

        $rawSubject = $campaign->name; // Default subject is campaign name
        $rawBody    = $campaign->email_body;
        $fromName   = null;
        $fromEmail  = null;

        if ($template) {
            $fromName  = $template->from_name  ?? null;
            $fromEmail = $template->from_email ?? null;
        }

        // Render placeholders.
        if ($recipient) {
            $renderedSubject = $variableService->renderForRecipient($rawSubject, $recipient);
            $renderedBody    = $variableService->renderForRecipient($rawBody, $recipient);
        } else {
            // Fallback: render with raw email if model not found (e.g. deleted lead).
            $renderedSubject = $rawSubject;
            $renderedBody    = $rawBody;
        }

        // Build the unsubscribe URL.
        $unsubscribeUrl = route('crm-email.unsubscribe-form', [
            'email'       => urlencode($log->email),
            'campaign_id' => $log->campaign_id,
        ]);

        try {
            // Build and send the Mailable — all sender details are applied on the instance.
            $mailable = new CampaignMailable(
                $renderedSubject,
                $renderedBody,
                $fromName,
                $fromEmail,
                $unsubscribeUrl
            );

            // Capture the message ID for delivery tracking.
            $messageId = null;
            $mailable->withSymfonyMessage(function ($message) use (&$messageId) {
                $messageId = $message->getId();
            });

            Mail::to($log->email)->send($mailable);

            // Update the log record to reflect successful delivery.
            $log->update([
                'status'     => 'sent',
                'sent_at'    => now(),
                'message_id' => $messageId,
                'error_message' => null,
            ]);

        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();

            Log::error("CRMEmail: Failed to send to {$log->email} (Campaign #{$log->campaign_id}). Error: {$errorMessage}");

            // Determine if the failure indicates a permanently bad email address.
            if ($this->isPermanentFailure($errorMessage)) {
                // Mark email as permanently invalid to exclude from future campaigns.
                InvalidEmail::firstOrCreate(
                    ['email'      => strtolower(trim($log->email)), 'company_id' => $log->company_id],
                    ['reason'     => 'bounce', 'error_message' => $errorMessage]
                );

                $log->update([
                    'status'        => 'failed',
                    'error_message' => 'Permanent failure: ' . $errorMessage,
                ]);

                // Do NOT re-throw — permanent failures should not block the batch.
                return;
            }

            // Transient failure — update the record and re-throw to trigger a retry.
            $log->update([
                'status'        => 'failed',
                'error_message' => $errorMessage,
                'next_retry_at' => now()->addSeconds($this->backoff[$this->attempts() - 1] ?? 120),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job that has exhausted all retry attempts.
     *
     * @param Throwable $e
     * @return void
     */
    public function failed(Throwable $e): void
    {
        $log = CampaignEmail::find($this->campaignEmailId);

        if ($log && $log->status !== 'sent') {
            $log->update([
                'status'        => 'failed',
                'error_message' => 'Max attempts reached: ' . $e->getMessage(),
                'next_retry_at' => null,
            ]);

            Log::error("CRMEmail: CampaignEmail #{$this->campaignEmailId} permanently failed after all retries.");
        }
    }

    /**
     * Resolve the Eloquent recipient model from its polymorphic type and ID.
     *
     * @param string $type  'client' | 'lead' | 'contact'
     * @param int    $id
     * @return User|Lead|ClientContact|null
     */
    private function resolveRecipient(string $type, int $id)
    {
        return match ($type) {
            'client'  => User::with(['clientDetails', 'country'])->find($id),
            'lead'    => Lead::find($id),
            'contact' => ClientContact::with(['client.clientDetails', 'client.country'])->find($id),
            default   => null,
        };
    }

    /**
     * Determine whether the exception indicates a permanent, non-retryable failure.
     *
     * @param string $errorMessage
     * @return bool
     */
    private function isPermanentFailure(string $errorMessage): bool
    {
        $permanentKeywords = [
            '550',   // Mailbox does not exist
            '551',   // User not local
            '553',   // Mailbox name not allowed
            '554',   // Transaction failed / spam
            'user unknown',
            'no such user',
            'does not exist',
            'invalid address',
            'undeliverable',
            'address rejected',
        ];

        $lower = strtolower($errorMessage);

        foreach ($permanentKeywords as $keyword) {
            if (str_contains($lower, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
