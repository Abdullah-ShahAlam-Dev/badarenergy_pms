@extends('layouts.app')

@push('datatable-styles')
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
    @include('sections.datatable_css')
@endpush

@section('filter-section')


    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange2" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- CLIENT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="employee_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($employees as $employee)
                        <x-user-option :user="$employee" />
                    @endforeach
                </select>
            </div>
        </div>
        <!-- CLIENT END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

    </x-filters.filter-box>

@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">

        <!-- Add Task Export Buttons Start -->
        <div class="d-flex flex-column">

            <div id="table-actions" class="flex-grow-1 align-items-center mt-4">
            </div>

        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function () {

            // ─────────────────────────────────────────────────────────
            // setDate — re-initializes the daterangepicker each nav
            // ─────────────────────────────────────────────────────────
            function setDate() {
                if (!document.getElementById('datatableRange2')) return;
                var start = moment().clone().startOf('month');
                var end   = moment();

                $('#datatableRange2').daterangepicker({
                    locale: daterangeLocale,
                    linkedCalendars: false,
                    startDate: start,
                    endDate: end,
                    ranges: daterangeConfig
                }, cb);
            }

            // ─────────────────────────────────────────────────────────
            // initLeaveReport — re-runs on every turbo:load navigation
            // ─────────────────────────────────────────────────────────
            function initLeaveReport() {
                if (!document.getElementById('leave-report-table')) return;

                // Re-initialize daterangepicker on fresh element
                setDate();

                // Bind daterangepicker apply on fresh element
                $('#datatableRange2').off('apply.daterangepicker.leaveRep')
                    .on('apply.daterangepicker.leaveRep', function () {
                        if (typeof window.showTable === 'function') window.showTable();
                    });

                // showTable: null-guarded layout with Debounce to stop ajax overlap freezing
                var showTableTimeout;
                window.showTable = function () {
                    clearTimeout(showTableTimeout);
                    showTableTimeout = setTimeout(function() {
                        if (window.LaravelDataTables && window.LaravelDataTables['leave-report-table']) {
                            window.LaravelDataTables['leave-report-table'].draw(false);
                        }
                    }, 500);
                };

                // Destroy DataTable object to prevent cache-locking on navigation Return
                document.addEventListener('turbo:before-cache', function () {
                    if (window.LaravelDataTables && window.LaravelDataTables['leave-report-table']) {
                        window.LaravelDataTables['leave-report-table'].destroy();
                        delete window.LaravelDataTables['leave-report-table'];
                    }
                }, { once: true });

                // preXhr — re-attach to fresh DOM element
                $('#leave-report-table').off('preXhr.dt.leaveRep').on('preXhr.dt.leaveRep', function (e, settings, data) {
                    var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                    var startDateVal    = $('#datatableRange2').val();
                    var startDate = null, endDate = null;

                    if (startDateVal !== '' && dateRangePicker) {
                        startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                        endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                    }

                    var employeeId = $('#employee_id').val() || 0;

                    data['startDate']   = startDate;
                    data['endDate']     = endDate;
                    data['employeeId']  = employeeId;
                    data['_token']      = '{{ csrf_token() }}';
                });
            }

            // ─────────────────────────────────────────────────────────
            // Delegated listeners — survive all Turbo navigations
            // ─────────────────────────────────────────────────────────
            $(document)
                .off('change.leaveRepF changed.bs.select.leaveRepF')
                .on('change.leaveRepF changed.bs.select.leaveRepF', '#employee_id', function () {
                    if (!document.getElementById('leave-report-table')) return;
                    var hasFilter = $(this).val() && $(this).val() !== 'all';
                    $('#reset-filters').toggleClass('d-none', !hasFilter);
                    if (typeof window.showTable === 'function') window.showTable();
                });

            $(document).off('click.leaveRepReset').on('click.leaveRepReset', '#reset-filters', function () {
                if (!document.getElementById('leave-report-table')) return;
                var $form = $('#filter-form');
                if ($form.length) $form[0].reset();
                setDate();
                $('.filter-box .select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            // view-leaves modal — dynamic table rows so must be delegated
            $(document).off('click.leaveRepView').on('click.leaveRepView', '.view-leaves', function (event) {
                if (!document.getElementById('leave-report-table')) return;
                event.preventDefault();

                var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                var startDateVal    = $('#datatableRange2').val();
                var startDate = null, endDate = null;

                if (startDateVal !== '' && dateRangePicker) {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                var id  = $(this).data('user-id');
                var url = "{{ route('leave-report.show', ':id') }}?startDate=" + encodeURIComponent(startDate) +
                    '&endDate=' + encodeURIComponent(endDate);
                url = url.replace(':id', id);

                $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_XL, url);
            });

            // ajax-tab delegated click
            $(document).off('click.leaveRepTab').on('click.leaveRepTab', '.ajax-tab', function (event) {
                if (!document.getElementById('leave-report-table')) return;
                event.preventDefault();
                $('.task-tabs .ajax-tab').removeClass('active');
                $(this).addClass('active');
                $.easyAjax({
                    url: this.href,
                    blockUI: true,
                    container: '#nav-tabContent',
                    historyPush: false,
                    data: { 'json': true },
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#nav-tabContent').html(response.html);
                        }
                    }
                });
            });

            // ─────────────────────────────────────────────────────────
            // Turbo:load hook + immediate call for full-page loads
            // ─────────────────────────────────────────────────────────
            document.addEventListener('turbo:load', function () {
                initLeaveReport();
            });

            initLeaveReport();

        })();
    </script>
@endpush
