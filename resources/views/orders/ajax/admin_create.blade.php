@php
$addProductPermission = user()->permission('add_product');
@endphp

<!-- CREATE INVOICE START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('modules.orders.createOrder')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveInvoiceForm">
        <input type="hidden" name="type" value="send">
        <!-- CLIENT, PROJECT, GST, BILLING, SHIPPING ADDRESS START -->
        <div class="row px-lg-4 px-md-4 px-3 pt-3">
            <!-- ORDER NUMBER START -->
            <div class="col-md-3 mb-4">
                <div class="form-group mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label class="mb-12" fieldId="order_number" :fieldLabel="__('modules.orders.orderNumber')" fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <x-slot name="prepend">
                            <span
                                class="input-group-text">{{ invoice_setting()->order_prefix }}{{ invoice_setting()->order_number_separator }}{{ $zero }}</span>
                        </x-slot>
                        <input type="text" name="order_number" id="order_number"
                            class="form-control height-35 f-15" value="{{ is_null($lastOrder) ? 1 : $lastOrder }}">
                    </x-forms.input-group>
                </div>
            </div>
            <!-- ORDER NUMBER END -->

            <!-- ORDER DATE START -->
            <div class="col-md-3 mb-4">
                <div class="form-group mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label fieldId="order_date" :fieldLabel="__('modules.orders.orderDate')" fieldRequired="true"></x-forms.label>
                    <div class="input-group ">
                        <input type="text" id="order_date" name="order_date"
                            class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                            placeholder="@lang('placeholders.date')"
                            value="{{ now(company()->timezone)->translatedFormat(company()->date_format) }}">
                    </div>
                </div>
            </div>
            <!-- ORDER DATE END -->

            <!-- DUE DATE START -->
            <div class="col-md-3 mb-4">
                <div class="form-group mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label fieldId="due_date" :fieldLabel="__('app.dueDate')" fieldRequired="true"></x-forms.label>
                    <div class="input-group ">
                        <input type="text" id="due_date" name="due_date"
                            class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                            placeholder="@lang('placeholders.date')"
                            value="{{ Carbon\Carbon::now(company()->timezone)->addDays(15)->format(company()->date_format) }}">
                    </div>
                </div>
            </div>
            <!-- DUE DATE END -->

            <!-- CURRENCY START -->
            <div class="col-md-3 mb-4">
                <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="currency_id" id="currency_id">
                            @forelse ($currencies as $currency)
                                <option @if ($currency->id == company()->currency_id) selected @endif
                                    value="{{ $currency->id }}" data-currency-code="{{$currency->currency_code}}">
                                    {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                </option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>
            <!-- CURRENCY END -->
            <!-- CUSTOMER TYPE START -->
            <div class="col-md-3 mb-4">
                <div class="form-group c-inv-select mb-0">
                    <x-forms.label fieldId="customer_type" fieldLabel="Customer Type" fieldRequired="true"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="customer_type" id="customer_type">
                            <option value="dealer">Dealers</option>
                            <option value="distributor">Distributor</option>
                            <option value="end_to_end">End to End Customer</option>
                            <option value="care_of">Care of</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- CUSTOMER TYPE END -->

            <!-- CLIENT / DEALER / DISTRIBUTOR / END-TO-END CUSTOMER / CARE OF START -->

            <!-- DEALERS SELECTION START -->
            <div class="col-md-4 mb-4" id="dealer_select_div">
                <x-forms.label fieldId="client_id" fieldLabel="Select Dealer" fieldRequired="true"></x-forms.label>
                <div class="select-others height-35 rounded">
                    <select class="form-control select-picker" data-live-search="true" data-size="8" name="client_id" id="client_id">
                        <option value="">-- Select Dealer --</option>
                        @foreach ($dealers as $dlr)
                            <option value="{{ $dlr->id }}">{{ mb_ucwords($dlr->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- DEALERS SELECTION END -->

            <!-- DISTRIBUTORS SELECTION START -->
            <div class="col-md-4 mb-4 d-none" id="distributor_select_div">
                <x-forms.label fieldId="distributor_id" fieldLabel="Select Distributor" fieldRequired="true"></x-forms.label>
                <div class="select-others height-35 rounded">
                    <select class="form-control select-picker" data-live-search="true" data-size="8" name="distributor_id" id="distributor_id">
                        <option value="">-- Select Distributor --</option>
                        @foreach ($distributors as $dst)
                            <option value="{{ $dst->id }}">{{ mb_ucwords($dst->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- DISTRIBUTORS SELECTION END -->

            <!-- CARE OF SELECTION START -->
            <div class="col-md-4 mb-4 d-none" id="care_of_select_div">
                <x-forms.label fieldId="care_of_id" fieldLabel="Select Employee (Care of)" fieldRequired="true"></x-forms.label>
                <div class="select-others height-35 rounded">
                    <select class="form-control select-picker" data-live-search="true" data-size="8" name="care_of_id" id="care_of_id">
                        <option value="">-- Select Employee --</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ mb_ucwords($emp->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- CARE OF SELECTION END -->

            <!-- END TO END CUSTOMER FIELDS START -->
            <div class="col-md-12 mb-4 d-none" id="end_to_end_customer_div">
                <div class="card border-0 bg-light p-3">
                    <h6 class="f-15 text-dark font-weight-bold mb-3">End to End Customer Details</h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <x-forms.text fieldId="custom_customer_name" fieldName="custom_customer_name" fieldLabel="Customer Name" fieldPlaceholder="Enter Customer Name" fieldRequired="true"></x-forms.text>
                        </div>
                        <div class="col-md-4 mb-2">
                            <x-forms.text fieldId="custom_customer_number" fieldName="custom_customer_number" fieldLabel="Phone Number (Optional)" fieldPlaceholder="Enter Phone Number"></x-forms.text>
                        </div>
                        <div class="col-md-4 mb-2">
                            <x-forms.text fieldId="custom_customer_address" fieldName="custom_customer_address" fieldLabel="Address (Optional)" fieldPlaceholder="Enter Address"></x-forms.text>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TO END CUSTOMER FIELDS END -->
            <!-- BILLING ADDRESS START -->
            <div class="col-md-4 mb-4" id="billing_address_div">
                <div class="form-group c-inv-select mb-0">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize w-100"
                        for="usr">@lang('modules.invoices.billingAddress')</label>
                    <p class="f-15" id="client_billing_address">
                        <span class="text-lightest">@lang('messages.selectCustomerForBillingAddress')</span>
                    </p>
                </div>
            </div>
            <!-- SHIPPING ADDRESS START -->
            <div class="col-md-4" id="shipping_address_div">
                <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize w-100"
                        for="usr">@lang('modules.invoices.shippingAddress') </label>
                    <p class="f-15" id="client_shipping_address">
                        @if (isset($estimate) && $estimate->client && $estimate->client->clientDetails->shipping_address)
                            {!! nl2br($estimate->client->clientDetails->shipping_address) !!}
                        @elseif(isset($client) && $client->clientDetails && $client->clientDetails->shipping_address)
                            {!! nl2br($client->clientDetails->shipping_address) !!}
                        @else
                            <a href="javascript:;" class="text-capitalize" id="show-shipping-field"><i
                                    class="f-12 mr-2 fa fa-plus"></i>@lang('app.addShippingAddress')</a>
                        @endif
                    </p>
                    <p class="d-none" id="add-shipping-field">
                        <textarea class="form-control f-14 pt-2" rows="3" placeholder="@lang('placeholders.address')"
                            name="shipping_address"
                            id="shipping_address">@if (isset($estimate) && $estimate->client) {!! nl2br($estimate->client->clientDetails->shipping_address) !!} @endif</textarea>
                    </p>
                </div>
            </div>
            <!-- SHIPPING ADDRESS END -->

            <!-- PROJECT AND GENERATED BY REMOVED -->

            <!-- Order Status (Default to Pending on creation) -->
            <input type="hidden" name="status" id="status" value="pending">

            <!-- EXCHANGE RATE START -->
            <div class="col-md-3">
                <x-forms.label fieldId="exchange_rate" :fieldLabel="__('modules.currencySettings.exchangeRate')" fieldRequired="true"></x-forms.label>
                <input type="number" id="exchange_rate" name="exchange_rate"
                    class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                    value="{{$companyCurrency->exchange_rate}}" readonly>
                <small id="currency_exchange" class="form-text text-muted">( {{company()->currency->currency_code}} @lang('app.to') {{company()->currency->currency_code}} )</small>
            </div>
            <!-- EXCHANGE RATE END -->

            <!-- CALCULATE TAX START -->
            <div class="col-md-3">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="calculate_tax" :fieldLabel="__('modules.invoices.calculateTax')"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="calculate_tax" id="calculate_tax">
                            <option value="after_discount">After Discount</option>
                            <option value="before_discount">Before Discount</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- CALCULATE TAX END -->
        </div>

        <hr class="m-0 mt-2 border-top-grey">

        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <div class="col-md-3 d-none product-category-filter">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.input-group>
                        <select class="form-control select-picker" name="category_id"
                                id="product_category_id" data-live-search="true">
                            <option value="">{{ __('app.select') . ' ' . __('app.product') . ' ' . __('app.category')  }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group c-inv-select mb-4">
                <x-forms.input-group>
                    <select class="form-control select-picker" data-live-search="true" data-size="8" id="add-products" title="{{ __('app.menu.selectProduct') }}">
                        @foreach ($products as $item)
                            <option data-content="{{ $item->name }}" value="{{ $item->id }}">
                                {{ $item->name }}</option>
                        @endforeach
                    </select>
                    <x-slot name="preappend">
                        <a href="javascript:;"
                            class="btn btn-outline-secondary border-grey toggle-product-category"
                            data-toggle="tooltip" data-original-title="{{ __('modules.productCategory.filterByCategory') }}"><i class="fa fa-filter"></i></a>
                    </x-slot>
                    @if ($addProductPermission == 'all' || $addProductPermission == 'added')
                        <x-slot name="append">
                            <a href="{{ route('products.create') }}" data-redirect-url="no"
                                class="btn btn-outline-secondary border-grey openRightModal"
                                data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.dashboard.newproduct') }}">@lang('app.add')</a>
                        </x-slot>
                    @endif
                </x-forms.input-group>
                </div>
            </div>
        </div>

        <x-alert class="my-4 mx-4" id="alertMessage" type="danger">@lang('messages.addItem')</x-alert>
        <div id="sortable">
        </div>

        <hr class="m-0 border-top-grey">

        <!-- TOTAL, DISCOUNT START -->
        <div class="d-flex px-lg-4 px-md-4 px-3 pb-3 c-inv-total">
            <table width="100%" class="text-right f-14 text-capitalize">
                <tbody>
                    <tr>
                        <td width="50%" class="border-0 d-lg-table d-md-table d-none"></td>
                        <td width="50%" class="p-0 border-0 c-inv-total-right">
                            <table width="100%">
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="border-top-0 text-dark-grey">
                                            @lang('modules.invoices.subTotal')</td>
                                        <td width="30%" class="border-top-0 sub-total">0.00</td>
                                        <input type="hidden" class="sub-total-field" name="sub_total" value="0">
                                    </tr>
                                    <tr>
                                        <td width="20%" class="text-dark-grey">@lang('modules.invoices.discount')
                                        </td>
                                        <td width="40%" style="padding: 5px;">
                                            <table width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td width="70%" class="c-inv-sub-padding">
                                                            <input type="number" min="0" name="discount_value"
                                                                class="form-control f-14 border-0 w-100 text-right discount_value"
                                                                placeholder="0"
                                                                value="{{ isset($estimate) ? $estimate->discount : '0' }}">
                                                        </td>
                                                        <td width="30%" align="left" class="c-inv-sub-padding">
                                                            <div
                                                                class="select-others select-tax height-35 rounded border-0">
                                                                <select class="form-control select-picker"
                                                                    id="discount_type" name="discount_type">
                                                                    <option @if (isset($estimate) && $estimate->discount_type == 'percent') selected @endif value="percent">%
                                                                    </option>
                                                                    <option @if (isset($estimate) && $estimate->discount_type == 'fixed') selected @endif value="fixed">
                                                                        @lang('modules.invoices.amount')</option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td><span
                                                id="discount_amount">{{ isset($estimate) ? number_format((float) $estimate->discount, 2, '.', '') : '0.00' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('modules.invoices.tax')</td>
                                        <td colspan="2" class="p-0 border-0">
                                            <table width="100%" id="invoice-taxes">
                                                <tr>
                                                    <td colspan="2"><span class="tax-percent">0.00</span></td>
                                                </tr>
                                            </table>
                                        </td>

                                    </tr>
                                    <tr class="bg-amt-grey f-16 f-w-500">
                                        <td colspan="2">@lang('modules.invoices.total')</td>
                                        <td><span class="total">0.00</span></td>
                                        <input type="hidden" class="total-field" name="total" value="0">
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- TOTAL, DISCOUNT END -->

        <!-- SALE TYPE & PAYMENT DETAILS BOTTOM START -->
        <div class="row px-lg-4 px-md-4 px-3 py-3 border-top-grey bg-light">
            <!-- SALE TYPE START -->
            <div class="col-md-3">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="sale_type" fieldLabel="Sale Type" fieldRequired="true"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="sale_type" id="sale_type">
                            <option value="0">Credit Sale</option>
                            <option value="1">Cash Sale</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- SALE TYPE END -->

            <!-- WAREHOUSE START -->
            <input type="hidden" name="warehouse_id" value="">
            <!-- WAREHOUSE END -->

            <!-- PAYMENT OPTIONS FOR CASH SALE START -->
            <div class="col-md-3 d-none" id="payment_gateway_div">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="gateway" fieldLabel="Payment Gateway" fieldRequired="true"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="gateway" id="gateway">
                            <option value="Offline">Offline</option>
                            @if ($paymentGateway && $paymentGateway->stripe_status === 'active')
                                <option value="stripe">Stripe</option>
                            @endif
                            @if ($paymentGateway && $paymentGateway->paypal_status === 'active')
                                <option value="paypal">PayPal</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- OFFLINE METHOD START -->
            <div class="col-md-3 d-none" id="offline_methods_div">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="offline_methods" fieldLabel="Offline Payment Methods" fieldRequired="true"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="offline_methods" id="offline_methods">
                            @foreach ($offlineMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- TRANSACTION ID START -->
            <div class="col-md-3 d-none" id="transaction_id_div">
                <div class="form-group mb-4">
                    <x-forms.text fieldId="transaction_id" fieldName="transaction_id" fieldLabel="Transaction ID / Cheque No." fieldPlaceholder="Enter Receipt/Cheque reference no."></x-forms.text>
                </div>
            </div>

            <!-- PAYMENT SLIP FILE UPLOAD START -->
            <div class="col-md-3 d-none" id="payment_slip_div">
                <div class="form-group mb-4">
                    <x-forms.label fieldId="payment_slip" fieldLabel="Attach Slip / Receipt"></x-forms.label>
                    <input type="file" name="payment_slip" id="payment_slip" class="form-control height-35 f-15">
                </div>
            </div>
        </div>
        <!-- SALE TYPE & PAYMENT DETAILS BOTTOM END -->

    <!-- NOTE AND TERMS AND CONDITIONS START -->
    <div class="d-flex flex-wrap px-lg-4 px-md-4 px-3 py-3">
        <div class="col-md-6 col-sm-12 c-inv-note-terms p-0 mb-lg-0 mb-md-0 mb-3">
            <label class="f-14 text-dark-grey mb-12 text-capitalize w-100"
                for="usr">@lang('app.clientNote')</label>
            <textarea class="form-control" name="note" id="note" rows="4"></textarea>
        </div>

    </div>
    <!-- NOTE AND TERMS AND CONDITIONS END -->
        <!-- CANCEL SAVE SEND START -->
        <x-form-actions class="c-inv-btns d-block d-lg-flex d-md-flex">
            <x-forms.button-primary id="createOrder">@lang('app.submit')</x-forms.button-primary>

            <x-forms.button-cancel :link="route('orders.index')" class="ml-2 border-0 ">@lang('app.cancel')
            </x-forms.button-cancel>

        </x-form-actions>
        <!-- CANCEL SAVE SEND END -->

    </x-form>
    <!-- FORM END -->
</div>
<!-- CREATE INVOICE END -->
<script>
    $(document).ready(function() {

         $('.toggle-product-category').click(function() {
            $('.product-category-filter').toggleClass('d-none');
        });

        $('#product_category_id').on('change', function(){
            var categoryId = $(this).val();
            var url = "{{route('invoices.product_category', ':id')}}",
            url = (categoryId) ? url.replace(':id', categoryId) : url.replace(':id', null);;
            $.easyAjax({
                url : url,
                type : "GET",
                container: '#saveInvoiceForm',
                blockUI: true,
                success: function (response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            selectData = '<option value="' + value.id + '">' + value.name +
                                '</option>';
                            options.push(selectData);
                        });
                        $('#add-products').html(
                            '<option value="" class="form-control" >{{ __('app.select') . ' ' . __('app.product') }}</option>' +
                            options);
                        $('#add-products').selectpicker('refresh');
                    }
                }
            });
        });

        const hsn_status = {{ $invoiceSetting->hsn_sac_code_show }};

        const dp1 = datepicker('#order_date', { position: 'bl', ...datepickerConfig });
        const dp2 = datepicker('#due_date',     { position: 'bl', ...datepickerConfig });

        $('#sale_type').change(function() {
            var val = $(this).val();
            if (val == '1') { // Cash Sale
                $('#payment_gateway_div, #offline_methods_div, #transaction_id_div, #payment_slip_div').removeClass('d-none');
            } else { // Credit Sale
                $('#payment_gateway_div, #offline_methods_div, #transaction_id_div, #payment_slip_div').addClass('d-none');
            }
        });

        $('#gateway').change(function() {
            var val = $(this).val();
            if (val == 'Offline') {
                $('#offline_methods_div, #transaction_id_div, #payment_slip_div').removeClass('d-none');
            } else {
                $('#offline_methods_div, #transaction_id_div, #payment_slip_div').addClass('d-none');
            }
        });

        $('#customer_type').change(function() {
            var val = $(this).val();

            if (val == 'care_of' || val == 'end_to_end') {
                $('#billing_address_div, #shipping_address_div').addClass('d-none');
            } else {
                $('#billing_address_div, #shipping_address_div').removeClass('d-none');
            }

            if (val == 'dealer') {
                $('#dealer_select_div').removeClass('d-none');
                $('#distributor_select_div, #end_to_end_customer_div, #care_of_select_div').addClass('d-none');
                var dealerId = $('#client_id').val();
                if (dealerId) changeClient(dealerId);
            } else if (val == 'distributor') {
                $('#distributor_select_div').removeClass('d-none');
                $('#dealer_select_div, #end_to_end_customer_div, #care_of_select_div').addClass('d-none');
            } else if (val == 'end_to_end') {
                $('#end_to_end_customer_div').removeClass('d-none');
                $('#dealer_select_div, #distributor_select_div, #care_of_select_div').addClass('d-none');
                $('#client_billing_address').html('<span class="text-lightest">End to End Customer</span>');
                $('#client_shipping_address').html('<span class="text-lightest">End to End Customer</span>');
            } else if (val == 'care_of') {
                $('#care_of_select_div').removeClass('d-none');
                $('#dealer_select_div, #distributor_select_div, #end_to_end_customer_div').addClass('d-none');
            }
        });

        $('#client_id').change(function() {
            var id = $(this).val();
            if (id) changeClient(id);
        });

        function changeClient(id) {

            if (id == '') {
                id = 0;
            }
            console.log(id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: "{{ route('clients.project_list', ':id') }}".replace(':id', id),
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                data: { _token: token },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#project_id').html(response.data);
                        $('#project_id').selectpicker('refresh');
                    }
                }
            });

            var url = "{{ route('clients.ajax_details', ':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        if (response.data !== null) {
                            $('#client_billing_address').html(nl2br(response.data.client_details
                                .address));
                            $('#add-shipping-field').addClass('d-none');
                            $('#client_shipping_address').removeClass('d-none');

                            if (response.data.client_details.shipping_address === null) {
                                var addShippingLink =
                                    '<a href="javascript:;" class="text-capitalize" id="show-shipping-field"><i class="f-12 mr-2 fa fa-plus"></i>@lang("app.addShippingAddress")</a>';
                                $('#client_shipping_address').html(addShippingLink);
                            } else {
                                $('#client_shipping_address').html(nl2br(response.data
                                    .client_details
                                    .shipping_address));
                            }

                            // Credit details section
                            if (response.data.client_details !== undefined && response.data.client_details !== null) {
                                var details = response.data.client_details;
                                var creditLimit = parseFloat(details.credit_limit) || 0.00;
                                var outstanding = parseFloat(details.outstanding_balance) || 0.00;
                                var available = parseFloat(details.available_credit) || 0.00;

                                var badgeClass = available > 0 ? 'badge-success' : 'badge-danger';
                                var html = '<div class="alert alert-light border p-2 mt-2 mb-0" style="background-color: #f8f9fa;">' +
                                    '<div class="d-flex justify-content-between mb-1"><span>Credit Limit:</span> <strong>PKR ' + creditLimit.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</strong></div>' +
                                    '<div class="d-flex justify-content-between mb-1"><span>Outstanding:</span> <strong>PKR ' + outstanding.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</strong></div>' +
                                    '<div class="d-flex justify-content-between"><span>Available Credit:</span> <span class="badge ' + badgeClass + '">PKR ' + available.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</span></div>' +
                                    '</div>';
                                $('#client-credit-info').html(html).removeClass('d-none');
                            } else {
                                $('#client-credit-info').addClass('d-none').html('');
                            }

                        } else {
                            $('#client_billing_address').html(
                                '<span class="text-lightest">@lang("messages.selectCustomerForBillingAddress")</span>'
                            );
                            $('#client-credit-info').addClass('d-none').html('');
                        }
                    } else {
                        var addShippingLink =
                            '<a href="javascript:;" class="text-capitalize" id="show-shipping-field"><i class="f-12 mr-2 fa fa-plus"></i>@lang("app.addShippingAddress")</a>';
                        $('#client_shipping_address').html(addShippingLink);
                        $('#client-credit-info').addClass('d-none').html('');
                    }
                }
            });

        }

        $('body').on('click', '#show-shipping-field', function() {
            $('#add-shipping-field, #client_shipping_address').toggleClass('d-none');
        });

        const resetAddProductButton = () => {
            $("#add-products").val('').selectpicker("refresh");
        };

        $('#add-products').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
            e.stopImmediatePropagation()
            var id = $(this).val();
            if (previousValue != id && id != '') {
                addProduct(id);
                resetAddProductButton();
            }
        });

        function ucWord(str){
            str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                return letter.toUpperCase();
            });
            return str;
        }

        function addProduct(id) {
            $.easyAjax({
                url: "{{ route('orders.add_item') }}",
                type: "GET",
                data: {
                    id: id
                },
                blockUI: true,
                success: function(response) {
                    if($('input[name="item_name[]"]').val() == ''){
                        $("#sortable .item-row").remove();
                    }
                    $(response.view).hide().appendTo("#sortable").fadeIn(500);
                    $('.selectpicker').selectpicker('refresh');
                    calculateTotal();
                    $('.dropify').dropify();
                    $('#alertMessage').hide().fadeOut(500);
                    var noOfRows = $(document).find('#sortable .item-row').length;
                    var i = $(document).find('.item_name').length - 1;
                    var itemRow = $(document).find('#sortable .item-row:nth-child(' + noOfRows +
                        ') select.type');
                    itemRow.attr('id', 'multiselect' + i);
                    itemRow.attr('name', 'taxes[' + i + '][]');
                    $(document).find('#multiselect' + i).selectpicker();

                    $(document).find('#dropify' + i).dropify({
                        messages: dropifyMessages
                    });

                    // Disable option in dropdown
                    var option = $('#add-products option[value="' + id + '"]');
                    if (option.length) {
                        var originalText = option.text().replace(' (Selected)', '');
                        option.text(originalText + ' (Selected)');
                        option.attr('data-content', originalText + ' <span class="badge badge-secondary">Selected</span>');
                        option.prop('disabled', true);
                    }
                    $('#add-products').selectpicker('refresh');
                }
            });
        }


        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            var row = $(this).closest('.item-row');
            var productId = row.find('input[name="product_id[]"]').val();

            row.fadeOut(300, function() {
                row.remove();

                if (productId) {
                    var option = $('#add-products option[value="' + productId + '"]');
                    if (option.length) {
                        var originalText = option.text().replace(' (Selected)', '');
                        option.text(originalText);
                        option.attr('data-content', originalText);
                        option.prop('disabled', false);
                    }
                    $('#add-products').selectpicker('refresh');
                }

                $('select.customSequence').each(function(index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });

                if($(document).find('#sortable .item-row').length == 0){
                    $('#alertMessage').show().fadeIn(500);
                }

                calculateTotal();
            });
        });

        $('#createOrder').click(function() {

            if (KTUtil.isMobileDevice()) {
                $('.desktop-description').remove();
            } else {
                $('.mobile-description').remove();
            }

            calculateTotal();

            var discount = $('#discount_amount').html();
            var total = $('.sub-total-field').val();

            if (parseFloat(discount) > parseFloat(total)) {
                Swal.fire({
                    icon: 'error',
                    text: "{{ __('messages.discountExceed') }}",

                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                });
                return false;
            }

            $.easyAjax({
                url: "{{ route('orders.store') }}",
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                file: true,
                data: $('#saveInvoiceForm').serialize()
            })
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                $('select.customSequence').each(function(index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });
                calculateTotal();
            });
        });

        $('#saveInvoiceForm').on('keyup', '.quantity,.cost_per_item,.item_name, .discount_value', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveInvoiceForm').on('change', '.type, #discount_type, #calculate_tax', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveInvoiceForm').on('input', '.quantity', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        calculateTotal();

        // Cleanup modal backdrops and reset body classes on Turbo page transitions
        document.addEventListener('turbo:before-cache', function cleanup() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            document.removeEventListener('turbo:before-cache', cleanup);
        });

        init(RIGHT_MODAL);
    });

</script>
