<?php

namespace Modules\WorkOrder\Notifications;

use App\Models\EmailNotificationSetting;
use App\Notifications\BaseNotification;
use Modules\WorkOrder\Entities\WorkOrder;
use NotificationChannels\OneSignal\OneSignalChannel;

class NewWorkOrder extends BaseNotification
{
    private WorkOrder $workOrder;
    private $emailSetting;

    public function __construct(WorkOrder $workOrder)
    {
        $this->workOrder    = $workOrder;
        $this->company      = $this->workOrder->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'work-order-notification')
            ->first();
    }

    public function via($notifiable): array
    {
        if (!$this->emailSetting) {
            return ['database'];
        }

        $via = ($this->emailSetting->send_email == 'yes'
            && $notifiable->email_notifications
            && $notifiable->email != '')
            ? ['mail', 'database']
            : ['database'];

        if ($this->emailSetting->send_push == 'yes') {
            array_push($via, OneSignalChannel::class);
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        $build = parent::build();
        $url   = route('work-orders.show', $this->workOrder->id);
        $url   = getDomainSpecificUrl($url, $this->company);

        return $build
            ->subject('New Work Order ' . $this->workOrder->wo_number . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url'            => $url,
                'content'        => 'A new Work Order <strong>' . $this->workOrder->wo_number . '</strong> has been submitted and requires your approval.',
                'themeColor'     => $this->company->header_color,
                'actionText'     => 'Review Work Order',
                'notifiableName' => $notifiable->name,
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'id'        => $this->workOrder->id,
            'wo_number' => $this->workOrder->wo_number,
        ];
    }
}
