<link rel="stylesheet" href="{{ asset('vendor/full-calendar/main.min.css') }}">

@php
    $manageEmployeeShiftPermission = user()->permission('manage_employee_shifts');
@endphp

@if ($manageEmployeeShiftPermission != 'all')
    <style>
        .fc-event{
            cursor: default;
        }
    </style>

@endif

<x-cards.data class="mt-4">
    <div id="calendar"></div>
</x-cards.data>

<script src="{{ asset('vendor/full-calendar/main.min.js') }}"></script>
<script src="{{ asset('vendor/full-calendar/locales-all.min.js') }}"></script>

<script>
    (function() {
        var initialLocaleCode = '{{ user()->locale }}';
        var calendarEl = document.getElementById('calendar');
        var global_settings = @json(company());
        var manageShiftPermission = "{{ $manageEmployeeShiftPermission }}";

        var getEventDetail = function(userId, day, month, year) {
            if (manageShiftPermission != 'all') { return false; }
            var url = "{{ route('shifts.mark', [':userid', ':day', ':month', ':year']) }}";
            url = url.replace(':userid', userId).replace(':day', day).replace(':month', month).replace(':year', year);
            $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_DEFAULT, url);
        };

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: initialLocaleCode,
            timeZone: '{{ company()->timezone }}',
            firstDay: parseInt("{{ attendance_setting()?->week_start_from }}"),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            navLinks: true,
            selectable: (manageShiftPermission == 'all'),
            selectMirror: true,
            select: function(arg) {
                getEventDetail("{{ $employee->id }}", arg.start.getDate(), arg.start.getMonth() + 1, arg.start.getFullYear());
                calendar.unselect();
            },
            eventClick: function(arg) {
                getEventDetail(arg.event.extendedProps.userId, arg.event.extendedProps.day, arg.event.extendedProps.month, arg.event.extendedProps.year);
            },
            editable: false,
            dayMaxEvents: true,
            events: {
                url: "{{ route('shifts.employee_shift_calendar') }}",
                extraParams: function() { return { employeeId: "{{ $employee->id }}" }; }
            },
            eventDidMount: function(info) {
                $(info.el).css('background-color', info.event.extendedProps.bg_color);
                $(info.el).css('color', info.event.extendedProps.color);
            },
            eventTimeFormat: {
                hour: global_settings.time_format == 'H:i' ? '2-digit' : 'numeric',
                minute: '2-digit',
                meridiem: global_settings.time_format == 'H:i' ? false : true
            }
        });

        calendar.render();

        window.loadData = function() {
            calendar.refetchEvents();
            calendar.destroy();
            calendar.render();
            window.location.reload();
        };

        var doAdaptShifts = function() {};
        window.addEventListener('resize', doAdaptShifts);

        document.addEventListener("turbo:before-cache", function cleanup() {
            calendar.destroy();
            window.removeEventListener('resize', doAdaptShifts);
            delete window.loadData;
            document.removeEventListener("turbo:before-cache", cleanup);
        }, { once: true });
    })();
</script>
