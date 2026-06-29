@extends('layouts.app')

@section('filter-section')
    <x-filters.filter-box>
        <!-- Dealer/Client Filter -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Dealer</p>
            <div class="select-status">
                <select class="form-control select-picker" name="dealer_id" id="filter_dealer_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($dealers as $dealer)
                        <option value="{{ $dealer->id }}">{{ $dealer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

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
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
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

    <div class="filter-box-more d-none">
        <x-filters.filter-box>
            <!-- Tier Filter -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Tier</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="dealer_tier" id="filter_dealer_tier">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($tiers as $tier)
                            <option value="{{ $tier }}">{{ ucfirst($tier) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Outstanding Only -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Outstanding Only</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="outstanding_only" id="filter_outstanding_only">
                        <option value="all">@lang('app.all')</option>
                        <option value="yes">@lang('app.yes')</option>
                    </select>
                </div>
            </div>

            <!-- Credit Exceeded -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Credit Exceeded</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="credit_exceeded" id="filter_credit_exceeded">
                        <option value="all">@lang('app.all')</option>
                        <option value="yes">@lang('app.yes')</option>
                    </select>
                </div>
            </div>
        </x-filters.filter-box>
    </div>
@endsection

@section('content')
    <div class="content-wrapper">
        <!-- Submenu Tab bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between pb-3 border-bottom-grey">
            <div class="d-flex align-items-center">
                <a href="{{ route('aging.index') }}" class="btn btn-secondary mr-2">Dealer Aging Report</a>
                <a href="{{ route('aging.salesperson') }}" class="btn btn-outline-secondary mr-2">Salesperson Report</a>
                <a href="{{ route('aging.dashboard') }}" class="btn btn-outline-secondary">Aging Dashboard</a>
            </div>
            
            <div id="table-actions" class="d-flex align-items-center mt-3 mt-md-0">
                @if (in_array('admin', user_roles()))
                    <button id="btn-sync-snapshots" class="btn btn-primary mr-2">
                        <i class="fa fa-sync"></i> Rebuild Snapshot Cache
                    </button>
                @endif
                <x-forms.button-secondary id="btn-more-filters" icon="filter">
                    More Filters
                </x-forms.button-secondary>
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
            $('#filter_dealer_id, #filter_salesperson_id, #filter_city, #filter_dealer_tier, #filter_outstanding_only, #filter_credit_exceeded').on('change', function() {
                window.LaravelDataTables["dealer-aging-table"].draw();
            });

            // Bind filter parameters to AJAX request
            $('#dealer-aging-table').on('preXhr.dt', function(e, settings, data) {
                data['dealerId'] = $('#filter_dealer_id').val();
                data['salespersonId'] = $('#filter_salesperson_id').val();
                data['city'] = $('#filter_city').val();
                data['dealerTier'] = $('#filter_dealer_tier').val();
                data['outstandingOnly'] = $('#filter_outstanding_only').val();
                data['creditExceeded'] = $('#filter_credit_exceeded').val();
            });

            // More filters toggle
            $('#btn-more-filters').click(function() {
                $('.filter-box-more').toggleClass('d-none');
            });

            // Reset filters button
            $('#btn-reset-filters').click(function() {
                $('#filter_dealer_id').val('all').selectpicker('refresh');
                $('#filter_salesperson_id').val('all').selectpicker('refresh');
                $('#filter_city').val('all').selectpicker('refresh');
                $('#filter_dealer_tier').val('all').selectpicker('refresh');
                $('#filter_outstanding_only').val('all').selectpicker('refresh');
                $('#filter_credit_exceeded').val('all').selectpicker('refresh');
                window.LaravelDataTables["dealer-aging-table"].draw();
            });

            // Rebuild snapshot cache via ajax
            $('#btn-sync-snapshots').click(function() {
                $.easyAjax({
                    url: "{{ route('aging.sync') }}",
                    type: "POST",
                    blockUI: true,
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            window.LaravelDataTables["dealer-aging-table"].draw();
                        }
                    }
                });
            });
        });
    </script>
@endpush
