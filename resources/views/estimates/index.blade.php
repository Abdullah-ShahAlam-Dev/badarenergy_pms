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
                <input type="text" class="positiozn-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- CLIENT START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="filter-status">
                    <option value="all">@lang('app.all')</option>
                    <option value="waiting">@lang('modules.estimates.waiting')</option>
                    <option value="accepted">@lang('modules.estimates.accepted')</option>
                    <option value="declined">@lang('modules.estimates.declined')</option>
                    <option value="draft">@lang('modules.estimates.draft')</option>
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

    </x-filters.filter-box>

@endsection

@php
$addEstimatePermission = user()->permission('add_estimates');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addEstimatePermission == 'all' || $addEstimatePermission == 'added')
                    <x-forms.link-primary :link="route('estimates.create')" class="mr-3 float-left" icon="plus">
                        @lang('modules.estimates.createEstimate')
                    </x-forms.link-primary>
                <x-forms.link-secondary :link="route('estimate-template.index')"
                class="mr-3 mb-2 mb-lg-0 mb-md-0 float-left" icon="layer-group">
                @lang('modules.estimates.estimateTemplate')
                </x-forms.link-secondary>
                @endif
            </div>

            <div class="btn-group mt-3 mt-lg-0 mt-md-0 ml-lg-3" role="group">
                <a href="javascript:;" class="img-lightbox btn btn-secondary f-14"
                data-image-url="{{ asset('img/estimate-lc.png') }}" data-toggle="tooltip"
                data-original-title="@lang('app.howItWorks')"><i class="side-icon bi bi-question-circle"></i></a>
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
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
    <script>
        (function() {
            var $body = $('body');
            // NOTE: Worksuite re-uses 'invoices-table' as the DataTable ID for estimates - unchanged
            var $table = $('#invoices-table');
            var namespace = '.estimatesIndex';
            var clipboard = null;

            // ── ClipboardJS ──────────────────────────────────────────────
            if (typeof ClipboardJS !== 'undefined' && document.querySelector('.btn-copy')) {
                clipboard = new ClipboardJS('.btn-copy');
                clipboard.on('success', function(e) {
                    Swal.fire({
                        icon: 'success',
                        text: '@lang("app.copied")',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        customClass: { confirmButton: 'btn btn-primary' },
                        showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    });
                });
            }

            // ── DataTable preXhr ─────────────────────────────────────────
            $table.off('preXhr.dt').on('preXhr.dt', function(e, settings, data) {
                var dateRangePicker = $('#datatableRange').data('daterangepicker');
                var startDate = $('#datatableRange').val();
                var endDate = null;

                if (startDate == '') {
                    startDate = null;
                } else {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                data['startDate']   = startDate;
                data['endDate']     = endDate;
                data['status']      = $('#filter-status').val();
                data['searchText']  = $('#search-text-field').val();
            });

            // ── showTable ─────────────────────────────────────────────────
            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["invoices-table"]) {
                    window.LaravelDataTables["invoices-table"].draw(false);
                }
            }
            window.showTable = showTable;

            // ── applyQuickAction ──────────────────────────────────────────
            function applyQuickAction() {
                var rowdIds = $("#invoices-table input:checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                $.easyAjax({
                    url: "{{ route('invoices.apply_quick_action') }}?row_ids=" + rowdIds,
                    container: '#quick-action-form',
                    type: "POST",
                    disableButton: true,
                    buttonSelector: "#quick-action-apply",
                    data: $('#quick-action-form').serialize(),
                    blockUI: true,
                    success: function(response) {
                        if (response.status == 'success') {
                            showTable();
                            if (typeof resetActionButtons === 'function') resetActionButtons();
                        }
                    }
                });
            }
            window.applyQuickAction = applyQuickAction;

            // ── Event delegation ─────────────────────────────────────────
            $body.off(namespace);

            $body.on('change' + namespace + ' keyup' + namespace, '#filter-status', function() {
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

            $body.on('click' + namespace, '#reset-filters, #reset-filters-2', function() {
                $('#filter-form')[0].reset();
                $('.filter-box .select-picker').selectpicker('refresh');
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
                    }).then((result) => { if (result.isConfirmed) { applyQuickAction(); } });
                } else {
                    applyQuickAction();
                }
            });

            $body.on('click' + namespace, '.change-status', function() {
                var id = $(this).data('estimate-id');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.estimateCancelText')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmCancel')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            type: 'GET',
                            url: "{{ route('estimates.change_status', ':id') }}".replace(':id', id),
                            container: '#invoices-table',
                            blockUI: true,
                            success: function(response) {
                                if (response.status == "success") { showTable(); }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.delete-table-row', function() {
                var id = $(this).data('estimate-id');
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
                        $.easyAjax({
                            type: 'POST',
                            url: "{{ route('estimates.destroy', ':id') }}".replace(':id', id),
                            blockUI: true,
                            data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                            success: function(response) {
                                if (response.status == "success") { showTable(); }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.sendButton', function() {
                var id = $(this).data('estimate-id');
                $.easyAjax({
                    type: 'POST',
                    url: "{{ route('estimates.send_estimate', ':id') }}".replace(':id', id),
                    container: '#invoices-table',
                    blockUI: true,
                    data: { '_token': "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.status == "success") { showTable(); }
                    }
                });
            });

            // ── Turbo cleanup ─────────────────────────────────────────────
            document.addEventListener("turbo:before-cache", function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt');
                if (clipboard) { clipboard.destroy(); clipboard = null; }
                delete window.showTable;
                delete window.applyQuickAction;
                document.removeEventListener("turbo:before-cache", cleanup);
            }, { once: true });

        })();
    </script>
@endpush
