@extends('layouts.app')

@push('styles')
    @include('sections.daterange_css')
@endpush

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

        <!-- More Filters toggle -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-primary id="btn-reset-filters" class="btn-xs btn-light" icon="sync">Reset</x-forms.button-primary>
        </div>
    </x-filters.filter-box>

    <div class="filter-box-more d-none">
        <x-filters.filter-box>
            <!-- Outstanding only -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Outstanding Only</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="outstanding_only" id="filter_outstanding_only">
                        <option value="no">@lang('app.no')</option>
                        <option value="yes">@lang('app.yes')</option>
                    </select>
                </div>
            </div>

            <!-- Credit Limit Exceeded -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Credit Exceeded</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="credit_exceeded" id="filter_credit_exceeded">
                        <option value="no">@lang('app.no')</option>
                        <option value="yes">@lang('app.yes')</option>
                    </select>
                </div>
            </div>

            <!-- As of Date -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">As of Date</p>
                <div class="select-status">
                    <input type="text" id="filter_end_date" class="form-control height-35 f-14" placeholder="Select Date" autocomplete="off" />
                </div>
            </div>
        </x-filters.filter-box>
    </div>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex flex-column flex-lg-row justify-content-between pb-3 border-bottom-grey">
            <div id="table-actions" class="d-flex align-items-center">
                @if (in_array('admin', user_roles()))
                    <x-forms.button-primary id="btn-add-adjustment" class="mr-3" icon="plus">
                        Post Adjustment
                    </x-forms.button-primary>
                @endif
                <x-forms.button-secondary id="btn-more-filters" icon="filter">
                    More Filters
                </x-forms.button-secondary>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded bg-white mt-4">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.daterange_js')
    {!! $dataTable->scripts() !!}

    <script>
        $(document).ready(function() {
            // Re-draw datatable on filter changes
            $('#filter_dealer_id, #filter_salesperson_id, #filter_city, #filter_outstanding_only, #filter_credit_exceeded').on('change', function() {
                window.LaravelDataTables["dealer-ledger-summary-table"].draw();
            });

            // Date picker for filter_end_date
            datepicker('#filter_end_date', {
                position: 'bl',
                formatter: (input, date, instance) => {
                    const value = date.toLocaleDateString('en-US'); // Will format according to requirements or Worksuite standard
                    input.value = moment(date).format('{{ company()->date_format_js }}');
                    window.LaravelDataTables["dealer-ledger-summary-table"].draw();
                },
                onSelect: (instance, date) => {
                    window.LaravelDataTables["dealer-ledger-summary-table"].draw();
                }
            });

            // Pass filters to DataTable ajax request
            $('#dealer-ledger-summary-table').on('preXhr.dt', function(e, settings, data) {
                data['dealerId'] = $('#filter_dealer_id').val();
                data['salespersonId'] = $('#filter_salesperson_id').val();
                data['city'] = $('#filter_city').val();
                data['outstandingOnly'] = $('#filter_outstanding_only').val();
                data['creditExceeded'] = $('#filter_credit_exceeded').val();
                data['endDate'] = $('#filter_end_date').val();
            });

            // More filters toggle
            $('#btn-more-filters').click(function() {
                $('.filter-box-more').toggleClass('d-none');
            });

            // Reset filters
            $('#btn-reset-filters').click(function() {
                $('#filter_dealer_id').val('all').selectpicker('refresh');
                $('#filter_salesperson_id').val('all').selectpicker('refresh');
                $('#filter_city').val('all').selectpicker('refresh');
                $('#filter_outstanding_only').val('no').selectpicker('refresh');
                $('#filter_credit_exceeded').val('no').selectpicker('refresh');
                $('#filter_end_date').val('');
                window.LaravelDataTables["dealer-ledger-summary-table"].draw();
            });

            // Add manual adjustment voucher modal trigger
            $('#btn-add-adjustment').click(function() {
                const url = "{{ route('ledgers.create_adjustment') }}";
                $(MODAL_LG + ' ' + MODAL_HEADING).html('Post Adjustment Voucher');
                $.ajaxModal(MODAL_LG, url);
            });
        });
    </script>
@endpush
