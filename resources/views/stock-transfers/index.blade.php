@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- Date range filter start -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Date</p>
            <div class="select-status d-flex">
                <input type="date" class="form-control f-14 p-1 border-additional-grey" id="start-date" placeholder="Start Date" style="width: 130px; height: 35px;">
                <span class="mx-2 align-self-center">to</span>
                <input type="date" class="form-control f-14 p-1 border-additional-grey" id="end-date" placeholder="End Date" style="width: 130px; height: 35px;">
            </div>
        </div>

        <!-- Status Filter -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Status</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="status" data-live-search="true">
                    <option value="all">All</option>
                    <option value="draft">Draft</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="dispatched">Dispatched</option>
                    <option value="in_transit">In Transit</option>
                    <option value="partially_received">Partially Received</option>
                    <option value="received">Received</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <!-- Source Warehouse Filter -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Source</p>
            <div class="select-status">
                <select class="form-control select-picker" name="source_warehouse_id" id="source_warehouse_id" data-live-search="true">
                    <option value="all">All</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Reset Button -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                Clear Filters
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Action Button -->
    <div class="d-flex justify-content-between action-bar">
        <div id="table-actions" class="flex-grow-1 align-items-center">
            @if(user()->permission('add_stock_transfer') != 'none')
                <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary rounded f-14"><i class="fa fa-plus"></i> Create Stock Transfer</a>
            @endif
        </div>
    </div>

    <!-- Table Container -->
    <div class="d-flex flex-column w-tables rounded mt-3 bg-white p-4 shadow-sm">
        {!! $dataTable->table(['class' => 'table table-hover', 'id' => 'stock-transfers-table']) !!}
    </div>
</div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    {!! $dataTable->scripts() !!}
    
    <script>
        // Reload DataTable when filters change
        $('#status, #source_warehouse_id, #start-date, #end-date').on('change keyup', function () {
            window.LaravelDataTables["stock-transfers-table"].draw();
        });

        // Setup filter parameters inside the ajax call
        $('#stock-transfers-table').on('preXhr.dt', function (e, settings, data) {
            data['startDate'] = $('#start-date').val();
            data['endDate'] = $('#end-date').val();
            data['status'] = $('#status').val();
            data['source_warehouse_id'] = $('#source_warehouse_id').val();
        });
    </script>
@endpush
