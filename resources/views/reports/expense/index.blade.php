@extends('layouts.app')

@push('datatable-styles')
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
    @include('sections.datatable_css')
@endpush

@push('styles')
    <style>
        .action-bar{
            float: right;
        }
    </style>
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

        <!-- EXPENSE CATEGORY START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.category')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="category" id="category_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- EXPENSE CATEGORY END -->

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

        <!-- PROJECT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.project')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="project_id" id="project_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->project_name }}</option>
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
                <x-cards.widget :title="__('modules.dashboard.totalExpenses')" value="0" icon="coins"
                    widgetId="totalExpense" />
            </div>
            <div class="col-md-8">
                <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar" id="reports">
                    <div class="btn-group mt-3 mt-lg-0 mt-md-0 ml-lg-3" role="group">
                        <a href="{{ route('expense-report.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                            data-original-title="@lang('app.menu.expenseReport')"><i class="side-icon bi bi-list-ul"></i></a>

                        <a href="{{ route('expense-report.expense_category_report') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                            data-original-title="@lang('modules.expenseCategory.expenseCategoryReport')"><i class="side-icon bi bi-receipt"></i></a>

                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-lg-6">

                <div class="d-flex flex-column">
                    <!-- EXPENSE STATUS START -->
                    <x-cards.data id="e" :title="__($pageTitle)">
                    </x-cards.data>
                    <!-- EXPENSE STATUS END -->
                </div>

                <div id="table-actions" class="flex-grow-1 align-items-center mt-4">
                </div>
            </div>
            <div class="col-lg-6">
                <x-cards.data :title="__($categoryTitle)">
                    <div id="expense-chart-card"></div>
                </x-cards.data>
            </div>
        </div>

        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
@include('sections.datatable_js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        // barChart — loads expense chart data from server
        // ─────────────────────────────────────────────────────────
        function barChart() {
            if (!document.getElementById('expense-report-table')) return;

            var dateRangePicker = $('#datatableRange2').data('daterangepicker');
            var startDateVal    = $('#datatableRange2').val();
            var startDate = null, endDate = null;

            if (startDateVal !== '' && dateRangePicker) {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            $.easyAjax({
                url: "{{ route('expense-report.chart') }}",
                container: '#e',
                blockUI: true,
                type: 'POST',
                data: {
                    startDate:  startDate,
                    endDate:    endDate,
                    categoryID: $('#category_id').val() || 'all',
                    projectID:  $('#project_id').val()  || 'all',
                    employeeID: $('#employee_id').val() || 'all',
                    _token:     '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#e .card-body').html(response.html);
                    $('#expense-chart-card').html(response.html2);
                    $('#totalExpense').html(response.totalExpenses);
                }
            });
        }

        // ─────────────────────────────────────────────────────────
        // initExpenseReport — re-runs on every turbo:load
        // ─────────────────────────────────────────────────────────
        function initExpenseReport() {
            if (!document.getElementById('expense-report-table')) return;

            // Re-initialize daterangepicker on fresh element
            setDate();

            // Bind daterangepicker apply on fresh element
            $('#datatableRange2').off('apply.daterangepicker.expRep')
                .on('apply.daterangepicker.expRep', function () {
                    if (typeof window.showTable === 'function') window.showTable();
                });

            // showTable: null-guarded
            window.showTable = function () {
                if (window.LaravelDataTables && window.LaravelDataTables['expense-report-table']) {
                    window.LaravelDataTables['expense-report-table'].draw(false);
                    barChart();
                }
            };

            // preXhr — re-attach to fresh DOM element each navigation
            $('#expense-report-table').off('preXhr.dt.expRep').on('preXhr.dt.expRep', function (e, settings, data) {
                var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                var startDateVal    = $('#datatableRange2').val();
                var startDate = null, endDate = null;

                if (startDateVal !== '' && dateRangePicker) {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                data['categoryID']  = $('#category_id').val() || 'all';
                data['employeeID']  = $('#employee_id').val() || 'all';
                data['projectID']   = $('#project_id').val()  || 'all';
                data['startDate']   = startDate;
                data['endDate']     = endDate;
                data['searchText']  = $('#search-text-field').val() || '';
            });

            // Run chart immediately on page load/re-load
            barChart();
        }

        // ─────────────────────────────────────────────────────────
        // Delegated listeners — survive all Turbo navigations
        // ─────────────────────────────────────────────────────────
        var filterSel = '#category_id, #employee_id, #project_id';

        $(document)
            .off('change.expRepF changed.bs.select.expRepF')
            .on('change.expRepF changed.bs.select.expRepF', filterSel, function () {
                if (!document.getElementById('expense-report-table')) return;
                var anyActive = $(filterSel).toArray().some(function (el) {
                    return $(el).val() && $(el).val() !== 'all';
                });
                $('#reset-filters').toggleClass('d-none', !anyActive);
                if (typeof window.showTable === 'function') window.showTable();
            });

        $(document).off('keyup.expRepSearch').on('keyup.expRepSearch', '#search-text-field', function () {
            if (!document.getElementById('expense-report-table')) return;
            if ($(this).val() !== '') $('#reset-filters').removeClass('d-none');
            if (typeof window.showTable === 'function') window.showTable();
        });

        $(document).off('click.expRepReset').on('click.expRepReset', '#reset-filters, #reset-filters-2', function () {
            if (!document.getElementById('expense-report-table')) return;
            var $form = $('#filter-form');
            if ($form.length) $form[0].reset();
            setDate();
            $('.filter-box .select-picker').selectpicker('refresh');
            $('#reset-filters').addClass('d-none');
            if (typeof window.showTable === 'function') window.showTable();
        });

        // ─────────────────────────────────────────────────────────
        // Turbo:load hook + immediate call for full-page loads
        // ─────────────────────────────────────────────────────────
        document.addEventListener('turbo:load', function () {
            initExpenseReport();
        });

        initExpenseReport();

    })();
</script>
@endpush
