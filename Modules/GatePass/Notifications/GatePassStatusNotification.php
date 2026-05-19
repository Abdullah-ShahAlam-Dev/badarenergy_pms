<?php

namespace Modules\GatePass\Notifications;

use App\Notifications\BaseNotification;
use Modules\GatePass\Entities\GatePassRequest;
use Illuminate\Notifications\Messages\MailMessage;

class GatePassStatusNotification extends BaseNotification
{
    public $gatePass;
    public $action;
    public $remarks;
    public $actor;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(GatePassRequest $gatePass, $action, $remarks = '', $actor = null)
    {
        parent::__construct();
        $this->gatePass = $gatePass;
        $this->action = $action;
        $this->remarks = $remarks;
        $this->actor = $actor;
        
        if ($gatePass->company) {
            $this->company = $gatePass->company;
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = array();

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $build = parent::build();

        $subject = '';
        $content = '';
        $actorName = $this->actor ? $this->actor->name : 'Approver';
        $gpNumber = 'GP-' . str_pad($this->gatePass->id, 4, '0', STR_PAD_LEFT);

        switch ($this->action) {
            case 'hod_sent_back':
                $subject = "[$gpNumber] Gate Pass Returned for Correction";
                $content = "Your Gate Pass Request <strong>#$gpNumber</strong> has been returned for changes by HOD <strong>$actorName</strong>.<br><br>";
                if ($this->remarks) {
                    $content .= "<strong>Correction Details:</strong><br>\"" . e($this->remarks) . "\"<br><br>";
                }
                $content .= "Please login to update and resubmit the gate pass request.";
                break;

            case 'store_sent_back':
                $subject = "[$gpNumber] Gate Pass Sent Back by Store";
                $content = "Gate Pass Request <strong>#$gpNumber</strong> has been sent back by Store verifier <strong>$actorName</strong> for re-approval.<br><br>";
                if ($this->remarks) {
                    $content .= "<strong>Reason:</strong><br>\"" . e($this->remarks) . "\"<br><br>";
                }
                $content .= "Please review, edit if necessary, and re-approve the request.";
                break;

            case 'security_sent_back':
                $subject = "[$gpNumber] Gate Pass Sent Back by Security";
                $content = "Gate Pass Request <strong>#$gpNumber</strong> has been sent back by Security <strong>$actorName</strong> for re-verification.<br><br>";
                if ($this->remarks) {
                    $content .= "<strong>Reason:</strong><br>\"" . e($this->remarks) . "\"<br><br>";
                }
                $content .= "Please review and re-authorize the request.";
                break;
                
            default:
                $subject = "[$gpNumber] Gate Pass Status Update";
                $content = "Gate Pass Request <strong>#$gpNumber</strong> status has been updated by <strong>$actorName</strong>.";
                break;
        }

        $url = route('gate-pass.show', $this->gatePass->id);
        $url = $this->modifyUrl($url);

        return $build
            ->subject($subject . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'actionText' => 'View Gate Pass',
                'notifiableName' => $notifiable->name,
                'themeColor' => $this->company ? $this->company->header_color : null
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'gate_pass_request_id' => $this->gatePass->id,
            'action' => $this->action,
            'remarks' => $this->remarks
        ];
    }
}
