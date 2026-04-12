@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    @include('leads.filters')

@endsection

@php
$addLeadPermission = user()->permission('add_lead');
$addLeadCustomFormPermission = user()->permission('manage_lead_custom_forms');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addLeadPermission == 'all' || $addLeadPermission == 'added')
                    <x-forms.link-primary :link="route('leads.create')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="plus">
                        @lang('app.add')
                        @lang('app.lead')
                    </x-forms.link-primary>
                @endif

                @if ($addLeadCustomFormPermission == 'all')
                    <x-forms.button-secondary icon="pencil-alt" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" id="add-lead">
                        @lang('modules.lead.leadForm')
                    </x-forms.button-secondary>
                @endif

                @if ($addLeadPermission == 'all' || $addLeadPermission == 'added')
                    <x-forms.link-secondary :link="route('leads.import')" class="mr-3 openRightModal float-left mb-2 mb-lg-0 mb-md-0" icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="change-agent">@lang('modules.lead.changeAgent')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-agent-action">
                    <select name="agent_id" class="form-control select-picker">
                        @foreach ($leadAgents as $emp)
                            <x-user-option :user="$emp->user" :userID="$emp->id" />
                        @endforeach
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        @foreach ($status as $st)
                            <option value="{{ $st->id }}">{{ $st->type }}</option>
                        @endforeach
                    </select>
                </div>
            </x-datatable.actions>

            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('leads.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                    data-original-title="@lang('modules.leaves.tableView')"><i class="side-icon bi bi-list-ul"></i></a>

                <a href="{{ route('leadboards.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip" data-original-title="@lang('modules.lead.kanbanboard')"><i class="side-icon bi bi-kanban"></i></a>
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
            var $table = $('#leads-table');
            var namespace = '.leadsIndex';

            document.addEventListener("turbo:before-cache", function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt' + namespace);
                delete window.showTable;
                delete window.applyQuickAction;
                delete window.changeStatus;
                delete window.followUp;
                document.removeEventListener("turbo:before-cache", cleanup);
            }, { once: true });

            $table.off('preXhr.dt').on('preXhr.dt' + namespace, function(e, settings, data) {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = $('#search-text-field').val();
            data['type'] = $('#type').val();
            data['followUp'] = $('#followUp').val();
            data['agent'] = $('#filter_agent_id').val();
            data['min'] = $('#min').val();
            data['max'] = $('#max').val();
            data['category_id'] = $('#filter_category_id').val();
            data['source_id'] = $('#filter_source_id').val();
            data['status_id'] = $('#filter_status_id').val();
            data['date_filter_on'] = $('#date_filter_on').val();
        });

            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["leads-table"]) {
                    window.LaravelDataTables["leads-table"].draw(false);
                }
            }
            window.showTable = showTable;

            $body.off(namespace);

            $body.on('click' + namespace, '#reset-filters', function() {
            $('#filter-form')[0].reset();
            $('.filter-box #status').val('not finished');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $body.on('click' + namespace, '#reset-filters-2', function() {
            $('#filter-form')[0].reset();
            $('.filter-box #status').val('all');
            $('.filter-box #leave_type').val('all');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $body.on('change' + namespace, '#quick-action-type', function() {
            var actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                $('.quick-action-field').addClass('d-none');
                if (actionValue == 'change-status') {
                    $('#change-status-action').removeClass('d-none');
                } else if (actionValue == 'change-agent') {
                    $('#change-agent-action').removeClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

            function applyQuickAction() {
                var rowdIds = $("#leads-table input:checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                var url = "{{ route('leads.apply_quick_action') }}?row_ids=" + rowdIds;

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
                });
            }
            window.applyQuickAction = applyQuickAction;

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
            var id = $(this).data('id');
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
                    var url = "{{ route('leads.destroy', ':id') }}".replace(':id', id);
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
            var url = "{{ route('leads.change_status') }}";
            var token = "{{ csrf_token() }}";
            var id = $(this).data('task-id');
            var status = $(this).val();

            if (id != "" && status != "") {
                $.easyAjax({
                    url: url,
                    type: "POST",
                    data: { '_token': token, taskId: id, status: status, sortBy: 'id' },
                    success: function(data) {
                        showTable();
                        if (typeof resetActionButtons === 'function') resetActionButtons();
                        if (typeof deSelectAll === 'function') deSelectAll();
                    }
                });
            }
        });

            function changeStatus(leadID, statusID) {
                var url = "{{ route('leads.change_status') }}";
                var token = "{{ csrf_token() }}";
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: { '_token': token, 'leadID': leadID, 'statusID': statusID },
                    success: function(response) {
                        if (response.status == "success") {
                            showTable();
                            if (typeof resetActionButtons === 'function') resetActionButtons();
                            if (typeof deSelectAll === 'function') deSelectAll();
                        }
                    }
                });
            }
            window.changeStatus = changeStatus;

            function followUp(leadID) {
                var url = '{{ route('leads.follow_up', ':id') }}'.replace(':id', leadID);
                $.ajaxModal(MODAL_LG, url);
            }
            window.followUp = followUp;

            $body.on('click' + namespace, '#add-lead', function() {
            window.location.href = "{{ route('lead-form.index') }}";
        });

        // Initialize from request params if present
        @if (!is_null(request('start')) && !is_null(request('end')))
            $('#datatableRange').val('{{ request('start') }}' + ' @lang("app.to") ' + '{{ request('end') }}');
            if ($('#datatableRange').data('daterangepicker')) {
                $('#datatableRange').data('daterangepicker').setStartDate("{{ request('start') }}");
                $('#datatableRange').data('daterangepicker').setEndDate("{{ request('end') }}");
            }
            showTable();
        @endif

    })();
</script>
@endpush
