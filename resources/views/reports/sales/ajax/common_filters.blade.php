<x-filters.filter-box>
    <!-- DATE RANGE -->
    <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
        <div class="select-status d-flex">
            <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                id="datatableRange2" placeholder="@lang('placeholders.dateRange')">
        </div>
    </div>

    <!-- DEALER -->
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

    <!-- SALESPERSON -->
    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Salesperson</p>
        <div class="select-status">
            <select class="form-control select-picker" name="salesperson_id" id="filter_salesperson_id" data-live-search="true" data-size="8">
                <option value="all">@lang('app.all')</option>
                @foreach ($salespersons as $sp)
                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- RESET & MORE -->
    <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
        <x-forms.button-primary id="btn-reset-filters" class="btn-xs btn-light mr-2" icon="sync">Reset</x-forms.button-primary>
        <x-forms.button-secondary id="btn-more-filters" class="btn-xs" icon="filter">Filters</x-forms.button-secondary>
    </div>
</x-filters.filter-box>

<!-- Collapsible Advanced Filters -->
<div class="filter-box-more d-none mt-2">
    <x-filters.filter-box>
        <!-- WAREHOUSE -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Warehouse</p>
            <div class="select-status">
                <select class="form-control select-picker" name="warehouse_id" id="filter_warehouse_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- PRODUCT -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Product</p>
            <div class="select-status">
                <select class="form-control select-picker" name="product_id" id="filter_product_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- MODEL -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Model</p>
            <div class="select-status">
                <select class="form-control select-picker" name="model_id" id="filter_model_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($models as $m)
                        <option value="{{ $m->id }}">{{ $m->category_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- LOCATION/CITY -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Location</p>
            <div class="select-status">
                <select class="form-control select-picker" name="city" id="filter_city" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- PAYMENT STATUS -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Payment</p>
            <div class="select-status">
                <select class="form-control select-picker" name="payment_status" id="filter_payment_status">
                    <option value="all">@lang('app.all')</option>
                    <option value="complete">Complete</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>

        <!-- INVOICE STATUS -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Invoice</p>
            <div class="select-status">
                <select class="form-control select-picker" name="invoice_status" id="filter_invoice_status">
                    <option value="all">@lang('app.all')</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                </select>
            </div>
        </div>
    </x-filters.filter-box>
</div>
