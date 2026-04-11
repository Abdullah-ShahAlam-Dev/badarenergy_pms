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

        @if (!in_array('client', user_roles()))
            <!-- CLIENT START -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.client')</p>
                <div class="select-status">
                    <select class="form-control select-picker" id="clientID" data-live-search="true" data-size="8">
                        @if (!in_array('client', user_roles()))
                            <option value="all">@lang('app.all')</option>
                        @endif
                        @foreach ($clients as $client)
                                <x-user-option :user="$client" />
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- CLIENT END -->
        @endif

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
                        <select class="form-control select-picker" name="project_id" id="filter_project_id"
                            data-container="body" data-live-search="true" data-size="8">
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
                            <option {{ request('status') == 'pending' ? 'selected' : '' }} value="pending">
                                @lang('app.pending')</option>
                            <option {{ request('status') == 'unpaid' ? 'selected' : '' }} value="unpaid">
                                @lang('app.unpaid')</option>
                            <option {{ request('status') == 'paid' ? 'selected' : '' }} value="paid">@lang('app.paid')
                            </option>
                            <option {{ request('status') == 'partial' ? 'selected' : '' }} value="partial">
                                @lang('app.partial')</option>
                            <option {{ request('status') == 'canceled' ? 'selected' : '' }} value="canceled">
                                @lang('app.canceled')</option>
                        </select>
                    </div>
                </div>
            </div>


        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->

    </x-filters.filter-box>

@endsection

@php
$addInvoicesPermission = user()->permission('add_invoices');
$manageRecurringInvoicesPermission = user()->permission('manage_recurring_invoice');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-block d-lg-flex d-md-flex">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if ($addInvoicesPermission == 'all')
                    <x-forms.link-primary :link="route('invoices.create')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0"
                        icon="plus">
                        @lang('modules.invoices.addInvoice')
                    </x-forms.link-primary>
                @endif
                @if ($addInvoicesPermission == 'all' || $manageRecurringInvoicesPermission == 'all')
                    <x-forms.link-secondary class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="redo"
                        :link="route('recurring-invoices.index')">
                        @lang('app.invoiceRecurring')
                    </x-forms.link-secondary>
                @endif
                @if ($addInvoicesPermission == 'all')
                    <x-forms.link-secondary class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="plus"
                        :link="route('invoices.create', ['type' => 'timelog'])">
                        @lang('app.create') @lang('app.timeLog') @lang('app.invoice')
                    </x-forms.link-secondary>
                @endif

            </div>

            <div class="btn-group mt-3 mt-lg-0 mt-md-0 ml-lg-3" role="group">
                <a href="javascript:;" class="img-lightbox btn btn-secondary f-14"
                data-image-url="{{ asset('img/invoice-lc.png') }}" data-toggle="tooltip"
                data-original-title="@lang('app.howItWorks')"><i class="side-icon bi bi-question-circle"></i></a>
            </div>

        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive">

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
            var $table = $('#invoices-table');
            var namespace = '.invoicesIndex';
            var clipboard = null;

            // ── ClipboardJS ──────────────────────────────────────────────
            // Prevent duplicate bindings on Turbo revisits
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
                } else if (dateRangePicker) {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                data['clientID']    = $('#clientID').val();
                data['projectID']   = $('#filter_project_id').val() || 0;
                data['status']      = $('#status').val();
                data['startDate']   = startDate;
                data['endDate']     = endDate;
                data['searchText']  = $('#search-text-field').val();
            });

            // ── showTable (module-scoped + window alias for DataTable callbacks) ──
            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["invoices-table"]) {
                    window.LaravelDataTables["invoices-table"].draw(false);
                }
            }
            window.showTable = showTable;

            // ── Event delegation ─────────────────────────────────────────
            $body.off(namespace);

            $body.on('change' + namespace + ' keyup' + namespace,
                '#clientID, #filter_project_id, #status', function() {
                var filtersActive = ($('#filter_project_id').val() != "all") ||
                                    ($('#status').val() != "all") ||
                                    ($('#clientID').val() != "all");
                $('#reset-filters').toggleClass('d-none', !filtersActive);
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
                    $('.quick-action-field').addClass('d-none');
                    if (actionValue == 'change-status') {
                        $('#change-status-action').removeClass('d-none');
                    }
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.quick-action-field').addClass('d-none');
                }
            });

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

            $body.on('click' + namespace, '.delete-table-row', function() {
                var id = $(this).data('invoice-id');
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
                            url: "{{ route('invoices.destroy', ':id') }}".replace(':id', id),
                            blockUI: true,
                            data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                            success: function(response) {
                                if (response.status == "success") { showTable(); }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.unpaidAndPartialPaidCreditNote', function() {
                var id = $(this).data('invoice-id');
                Swal.fire({
                    title: "@lang('messages.confirmation.createCreditNotes')",
                    text: "@lang('messages.creditText')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('app.yes')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.href = "{{ route('creditnotes.create') }}?invoice=:id".replace(':id', id);
                    }
                });
            });

            $body.on('click' + namespace, '.sendButton', function() {
                var id       = $(this).data('invoice-id');
                var dataType = $(this).data('type');
                $.easyAjax({
                    type: 'POST',
                    url: "{{ route('invoices.send_invoice', ':id') }}".replace(':id', id),
                    container: '#invoices-table',
                    blockUI: true,
                    data: { '_token': "{{ csrf_token() }}", 'data_type': dataType },
                    success: function(response) {
                        if (response.status == "success") { showTable(); }
                    }
                });
            });

            $body.on('click' + namespace, '.reminderButton', function() {
                var id = $(this).data('invoice-id');
                $.easyAjax({
                    type: 'GET',
                    container: '#invoices-table',
                    blockUI: true,
                    url: "{{ route('invoices.payment_reminder', ':id') }}".replace(':id', id),
                    success: function(response) {
                        if (response.status == "success") { showTable(); }
                    }
                });
            });

            $body.on('click' + namespace, '.invoice-upload', function() {
                var invoiceId = $(this).data('invoice-id');
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, "{{ route('invoices.file_upload') }}?invoice_id=" + invoiceId);
            });

            $body.on('click' + namespace, '.cancel-invoice', function() {
                var id = $(this).data('invoice-id');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.invoiceText')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('app.yes')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            type: 'GET',
                            url: "{{ route('invoices.update_status', ':id') }}".replace(':id', id),
                            container: '#invoices-table',
                            blockUI: true,
                            success: function(response) {
                                if (response.status == "success") { showTable(); }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.toggle-shipping-address', function() {
                var invoiceId = $(this).data('invoice-id');
                $.easyAjax({
                    url: "{{ route('invoices.toggle_shipping_address', ':id') }}".replace(':id', invoiceId),
                    type: 'GET',
                    container: '#invoices-table',
                    blockUI: true,
                    success: function(response) {
                        if (response.status === 'success') { showTable(); }
                    }
                });
            });

            $body.on('click' + namespace, '.add-shipping-address', function() {
                var invoiceId = $(this).data('invoice-id');
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG,
                    "{{ route('invoices.shipping_address_modal', [':id']) }}".replace(':id', invoiceId));
            });

            @if (request('start') && request('end'))
                $('#datatableRange').val('{{ request('start') }}' + ' @lang("app.to") ' + '{{ request('end') }}');
                var drp = $('#datatableRange').data('daterangepicker');
                if (drp) {
                    drp.setStartDate("{{ request('start') }}");
                    drp.setEndDate("{{ request('end') }}");
                }
                showTable();
            @endif

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
