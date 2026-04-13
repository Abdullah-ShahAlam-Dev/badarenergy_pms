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

        <!-- EMPLOYEE START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="employee" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($employees as $employee)
                        <x-user-option :user="$employee" />
                    @endforeach
                </select>
            </div>
        </div>
        <!-- EMPLOYEE END -->

        <!-- PROJECT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.project')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="project_id" id="project_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ mb_ucwords($project->project_name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- PROJECT END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>

            <!-- CLIENT START -->
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.client')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="client" id="client" data-live-search="true"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($clients as $client)
                                <x-user-option :user="$client" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- CLIENT END -->
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.status')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-container="body"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.approved')</option>
                            <option value="0">@lang('app.pending')</option>
                            <option value="2">@lang('app.active')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.invoiceGenerate')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="invoice_generate" id="invoice_generate" data-container="body" data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.yes')</option>
                            <option value="0">@lang('app.no')</option>
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
        <div class="d-flex flex-column">
            <!-- TASK STATUS START -->
            <x-cards.data id="task-chart-card" :title="__($pageTitle)" padding="false">
            </x-cards.data>
            <!-- TASK STATUS END -->

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
            // getDate — initializes the daterangepicker.
            // Must re-run on each turbo:load because the input element
            // is replaced when Turbo swaps the body.
            // ─────────────────────────────────────────────────────────
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

            // ─────────────────────────────────────────────────────────
            // pieChart — reads current filter values and loads chart
            // ─────────────────────────────────────────────────────────
            function pieChart() {
                if (!document.getElementById('timelogs-table')) return;
                var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                var startDateVal    = $('#datatableRange2').val();
                var startDate = null, endDate = null;

                if (startDateVal !== '' && dateRangePicker) {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                $.easyAjax({
                    url: "{{ route('time-log-report.chart') }}",
                    container: '#task-chart-card',
                    blockUI: true,
                    type: 'POST',
                    data: {
                        startDate:  startDate,
                        endDate:    endDate,
                        projectId:  $('#project_id').val()      || 'all',
                        employee:   $('#employee').val()        || 'all',
                        client:     $('#client').val()          || 'all',
                        approved:   $('#status').val()          || 'all',
                        invoice:    $('#invoice_generate').val() || 'all',
                        _token:     '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#task-chart-card').html(response.html);
                    }
                });
            }

            // ─────────────────────────────────────────────────────────
            // initTimelogsReport — re-runs on every turbo:load
            // ─────────────────────────────────────────────────────────
            function initTimelogsReport() {
                if (!document.getElementById('timelogs-table')) return;

                // Re-initialize the daterangepicker on the fresh element
                getDate();

                // Bind daterangepicker apply event on the fresh element
                $('#datatableRange2').off('apply.daterangepicker.timelogRep')
                    .on('apply.daterangepicker.timelogRep', function () {
                        if (typeof window.showTable === 'function') window.showTable();
                    });

                // showTable: null-guarded
                window.showTable = function () {
                    if (window.LaravelDataTables && window.LaravelDataTables['timelogs-table']) {
                        window.LaravelDataTables['timelogs-table'].draw(false);
                        pieChart();
                    }
                };

                // preXhr — re-attach to fresh DOM element
                $('#timelogs-table').off('preXhr.dt.timelogRep').on('preXhr.dt.timelogRep', function (e, settings, data) {
                    var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                    var startDateVal    = $('#datatableRange2').val();
                    var startDate = null, endDate = null;

                    if (startDateVal !== '' && dateRangePicker) {
                        startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                        endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                    }

                    data['startDate']  = startDate;
                    data['endDate']    = endDate;
                    data['projectId']  = $('#project_id').val()      || 'all';
                    data['employee']   = $('#employee').val()        || 'all';
                    data['client']     = $('#client').val()          || 'all';
                    data['approved']   = $('#status').val()          || 'all';
                    data['invoice']    = $('#invoice_generate').val() || 'all';
                    data['searchText'] = $('#search-text-field').val() || '';
                });

                // Run chart immediately on page load/re-load
                pieChart();
            }

            // ─────────────────────────────────────────────────────────
            // Delegated listeners — survive all Turbo navigations
            // ─────────────────────────────────────────────────────────
            var filterSel = '#project_id, #employee, #client, #status, #invoice_generate';

            $(document)
                .off('change.timelogRepF changed.bs.select.timelogRepF')
                .on('change.timelogRepF changed.bs.select.timelogRepF', filterSel, function () {
                    if (!document.getElementById('timelogs-table')) return;
                    var anyActive = $(filterSel).toArray().some(function (el) {
                        return $(el).val() && $(el).val() !== 'all';
                    });
                    $('#reset-filters').toggleClass('d-none', !anyActive);
                    if (typeof window.showTable === 'function') window.showTable();
                });

            $(document).off('keyup.timelogRepSearch').on('keyup.timelogRepSearch', '#search-text-field', function () {
                if (!document.getElementById('timelogs-table')) return;
                if ($(this).val() !== '') $('#reset-filters').removeClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            $(document).off('click.timelogRepReset').on('click.timelogRepReset', '#reset-filters, #reset-filters-2', function () {
                if (!document.getElementById('timelogs-table')) return;
                var $form = $('#filter-form');
                if ($form.length) $form[0].reset();
                getDate();
                $('.filter-box .select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            // ─────────────────────────────────────────────────────────
            // Turbo:load hook + immediate call for full-page loads
            // ─────────────────────────────────────────────────────────
            document.addEventListener('turbo:load', function () {
                initTimelogsReport();
            });

            initTimelogsReport();

        })();
    </script>
@endpush
