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
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.client')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="clientID" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($clients as $client)
                        <x-user-option :user="$client" />
                    @endforeach
                </select>
            </div>
        </div>
        <!-- CLIENT END -->

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

    </x-filters.filter-box>

@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="row mb-4">
            <div class="col-lg-4">
                <x-cards.widget :title="__('modules.dashboard.totalEarnings')" value="0" icon="coins"
                    widgetId="totalEarnings" />
            </div>
        </div>

        <!-- Add Task Export Buttons Start -->
        <div class="d-flex flex-column">
            <!-- TASK STATUS START -->
            <x-cards.data id="task-chart-card" :title="__($pageTitle)">
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
        (function() {

            function getDate() {
                if (!document.getElementById('datatableRange2')) return;
                var start = moment().clone().startOf('month');
                var end = moment();

                $('#datatableRange2').daterangepicker({
                    locale: daterangeLocale,
                    linkedCalendars: false,
                    startDate: start,
                    endDate: end,
                    ranges: daterangeConfig
                }, cb);
            }

            function pieChart() {
                if (!document.getElementById('payments-table')) return;
                var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                var startDateVal = $('#datatableRange2').val();
                var startDate = null, endDate = null;

                if (startDateVal !== '' && dateRangePicker) {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                $.easyAjax({
                    url: "{{ route('finance-report.chart') }}",
                    container: '#task-chart-card',
                    blockUI: true,
                    type: "POST",
                    data: {
                        startDate: startDate,
                        endDate: endDate,
                        projectID: $('#project_id').val(),
                        clientID: $('#clientID').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#task-chart-card .card-body').html(response.html);
                        $('#totalEarnings').html(response.totalEarnings);
                    }
                });
            }

            function initFinanceReport() {
                if (!document.getElementById('payments-table')) return;

                getDate();

                $('#datatableRange2').off('apply.daterangepicker.finRep')
                    .on('apply.daterangepicker.finRep', function() {
                        if (typeof window.showTable === 'function') window.showTable();
                    });

                var showTableTimeout;
                window.showTable = function() {
                    clearTimeout(showTableTimeout);
                    showTableTimeout = setTimeout(function() {
                        if (window.LaravelDataTables && window.LaravelDataTables['payments-table']) {
                            window.LaravelDataTables['payments-table'].draw(false);
                            pieChart();
                        }
                    }, 500);
                };

                document.addEventListener('turbo:before-cache', function () {
                    if (window.LaravelDataTables && window.LaravelDataTables['payments-table']) {
                        window.LaravelDataTables['payments-table'].destroy();
                        delete window.LaravelDataTables['payments-table'];
                    }
                }, { once: true });

                $('#payments-table').off('preXhr.dt.finRep').on('preXhr.dt.finRep', function(e, settings, data) {
                    var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                    var startDateVal = $('#datatableRange2').val();
                    var startDate = null, endDate = null;

                    if (startDateVal !== '' && dateRangePicker) {
                        startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                        endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                    }

                    data['clientID'] = $('#clientID').val();
                    data['projectID'] = $('#project_id').val() || 0;
                    data['startDate'] = startDate;
                    data['endDate'] = endDate;
                    data['searchText'] = $('#search-text-field').val();
                });

                pieChart();
            }

            var filterSel = '#clientID, #project_id, #status';
            $(document).off('change.finRepF changed.bs.select.finRepF').on('change.finRepF changed.bs.select.finRepF', filterSel, function() {
                if (!document.getElementById('payments-table')) return;
                var anyActive = $(filterSel).toArray().some(function(el) {
                    return $(el).val() && $(el).val() !== 'all';
                });
                $('#reset-filters').toggleClass('d-none', !anyActive);
                if (typeof window.showTable === 'function') window.showTable();
            });

            $(document).off('keyup.finRepSearch').on('keyup.finRepSearch', '#search-text-field', function() {
                if (!document.getElementById('payments-table')) return;
                $('#reset-filters').toggleClass('d-none', $(this).val() === "");
                if (typeof window.showTable === 'function') window.showTable();
            });

            $(document).off('click.finRepReset').on('click.finRepReset', '#reset-filters, #reset-filters-2', function() {
                if (!document.getElementById('payments-table')) return;
                var $form = $('#filter-form');
                if ($form.length) $form[0].reset();
                getDate();
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').addClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            document.addEventListener('turbo:load', function() {
                initFinanceReport();
            });

            initFinanceReport();

        })();
    </script>
@endpush
