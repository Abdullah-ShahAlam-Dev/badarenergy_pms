@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

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

        <div class="select-box d-flex py-2 {{ !in_array('client', user_roles()) ? 'px-lg-2 px-md-2 px-0' : '' }}  border-right-grey border-right-grey-sm-0 pr-2 pl-2">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-size="8">
                    <option {{ request('status') == 'all' ? 'selected' : '' }} value="all">@lang('app.all')</option>
                    <option {{ request('status') == 'overdue' ? 'selected' : '' }} value="overdue">@lang('app.overdue')
                    </option>
                    @foreach ($projectStatus as $status)
                        <option value="{{$status->status_name}}">{{ ucfirst($status->status_name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="select-box d-flex py-2 {{ !in_array('client', user_roles()) ? 'px-lg-2 px-md-2 px-0' : '' }}  border-right-grey border-right-grey-sm-0 pr-2 pl-2">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.progress')</p>
            <div class="select-status mw-120">
                <x-forms.input-group>
                    <select class="select-picker form-control" multiple name="progress[]" id="progress" data-live-search="true" data-size="8">
                        <option value="0-20" selected>0% - 20%</option>
                        <option value="21-40" selected>21% - 40%</option>
                        <option value="41-60" selected>41% - 60%</option>
                        <option value="61-80" selected>61% - 80%</option>
                        <option value="81-99" selected>81% - 99%</option>
                        <option value="100-100" {{ request()->projects == 'all' ? 'selected' : ''}}>100%</option>
                    </select>
                </x-forms.input-group>
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
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
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
                        <option value="deadline">@lang('app.deadline')</option>
                        <option value="start_date">@lang('app.startDate')</option>
                    </select>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.clientName')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="client_id" id="client_id" data-container="body"
                            data-size="8">
                            @if (!in_array('client', user_roles()))
                                <option selected value="all">@lang('app.all')</option>
                            @endif
                            @foreach ($clients as $client)
                                <x-user-option :user="$client" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize"
                    for="usr">@lang('modules.projects.projectCategory')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="category_id" id="category_id"
                            data-live-search="true" data-container="body" data-size="8">
                            <option selected value="all">@lang('app.all')</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


            @if (!in_array('client', user_roles()))
                <div class="more-filter-items">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.projectMember')</label>
                    <div class="select-filter mb-4">
                        <div class="select-others">
                            <select class="form-control select-picker" name="employee_id" id="employee_id"
                                data-live-search="true" data-container="body" data-size="8">
                                @if ($allEmployees->count() > 1 || in_array('admin', user_roles()))
                                    <option value="all">@lang('app.all')</option>
                                @endif
                                @foreach ($allEmployees as $employee)
                                        <x-user-option :user="$employee" :selected="request('assignee') == 'me' && $employee->id == user()->id"/>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.department')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="team_id" id="team_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.pinned')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="pinned" id="pinned" data-container="body"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="pinned">@lang('app.pinned')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize"
                    for="usr">@lang('app.public')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="public" id="public"
                            data-live-search="true" data-container="body" data-size="8">
                            <option selected value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.public')</option>
                        </select>
                    </div>
                </div>
            </div>
        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->

    </x-filters.filter-box>

@endsection

@php
$addProjectPermission = user()->permission('add_projects');
$manageProjectTemplatePermission = user()->permission('manage_project_template');
$viewProjectTemplatePermission = user()->permission('view_project_template');
$deleteProjectPermission = user()->permission('delete_projects');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if ($addProjectPermission == 'all' || $addProjectPermission == 'added' || $addProjectPermission == 'both')
                    <x-forms.link-primary :link="route('projects.create')"
                        class="mr-3 openRightModal float-left mb-2 mb-lg-0 mb-md-0" icon="plus">
                        @lang('app.add')
                        @lang('app.project')
                    </x-forms.link-primary>
                @endif
                @if ($viewProjectTemplatePermission == 'all' || in_array($manageProjectTemplatePermission , ['added', 'all']))
                    <x-forms.link-secondary :link="route('project-template.index')"
                        class="mr-3 mb-2 mb-lg-0 mb-md-0 float-left" icon="layer-group">
                        @lang('app.menu.projectTemplate')
                    </x-forms.link-secondary>
                @endif


                @if ($addProjectPermission == 'all' || $addProjectPermission == 'added' || $addProjectPermission == 'both')
                    <x-forms.link-secondary :link="route('projects.import')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif

            </div>

            @if (!in_array('client', user_roles()))
                <x-datatable.actions>
                    <div class="select-status mr-3 pl-3">
                        <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                            <option value="">@lang('app.selectAction')</option>
                            <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                            <option value="archive">@lang('app.archive')</option>
                            <option value="delete">@lang('app.delete')</option>
                        </select>
                    </div>
                    <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select-picker">
                            <option value="not started">@lang('app.notStarted')</option>
                            <option value="in progress">@lang('app.inProgress')</option>
                            <option value="on hold">@lang('app.onHold')</option>
                            <option value="canceled">@lang('app.canceled')</option>
                            <option value="finished">@lang('app.finished')</option>
                        </select>
                    </div>
                </x-datatable.actions>
            @endif


            <div class="btn-group ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary f-14 btn-active projects" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.projects')"><i class="side-icon bi bi-list-ul"></i></a>

                    @if ($deleteProjectPermission != 'none')
                        <a href="{{ route('projects.archive') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                            data-original-title="@lang('app.archive')"><i class="side-icon bi bi-archive"></i></a>
                    @endif
                    <a href="{{ route('project-calendar.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
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

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var deadLineStartDate = '';
            var deadLineEndDate = '';
            var startFilterDate = '';
            var endFilterDate = '';
            var $body = $('body');
            var namespace = '.projectsIndex';
            var $table = $('#projects-table');

            // Initial cleanup of any previous instances/listeners
            $body.off(namespace);
            if (window.LaravelDataTables && window.LaravelDataTables["projects-table"]) {
                window.LaravelDataTables["projects-table"].destroy();
            }

            $(".select-picker").selectpicker();

            $table.on('preXhr.dt' + namespace, function(e, settings, data) {
                var dateRangePicker = $('#datatableRange').data('daterangepicker');
                var currentRange = $('#datatableRange').val();

                if (currentRange == '') {
                    startFilterDate = null;
                    endFilterDate = null;
                } else if (dateRangePicker) {
                    startFilterDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endFilterDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                var status = $('#status').val();
                var clientID = $('#client_id').val();
                var categoryID = $('#category_id').val();
                var teamID = $('#team_id').val();
                var employee_id = $('#employee_id').val();
                var pinned = $('#pinned').val();
                var date_filter_on = $('#date_filter_on').val();
                var public = $('#public').val();
                var searchText = $('#search-text-field').val();
                var progress = $('#progress').val();

                @if (request('deadLineStartDate') && request('deadLineEndDate'))
                    deadLineStartDate = '{{ request("deadLineStartDate") }}';
                    deadLineEndDate = '{{ request("deadLineEndDate") }}';
                @endif

                data['status'] = status;
                data['progress'] = progress;
                data['client_id'] = clientID;
                data['pinned'] = pinned;
                data['date_filter_on'] = date_filter_on;
                data['category_id'] = categoryID;
                data['team_id'] = teamID;
                data['public'] = public;
                data['employee_id'] = employee_id;
                data['deadLineStartDate'] = deadLineStartDate;
                data['deadLineEndDate'] = deadLineEndDate;
                data['startFilterDate'] = startFilterDate;
                data['endFilterDate'] = endFilterDate;
                data['searchText'] = searchText;
                @if (!is_null(request('start')) && !is_null(request('end')))
                    data['startDate'] = '{{ request('start') }}';
                    data['endDate'] = '{{ request('end') }}';
                @endif
            });

            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["projects-table"]) {
                    window.LaravelDataTables["projects-table"].draw(false);
                }
            }
            window.showTable = showTable;

            $body.on('change' + namespace + ' keyup' + namespace, '#client_id, #status, #employee_id, #team_id, #category_id, #pinned, #date_filter_on, #public, #progress', function() {
                var filtersActive = ($('#status').val() != "not finished") ||
                                    ($('#employee_id').val() != "all") ||
                                    ($('#team_id').val() != "all") ||
                                    ($('#category_id').val() != "all") ||
                                    ($('#client_id').val() != "all") ||
                                    ($('#pinned').val() != "all") ||
                                    ($('#date_filter_on').val() != "deadline") ||
                                    ($('#public').val() != "all") ||
                                    ($('#progress').val() != "all");

                if (filtersActive) {
                    $('#reset-filters').removeClass('d-none');
                } else {
                    $('#reset-filters').addClass('d-none');
                }
                window.showTable();
            });

            $body.on('keyup' + namespace, '#search-text-field', function() {
                if ($(this).val() != "") {
                    $('#reset-filters').removeClass('d-none');
                }
                window.showTable();
            });

            $body.on('click' + namespace, '.show-pinned', function() {
                $('.projects').removeClass('btn-active');
                if ($(this).hasClass('btn-active')) {
                    $('#pinned').val('all');
                } else {
                    $('#pinned').val('pinned');
                }

                $('#pinned').selectpicker('refresh');
                $(this).toggleClass('btn-active');
                $('#reset-filters').removeClass('d-none');
                window.showTable();
            });

            $body.on('click' + namespace, '#reset-filters, #reset-filters-2', function() {
                $('#filter-form')[0].reset();
                $('.filter-box #date_filter_on').val('deadline');
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').addClass('d-none');
                window.showTable();
            });

            $body.on('change' + namespace, '.change-status', function() {
                var url = "{{ route('projects.change_status') }}";
                var token = "{{ csrf_token() }}";
                var id = $(this).data('project-id');
                var status = $(this).val();

                if (id != "" && status != "") {
                    $.easyAjax({
                        url: url,
                        type: "POST",
                        container: '.content-wrapper',
                        blockUI: true,
                        data: {
                            '_token': token,
                            projectId: id,
                            statusId: status,
                            sortBy: 'id'
                        },
                        success: function(data) {
                            window.showTable();
                        }
                    });
                }
            });

            $body.on('change' + namespace, '#quick-action-type', function() {
                var actionValue = $(this).val();
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
                        if (result.isConfirmed) {
                            window.applyQuickAction();
                        }
                    });
                } else {
                    window.applyQuickAction();
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
                        var url = "{{ route('projects.destroy', ':id') }}";
                        url = url.replace(':id', id);
                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            container: '.content-wrapper',
                            blockUI: true,
                            data: { '_token': token, '_method': 'DELETE' },
                            success: function(response) {
                                if (response.status == "success") { window.showTable(); }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.archive', function() {
                var id = $(this).data('user-id');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.archiveMessage')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmArchive')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "{{ route('projects.archive_delete', ':id') }}";
                        url = url.replace(':id', id);
                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            container: '.content-wrapper',
                            blockUI: true,
                            data: { '_token': token },
                            success: function(response) {
                                if (response.status == "success") { window.showTable(); }
                            }
                        });
                    }
                });
            });

            function applyQuickAction() {
                var rowdIds = $("#projects-table input:checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                var url = "{{ route('projects.apply_quick_action') }}?row_ids=" + rowdIds;

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
                            $('#quick-action-apply').attr('disabled', 'disabled');
                            $('#change-status-action').addClass('d-none');
                            $('#quick-action-form').hide();
                        }
                    }
                })
            }
            window.applyQuickAction = applyQuickAction;

            $body.on('click' + namespace, '.duplicateProject', function() {
                var id = $(this).data('project-id');
                var url = "{{ route('projects.duplicate_project', ':id') }}".replace(':id', id);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            document.addEventListener('turbo:before-cache', function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt' + namespace);
                if (window.LaravelDataTables && window.LaravelDataTables["projects-table"]) {
                    window.LaravelDataTables["projects-table"].destroy();
                }
                delete window.showTable;
                delete window.applyQuickAction;
                document.removeEventListener('turbo:before-cache', cleanup);
            }, { once: true });

        })();
    </script>
@endpush
