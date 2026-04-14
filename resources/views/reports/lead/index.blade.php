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
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.leadAgent')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="agent" id="agent" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($agents as $agent)
                        <x-user-option :user="$agent->user" />
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

            function getDate() {
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

            function initLeadReport() {
                if (!document.getElementById('lead-report-table')) return;

                getDate();

                $('#datatableRange2').off('apply.daterangepicker.leadRep')
                    .on('apply.daterangepicker.leadRep', function () {
                        if (typeof window.showTable === 'function') window.showTable();
                    });

                // showTable: safe null-guarded redraw with Debounce to prevent overlapping AJAX freezes
                var showTableTimeout;
                window.showTable = function () {
                    clearTimeout(showTableTimeout);
                    showTableTimeout = setTimeout(function() {
                        if (window.LaravelDataTables && window.LaravelDataTables['lead-report-table']) {
                            window.LaravelDataTables['lead-report-table'].draw(false);
                        }
                    }, 500);
                };

                // Destroy DataTable object to prevent cache-locking on navigation Return
                document.addEventListener('turbo:before-cache', function () {
                    if (window.LaravelDataTables && window.LaravelDataTables['lead-report-table']) {
                        window.LaravelDataTables['lead-report-table'].destroy();
                        delete window.LaravelDataTables['lead-report-table'];
                    }
                }, { once: true });

                $('#lead-report-table').off('preXhr.dt.leadRep').on('preXhr.dt.leadRep', function (e, settings, data) {
                    var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                    var startDateVal    = $('#datatableRange2').val();
                    var startDate = null, endDate = null;

                    if (startDateVal !== '' && dateRangePicker) {
                        startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                        endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                    }

                    data['startDate'] = startDate;
                    data['endDate']   = endDate;
                    data['agent']     = $('#agent').val() || 'all';
                });
            }

            $(document)
                .off('change.leadRepF changed.bs.select.leadRepF')
                .on('change.leadRepF changed.bs.select.leadRepF', '#agent', function () {
                    if (!document.getElementById('lead-report-table')) return;
                    var hasFilter = $(this).val() && $(this).val() !== 'all';
                    $('#reset-filters').toggleClass('d-none', !hasFilter);
                    if (typeof window.showTable === 'function') window.showTable();
                });

            $(document).off('click.leadRepReset').on('click.leadRepReset', '#reset-filters, #reset-filters-2', function () {
                if (!document.getElementById('lead-report-table')) return;
                var $form = $('#filter-form');
                if ($form.length) $form[0].reset();
                getDate();
                $('.filter-box .select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            document.addEventListener('turbo:load', function () {
                initLeadReport();
            });

            initLeadReport();

        })();
    </script>
@endpush
