@php
$addTaskPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('add_tasks');
$viewUnassignedTasksPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('view_unassigned_tasks');
$projectArchived = $project->trashed();
@endphp

<!-- ROW START -->
<div class="row py-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        @if ($projectArchived)
            <x-alert type="info" icon="info-circle">@lang('messages.archivedTaskNotWork')</x-alert>
        @endif

        <div class="d-flex" id="table-actions">
            @if (($addTaskPermission == 'all' || $addTaskPermission == 'added' || $project->project_admin == user()->id) && !$projectArchived)
                <x-forms.link-primary :link="route('tasks.create').'?task_project_id='.$project->id"
                    class="mr-3 openRightModal" icon="plus" data-redirect-url="{{ url()->full() }}">
                    @lang('app.add')
                    @lang('app.task')
                </x-forms.link-primary>
            @endif
        </div>
        <!-- Add Task Export Buttons End -->


        <div class="d-flex justify-content-between">
            <form action="" class="flex-grow-1 " id="filter-form">
                <div class="d-flex mt-3">
                    <!-- STATUS START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('app.status')" fieldId="status" />
                        <select class="form-control select-picker" name="status" id="status">
                            <option value="not finished">@lang('modules.tasks.hideCompletedTask')</option>
                            <option value="all">@lang('app.all')</option>
                            @foreach ($taskBoardStatus as $status)
                                <option value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : mb_ucwords($status->column_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- STATUS END -->

                    <!-- STATUS START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('modules.tasks.assignTo')" fieldId="assignedTo" />
                        <select class="form-control select-picker" id="assignedTo" data-live-search="true"
                        data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($project->projectMembers as $employee)
                                <x-user-option :user="$employee"></x-user-option>
                            @endforeach
                            @if ($viewUnassignedTasksPermission == 'all')
                                <option value="unassigned">@lang('modules.tasks.unassigned')</option>
                            @endif
                        </select>
                    </div>
                    <!-- STATUS END -->

                    <!-- STATUS START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('modules.projects.milestones')" fieldId="milestone_id" />
                        <select class="form-control select-picker" id="milestone_id" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($project->milestones as $milestone)
                                <option value="{{ $milestone->id }}">{{ $milestone->milestone_title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- STATUS END -->


                    <!-- SEARCH BY TASK START -->
                    <div class="select-box py-2 px-lg-2 px-md-2 px-0 mr-3">
                        <x-forms.label fieldId="status" />
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
                    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 mt-4">
                        <x-forms.button-secondary class="btn-xs d-none height-35 mt-2" id="reset-filters"
                            icon="times-circle">
                            @lang('app.clearFilters')
                        </x-forms.button-secondary>
                    </div>
                    <!-- RESET END -->
                </div>
            </form>

            <x-datatable.actions class="mt-5">
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
<!-- ROW END -->
@include('sections.datatable_js')

<script>
    (function() {
        var $body = $('body');
        var $table = $('#allTasks-table');
        var namespace = '.projectTasks';

        $table.off('preXhr.dt' + namespace).on('preXhr.dt' + namespace, function(e, settings, data) {
            var projectID = "{{ $project->id }}";
            var status = $('#status').val();
            var searchText = $('#search-text-field').val();
            var assignedTo = $('#assignedTo').val();
            var trashedData = "{{ $project->trashed() == 1 ? 'true' : 'false' }}";
            var milestone_id = $('#milestone_id').val();
            data['projectId'] = projectID;
            data['status'] = status;
            data['assignedTo'] = assignedTo;
            data['searchText'] = searchText;
            data['trashedData'] = trashedData;
            data['milestone_id'] = milestone_id;
            data['project_admin'] = "{{ ($project->project_admin == user()->id) ? 1 : 0 }}";
        });

        function showTable() {
            if (window.LaravelDataTables && window.LaravelDataTables["allTasks-table"]) {
                window.LaravelDataTables["allTasks-table"].draw(false);
            }
        }
        window.showTable = showTable;

        $body.off(namespace);

        $body.on('change' + namespace + ' keyup' + namespace, '#status, #assignedTo, #milestone_id', function() {
            if ($('#status').val() != "not finished" || $('#assignedTo').val() != "all" || $('#milestone_id').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
            showTable();
        });

        $body.on('keyup' + namespace, '#search-text-field', function() {
            if ($(this).val() != "") {
                $('#reset-filters').removeClass('d-none');
            }
            showTable();
        });

        $body.on('click' + namespace, '#reset-filters, #reset-filters-2', function() {
            $('#filter-form')[0].reset();
            $('#filter-form #status').val('not finished');
            $('#filter-form .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
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
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") { showTable(); }
                        }
                    });
                }
            });
        });

        $table.on('change' + namespace, '.change-status', function() {
            var url = "{{ route('tasks.change_status') }}";
            var token = "{{ csrf_token() }}";
            var id = $(this).data('task-id');
            var status = $(this).val();

            if (id != "" && status != "") {
                $.easyAjax({
                    url: url,
                    type: "POST",
                    data: { '_token': token, taskId: id, status: status, sortBy: 'id' },
                    success: function() { showTable(); }
                });
            }
        });

        $body.on('change' + namespace, '#quick-action-type', function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                $('.quick-action-field').addClass('d-none');
                if (actionValue == 'change-status') {
                    $('#change-status-action').removeClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $body.on('click' + namespace, '#quick-action-apply', function() {
            const actionValue = $('#quick-action-type').val();
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

        function applyQuickAction() {
            var rowdIds = $("#allTasks-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('tasks.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        if (typeof resetActionButtons === 'function') resetActionButtons();
                        if (typeof deSelectAll === 'function') deSelectAll();
                    }
                }
            });
        }
        window.applyQuickAction = applyQuickAction;

        $body.on('click' + namespace, '.start-timer', function() {
            var url = "{{ route('timelogs.start_timer') }}";
            var user_id = "{{ user()->id }}";
            var token = "{{ csrf_token() }}";
            var task_id = $(this).data('task-id');
            var memo = "{{ __('app.task') }}#" + $(this).data('task-id');

            $.easyAjax({
                url: url,
                container: '#allTasks-table',
                type: "POST",
                blockUI: true,
                data: { task_id: task_id, memo: memo, '_token': token, user_id: user_id },
                success: function(response) {
                    if (response.status == 'success') {
                        if (typeof window.syncGlobalStats === 'function') {
                            window.syncGlobalStats(response);
                        }
                        showTable();
                    }
                }
            })
        });

        $body.on('click' + namespace, '.stop-timer, .pause-timer, .resume-timer', function() {
            var id = $(this).data('time-id');
            var action = $(this).hasClass('stop-timer') ? 'stop_timer' : ($(this).hasClass('pause-timer') ? 'pause_timer' : 'resume_timer');
            var url = "{{ route('timelogs.show', ':id') }}";
            url = url.replace('show', action);
            url = url.replace(':id', id);
            var token = $('meta[name="csrf-token"]').attr('content');
            var $this = $(this);

            $.easyAjax({
                url: url,
                blockUI: true,
                container: '#allTasks-table',
                type: "POST",
                disableButton: true,
                buttonSelector: $this,
                data: { timeId: id, _token: token },
                success: function(response) {
                    if (typeof window.syncGlobalStats === 'function') {
                        window.syncGlobalStats(response);
                    }
                    showTable();
                }
            })
        });

        document.addEventListener('turbo:before-cache', function cleanup() {
            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);
            delete window.showTable;
            delete window.applyQuickAction;
            document.removeEventListener('turbo:before-cache', cleanup);
        }, { once: true });
    })();
</script>
