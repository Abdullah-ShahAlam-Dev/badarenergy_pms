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

        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="filter-status">
                    <option value="all">@lang('app.all')</option>
                    <option value="pending">@lang('app.pending')</option>
                    <option value="approved">@lang('app.approved')</option>
                    <option value="rejected">@lang('app.rejected')</option>
                </select>
            </div>
        </div>
        <!-- STATUS END -->

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
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.employee')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="employee2" data-live-search="true" data-container="body" data-size="8">
                            @if ($employees->count() > 1 || in_array('admin', user_roles()))
                                <option value="all">@lang('app.all')</option>
                            @endif
                            @foreach ($employees as $item)
                                    <x-user-option :user="$item" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.project')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="project_id" id="project_id2" data-container="body" data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ mb_ucwords($project->project_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.category')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="category_id" id="category_id" data-container="body" data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ mb_ucwords($category->category_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->

    </x-filters.filter-box>

@endsection

@php
$addExpensesPermission = user()->permission('add_expenses');
$recurringExpensesPermission = user()->permission('manage_recurring_expense');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addExpensesPermission == 'all' || $addExpensesPermission == 'added')
                    <x-forms.link-primary :link="route('expenses.create')" class="mr-3 float-left openRightModal"
                        icon="plus">
                        @lang('modules.expenses.addExpense')
                    </x-forms.link-primary>
                @endif
                @if ($recurringExpensesPermission == 'all')
                    <x-forms.link-secondary :link="route('recurring-expenses.index')" class="mr-3 float-left"
                        icon="sync">
                        @lang('app.menu.expensesRecurring')
                    </x-forms.link-secondary>
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
                        <option value="pending">@lang('app.pending')</option>
                        <option value="approved">@lang('app.approved')</option>
                        <option value="rejected">@lang('app.rejected')</option>
                    </select>
                </div>
            </x-datatable.actions>

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
            var $table = $('#expenses-table');
            var namespace = '.expensesIndex';

            $table.off('preXhr.dt').on('preXhr.dt', function(e, settings, data) {
                var dateRangePicker = $('#datatableRange').data('daterangepicker');
                var startDate = $('#datatableRange').val();
                var endDate = null;

                if (startDate == '') {
                    startDate = null;
                } else if (dateRangePicker) {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                var status = $('#filter-status').val();
                var searchText = $('#search-text-field').val();
                var employee = $('#employee2').val();
                var projectId = $('#project_id2').val();
                var categoryId = $('#category_id').val();

                data['startDate'] = startDate;
                data['endDate'] = endDate;
                data['status'] = status;
                data['employee'] = employee;
                data['projectId'] = projectId;
                data['categoryId'] = categoryId;
                data['searchText'] = searchText;
            });

            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["expenses-table"]) {
                    window.LaravelDataTables["expenses-table"].draw(false);
                }
            }
            window.showTable = showTable;

            $body.off(namespace);

            $body.on('change' + namespace + ' keyup' + namespace,
                '#filter-status, #employee2, #project_id2, #category_id', function() {
                if ($('#filter-status').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                } else {
                    $('#reset-filters').addClass('d-none');
                }
                showTable();
            });

            $body.on('keyup' + namespace, '#search-text-field', function() {
                if ($(this).val() != '') { $('#reset-filters').removeClass('d-none'); }
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters', function() {
                $('#filter-form')[0].reset();
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters-2', function() {
                $('#filter-form')[0].reset();
                $('.filter-box #status').val('not finished');
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
                            applyQuickAction();
                        }
                    });
                } else {
                    applyQuickAction();
                }
            });

            $body.on('change' + namespace, '.change-expense-status', function() {
                var id = $(this).data('expense-id');
                var token = "{{ csrf_token() }}";
                var status = $(this).val();

                if (typeof id !== 'undefined') {
                    $.easyAjax({
                        url: "{{ route('expenses.change_status') }}",
                        type: "POST",
                        data: { '_token': token, expenseId: id, status: status },
                        success: function(response) {
                            if (response.status == "success") {
                                showTable();
                                if (typeof resetActionButtons === 'function') resetActionButtons();
                                if (typeof deSelectAll === 'function') deSelectAll();
                            }
                        }
                    });
                }
            });

            $body.on('click' + namespace, '.delete-table-row', function() {
                var id = $(this).data('expense-id');
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
                        var url = "{{ route('expenses.destroy', ':id') }}".replace(':id', id);
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

            function applyQuickAction() {
                var rowdIds = $("#expenses-table input:checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                $.easyAjax({
                    url: "{{ route('expenses.apply_quick_action') }}?row_ids=" + rowdIds,
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

            document.addEventListener("turbo:before-cache", function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt');
                delete window.showTable;
                delete window.applyQuickAction;
                document.removeEventListener("turbo:before-cache", cleanup);
            }, { once: true });
        })();
    </script>
@endpush
