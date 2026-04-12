@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <meta name="turbo-cache-control" content="no-cache">
@endpush


@php
$addTaskPermission = user()->permission('add_tasks');
$viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');
@endphp


@section('filter-section')

    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-size="8">
                    <option value="not finished">@lang('modules.tasks.hideCompletedTask')</option>
                    <option {{ request('status') == 'all' ? 'selected' : '' }} value="all">@lang('app.all')</option>
                    @foreach ($taskBoardStatus as $status)
                        <option value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : $status->column_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs {{ request('overdue') != 'yes' ? 'd-none' : '' }}" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.dateFilterOn')</label>
                <div class="select-filter mb-4">
                    <select class="form-control select-picker" name="date_filter_on" id="date_filter_on">
                        <option value="start_date">@lang('app.startDate')</option>
                        <option value="due_date">@lang('app.dueDate')</option>
                        <option value="completed_on">@lang('app.date') @lang('app.completed')</option>
                    </select>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.tickets.type')</label>
                <div class="select-filter mb-4">
                    <select class="form-control select-picker" name="pinned" id="pinned" data-container="body">
                        <option value="all">@lang('app.all')</option>
                        <option value="pinned">@lang('app.pinned')</option>
                        <option value="private">@lang('app.private')</option>
                    </select>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.project')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="project_id_filter" id="project_id_filter" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.client')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="clientID" data-live-search="true"
                            data-container="body" data-size="8">
                            @if (!in_array('client', user_roles()))
                                <option value="all">@lang('app.all')</option>
                            @endif
                            @foreach ($clients as $client)
                                <x-user-option :user="$client"></x-user-option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.tasks.assignTo')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="assignedTo" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($employees as $employee)
                                <x-user-option :user="$employee"
                                               :selected="request('assignee') == 'me' && $employee->id == user()->id">
                                </x-user-option>
                            @endforeach
                            @if ($viewUnassignedTasksPermission == 'all')
                                <option value="unassigned">@lang('modules.tasks.unassigned')</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.tasks.assignBy')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="assignedBY" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($employees as $employee)
                                <x-user-option :user="$employee" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.label')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="label" data-live-search="true" data-container="body"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($taskLabels as $label)
                                <option
                                    data-content="<span class='badge b-all' style='background:{{ $label->label_color }};'>{{ $label->label_name }}</span> "
                                    value="{{ $label->id }}">{{ $label->label_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize"
                    for="usr">@lang('modules.taskCategory.taskCategory')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="category_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($taskCategories as $categ)
                                <option value="{{ $categ->id }}">{{ $categ->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.billableTask')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="billable_task" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.yes')</option>
                            <option value="0">@lang('app.no')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.projects.milestones')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="milestone_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($milestones as $milestone)
                                <option value="{{ $milestone->id }}">{{ $milestone->milestone_title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>

@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addTaskPermission == 'all' || $addTaskPermission == 'added')
                    <x-forms.link-primary :link="route('tasks.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('app.add')
                        @lang('app.task')
                    </x-forms.link-primary>
                @endif

                @if (!in_array('client', user_roles()))
                    <x-forms.button-secondary id="filter-my-task" class="mr-3 float-left" icon="user">
                        @lang('modules.tasks.myTask')
                    </x-forms.button-primary>
                @endif

                @if ($addTaskPermission == 'all' || $addTaskPermission == 'added')
                    {{-- Task Format Export Button --}}
                    <button type="button" id="task-format-export-btn"
                            class="btn btn-outline-success mr-3 float-left f-14"
                            data-toggle="modal" data-target="#taskFormatExportModal"
                            data-toggle-tooltip="tooltip"
                            title="Download blank CSV template to fill and import tasks">
                        <i class="fa fa-file-download mr-1"></i> Task Format Export
                    </button>

                    {{-- Import CSV Button --}}
                    <button type="button" id="task-csv-import-btn"
                            class="btn btn-outline-info mr-3 float-left f-14"
                            data-toggle="modal" data-target="#taskCsvImportModal"
                            title="Import tasks from a filled CSV template">
                        <i class="fa fa-file-upload mr-1"></i> Import CSV
                    </button>
                @endif

            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-lg-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        @foreach ($taskBoardStatus as $status)
                            <option value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : $status->column_name }}</option>
                        @endforeach
                    </select>
                </div>
            </x-datatable.actions>

            <div class="btn-group mt-3 mt-lg-0 mt-md-0 ml-lg-3" role="group">
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary f-14 btn-active task" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.tasks')"><i class="side-icon bi bi-list-ul"></i></a>

                <a href="{{ route('taskboards.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                    data-original-title="@lang('modules.tasks.taskBoard')"><i class="side-icon bi bi-kanban"></i></a>

                <a href="{{ route('task-calendar.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.calendar')"><i class="side-icon bi bi-calendar"></i></a>

                <a href="javascript:;" class="btn btn-secondary f-14 show-pinned" data-toggle="tooltip"
                    data-original-title="@lang('app.pinned')"><i class="side-icon bi bi-pin-angle"></i></a>
            </div>
        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

    {{-- ============================================================ --}}
    {{-- TASK FORMAT EXPORT MODAL --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="taskFormatExportModal" tabindex="-1" role="dialog"
         aria-labelledby="taskFormatExportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskFormatExportModalLabel">
                        <i class="fa fa-file-download text-success mr-2"></i> Task Format Export
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted f-13 mb-3">
                        Select a project to download a blank CSV import template.<br>
                        Fill in the template and re-upload it using the <strong>Import CSV</strong> button.
                    </p>
                    <div class="form-group">
                        <label for="export_project_id" class="f-14 text-dark-grey">
                            Project <span class="text-danger">*</span>
                        </label>
                        <select class="form-control select-picker" id="export_project_id"
                                data-live-search="true" data-size="8">
                            <option value="">-- Select Project --</option>
                            @foreach ($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a href="#" id="download-task-template-btn" class="btn btn-success">
                        <i class="fa fa-download mr-1"></i> Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- IMPORT CSV MODAL --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="taskCsvImportModal" tabindex="-1" role="dialog"
         aria-labelledby="taskCsvImportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskCsvImportModalLabel">
                        <i class="fa fa-file-upload text-info mr-2"></i> Import Tasks from CSV
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="task-csv-import-form" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted f-13 mb-3">
                            Select the target project and upload your filled task template CSV.
                            Tasks will be inserted into the selected project immediately.
                        </p>

                        <div class="form-group">
                            <label for="import_project_id" class="f-14 text-dark-grey">
                                Project <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select-picker" name="project_id"
                                    id="import_project_id" data-live-search="true" data-size="8" required>
                                <option value="">-- Select Project --</option>
                                @foreach ($projects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->project_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="import_csv_file" class="f-14 text-dark-grey">
                                CSV File <span class="text-danger">*</span>
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="import_csv_file"
                                       name="import_file" accept=".csv,.txt" required>
                                <label class="custom-file-label" for="import_csv_file">Choose CSV file...</label>
                            </div>
                            <small class="text-muted mt-1 d-block">Only .csv files accepted. Use the Task Format Export template.</small>
                        </div>

                        <div id="import-result" class="d-none mt-3">
                            <div id="import-success-msg" class="alert alert-success d-none"></div>
                            <div id="import-error-msg" class="alert alert-warning d-none"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="import-csv-submit-btn" class="btn btn-info">
                            <i class="fa fa-upload mr-1"></i> Import Tasks
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var $doc = $(document);
            var namespace = '.tasksIndex';

            $('#allTasks-table').on('preXhr.dt' + namespace, function(e, settings, data) {
                var dateRangePicker = $('#datatableRange').data('daterangepicker');
                var startDate = $('#datatableRange').val();

                if (startDate == '') {
                    startDate = null;
                    endDate = null;
                } else {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                var projectID = $('#project_id_filter').val();
                if (!projectID) {
                    projectID = 0;
                }

                var clientID = $('#clientID').val();
                var assignedBY = $('#assignedBY').val();
                var assignedTo = $('#assignedTo').val();
                var status = $('#status').val();
                var label = $('#label').val();
                var category_id = $('#category_id').val();
                var billable = $('#billable_task').val();
                var pinned = $('#pinned').val();
                var date_filter_on = $('#date_filter_on').val();
                var searchText = $('#search-text-field').val();
                var milestone_id = $('#milestone_id').val();

                data['clientID'] = clientID;
                data['assignedBY'] = assignedBY;
                data['assignedTo'] = assignedTo;
                data['status'] = status;
                data['label'] = label;
                data['category_id'] = category_id;
                data['billable'] = billable;
                data['projectId'] = projectID;
                data['pinned'] = pinned;
                data['date_filter_on'] = date_filter_on;
                data['startDate'] = startDate;
                data['endDate'] = endDate;
                data['searchText'] = searchText;
                data['milestone_id'] = milestone_id;
            });

            var showTable = function() {
                var table = window.LaravelDataTables["allTasks-table"];
                if (table) {
                    table.draw(true);
                }
            }
            window.showTable = showTable;

            $doc.on('click' + namespace, '.show-pinned', function() {
                $('.task').removeClass('btn-active');
                if ($(this).hasClass('btn-active')) {
                    $('#pinned').val('all');
                } else {
                    $('#pinned').val('pinned');
                }
                $('#pinned').selectpicker('refresh');
                $(this).toggleClass('btn-active');
                $('#reset-filters').removeClass('d-none');
                showTable();
            });

            $doc.on('click' + namespace, '#reset-filters, #reset-filters-2', function() {
                $('#filter-form')[0].reset();
                $('.filter-box #status').val('not finished');
                $('.filter-box #date_filter_on').val('start_date');
                $('.filter-box #assignedTo').val('all');
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            $doc.on('change' + namespace, '#quick-action-type', function() {
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

            $doc.on('click' + namespace, '#quick-action-apply', function() {
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
                        customClass: {
                            confirmButton: 'btn btn-primary mr-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            applyQuickAction();
                        }
                    });
                } else {
                    applyQuickAction();
                }
            });

            $doc.on('click' + namespace, '.delete-table-row', function() {
                var id = $(this).data('user-id');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.recoverRecord')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: {
                        confirmButton: 'btn btn-primary mr-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "{{ route('tasks.destroy', ':id') }}";
                        url = url.replace(':id', id);
                        var token = "{{ csrf_token() }}";

                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                '_method': 'DELETE'
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    showTable();
                                    if (typeof syncGlobalStats === "function") {
                                        syncGlobalStats();
                                    }
                                }
                            }
                        });
                    }
                });
            });

            var applyQuickAction = function() {
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
                            $('#quick-action-form').hide();
                        }
                    }
                })
            };

            $('#allTasks-table').on('change' + namespace, '.change-status', function() {
                var url = "{{ route('tasks.change_status') }}";
                var token = "{{ csrf_token() }}";
                var id = $(this).data('task-id');
                var status = $(this).val();

                if (id != "" && status != "") {
                    $.easyAjax({
                        url: url,
                        type: "POST",
                        container: '.content-wrapper',
                        blockUI: true,
                        data: {
                            '_token': token,
                            taskId: id,
                            status: status,
                            sortBy: 'id'
                        },
                        success: function(response) {
                            if ($('#timer-clock').length) {
                                $('#timer-clock').html(response.clockHtml);
                            }
                            showTable();
                        }
                    });
                }
            });

            $doc.on('click' + namespace, '#filter-my-task', function () {
                $('.filter-box #assignedTo').val('{{ user()->id }}');
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').removeClass('d-none');
                showTable();
            });

            $('#allTasks-table').on('click' + namespace, '.start-timer', function() {
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
                    data: {
                        task_id: task_id,
                        memo: memo,
                        '_token': token,
                        user_id: user_id
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            if (response.activeTimerCount > 0) {
                                $('#show-active-timer .active-timer-count').html(response.activeTimerCount).removeClass('d-none');
                            } else {
                                $('#show-active-timer .active-timer-count').addClass('d-none');
                            }
                            if ($('#timer-clock').length) {
                                $('#timer-clock').html(response.clockHtml);
                            }
                            showTable();
                        }
                    }
                })
            });

            $('#allTasks-table').on('click' + namespace, '.stop-timer', function() {
                var id = $(this).data('time-id');
                var url = "{{ route('timelogs.stop_timer', ':id') }}";
                url = url.replace(':id', id);
                var token = '{{ csrf_token() }}';
                $.easyAjax({
                    url: url,
                    blockUI: true,
                    container: '#allTasks-table',
                    type: "POST",
                    data: {
                        timeId: id,
                        _token: token
                    },
                    success: function(response) {
                        if (response.activeTimerCount > 0) {
                            $('#show-active-timer .active-timer-count').html(response.activeTimerCount).removeClass('d-none');
                        } else {
                            $('#show-active-timer .active-timer-count').addClass('d-none');
                        }
                        if (response.activeTimer == null) {
                            if ($('#timer-clock').length) {
                                $('#timer-clock').html('');
                            }
                            if (typeof runTimeClock !== 'undefined') runTimeClock = false;
                        }
                        showTable();
                    }
                })
            });

            $('#allTasks-table').on('click' + namespace, '.resume-timer', function() {
                var id = $(this).data('time-id');
                var url = "{{ route('timelogs.resume_timer', ':id') }}";
                url = url.replace(':id', id);
                var token = '{{ csrf_token() }}';
                $.easyAjax({
                    url: url,
                    blockUI: true,
                    type: "POST",
                    data: {
                        timeId: id,
                        _token: token
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            if (response.activeTimerCount > 0) {
                                $('#show-active-timer .active-timer-count').html(response.activeTimerCount).removeClass('d-none');
                            } else {
                                $('#show-active-timer .active-timer-count').addClass('d-none');
                            }
                            if ($('#timer-clock').length) {
                                $('#timer-clock').html(response.clockHtml);
                            }
                            showTable();
                        }
                    }
                })
            });

            $('#allTasks-table').on('click' + namespace, '.pause-timer', function() {
                var id = $(this).data('time-id');
                var url = "{{ route('timelogs.pause_timer', ':id') }}";
                url = url.replace(':id', id);
                var token = '{{ csrf_token() }}';
                $.easyAjax({
                    url: url,
                    blockUI: true,
                    type: "POST",
                    disableButton: true,
                    buttonSelector: "#pause-timer-btn",
                    data: {
                        timeId: id,
                        _token: token
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            if (response.activeTimerCount > 0) {
                                $('#show-active-timer .active-timer-count').html(response.activeTimerCount).removeClass('d-none');
                            } else {
                                $('#show-active-timer .active-timer-count').addClass('d-none');
                            }
                            if ($('#timer-clock').length) {
                                $('#timer-clock').html(response.clockHtml);
                            }
                            if (typeof runTimeClock !== 'undefined') runTimeClock = false;
                            showTable();
                        }
                    }
                })
            });

            // Export Modal Logic
            $body.on('show.bs.modal' + namespace, '#taskFormatExportModal', function () {
                $('#export_project_id').selectpicker('refresh');
            });

            $body.on('change' + namespace, '#export_project_id', function () {
                var pid = $(this).val();
                if (pid) {
                    var url = '{{ route('task_format_export', ':pid') }}'.replace(':pid', pid);
                    $('#download-task-template-btn').attr('href', url);
                } else {
                    $('#download-task-template-btn').attr('href', '#');
                }
            });

            $body.on('click' + namespace, '#download-task-template-btn', function (e) {
                var pid = $('#export_project_id').val();
                if (!pid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Project Required',
                        text: 'Please select a project before downloading the template.',
                        timer: 2500,
                        showConfirmButton: false
                    });
                    return;
                }
                $('#taskFormatExportModal').modal('hide');
            });

            // Import Modal Logic
            $body.on('show.bs.modal' + namespace, '#taskCsvImportModal', function () {
                $('#import_project_id').selectpicker('refresh');
                $('#import-result').addClass('d-none');
                $('#import-success-msg, #import-error-msg').addClass('d-none').html('');
                $('#task-csv-import-form')[0].reset();
                $('.custom-file-label').text('Choose CSV file...');
                $('#import-csv-submit-btn').prop('disabled', false)
                    .html('<i class="fa fa-upload mr-1"></i> Import Tasks');
            });

            $body.on('change' + namespace, '#import_csv_file', function () {
                var fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').text(fileName || 'Choose CSV file...');
            });

            $body.on('submit' + namespace, '#task-csv-import-form', function (e) {
                e.preventDefault();
                var pid = $('#import_project_id').val();
                if (!pid) {
                    Swal.fire({ icon: 'warning', title: 'Project Required',
                        text: 'Please select a project before importing.', timer: 2500, showConfirmButton: false });
                    return;
                }

                var fileInput = document.getElementById('import_csv_file');
                if (!fileInput.files.length) {
                    Swal.fire({ icon: 'warning', title: 'File Required',
                        text: 'Please choose a CSV file to upload.', timer: 2500, showConfirmButton: false });
                    return;
                }

                var formData = new FormData(this);
                var submitBtn = $('#import-csv-submit-btn');
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Importing...');

                $('#import-result').addClass('d-none');
                $('#import-success-msg, #import-error-msg').addClass('d-none').html('');

                $.ajax({
                    url: '{{ route('task_format_import') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        submitBtn.prop('disabled', false).html('<i class="fa fa-upload mr-1"></i> Import Tasks');
                        $('#import-result').removeClass('d-none');
                        if (response.status === 'success') {
                            var msg = '<strong>' + response.successCount + ' task(s) imported successfully!</strong>';
                            $('#import-success-msg').removeClass('d-none').html(msg);
                            if (response.errorRows && response.errorRows.length > 0) {
                                var errHtml = '<strong>Some rows were skipped:</strong><ul class="mb-0 mt-1">';
                                $.each(response.errorRows, function (i, err) {
                                    errHtml += '<li class="f-13">' + err + '</li>';
                                });
                                errHtml += '</ul>';
                                $('#import-error-msg').removeClass('d-none').html(errHtml);
                            }
                            showTable();
                        }
                    },
                    error: function (xhr) {
                        submitBtn.prop('disabled', false).html('<i class="fa fa-upload mr-1"></i> Import Tasks');
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Import failed.';
                        Swal.fire({ icon: 'error', title: 'Import Failed', text: msg });
                    }
                });
            });

            // Turbo Lifecycle Cleanup
            document.addEventListener('turbo:before-cache', function cleanup() {
                $doc.off(namespace);
                $('#allTasks-table').off(namespace);
                if (window.LaravelDataTables && window.LaravelDataTables["allTasks-table"]) {
                    window.LaravelDataTables["allTasks-table"].destroy();
                }
                window.showTable = undefined;
                window.applyQuickAction = undefined;
                document.removeEventListener('turbo:before-cache', cleanup);
            }, { once: true });
        })();
    </script>
@endpush
