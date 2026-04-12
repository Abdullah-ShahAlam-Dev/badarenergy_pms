@php
$addTaskPermission = user()->permission('add_tasks');
@endphp

<!-- ROW START -->
<div class="row py-0 py-md-0 py-lg-3">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">

        <form action="" id="filter-form">
            <div class="d-flex my-3">
                <!-- STATUS START -->
                <div class="select-box py-2 px-0 mr-3">
                    <select class="form-control select-picker" name="status" id="status">
                        <option value="not finished">@lang('modules.tasks.hideCompletedTask')</option>
                        <option value="all">@lang('app.all')</option>
                        @foreach ($taskBoardStatus as $status)
                            <option value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : mb_ucwords($status->column_name) }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- STATUS END -->

                <!-- SEARCH BY TASK START -->
                <div class="select-box py-2 px-lg-2 px-md-2 px-0 mr-3">

                    <div class="input-group bg-grey rounded">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-additional-grey">
                                <i class="fa fa-search f-13 text-dark-grey"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control f-14 p-1 height-35 border" id="search-text-field"
                            placeholder="@lang('app.startTyping')">
                    </div>
                </div>
                <!-- SEARCH BY TASK END -->

                <!-- RESET START -->
                <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                    <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                        @lang('app.clearFilters')
                    </x-forms.button-secondary>
                </div>
                <!-- RESET END -->
            </div>
        </form>

        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="align-items-center">
                @if ($addTaskPermission == 'all' || $addTaskPermission == 'added')
                    <x-forms.link-primary :link="route('tasks.create').'?default_assign='.$employee->id"
                        class="mr-3 openRightModal float-left" data-redirect-url="{{ url()->full() }}" icon="plus">
                        @lang('app.add')
                        @lang('app.task')
                    </x-forms.link-primary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        @foreach ($taskBoardStatus as $status)
                            <option value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : mb_ucwords($status->column_name) }}</option>
                        @endforeach
                    </select>
                </div>
            </x-datatable.actions>

        </div>

        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
</div>

@include('sections.datatable_js')

<script>
    (function() {
        var $body = $('body');
        var namespace = '.employeeTasks';
        var $table = $('#allTasks-table');

        $table.off('preXhr.dt' + namespace).on('preXhr.dt' + namespace, function(e, settings, data) {
            data['assignedTo'] = "{{ $employee->id }}";
            data['status'] = $('#status').val();
            data['searchText'] = $('#search-text-field').val();
        });

        var showTable = function() {
            if (window.LaravelDataTables && window.LaravelDataTables["allTasks-table"]) {
                window.LaravelDataTables["allTasks-table"].draw(false);
            }
        };
        window.showTable = showTable;

        $body.off(namespace);

        $body.on('change' + namespace + ' keyup' + namespace, '#status', function() {
            if ($('#status').val() != "not finished") {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
            showTable();
        });

        $body.on('keyup' + namespace, '#search-text-field', function() {
            if ($(this).val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $body.on('click' + namespace, '#reset-filters, #reset-filters-2', function() {
            $('#filter-form')[0].reset();
            $('#filter-form #status').val('not finished');
            $('#filter-form .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $body.on('change' + namespace, '#quick-action-type', function() {
            var actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $body.on('click' + namespace, '#quick-action-apply', function() {
            var actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.recoverRecord')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) { applyQuickAction(); }
                });
            } else {
                applyQuickAction();
            }
        });

        $body.on('click' + namespace, '.delete-table-row', function() {
            var id = $(this).data('user-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('tasks.destroy', ':id') }}".replace(':id', id);
                    $.easyAjax({
                        type: 'POST', url: url,
                        data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) { if (response.status == "success") { showTable(); } }
                    });
                }
            });
        });

        $table.on('change' + namespace, '.change-status', function() {
            var id = $(this).data('task-id');
            var status = $(this).val();
            if (id != "" && status != "") {
                $.easyAjax({
                    url: "{{ route('tasks.change_status') }}", type: "POST",
                    data: { '_token': "{{ csrf_token() }}", taskId: id, status: status, sortBy: 'id' },
                    success: function() { window.LaravelDataTables["allTasks-table"].draw(false); }
                });
            }
        });

        $table.on('click' + namespace, '.start-timer', function() {
            var task_id = $(this).data('task-id');
            $.easyAjax({
                url: "{{ route('timelogs.start_timer') }}", type: "POST", blockUI: true,
                container: '#allTasks-table',
                data: { task_id: task_id, memo: "{{ __('app.task') }}#" + task_id, '_token': "{{ csrf_token() }}", user_id: "{{ user()->id }}" },
                success: function(response) {
                    if (response.status == 'success') {
                        if (response.activeTimerCount > 0) { $('#show-active-timer .active-timer-count').html(response.activeTimerCount); }
                        else { $('#show-active-timer .active-timer-count').addClass('d-none'); }
                        $('#timer-clock').html(response.clockHtml);
                        if ($('#allTasks-table').length) { window.LaravelDataTables["allTasks-table"].draw(false); }
                    }
                }
            });
        });

        $table.on('click' + namespace, '.stop-timer', function() {
            var id = $(this).data('time-id');
            $.easyAjax({
                url: "{{ route('timelogs.stop_timer', ':id') }}".replace(':id', id),
                blockUI: true, container: '#allTasks-table', type: "POST",
                data: { timeId: id, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.activeTimerCount > 0) { $('#show-active-timer .active-timer-count').html(response.activeTimerCount); }
                    else { $('#show-active-timer .active-timer-count').addClass('d-none'); }
                    $('#timer-clock').html('');
                    if ($('#allTasks-table').length) { window.LaravelDataTables["allTasks-table"].draw(false); }
                }
            });
        });

        $table.on('click' + namespace, '.resume-timer', function() {
            var id = $(this).data('time-id');
            $.easyAjax({
                url: "{{ route('timelogs.resume_timer', ':id') }}".replace(':id', id),
                blockUI: true, type: "POST",
                data: { timeId: id, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.status == 'success') {
                        if (response.activeTimerCount > 0) { $('#show-active-timer .active-timer-count').html(response.activeTimerCount); }
                        else { $('#show-active-timer .active-timer-count').addClass('d-none'); }
                        $('#timer-clock').html(response.clockHtml);
                        if ($('#allTasks-table').length) { window.LaravelDataTables["allTasks-table"].draw(false); }
                    }
                }
            });
        });

        $table.on('click' + namespace, '.pause-timer', function() {
            var id = $(this).data('time-id');
            $.easyAjax({
                url: "{{ route('timelogs.pause_timer', ':id') }}".replace(':id', id),
                blockUI: true, type: "POST", disableButton: true, buttonSelector: "#pause-timer-btn",
                data: { timeId: id, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.status == 'success') {
                        if (response.activeTimerCount > 0) { $('#show-active-timer .active-timer-count').html(response.activeTimerCount); }
                        else { $('#show-active-timer .active-timer-count').addClass('d-none'); }
                        $('#timer-clock').html(response.clockHtml);
                        if ($('#allTasks-table').length) { window.LaravelDataTables["allTasks-table"].draw(false); }
                    }
                }
            });
        });

        var applyQuickAction = function() {
            var rowdIds = $("#allTasks-table input:checkbox:checked").map(function() { return $(this).val(); }).get();
            $.easyAjax({
                url: "{{ route('tasks.apply_quick_action') }}?row_ids=" + rowdIds,
                container: '#quick-action-form', type: "POST", disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        if (typeof resetActionButtons === 'function') resetActionButtons();
                    }
                }
            });
        };

        document.addEventListener("turbo:before-cache", function cleanup() {
            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);
            $table.off(namespace);
            delete window.showTable;
            document.removeEventListener("turbo:before-cache", cleanup);
        }, { once: true });
    })();
</script>
