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
                        <option value="all">@lang('app.all')</option>
                        @foreach ($clients as $client)
                            <x-user-option :user="$client" />
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
                        <select class="form-control select-picker" name="project_id" id="project_id" data-live-search="true" data-container="body" data-size="8">
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
                        <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="open">@lang('app.open')</option>
                            <option value="closed">@lang('app.closed')</option>
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
        <div class="d-flex">
            <div id="table-actions" class="flex-grow-1 align-items-center">
            </div>

            <div class="btn-group mt-3 mt-lg-0 mt-md-0 ml-lg-3" role="group">
                <a href="javascript:;" class="img-lightbox btn btn-secondary f-14"
                data-image-url="{{ asset('img/credit-note-lc.png') }}" data-toggle="tooltip"
                data-original-title="@lang('app.howItWorks')"><i class="side-icon bi bi-question-circle"></i></a>
            </div>

        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

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
            // NOTE: Worksuite re-uses 'invoices-table' as the DataTable ID for credit-notes - unchanged
            var $table = $('#invoices-table');
            var namespace = '.creditNotesIndex';

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

                data['clientID']    = $('#clientID').val();
                data['projectID']   = $('#project_id').val() || 0;
                data['status']      = $('#status').val();
                data['startDate']   = startDate;
                data['endDate']     = endDate;
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

            $body.on('change' + namespace + ' keyup' + namespace, '#clientID, #project_id, #status', function() {
                var filtersActive = ($('#project_id').val() != "all") ||
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

            $body.on('click' + namespace, '.delete-table-row', function() {
                var id = $(this).data('credit-notes-id');
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
                            url: "{{ route('creditnotes.destroy', ':id') }}".replace(':id', id),
                            data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                            success: function(response) {
                                if (response.status == "success") { showTable(); }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.sendButton', function() {
                var id = $(this).data('invoice-id');
                $.easyAjax({
                    type: 'POST',
                    url: "{{ route('invoices.send_invoice', ':id') }}".replace(':id', id),
                    container: '#invoices-table',
                    blockUI: true,
                    data: { '_token': "{{ csrf_token() }}" },
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

            $body.on('click' + namespace, '.credit-notes-upload', function() {
                var creditNoteId = $(this).data('credit-notes-id');
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, "{{ route('creditnotes.file_upload') }}?credit_note=" + creditNoteId);
            });

            // ── Turbo cleanup ─────────────────────────────────────────────
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
