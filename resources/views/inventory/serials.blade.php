@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- SEARCH START -->
        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="Search Serial Number...">
                </div>
            </form>
        </div>
        <!-- SEARCH END -->

        <!-- PRODUCT FILTER START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.inventory.product')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="product_id" id="filter_product_id" data-live-search="true">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- PRODUCT FILTER END -->

        <!-- WAREHOUSE FILTER START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.inventory.warehouse')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="warehouse_id" id="filter_warehouse_id" data-live-search="true">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- WAREHOUSE FILTER END -->

        <!-- STATUS FILTER START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Status</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="filter_status" data-live-search="true">
                    <option value="all">@lang('app.all')</option>
                    <option value="available">Available</option>
                    <option value="sold">Sold</option>
                    <option value="faulty">Faulty/Damaged</option>
                    <option value="in_transit">In-Transit</option>
                </select>
            </div>
        </div>
        <!-- STATUS FILTER END -->

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
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                <!-- Action Buttons if any -->
            </div>
        </div>

        <!-- Table Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- Table Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var $body  = $('body');
            var $table = $('#serial-table');
            var namespace = '.serialIndex';

            $table.off('preXhr.dt').on('preXhr.dt', function(e, settings, data) {
                data['searchText']   = $('#search-text-field').val();
                data['product_id']   = $('#filter_product_id').val()   || 'all';
                data['warehouse_id'] = $('#filter_warehouse_id').val() || 'all';
                data['status']       = $('#filter_status').val()       || 'all';
            });

            var showTable = function() {
                var table = window.LaravelDataTables['serial-table'];
                if (table) { table.draw(false); }
            };
            window.showTable = showTable;

            $body.off(namespace);

            $body.on('change' + namespace + ' keyup' + namespace, '#filter_product_id, #filter_warehouse_id, #filter_status', function() {
                var hasFilters = ($('#filter_product_id').val() !== 'all') || ($('#filter_warehouse_id').val() !== 'all') || ($('#filter_status').val() !== 'all');
                hasFilters ? $('#reset-filters').removeClass('d-none') : $('#reset-filters').addClass('d-none');
                showTable();
            });

            $body.on('keyup' + namespace, '#search-text-field', function() {
                if ($(this).val() !== '') { $('#reset-filters').removeClass('d-none'); }
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters', function() {
                $('#filter_product_id').val('all');
                $('#filter_warehouse_id').val('all');
                $('#filter_status').val('all');
                $('.filter-box .select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                showTable();
            });
        })();
    </script>
@endpush
