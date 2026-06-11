<?php

namespace Modules\CRMEmail\Mail;

use App\Models\SmtpSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\CRMEmail\Entities\CrmEmailSetting;

class CampaignMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Number of times to attempt sending.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Backoff delays in seconds between retries.
     *
     * @var array
     */
    public $backoff = [30, 60, 120];

    /**
     * Pre-rendered email subject (placeholders already resolved).
     *
     * @var string
     */
    public string $emailSubject;

    /**
     * Pre-rendered HTML body (placeholders already resolved).
     *
     * @var string
     */
    public string $htmlBody;

    /**
     * Sender display name override from the template.
     *
     * @var string|null
     */
    public ?string $senderName;

    /**
     * Sender email override from the template.
     *
     * @var string|null
     */
    public ?string $senderEmail;

    /**
     * Optional unsubscribe URL to inject into the email footer.
     *
     * @var string|null
     */
    public ?string $unsubscribeUrl;

    /**
     * The company_id owning this campaign — used to resolve the DB settings row.
     *
     * @var int
     */
    public int $companyId;

    /**
     * Create a new CampaignMailable instance.
     *
     * @param string      $subject        Pre-rendered subject
     * @param string      $htmlBody       Pre-rendered HTML body
     * @param string|null $senderName     Template from_name override (may be null)
     * @param string|null $senderEmail    Template from_email override (may be null)
     * @param string|null $unsubscribeUrl One-click unsubscribe URL for the footer
     * @param int         $companyId      Company ID for resolving module settings
     */
    public function __construct(
        string $subject,
        string $htmlBody,
        ?string $senderName = null,
        ?string $senderEmail = null,
        ?string $unsubscribeUrl = null,
        int $companyId = 0
    ) {
        $this->emailSubject   = $subject;
        $this->htmlBody       = $htmlBody;
        $this->senderName     = $senderName;
        $this->senderEmail    = $senderEmail;
        $this->unsubscribeUrl = $unsubscribeUrl;
        $this->companyId      = $companyId;
    }

    /**
     * Build the Mailable.
     *
     * Sender resolution priority (highest → lowest):
     *   1. Template-level from_name / from_email (set per campaign template)
     *   2. CRM Email Module Settings (crm_email_settings DB row for the company)
     *   3. System SmtpSetting (mail_from_name / mail_from_email)
     *   4. Laravel config mail.from.* as ultimate fallback
     *
     * NEVER uses Config::set() — sender is applied on this Mailable instance only.
     *
     * @return $this
     */
    public function build()
    {
        // --- Priority 3: System SMTP settings (queue-safe: query directly, never session helper) ---
        $smtpSetting = SmtpSetting::first();

        // --- Priority 2: CRM Email module DB settings ---
        $crmSetting = CrmEmailSetting::withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->where('company_id', $this->companyId)
            ->first();

        // Resolve final sender: template override → CRM settings → SmtpSetting → Laravel config
        $resolvedSenderName  = $this->senderName
            ?: ($crmSetting?->from_name  ?: ($smtpSetting?->mail_from_name  ?? config('mail.from.name')));

        $resolvedSenderEmail = $this->senderEmail
            ?: ($crmSetting?->from_email ?: ($smtpSetting?->mail_from_email ?? config('mail.from.address')));

        if (config('mail.verified') === true) {
            // When mail is verified: the SMTP "From" must be the system address.
            // The template's configured sender becomes the Reply-To.
            $this->from($smtpSetting->mail_from_email, $resolvedSenderName)
                 ->replyTo($resolvedSenderEmail, $resolvedSenderName);
        } else {
            // No verified domain: send directly from the resolved sender.
            $this->from($resolvedSenderEmail, $resolvedSenderName)
                 ->replyTo($resolvedSenderEmail, $resolvedSenderName);
        }

        return $this->subject($this->emailSubject)
                    ->view('crmemail::mail.campaign', [
                        'htmlBody'       => $this->htmlBody,
                        'unsubscribeUrl' => $this->unsubscribeUrl,
                    ]);
    }
}

