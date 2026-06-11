<?php

namespace Modules\CRMEmail\Mail;

use App\Models\SmtpSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
     * Create a new CampaignMailable instance.
     *
     * @param string      $subject        Pre-rendered subject
     * @param string      $htmlBody       Pre-rendered HTML body
     * @param string|null $senderName     Template from_name override (may be null)
     * @param string|null $senderEmail    Template from_email override (may be null)
     * @param string|null $unsubscribeUrl One-click unsubscribe URL for the footer
     */
    public function __construct(
        string $subject,
        string $htmlBody,
        ?string $senderName = null,
        ?string $senderEmail = null,
        ?string $unsubscribeUrl = null
    ) {
        $this->emailSubject   = $subject;
        $this->htmlBody       = $htmlBody;
        $this->senderName     = $senderName;
        $this->senderEmail    = $senderEmail;
        $this->unsubscribeUrl = $unsubscribeUrl;
    }

    /**
     * Build the Mailable.
     *
     * Resolves sender details directly from the SmtpSetting DB record.
     * NEVER uses Config::set() — sender is applied on this Mailable instance only.
     *
     * @return $this
     */
    public function build()
    {
        // Query SmtpSetting directly — cannot use smtp_setting() session helper in queue context.
        $smtpSetting = SmtpSetting::first();

        $resolvedSenderName  = $this->senderName  ?: ($smtpSetting->mail_from_name  ?? config('mail.from.name'));
        $resolvedSenderEmail = $this->senderEmail ?: ($smtpSetting->mail_from_email ?? config('mail.from.address'));

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
