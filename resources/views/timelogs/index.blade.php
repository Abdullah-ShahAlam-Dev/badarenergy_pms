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
                       id="datatableRange" placeholder="@lang('placeholders.dateRange')" />
            </div>
        </div>
        <!-- DATE END -->

        <!-- CLIENT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="employee" data-live-search="true"
                        data-size="8">
                    @if ($employees->count() > 1 || in_array('admin', user_roles()))
                        <option value="all">@lang('app.all')</option>
                    @endif
                    @foreach ($employees as $employee)
                            <x-user-option :user="$employee" :selected="request('assignee') == 'me' && $employee->id == user()->id"/>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- CLIENT END -->


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
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.project')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="project_id" id="project_id"
                                data-live-search="true"
                                data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ mb_ucwords($project->project_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.status')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="status" id="status" data-live-search="true"
                                data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.approved')</option>
                            <option value="0">@lang('app.pending')</option>
                            <option value="2">@lang('app.active')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.invoiceGenerate')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="invoice_generate" id="invoice_generate"
                                data-container="body" data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.yes')</option>
                            <option value="0">@lang('app.no')</option>
                        </select>
                    </div>
                </div>
            </div>


        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>

@endsection

@php
    $addTimelogPermission = user()->permission('add_timelogs');
@endphp


@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-lg-flex d-md-flex d-block my-3 justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if ($addTimelogPermission == 'all' || $addTimelogPermission == 'added')
                    <x-forms.link-primary :link="route('timelogs.create')" class="mr-3 openRightModal float-left"
                                          icon="plus">
                        @lang('modules.timeLogs.logTime')
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
                        <option value="0">@lang('app.pending')</option>
                        <option value="1">@lang('app.approve')</option>
                    </select>
                </div>
            </x-datatable.actions>


            <div class="btn-group ml-3" role="group">
              @include('timelogs.timelog-menu')
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
            var $body = $('body');
            var $table = $('#timelogs-table');
            var namespace = '.timelogsIndex';

            $table.off('preXhr.dt' + namespace).on('preXhr.dt' + namespace, function(e, settings, data) {
                var dateRangePicker = $('#datatableRange').data('daterangepicker');
                var startDate = $('#datatableRange').val();
                var endDate;

                if (startDate === '') {
                    startDate = null;
                    endDate = null;
                } else {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                data['startDate'] = startDate;
                data['endDate'] = endDate;
                data['projectId'] = $('#project_id').val();
                data['employee'] = $('#employee').val();
                data['approved'] = $('#status').val();
                data['invoice'] = $('#invoice_generate').val();
                data['searchText'] = $('#search-text-field').val();
            });

            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["timelogs-table"]) {
                    window.LaravelDataTables["timelogs-table"].draw(false);
                }
            }
            window.showTable = showTable;

            function applyQuickAction() {
                var rowdIds = $("#timelogs-table input:checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                $.easyAjax({
                    url: "{{ route('timelogs.apply_quick_action') }}?row_ids=" + rowdIds,
                    container: '#quick-action-form',
                    type: "POST",
                    disableButton: true,
                    buttonSelector: "#quick-action-apply",
                    data: $('#quick-action-form').serialize(),
                    blockUI: true,
                    success: function(response) {
                        if (response.status === 'success') {
                            showTable();
                            if (typeof resetActionButtons === 'function') resetActionButtons();
                            if (typeof deSelectAll === 'function') deSelectAll();
                            $('#quick-action-form').hide();
                        }
                    }
                });
            }

            $body.off(namespace);

            $body.on('change' + namespace + ' keyup' + namespace, '#project_id, #employee, #status, #invoice_generate', function() {
                var anyFilter = $('#status').val() !== 'all' || $('#employee').val() !== 'all' ||
                                $('#project_id').val() !== 'all' || $('#invoice_generate').val() !== 'all';
                $('#reset-filters').toggleClass('d-none', !anyFilter);
                showTable();
            });

            $body.on('keyup' + namespace, '#search-text-field', function() {
                if ($(this).val() !== '') {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                }
            });

            $body.on('click' + namespace, '#reset-filters, #reset-filters-2', function() {
                $('#filter-form')[0].reset();
                $('.filter-box .select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            $body.on('change' + namespace, '#quick-action-type', function() {
                var actionValue = $(this).val();
                if (actionValue !== '') {
                    $('#quick-action-apply').removeAttr('disabled');
                    $('.quick-action-field').addClass('d-none');
                    if (actionValue === 'change-status') { $('#change-status-action').removeClass('d-none'); }
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.quick-action-field').addClass('d-none');
                }
            });

            $body.on('click' + namespace, '#quick-action-apply', function() {
                var actionValue = $('#quick-action-type').val();
                if (actionValue === 'delete') {
                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')", text: "@lang('messages.recoverRecord')",
                        icon: 'warning', showCancelButton: true, focusConfirm: false,
                        confirmButtonText: "@lang('messages.confirmDelete')", cancelButtonText: "@lang('app.cancel')",
                        customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                        showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }, buttonsStyling: false
                    }).then(function(result) { if (result.isConfirmed) { applyQuickAction(); } });
                } else { applyQuickAction(); }
            });

            $body.on('click' + namespace, '.delete-table-row', function() {
                var id = $(this).data('time-id');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')", text: "@lang('messages.recoverRecord')",
                    icon: 'warning', showCancelButton: true, focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')", cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }, buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var url = "{{ route('timelogs.destroy', ':id') }}".replace(':id', id);
                        $.easyAjax({
                            type: 'POST', url: url, blockUI: true,
                            data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                            success: function(response) { if (response.status === 'success') { showTable(); } }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.stop-active-timer', function() {
                var id = $(this).data('time-id');
                var url = "{{ route('timelogs.stop_timer', ':id') }}".replace(':id', id);
                $.easyAjax({
                    url: url, type: 'POST',
                    data: { timeId: id, _token: '{{ csrf_token() }}' },
                    success: function() { showTable(); }
                });
            });

            $body.on('click' + namespace, '.approve-timelog', function() {
                var id = $(this).data('time-id');
                var url = "{{ route('timelogs.approve_timelog', ':id') }}".replace(':id', id);
                $.easyAjax({
                    url: url, type: 'POST',
                    data: { id: id, _token: '{{ csrf_token() }}' },
                    success: function() { showTable(); }
                });
            });

            @if (!is_null(request('start')) && !is_null(request('end')))
                $('#datatableRange').data('daterangepicker').setStartDate('{{ request('start') }}');
                $('#datatableRange').data('daterangepicker').setEndDate('{{ request('end') }}');
                $('#datatableRange').val('{{ request('start') }}' + ' @lang("app.to") ' + '{{ request('end') }}');
                showTable();
            @endif

            document.addEventListener('turbo:before-cache', function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt' + namespace);
                delete window.showTable;
                document.removeEventListener('turbo:before-cache', cleanup);
            }, { once: true });
        })();
    </script>
@endpush
