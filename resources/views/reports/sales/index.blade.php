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

            function initSalesReport() {
                if (!document.getElementById('sales-report-table')) return;

                getDate();

                $('#datatableRange2').off('apply.daterangepicker.salesRep')
                    .on('apply.daterangepicker.salesRep', function() {
                        if (typeof window.showTable === 'function') window.showTable();
                    });

                var showTableTimeout;
                window.showTable = function() {
                    clearTimeout(showTableTimeout);
                    showTableTimeout = setTimeout(function() {
                        if (window.LaravelDataTables && window.LaravelDataTables['sales-report-table']) {
                            window.LaravelDataTables['sales-report-table'].draw(false);
                        }
                    }, 500);
                };

                document.addEventListener('turbo:before-cache', function () {
                    if (window.LaravelDataTables && window.LaravelDataTables['sales-report-table']) {
                        window.LaravelDataTables['sales-report-table'].destroy();
                        delete window.LaravelDataTables['sales-report-table'];
                    }
                }, { once: true });

                $('#sales-report-table').off('preXhr.dt.salesRep').on('preXhr.dt.salesRep', function(e, settings, data) {
                    var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                    var startDateVal = $('#datatableRange2').val();
                    var startDate = null, endDate = null;

                    if (startDateVal !== '' && dateRangePicker) {
                        startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                        endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                    }

                    data['startDate'] = startDate;
                    data['endDate'] = endDate;
                    data['clientID'] = $('#clientID').val();
                });
            }

            $(document).off('change.salesRepF changed.bs.select.salesRepF').on('change.salesRepF changed.bs.select.salesRepF', '#clientID', function() {
                if (!document.getElementById('sales-report-table')) return;
                $('#reset-filters').toggleClass('d-none', $(this).val() === "all");
                if (typeof window.showTable === 'function') window.showTable();
            });

            $(document).off('click.salesRepReset').on('click.salesRepReset', '#reset-filters, #reset-filters-2', function() {
                if (!document.getElementById('sales-report-table')) return;
                var $form = $('#filter-form');
                if ($form.length) $form[0].reset();
                getDate();
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').addClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            document.addEventListener('turbo:load', function() {
                initSalesReport();
            });

            initSalesReport();

        })();
    </script>
@endpush
