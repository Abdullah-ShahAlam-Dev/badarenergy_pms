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
                <input type="text"
                    class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        @if (!in_array('client', user_roles()))
            <!-- CLIENT START -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.client')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="client" id="client" data-live-search="true"
                        data-size="8">
                        @if (!in_array('client', user_roles()))
                            <option value="all">@lang('app.all')</option>
                        @endif
                        @foreach ($clients as $client)
                            <x-user-option :user="$client" />
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <!-- CLIENT END -->
        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.contracts.contractType')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="contract_type" id="contract_type" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($contractTypes as $item)
                        <option value="{{ $item->id }}">{{ mb_ucwords($item->name) }}</option>
                    @endforeach
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
    </x-filters.filter-box>

@endsection

@php
    $addContractPermission = user()->permission('add_contract');
    $manageContractTemplatePermission = user()->permission('manage_contract_template');

@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">

            <div id="table-actions" class="d-flex align-items-center">
                @if ($addContractPermission == 'all' || $addContractPermission == 'added')
                    <x-forms.link-primary :link="route('contracts.create')" class="mr-3 openRightModal" icon="plus">
                        @lang('modules.contracts.createContract')
                    </x-forms.link-primary>
                @endif

                @if ($manageContractTemplatePermission == 'all' || $manageContractTemplatePermission == 'added')
                    <x-forms.link-secondary :link="route('contract-template.index')" class="mr-3 mb-2 mb-lg-0 mb-md-0 float-left"
                        icon="layer-group">
                        @lang('app.menu.contractTemplate')
                    </x-forms.link-secondary>
                @endif
            </div>

            @if (!in_array('client', user_roles()))
                <x-datatable.actions>
                    <div class="select-status mr-3 pl-3">
                        <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                            <option value="">@lang('app.selectAction')</option>
                            <option value="delete">@lang('app.delete')</option>
                        </select>
                    </div>
                </x-datatable.actions>
            @endif
            {{--
            <div id="sign-modals" class="modal fade sign-modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog d-flex justify-content-center align-items-center modal-xl">
                    <div class="modal-content">
                        @include('contracts.companysign.sign')
                    </div>
                </div>
            </div> --}}

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
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

    <script>
        (function () {

            // ============================================================
            // initContractsPage — called on every page load (Turbo or full)
            // ============================================================
            function initContractsPage() {
                // Guard: only run if the contracts table exists in the DOM
                if (!document.getElementById('contracts-table')) return;

                // ---- showTable ----
                window.showTable = function () {
                    if (window.LaravelDataTables && window.LaravelDataTables['contracts-table']) {
                        window.LaravelDataTables['contracts-table'].draw(false);
                    }
                };

                // ---- preXhr: inject all filter values before each AJAX request ----
                // Off first to prevent duplicate listeners on re-navigation
                $('#contracts-table').off('preXhr.dt.contracts').on('preXhr.dt.contracts', function (e, settings, data) {
                    var dateRangePicker = $('#datatableRange').data('daterangepicker');
                    var startDateVal    = $('#datatableRange').val();
                    var startDate = null, endDate = null;

                    if (startDateVal !== '' && dateRangePicker) {
                        startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                        endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                    }

                    data['startDate']     = startDate;
                    data['endDate']       = endDate;
                    data['contract_type'] = $('#contract_type').val() || 'all';
                    data['client']        = $('#client').val()        || 'all';
                    data['searchText']    = $('#search-text-field').val() || '';
                });

                // ---- Quick action type selector ----
                $('#quick-action-type').off('change.contractsQA').on('change.contractsQA', function () {
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
                $('#quick-action-apply').off('click.contractsQA').on('click.contractsQA', function () {
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
                        }).then(function (result) {
                            if (result.isConfirmed) applyQuickAction();
                        });
                    } else {
                        applyQuickAction();
                    }
                });

                // ---- Set date range from URL params (replaces the broken $(document).ready block) ----
                @if (!is_null(request('start')) && !is_null(request('end')))
                    var $range = $('#datatableRange');
                    if ($range.length && $range.data('daterangepicker')) {
                        $range.val('{{ request('start') }}' + ' @lang('app.to') ' + '{{ request('end') }}');
                        $range.data('daterangepicker').setStartDate("{{ request('start') }}");
                        $range.data('daterangepicker').setEndDate("{{ request('end') }}");
                        if (typeof window.showTable === 'function') window.showTable();
                    }
                @endif
            }

            // ===================================================
            // Delegated listeners — attached to document ONCE.
            // They survive all Turbo navigations automatically.
            // Each is guarded with a contracts-table existence check.
            // ===================================================

            // Filter dropdowns: listen to both native 'change' AND
            // Bootstrap-Select's 'changed.bs.select' event
            $(document)
                .off('change.contractsF changed.bs.select.contractsF')
                .on('change.contractsF changed.bs.select.contractsF', '#client, #contract_type', function () {
                    if (!document.getElementById('contracts-table')) return;
                    var hasFilters = ($('#contract_type').val() !== 'all') ||
                                     ($('#client').val() && $('#client').val() !== 'all');
                    $('#reset-filters').toggleClass('d-none', !hasFilters);
                    if (typeof window.showTable === 'function') window.showTable();
                });

            // Search text field
            $(document).off('keyup.contractsSearch').on('keyup.contractsSearch', '#search-text-field', function () {
                if (!document.getElementById('contracts-table')) return;
                if ($(this).val() !== '') $('#reset-filters').removeClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            // Reset filters button
            $(document).off('click.contractsReset').on('click.contractsReset', '#reset-filters', function () {
                if (!document.getElementById('contracts-table')) return;
                var $form = $('#filter-form');
                if ($form.length) $form[0].reset();
                $('.filter-box .select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            // Delete row (table rows are dynamically rendered — must use delegation)
            $(document).off('click.contractsDel').on('click.contractsDel', '.delete-table-row', function () {
                if (!document.getElementById('contracts-table')) return;
                var id = $(this).data('contract-id');
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
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            type: 'POST',
                            url: "{{ route('contracts.destroy', ':id') }}".replace(':id', id),
                            data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                            success: function (response) {
                                if (response.status === 'success') {
                                    if (typeof window.showTable === 'function') window.showTable();
                                }
                            }
                        });
                    }
                });
            });

            // Sign modal — namespaced to prevent listener accumulation on re-navigation
            $(document).off('click.contractsSign').on('click.contractsSign', '.sign-modal', function () {
                if (!document.getElementById('contracts-table')) return;
                var id  = $(this).data('contract-id');
                var url = "{{ route('companySignStore.sign', ':id') }}".replace(':id', id);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            // ===================================================
            // Hook into turbo:load so initContractsPage() re-runs
            // after every Turbo navigation to this page.
            // ===================================================
            document.addEventListener('turbo:load', function () {
                initContractsPage();
            });

            // Also run immediately for a full-page (non-Turbo) load
            initContractsPage();

        })();

        // applyQuickAction lives outside the IIFE so it can be called from within
        function applyQuickAction() {
            var rowdIds = $('#contracts-table input:checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            $.easyAjax({
                url: "{{ route('contracts.apply_quick_action') }}?row_ids=" + rowdIds,
                container: '#quick-action-form',
                type: 'POST',
                disableButton: true,
                buttonSelector: '#quick-action-apply',
                data: $('#quick-action-form').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        if (typeof window.showTable === 'function') window.showTable();
                        if (typeof resetActionButtons === 'function') resetActionButtons();
                        if (typeof deSelectAll === 'function') deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            });
        }
    </script>
@endpush
