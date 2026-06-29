@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    @include('reports.sales.ajax.common_filters')
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex flex-column flex-md-row justify-content-between pb-3 border-bottom-grey">
            <div class="d-flex align-items-center">
                <h2 class="h4 mb-0 font-weight-bold text-dark-grey">Cash Flow Report</h2>
            </div>
            <div id="table-actions" class="d-flex align-items-center mt-3 mt-md-0"></div>
        </div>

        <div class="mt-4">
            @include('reports.sales.ajax.summary_cards')
        </div>

        <div class="d-flex flex-column w-tables rounded bg-white mt-2 shadow-sm">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    {!! $dataTable->scripts() !!}

    <script>
        $(document).ready(function() {
            $('#filter_dealer_id, #filter_salesperson_id, #filter_warehouse_id, #filter_product_id, #filter_model_id, #filter_city, #filter_payment_status, #filter_invoice_status').on('change', function() {
                window.LaravelDataTables["cashflow-report-table"].draw();
            });

            $('#cashflow-report-table').on('preXhr.dt', function(e, settings, data) {
                data['dealerId'] = $('#filter_dealer_id').val();
                data['salespersonId'] = $('#filter_salesperson_id').val();
                data['warehouseId'] = $('#filter_warehouse_id').val();
                data['productId'] = $('#filter_product_id').val();
                data['modelId'] = $('#filter_model_id').val();
                data['city'] = $('#filter_city').val();
                data['paymentStatus'] = $('#filter_payment_status').val();
                data['invoiceStatus'] = $('#filter_invoice_status').val();
            });

            $('#btn-more-filters').click(function() {
                $('.filter-box-more').toggleClass('d-none');
            });

            $('#btn-reset-filters').click(function() {
                $('.select-picker').val('all').selectpicker('refresh');
                window.LaravelDataTables["cashflow-report-table"].draw();
            });
        });
    </script>
@endpush
