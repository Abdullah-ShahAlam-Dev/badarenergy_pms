<?php

namespace Modules\Zoom\Console;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Zoom\Entities\ZoomMeeting;
use Modules\Zoom\Events\MeetingReminderEvent;

class SendMeetingReminder extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'send-zoom-meeting-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send meeting reminder to the attendees before time specified in database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $companies = Company::select(['id','timezone'])->get();

        foreach ($companies as $company) {

            $events = ZoomMeeting::select('id', 'meeting_name', 'label_color', 'description', 'start_date_time', 'end_date_time', 'repeat', 'send_reminder', 'remind_time', 'remind_type')
                ->where('start_date_time', '>=', now($company->timezone))
                ->where('send_reminder', 1)
                ->where('company_id', $company->id)
                ->get();

            foreach ($events as $event) {
                $reminderDateTime = $this->calculateReminderDateTime($event, $company);

                if ($reminderDateTime->equalTo(now()->timezone($company->timezone)->startOfMinute())) {
                    event(new MeetingReminderEvent($event));
                }
            }
        }
    }

    public function calculateReminderDateTime(ZoomMeeting $event, $company)
    {
        $time = $event->remind_time;
        $type = $event->remind_type;

        $reminderDateTime = '';

        switch ($type) {
        case 'day':
            $reminderDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $event->start_date_time, $company->timezone)->subDays($time);
            break;
        case 'hour':
            $reminderDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $event->start_date_time, $company->timezone)->subHours($time);
            break;
        case 'minute':
            $reminderDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $event->start_date_time, $company->timezone)->subMinutes($time);
            break;
        }

        return $reminderDateTime;
    }
}
