@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <meta name="turbo-cache-control" content="no-cache">
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- CLIENT START -->
        <div class="select-box py-2 d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="employee" data-live-search="true"
                        data-size="8">
                    @if ($employees->count() > 1 || in_array('admin', user_roles()))
                        <option value="all">@lang('app.all')</option>
                    @endif
                    @foreach ($employees as $employee)
                        <x-user-option :user="$employee"/>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- CLIENT END -->

        <!-- DESIGNATION START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.designation')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="designation" id="designation">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}">{{ ucfirst($designation->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- DESIGNATION END -->


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
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.department')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="department" data-container="body"
                                id="department">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ ucfirst($department->team_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize"
                       for="usr">@lang('modules.employees.role')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="role" id="role" data-container="body">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($roles as $role)
                                @if (in_array($role->name, ['admin', 'client', 'employee']))
                                    <option value="{{ $role->id }}">{{ __('app.' . $role->name) }}</option>
                                @else
                                    <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.status')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="status" id="status" data-container="body">
                            <option value="all">@lang('app.all')</option>
                            <option selected value="active">@lang('app.active')</option>
                            <option value="deactive">@lang('app.inactive')</option>
                            <option {{ request('status') == 'ex_employee' ? 'selected' : '' }} value="ex_employee">
                                @lang('modules.employees.exEmployee')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.employees.gender')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="gender" id="gender" data-container="body">
                            <option value="all">@lang('app.all')</option>
                            <option value="male">@lang('app.male')</option>
                            <option value="female">@lang('app.female')</option>
                            <option value="others">@lang('app.others')</option>
                        </select>
                    </div>
                </div>
            </div>

        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>

@endsection

@php
    $addEmployeePermission = user()->permission('add_employees');
    $addDesignationPermission = user()->permission('add_designation');
    $viewDesignationPermission = user()->permission('view_designation');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">

            <div id="table-actions" class="d-block d-lg-flex align-items-center">
                @if ($addEmployeePermission == 'all')
                    <x-forms.link-primary :link="route('employees.create')" class="mr-3 openRightModal" icon="plus">
                        @lang('app.add')
                        @lang('app.employee')
                    </x-forms.link-primary>

                    <x-forms.button-secondary class="mr-3 invite-member mb-2 mb-lg-0" icon="plus">
                        @lang('app.invite') @lang('app.employee')
                    </x-forms.button-secondary>
                @endif

                @if ($addEmployeePermission == 'all')
                    <x-forms.link-secondary :link="route('employees.import')" class="mr-3 openRightModal mb-2 mb-lg-0"
                                            icon="file-upload">
                        @lang('app.importExcel')
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
                        <option value="deactive">@lang('app.inactive')</option>
                        <option value="active">@lang('app.active')</option>
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
            // Blade-rendered values (safe — these are PHP variables baked in at render time)
            var startDate = null;
            var endDate = null;
            var lastStartDate = null;
            var lastEndDate = null;

            @if(request('startDate') != '' && request('endDate') != '' )
                startDate = '{{ request("startDate") }}';
                endDate   = '{{ request("endDate") }}';
            @endif

            @if(request('lastStartDate') !=='' && request('lastEndDate') !=='' )
                lastStartDate = '{{ request("lastStartDate") }}';
                lastEndDate   = '{{ request("lastEndDate") }}';
            @endif

            // ============================================================
            // initEmployeePage — called on every page load (Turbo or full)
            // ============================================================
            function initEmployeePage() {
                // Guard: only run if the employees table actually exists in DOM
                if (!document.getElementById('employees-table')) return;

                // ---- showTable ----
                window.showTable = function() {
                    if (window.LaravelDataTables && window.LaravelDataTables["employees-table"]) {
                        window.LaravelDataTables["employees-table"].draw(false);
                    }
                };

                // ---- Attach preXhr to the (possibly new) table element ----
                // Off first to prevent duplicate listeners on re-navigation
                $('#employees-table').off('preXhr.dt.emp').on('preXhr.dt.emp', function (e, settings, data) {
                    data['status']      = $('#status').val()      || 'all';
                    data['employee']    = $('#employee').val()    || 'all';
                    data['role']        = $('#role').val()        || 'all';
                    data['gender']      = $('#gender').val()      || 'all';
                    data['skill']       = $('#skill').val()       || null;
                    data['designation'] = $('#designation').val() || 'all';
                    data['department']  = $('#department').val()  || 'all';
                    data['searchText']  = $('#search-text-field').val() || '';

                    // Only send date range if it is a real non-null value — prevents the '01--' 500 error
                    if (startDate && typeof startDate === 'string' && startDate.trim() !== '' && startDate !== 'null') {
                        data['startDate']     = startDate;
                        data['endDate']       = endDate;
                        data['lastStartDate'] = lastStartDate;
                        data['lastEndDate']   = lastEndDate;
                    }
                });

                // ---- Quick action type selector ----
                $('#quick-action-type').off('change.empQA').on('change.empQA', function () {
                    var actionValue = $(this).val();
                    if (actionValue !== '') {
                        $('#quick-action-apply').removeAttr('disabled');
                        if (actionValue === 'change-status') {
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

                // ---- Quick action apply ----
                $('#quick-action-apply').off('click.empQA').on('click.empQA', function () {
                    var actionValue = $('#quick-action-type').val();
                    if (actionValue === 'delete') {
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
                        }).then(function(result) { if (result.isConfirmed) applyQuickAction(); });
                    } else {
                        applyQuickAction();
                    }
                });

                // ---- Settings links ----
                $('#designation-setting').off('click.empDS').on('click.empDS', function () {
                    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_LG, "{{ route('designations.create') }}");
                });
                $('.department-setting').off('click.empDept').on('click.empDept', function () {
                    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_LG, "{{ route('departments.create') }}");
                });
            }

            // ===================================================
            // Delegated listeners — attached to document once,
            // survive all Turbo navigations automatically
            // ===================================================

            // Filter change — covers top bar AND more filters panel (role/status/dept/gender)
            // 'changed.bs.select' is Bootstrap-Select's event; native 'change' is for plain selects & inputs
            $(document).off('change.empF changed.bs.select.empF keyup.empSearch').on(
                'change.empF changed.bs.select.empF',
                '#employee, #designation, #status, #role, #gender, #department',
                function() {
                    if (!document.getElementById('employees-table')) return;
                    var hasFilters = ($('#employee').val() !== 'all') ||
                                     ($('#designation').val() !== 'all') ||
                                     ($('#status').val() !== 'all') ||
                                     ($('#role').val() !== 'all') ||
                                     ($('#gender').val() !== 'all') ||
                                     ($('#department').val() !== 'all') ||
                                     ($('#search-text-field').val() !== '');
                    $('#reset-filters').toggleClass('d-none', !hasFilters);
                    if (typeof window.showTable === 'function') window.showTable();
                }
            ).on('keyup.empSearch', '#search-text-field', function() {
                if (!document.getElementById('employees-table')) return;
                if (typeof window.showTable === 'function') window.showTable();
            });

            // Reset filters
            $(document).off('click.empReset').on('click.empReset', '#reset-filters, #reset-filters-2', function () {
                if (!document.getElementById('employees-table')) return;
                var $form = $('#filter-form');
                if ($form.length) $form[0].reset();
                $('.select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            // Delete table row (delegated — works on dynamically rendered rows)
            $(document).off('click.empDel').on('click.empDel', '.delete-table-row', function () {
                if (!document.getElementById('employees-table')) return;
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
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            type: 'POST', blockUI: true,
                            url: "{{ route('employees.destroy', ':id') }}".replace(':id', id),
                            data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                            success: function (response) {
                                if (response.status === 'success') {
                                    if (typeof window.showTable === 'function') window.showTable();
                                    if (typeof syncGlobalStats === 'function') syncGlobalStats();
                                }
                            }
                        });
                    }
                });
            });

            // Assign role dropdown (in the DataTable rows — real-time role change)
            $(document).off('change.empRole').on('change.empRole', '.assign_role', function () {
                if (!document.getElementById('employees-table')) return;
                var id   = $(this).data('user-id');
                var role = $(this).val();
                if (typeof id === 'undefined') return;
                $.easyAjax({
                    url: "{{ route('employees.assign_role') }}",
                    type: 'POST', blockUI: true,
                    container: '#employees-table',
                    data: { role: role, userId: id, _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if (response.status === 'success') {
                            if (window.LaravelDataTables && window.LaravelDataTables["employees-table"]) {
                                window.LaravelDataTables["employees-table"].draw(false);
                            }
                            if (typeof syncGlobalStats === 'function') syncGlobalStats();
                        }
                    }
                });
            });

            // ===================================================
            // Hook into turbo:load so initEmployeePage() re-runs
            // after every Turbo navigation to the Employees page.
            // ===================================================
            document.addEventListener('turbo:load', function() {
                initEmployeePage();
            });

            // Also run immediately in case this is a full-page load (no turbo:load fires)
            initEmployeePage();

        })();

        // applyQuickAction lives outside the IIFE so it can be called from within
        function applyQuickAction() {
            var rowdIds = $("#employees-table input:checkbox:checked").map(function () {
                return $(this).val();
            }).get();
            $.easyAjax({
                url: "{{ route('employees.apply_quick_action') }}?row_ids=" + rowdIds,
                container: '#quick-action-form',
                type: 'POST',
                disableButton: true,
                buttonSelector: '#quick-action-apply',
                data: $('#quick-action-form').serialize(),
                blockUI: true,
                success: function (response) {
                    if (response.status === 'success') {
                        if (typeof window.showTable === 'function') window.showTable();
                        if (typeof resetActionButtons === 'function') resetActionButtons();
                        if (typeof deSelectAll === 'function') deSelectAll();
                        $('#quick-action-form').hide();
                        if (typeof syncGlobalStats === 'function') syncGlobalStats();
                    }
                }
            });
        }
    </script>
@endpush

