@extends('layouts.app')

@push('styles')
    @if ((!is_null($viewEventPermission) && $viewEventPermission != 'none')
        || (!is_null($viewHolidayPermission) && $viewHolidayPermission != 'none')
        || (!is_null($viewTaskPermission) && $viewTaskPermission != 'none')
        || (!is_null($viewTicketsPermission) && $viewTicketsPermission != 'none')
        || (!is_null($viewLeavePermission) && $viewLeavePermission != 'none')
        )
        <link rel="stylesheet" href="{{ asset('vendor/full-calendar/main.min.css') }}">
    @endif
    <style>
        .h-200 {
            max-height: 340px;
            overflow-y: auto;
        }

        .dashboard-settings {
            width: 600px;
        }

        @media (max-width: 768px) {
            .dashboard-settings {
                width: 300px;
            }
        }

        .fc-list-event-graphic{
            display: none;
        }

        .fc .fc-list-event:hover td{
            background-color: #fff !important;
            color:#000 !important;
        }
        .left-3{
            margin-right: -22px;
        }
        .clockin-right{
            margin-right: -10px;
        }

        .week-pagination li {
            margin-right: 5px;
            z-index: 1;
        }
        .week-pagination li a {
            border-radius: 50%;
            padding: 2px 6px !important;
            font-size: 11px !important;
        }

        .week-pagination li.page-item:first-child .page-link {
            border-top-left-radius: 50%;
            border-bottom-left-radius: 50%;
        }

        .week-pagination li.page-item:last-child .page-link {
            border-top-right-radius: 50%;
            border-bottom-right-radius: 50%;
        }
    </style>
@endpush

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="px-4 py-2 border-top-0 emp-dashboard">
        <!-- WELOCOME START -->
        @if (!is_null($checkTodayLeave))
            <div class="row pt-4">
                <div class="col-md-12">
                    <x-alert type="info" icon="info-circle">
                        <a href="{{ route('leaves.show', $checkTodayLeave->id) }}" class="openRightModal text-dark-grey">
                            <u>@lang('messages.youAreOnLeave')</u>
                        </a>
                    </x-alert>
                </div>
            </div>
        @elseif (!is_null($checkTodayHoliday))
            <div class="row pt-4">
                <div class="col-md-12">
                    <x-alert type="info" icon="info-circle">
                        <a href="{{ route('holidays.show', $checkTodayHoliday->id) }}" class="openRightModal text-dark-grey">
                            <u>@lang('messages.holidayToday')</u>
                        </a>
                    </x-alert>
                </div>
            </div>
        @endif


        @if(in_array('admin', user_roles()))
            <div class="row">
                @include('dashboard.update-message-dashboard')
                {{-- Remove for versions above 5.2.4 --}}
                @include('dashboard.update-gateway-credentials')
                <x-cron-message :modal="true"></x-cron-message>
            </div>
        @endif

        <div class="d-lg-flex d-md-flex d-block py-2 pb-2 align-items-center">

            <!-- WELOCOME NAME START -->
            <div>
                <h3 class="heading-h3 mb-0 f-21 text-capitalize font-weight-bold">@lang('app.welcome') {{ $user->name }}</h3>
            </div>
            <!-- WELOCOME NAME END -->

            <!-- CLOCK IN CLOCK OUT START -->
            <div
                class="ml-auto d-flex clock-in-out mb-3 mb-lg-0 mb-md-0 m mt-4 mt-lg-0 mt-md-0 justify-content-between">
                <p
                    class="mb-0 text-lg-right text-md-right f-18 font-weight-bold text-dark-grey d-grid align-items-center">
                    <input type="hidden" id="current-latitude" name="current_latitude">
                    <input type="hidden" id="current-longitude" name="current_longitude">

                    <span id="dashboard-clock">{!! now()->timezone(company()->timezone)->translatedFormat(company()->time_format) . '</span><span class="f-10 font-weight-normal">' . now()->timezone(company()->timezone)->translatedFormat('l') . '</span>' !!}

                    @if (!is_null($currentClockIn))
                        <span class="f-11 font-weight-normal text-lightest">
                            @lang('app.clockInAt') -
                            {{ $currentClockIn->clock_in_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                        </span>
                    @endif
                </p>

                @if (in_array('attendance', user_modules()) && $cannotLogin == false)
                    @if (is_null($currentClockIn) && is_null($checkTodayLeave) && is_null($checkTodayHoliday) && $checkJoiningDate == true)
                        <button type="button" class="btn-primary rounded f-15 ml-4" id="clock-in"><i
                        class="icons icon-login mr-2"></i>@lang('modules.attendance.clock_in')</button>
                    @endif
                @endif

                @if (!is_null($currentClockIn) && is_null($currentClockIn->clock_out_time))
                    <button type="button" class="btn-danger rounded f-15 ml-4" id="clock-out"><i
                            class="icons icon-login mr-2"></i>@lang('modules.attendance.clock_out')</button>
                @endif

                @if (in_array('admin', user_roles()))
                    <div class="private-dash-settings d-flex align-self-center">
                        <x-form id="privateDashboardWidgetForm" method="POST">
                            <div class="dropdown keep-open">
                                <a class="d-flex align-items-center justify-content-center dropdown-toggle px-4 text-dark left-3"
                                    type="link" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"

                                    aria-expanded="false">
                                    <i class="fa fa-cog" data-original-title="{{__('modules.dashboard.dashboardWidgetsSettings')}}" data-toggle="tooltip"></i>
                                </a>
                                <!-- Dropdown - User Information -->
                                <ul class="dropdown-menu dropdown-menu-right dashboard-settings p-20"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                    <li class="border-bottom mb-3">
                                        <h4 class="heading-h3">@lang('modules.dashboard.dashboardWidgets')</h4>
                                    </li>
                                    @foreach ($widgets as $widget)
                                        @php
                                            $wname = \Illuminate\Support\Str::camel($widget->widget_name);
                                        @endphp
                                        <li class="mb-2 float-left w-50">
                                            <div class="checkbox checkbox-info ">
                                                <input id="{{ $widget->widget_name }}" name="{{ $widget->widget_name }}"
                                                    value="true" @if ($widget->status) checked @endif type="checkbox">
                                                <label for="{{ $widget->widget_name }}">@lang('modules.dashboard.' .
                                                    $wname)</label>
                                            </div>
                                        </li>
                                    @endforeach
                                    @if (count($widgets) % 2 != 0)
                                        <li class="mb-2 float-left w-50 height-35"></li>
                                    @endif
                                    <li class="float-none w-100">
                                        <x-forms.button-primary id="save-dashboard-widget" icon="check">@lang('app.save')
                                        </x-forms.button-primary>
                                    </li>
                                </ul>
                            </div>
                        </x-form>
                    </div>
                @endif
            </div>
            <!-- CLOCK IN CLOCK OUT END -->
        </div>
        <!-- WELOCOME END -->
         <!-- EMPLOYEE DASHBOARD DETAIL START -->
         <div class="row emp-dash-detail">
            <!-- EMP DASHBOARD INFO NOTICES START -->
            @if(count(array_intersect(['profile', 'shift_schedule', 'birthday', 'notices'], $activeWidgets)) > 0)
                <div class="col-xl-5 col-lg-12 col-md-12 e-d-info-notices">
                    <div class="row">
                        @if (in_array('profile', $activeWidgets))
                        <!-- EMP DASHBOARD INFO START -->
                        <div class="col-md-12">
                            <div class="card border-0 b-shadow-4 mb-3 e-d-info">
                                <a @if(!in_array('client', user_roles())) href="{{ route('employees.show', user()->id) }}" @endif >
                                    <div class="card-horizontal align-items-center">
                                        <div class="card-img">
                                            <img class="" src=" {{ $user->image_url }}" alt="Card image">
                                        </div>
                                        <div class="card-body border-0 pl-0">
                                            <h4 class="card-title text-dark f-18 f-w-500 mb-0">{{ mb_ucfirst($user->name) }}</h4>
                                            <p class="f-14 font-weight-normal text-dark-grey mb-2">
                                                {{ $user->employeeDetails->designation->name ?? '--' }}</p>
                                            <p class="card-text f-12 text-lightest"> @lang('app.employeeId') :
                                                {{ mb_strtoupper($user->employeeDetails->employee_id) }}</p>
                                        </div>
                                    </div>
                                </a>

                                <div class="card-footer bg-white border-top-grey py-3">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        @if(in_array('tasks', user_modules()))
                                            <span>
                                                <label class="f-12 text-dark-grey mb-12 text-capitalize" for="usr">
                                                    @lang('app.open') @lang('app.menu.tasks') </label>
                                                <p class="mb-0 f-18 f-w-500">
                                                    <a href="{{ route('tasks.index') . '?assignee=me' }}"
                                                        class="text-dark">
                                                        {{ $inProcessTasks }}
                                                    </a>
                                                </p>
                                            </span>
                                        @endif
                                        @if(in_array('projects', user_modules()))
                                            <span>
                                                <label class="f-12 text-dark-grey mb-12 text-capitalize" for="usr">
                                                    @lang('app.menu.projects') </label>
                                                <p class="mb-0 f-18 f-w-500">
                                                    <a href="{{ route('projects.index') . '?assignee=me&status=all' }}"
                                                        class="text-dark">{{ $totalProjects }}</a>
                                                </p>
                                            </span>
                                        @endif
                                        @if (isset($totalOpenTickets) && in_array('tickets', user_modules()))
                                            <span>
                                                <label class="f-12 text-dark-grey mb-12 text-capitalize" for="usr">
                                                    @lang('modules.dashboard.totalOpenTickets') </label>
                                                <p class="mb-0 f-18 f-w-500">
                                                    <a href="{{ route('tickets.index') . '?agent=me&status=open' }}"
                                                        class="text-dark">{{ $totalOpenTickets }}</a>
                                                </p>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- EMP DASHBOARD INFO END -->
                        @endif

                        @if (!is_null($myActiveTimer) && in_array('tasks', user_modules()))
                            <div class="col-sm-12" id="myActiveTimerSection">
                                <x-cards.data class="mb-3" :title="__('modules.timeLogs.myActiveTimer')">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            {{ $myActiveTimer->start_time->timezone(company()->timezone)->translatedFormat('M d, Y' . ' - ' . company()->time_format) }}
                                            <p class="text-primary my-2">

                                                <strong>@lang('modules.timeLogs.totalHours'):</strong>
                                                {{ \Carbon\CarbonInterval::formatHuman(now()->diffInMinutes($myActiveTimer->start_time) - $myActiveTimer->breaks->sum('total_minutes')) }}
                                            </p>

                                            <ul class="list-group">
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                                    <span><i class="fa fa-clock"></i>
                                                        @lang('modules.timeLogs.startTime')</span>
                                                    {{ $myActiveTimer->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                                    <span><i class="fa fa-briefcase"></i> @lang('app.task')</span>
                                                    <a href="{{ route('tasks.show', $myActiveTimer->task->id) }}"
                                                        class="text-dark-grey openRightModal">{{ $myActiveTimer->task->heading }}</a>
                                                </li>
                                                @foreach ($myActiveTimer->breaks as $item)
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                                        @if (!is_null($item->end_time))

                                                            <span><i class="fa fa-mug-hot"></i>
                                                                @lang('modules.timeLogs.break')
                                                                ({{ \Carbon\CarbonInterval::formatHuman($item->end_time->diffInMinutes($item->start_time)) }})
                                                            </span>
                                                            {{ $item->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) . ' - ' . $item->end_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                                        @else
                                                            <span><i class="fa fa-mug-hot"></i>
                                                                @lang('modules.timeLogs.break')</span>
                                                            {{ $item->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>

                                        </div>
                                        <div class="col-sm-12 pt-3 text-right">
                                            @if ($editTimelogPermission == 'all' || ($editTimelogPermission == 'added' && $myActiveTimer->added_by == user()->id) || ($editTimelogPermission == 'owned' && (($myActiveTimer->project && $myActiveTimer->project->client_id == user()->id) || $myActiveTimer->user_id == user()->id)) || ($editTimelogPermission == 'both' && (($myActiveTimer->project && $myActiveTimer->project->client_id == user()->id) || $myActiveTimer->user_id == user()->id || $myActiveTimer->added_by == user()->id)))
                                                @if (is_null($myActiveTimer->activeBreak))
                                                    <x-forms.button-secondary icon="pause-circle"
                                                        data-time-id="{{ $myActiveTimer->id }}" id="pause-timer-btn">
                                                        @lang('modules.timeLogs.pauseTimer')</x-forms.button-secondary>
                                                    <x-forms.button-primary class="ml-3 stop-active-timer"
                                                        data-time-id="{{ $myActiveTimer->id }}" icon="stop-circle">
                                                        @lang('modules.timeLogs.stopTimer')</x-forms.button-primary>
                                                @else
                                                    <x-forms.button-primary id="resume-timer-btn" icon="play-circle"
                                                        data-time-id="{{ $myActiveTimer->activeBreak->id }}">
                                                        @lang('modules.timeLogs.resumeTimer')</x-forms.button-primary>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </x-cards.data>
                            </div>
                        @endif

                            @include('dashboard.employee.widgets.shift_schedule')

                            @include('dashboard.employee.widgets.birthday')

                            @include('dashboard.employee.widgets.past_birthday')

                            @include('dashboard.employee.widgets.appreciation')

                            @include('dashboard.employee.widgets.leave')

                            @include('dashboard.employee.widgets.work_from_home')

                            @include('dashboard.employee.widgets.work_anniversary')

                            @include('dashboard.employee.widgets.notice-period')

                            @include('dashboard.employee.widgets.probation')

                            @include('dashboard.employee.widgets.internship')

                            @include('dashboard.employee.widgets.contract')
                    </div>
                </div>
            @endif
            <!-- EMP DASHBOARD INFO NOTICES END -->
            <!-- EMP DASHBOARD TASKS PROJECTS EVENTS START -->
            <div class="col-xl-7 col-lg-12 col-md-12 e-d-tasks-projects-events">
                <!-- EMP DASHBOARD TASKS PROJECTS START -->
                <div class="row mb-3 mt-xl-0 mt-lg-4 mt-md-4 mt-4">
                    @if (in_array('tasks', $activeWidgets) && (!is_null($viewTaskPermission) && $viewTaskPermission != 'none') && in_array('tasks', user_modules()))
                        <div class="col-md-6 mb-3">
                            <div
                                class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
                                <div class="d-block text-capitalize">
                                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">@lang('app.menu.tasks')</h5>
                                    <div class="d-flex">
                                        <a href="{{ route('tasks.index') . '?assignee=me' }}">
                                            <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
                                                {{ $inProcessTasks }}<span class="f-12 font-weight-normal text-lightest">
                                                    @lang('app.pending') </span>
                                            </p>
                                        </a>
                                        <a href="{{ route('tasks.index') . '?assignee=me&overdue=yes' }}">
                                            <p class="mb-0 f-21 font-weight-bold text-red d-grid">{{ $dueTasks }}<span
                                                    class="f-12 font-weight-normal text-lightest">@lang('app.overdue')</span>
                                            </p>
                                        </a>
                                    </div>
                                </div>
                                <div class="d-block">
                                    <i class="fa fa-list text-lightest f-27"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    @include('dashboard.employee.widgets.projects')
                    @include('dashboard.employee.widgets.lead')
                    @include('dashboard.employee.widgets.week_timelog')
                </div>
                <!-- EMP DASHBOARD TASKS PROJECTS END -->
                @include('dashboard.employee.widgets.my_tasks')
                @include('dashboard.employee.widgets.tickets')
                @include('dashboard.employee.widgets.my_calendar')
                @include('dashboard.employee.widgets.notices')

            </div>
            <!-- EMP DASHBOARD TASKS PROJECTS EVENTS END -->
        </div>
        <!-- EMPLOYEE DASHBOARD DETAIL END -->

    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @if ((!is_null($viewEventPermission) && $viewEventPermission != 'none')
        || (!is_null($viewHolidayPermission) && $viewHolidayPermission != 'none')
        || (!is_null($viewTaskPermission) && $viewTaskPermission != 'none')
        || (!is_null($viewTicketsPermission) && $viewTicketsPermission != 'none')
        || (!is_null($viewLeavePermission) && $viewLeavePermission != 'none')
        )
        <script src="{{ asset('vendor/full-calendar/main.min.js') }}"></script>
        <script src="{{ asset('vendor/full-calendar/locales-all.min.js') }}"></script>
        <script>
            (function() {
                var initialLocaleCode = '{{ user()->locale }}';
                var calendarEl = document.getElementById('calendar');

                if (window.employeeCalendar) {
                    window.employeeCalendar.destroy();
                }

                if (calendarEl) {
                    window.employeeCalendar = new FullCalendar.Calendar(calendarEl, {
                        locale: initialLocaleCode,
                        timeZone: '{{ company()->timezone }}',
                        firstDay: parseInt("{{ attendance_setting()?->week_start_from }}"),
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                        },
                        navLinks: true,
                        selectable: false,
                        initialView: 'listWeek',
                        selectMirror: true,
                        select: function(arg) {
                            if (typeof addEventModal === 'function') addEventModal(arg.start, arg.end, arg.allDay);
                            window.employeeCalendar.unselect();
                        },
                        eventClick: function(arg) {
                            if (typeof getEventDetail === 'function') getEventDetail(arg.event.id, arg.event.extendedProps.event_type);
                        },
                        editable: false,
                        dayMaxEvents: true,
                        events: {
                            url: "{{ route('dashboard.private_calendar') }}",
                        },
                        eventDidMount: function(info) {
                            $(info.el).css('background-color', info.event.extendedProps.bg_color);
                            $(info.el).css('color', info.event.extendedProps.color);
                            $(info.el).find('td.fc-list-event-title').prepend('<i class="fa ' + info.event.extendedProps.icon + '"></i>&nbsp;&nbsp;');
                            if (info.event.extendedProps.event_type == 'leave') {
                                $(info.el).find('td.fc-list-event-title > a').css('cursor', 'default');
                                $(info.el).css('cursor', 'default');
                                $(info.el).tooltip({
                                    title: info.event.extendedProps.name,
                                    container: 'body',
                                    delay: { "show": 50, "hide": 50 }
                                });
                            }
                        },
                        eventTimeFormat: {
                            hour: company.time_format == 'H:i' ? '2-digit' : 'numeric',
                            minute: '2-digit',
                            meridiem: company.time_format == 'H:i' ? false : true
                        }
                    });

                    window.employeeCalendar.render();
                }

                var getEventDetail = function(id, type) {
                    if (type == 'ticket') {
                        window.location = "{{ route('tickets.show', ':id') }}".replace(':id', id);
                        return true;
                    }
                    if (type == 'leave') return true;

                    openTaskDetail();
                    var url = "";
                    switch (type) {
                        case 'task': url = "{{ route('tasks.show', ':id') }}"; break;
                        case 'event': url = "{{ route('events.show', ':id') }}"; break;
                        case 'holiday': url = "{{ route('holidays.show', ':id') }}"; break;
                        case 'leave': url = "{{ route('leaves.show', ':id') }}"; break;
                        default: return 0;
                    }

                    $.easyAjax({
                        url: url.replace(':id', id),
                        blockUI: true,
                        container: RIGHT_MODAL,
                        historyPush: true,
                        success: function(response) {
                            if (response.status == "success") {
                                $(RIGHT_MODAL_CONTENT).html(response.html);
                                $(RIGHT_MODAL_TITLE).html(response.title);
                            }
                        }
                    });
                };

                // Event Listeners for Calendar
                var $body = $('body');
                $body.off('click.calendarAction').on('click.calendarAction', '#event-btn', function() {
                    $('#cal-drop').toggle();
                });

                $(document).off('mouseup.calendarHide').on('mouseup.calendarHide', function(e) {
                    var $menu = $('.calendar-action');
                    if (!$menu.is(e.target) && $menu.has(e.target).length === 0) {
                        $('#cal-drop').hide();
                    }
                });

                $body.off('click.calendarFilter').on('click.calendarFilter', '.cal-filter', function() {
                    var filter = [];
                    $('.filter-check:checked').each(function() {
                        filter.push($(this).val());
                    });
                    if (filter.length < 1) filter.push('None');

                    window.employeeCalendar.removeAllEventSources();
                    window.employeeCalendar.addEventSource({
                        url: "{{ route('dashboard.private_calendar') }}",
                        extraParams: { filter: filter }
                    });
                });

                document.addEventListener("turbo:before-cache", function() {
                    if (window.employeeCalendar) {
                        window.employeeCalendar.destroy();
                        window.employeeCalendar = null;
                    }
                    $body.off('.calendarAction .calendarFilter');
                    $(document).off('.calendarHide');
                }, { once: true });

            })();
        </script>
        </script>
    @endif

    <script>
        (function() {
            if (typeof window.DashboardClockManager === 'undefined') {
                window.DashboardClockManager = (function() {
                    var clockInterval = null;
                    var start = function() {
                        stop();
                        clockInterval = setInterval(function () {
                            var $clock = $('#dashboard-clock');
                            if ($clock.length === 0) return;
                            var date = new Date();
                            $clock.html(moment.tz(date, "{{ company()->timezone }}").format(MOMENTJS_TIME_FORMAT));
                        }, 1000);
                    };
                    var stop = function() {
                        if (clockInterval) {
                            clearInterval(clockInterval);
                            clockInterval = null;
                        }
                    };
                    document.addEventListener("turbo:before-cache", stop);
                    document.addEventListener("turbo:before-visit", stop);
                    return { start, stop };
                })();
            }
            window.DashboardClockManager.start();

            var $body = $('body');
            $body.off('.empDashboard');

            $body.on('click.empDashboard', '#save-dashboard-widget', function() {
                $.easyAjax({
                    url: "{{ route('dashboard.widget', 'private-dashboard') }}",
                    container: '#privateDashboardWidgetForm',
                    blockUI: true,
                    type: "POST",
                    redirect: true,
                    data: $('#privateDashboardWidgetForm').serialize(),
                    success: function() {
                        window.location.reload();
                    }
                })
            });

            $body.on('click.empDashboard', '#clock-in', function() {
                var url = "{{ route('attendances.clock_in_modal') }}";
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            $body.on('click.empDashboard', '.request-shift-change', function() {
                var id = $(this).data('shift-schedule-id');
                var date = $(this).data('shift-schedule-date');
                var shiftId = $(this).data('shift-id');
                var url = "{{ route('shifts-change.edit', ':id') }}?date="+date+"&shift_id="+shiftId;
                url = url.replace(':id', id);
                $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_DEFAULT, url);
            });

            $body.on('click.empDashboard', '#view-shifts', function() {
                var url = "{{ route('employee-shifts.index') }}";
                $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_XL, url);
            });

            @if (!is_null($currentClockIn))
                $body.on('click.empDashboard', '#clock-out', function() {
                    var token = "{{ csrf_token() }}";
                    var currentLatitude = document.getElementById("current-latitude").value;
                    var currentLongitude = document.getElementById("current-longitude").value;
                    $.easyAjax({
                        url: "{{ route('attendances.update_clock_in') }}",
                        type: "GET",
                        data: {
                            currentLatitude: currentLatitude,
                            currentLongitude: currentLongitude,
                            _token: token,
                            id: '{{ $currentClockIn->id }}'
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                window.location.reload();
                            }
                        }
                    });
                });
            @endif

            $body.on('click.empDashboard', '#weekly-timelogs .week-timelog-day', function() {
                var date = $(this).data('date');
                $.easyAjax({
                    url: "{{ route('dashboard.week_timelog') }}",
                    container: '#weekly-timelogs',
                    blockUI: true,
                    type: "POST",
                    redirect: true,
                    data: {
                        'date': date,
                        '_token': "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#weekly-timelogs').html(response.html)
                    }
                })
            });

            @if (attendance_setting()->radius_check == 'yes' || attendance_setting()->save_current_location)
                var getLocation = function() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            var lat = document.getElementById("current-latitude");
                            var lon = document.getElementById("current-longitude");
                            if (lat) lat.value = position.coords.latitude;
                            if (lon) lon.value = position.coords.longitude;
                        });
                    }
                };
                getLocation();
            @endif

            document.addEventListener("turbo:before-cache", function() {
                $body.off('.empDashboard');
            }, { once: true });
        })();
    </script>
@endpush
