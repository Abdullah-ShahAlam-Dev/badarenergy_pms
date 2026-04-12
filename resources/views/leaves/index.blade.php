@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        .filter-box {
            z-index: 2;
        }

    </style>
@endpush


@php
$addLeavePermission = user()->permission('add_leave');
$approveRejectPermission = user()->permission('approve_or_reject_leaves');
@endphp

@section('filter-section')
    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')"
                    value="{{ request('start') && request('end') ? request('start') . ' ' . __('app.to') . ' ' . request('end') : '' }}">
            </div>
        </div>
        <!-- DATE END -->

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-2 px-0 border-right-grey align-items-center">
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
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.employee')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="employee_id" id="employee_id"
                            data-live-search="true" data-container="body" data-size="8">
                            @if ($employees->count() > 1 || in_array('admin', user_roles()))
                                <option value="all">@lang('app.all')</option>
                            @endif
                            @foreach ($employees as $employee)
                                    <x-user-option :user="$employee" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.leaves.leaveType')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="leave_type" id="leave_type" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}">{{ mb_ucwords($leaveType->type_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if ($approveRejectPermission == 'all')
                <div class="more-filter-items">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.status')</label>
                    <div class="select-filter mb-4">
                        <div class="select-others">
                            <select class="form-control select-picker" name="status" id="status" data-live-search="true"
                                data-container="body" data-size="8">
                                <option value="all">@lang('app.all')</option>
                                <option {{ request('status') == 'approved' ? 'selected' : '' }} value="approved">
                                    @lang('app.approved')</option>
                                <option value="pending">@lang('app.pending')</option>
                                <option value="rejected">@lang('app.rejected')</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif


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
                @if ($addLeavePermission == 'all' || $addLeavePermission == 'added')
                    <x-forms.link-primary :link="route('leaves.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('modules.leaves.addLeave')
                    </x-forms.link-primary>
                @endif
            </div>


            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        @if ($approveRejectPermission == 'all')
                            <option value="change-leave-status">@lang('app.change') @lang('app.leaveStatus')</option>
                        @endif
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="approved">@lang('app.approved')</option>
                        <option value="pending">@lang('app.pending')</option>
                        <option value="rejected">@lang('app.rejected')</option>
                    </select>
                </div>
            </x-datatable.actions>


            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-3" role="group" aria-label="Basic example">
                <a href="{{ route('leaves.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                    data-original-title="@lang('modules.leaves.tableView')"><i class="side-icon bi bi-list-ul"></i></a>

                <a href="{{ route('leaves.calendar') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.calendar')"><i class="side-icon bi bi-calendar"></i></a>

                <a href="{{ route('leaves.personal') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                    data-original-title="@lang('modules.leaves.myLeaves')"><i class="side-icon bi bi-person"></i></a>
            </div>
        </div>

        <!-- leave table Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- leave table End -->

    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var $body = $('body');
            var $table = $('#leaves-table');
            var namespace = '.leavesIndex';

            $table.off('preXhr.dt' + namespace).on('preXhr.dt' + namespace, function(e, settings, data) {
                @if (request('start') && request('end'))
                    $('#datatableRange').data('daterangepicker').setStartDate("{{ request('start') }}");
                    $('#datatableRange').data('daterangepicker').setEndDate("{{ request('end') }}");
                @endif

                var dateRangePicker = $('#datatableRange').data('daterangepicker');
                var startDate = $('#datatableRange').val();
                var endDate;

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
                data['employeeId'] = $('#employee_id').val();
                data['leaveTypeId'] = $('#leave_type').val();
                data['status'] = $('#status').val();
            });

            var showTable = function() {
                var table = window.LaravelDataTables["leaves-table"];
                if (table) {
                    table.draw(false);
                }
            }
            window.showTable = showTable;

            function applyQuickAction() {
                var rowdIds = $("#leaves-table input:checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                $.easyAjax({
                    url: "{{ route('leaves.apply_quick_action') }}?row_ids=" + rowdIds,
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

            $body.off(namespace);

            $body.on('change' + namespace + ' keyup' + namespace, '#start-date, #end-date, #employee_id, #leave_type, #status', function() {
                var anyFilter = $('#start-date').val() != "" || $('#end-date').val() != "" || 
                                $('#employee_id').val() != "all" || $('#leave_type').val() != "all" || 
                                $('#status').val() != "all";
                $('#reset-filters').toggleClass('d-none', !anyFilter);
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
                    if (actionValue == 'change-leave-status') { $('#change-status-action').removeClass('d-none'); }
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.quick-action-field').addClass('d-none');
                }
            });

            $body.on('click' + namespace, '#quick-action-apply', function() {
                var actionValue = $('#quick-action-type').val();
                if (actionValue == 'delete') {
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
                var id = $(this).data('leave-id');
                var uniId = $(this).data('unique-id');
                var duration = $(this).data('duration');
                var type = $(this).data('type');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')", text: "@lang('messages.recoverRecord')",
                    icon: 'warning', showCancelButton: true, focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')", cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }, buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var url = "{{ route('leaves.destroy', ':id') }}".replace(':id', id);
                        $.easyAjax({
                            type: 'POST', url: url, blockUI: true,
                            data: { 'uniId': uniId, 'duration': duration, '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                            success: function(response) {
                                if (response.status == "success") {
                                    if(type == 'multiple-leave'){ window.location.reload(); } else{ showTable(); }
                                }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.show-leave', function() {
                var leaveId = $(this).data('leave-id');
                var url = '{{ route('leaves.show', ':id') }}'.replace(':id', leaveId);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            $body.on('click' + namespace, '.leave-action-approved', function() {
                var action = $(this).data('leave-action');
                var leaveId = $(this).data('leave-id');
                var type = $(this).data('type') || 'single';
                var url = "{{ route('leaves.show_approved_modal') }}" + "?leave_action=" + action + "&leave_id=" + leaveId + "&type=" + type;
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            $body.on('click' + namespace, '.leave-action-reject', function() {
                var action = $(this).data('leave-action');
                var leaveId = $(this).data('leave-id');
                var type = $(this).data('type') || 'single';
                var url = "{{ route('leaves.show_reject_modal') }}" + "?leave_action=" + action + "&leave_id=" + leaveId + "&type=" + type;
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            $body.on('click' + namespace, '.leave-action-preapprove', function() {
                var action = $(this).data('leave-action');
                var leaveId = $(this).data('leave-id');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')", text: "@lang('messages.changeLeaveStatusConfirmation')",
                    icon: 'warning', showCancelButton: true, focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirm')", cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }, buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            type: 'POST', url: "{{ route('leaves.pre_approve_leave') }}", blockUI: true,
                            data: { 'action': action, 'leaveId': leaveId, '_token': '{{ csrf_token() }}' },
                            success: function(response) {
                                if (response.status == 'success') {
                                    showTable();
                                    if (typeof resetActionButtons === 'function') resetActionButtons();
                                    if (typeof deSelectAll === 'function') deSelectAll();
                                }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.view-related-leave', function() {
                var leaveId = $(this).data('leave-id');
                var uniqueId = $(this).data('unique-id');
                var url = "{{ route('leaves.view_related_leave', ':id') }}".replace(':id', leaveId) + "?uniqueId="+uniqueId;
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            document.addEventListener('turbo:before-cache', function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt' + namespace);
                delete window.showTable;
                document.removeEventListener('turbo:before-cache', cleanup);
            }, { once: true });
        })();
    </script>
@endpush
