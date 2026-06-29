@extends('layouts.app')

@section('filter-section')
    <x-filters.filter-box>
        <!-- Salesperson Filter -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Salesperson</p>
            <div class="select-status">
                <select class="form-control select-picker" name="salesperson_id" id="filter_salesperson_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($salespersons as $salesperson)
                        <option value="{{ $salesperson->id }}">{{ $salesperson->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- City/Location Filter -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">City</p>
            <div class="select-status">
                <select class="form-control select-picker" name="city" id="filter_city" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Reset Button -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-primary id="btn-reset-filters" class="btn-xs btn-light" icon="sync">Reset</x-forms.button-primary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <!-- Submenu Tab bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between pb-3 border-bottom-grey">
            <div class="d-flex align-items-center">
                <a href="{{ route('aging.index') }}" class="btn btn-outline-secondary mr-2">Dealer Aging Report</a>
                <a href="{{ route('aging.salesperson') }}" class="btn btn-secondary mr-2">Salesperson Report</a>
                <a href="{{ route('aging.dashboard') }}" class="btn btn-outline-secondary">Aging Dashboard</a>
            </div>

            <div id="table-actions" class="d-flex align-items-center mt-3 mt-md-0">
                <!-- Standard action slot -->
            </div>
        </div>

        <!-- DataTable Container -->
        <div class="d-flex flex-column w-tables rounded bg-white mt-4 shadow-sm">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}

    <script>
        $(document).ready(function() {
            // Apply filtering on change
            $('#filter_salesperson_id, #filter_city').on('change', function() {
                window.LaravelDataTables["salesperson-aging-table"].draw();
            });

            // Bind filter parameters to AJAX request
            $('#salesperson-aging-table').on('preXhr.dt', function(e, settings, data) {
                data['salespersonId'] = $('#filter_salesperson_id').val();
                data['city'] = $('#filter_city').val();
            });

            // Reset filters button
            $('#btn-reset-filters').click(function() {
                $('#filter_salesperson_id').val('all').selectpicker('refresh');
                $('#filter_city').val('all').selectpicker('refresh');
                window.LaravelDataTables["salesperson-aging-table"].draw();
            });
        });
    </script>
@endpush
