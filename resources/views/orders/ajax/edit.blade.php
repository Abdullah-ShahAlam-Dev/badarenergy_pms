@php
$addProductPermission = user()->permission('add_product');
@endphp
<style>
    .customSequence .btn {
        border: none;
    }
</style>

<!-- CREATE ORDER START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('app.order') @lang('app.details')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveOrderForm">
        @method('PUT')
        <!-- ORDER NUMBER, DATE, DUE DATE, FREQUENCY START -->
        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <!-- ORDER NUMBER START -->
            <div class="col-md-3 mb-4">
                <div class="form-group mb-lg-0 mb-md-0 mb-4">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize"
                        for="usr">@lang('modules.orders.orderNumber')</label>
                    <div class="input-group">
                        <input type="text" name="order_id" id="order_id"
                            class="form-control height-35 f-15 readonly-background" readonly
                            value="{{ $order->order_number }}">
                    </div>
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
                            value="{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format(company()->date_format) : now(company()->timezone)->translatedFormat(company()->date_format) }}">
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
                            value="{{ $order->due_date ? \Carbon\Carbon::parse($order->due_date)->format(company()->date_format) : Carbon\Carbon::now(company()->timezone)->addDays(15)->format(company()->date_format) }}">
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
                                <option @if ($currency->id == $order->currency_id) selected @endif
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
                            <option value="dealer" {{ ($order->customer_type ?? 'dealer') == 'dealer' ? 'selected' : '' }}>Dealers</option>
                            <option value="distributor" {{ ($order->customer_type ?? '') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                            <option value="end_to_end" {{ ($order->customer_type ?? '') == 'end_to_end' ? 'selected' : '' }}>End to End Customer</option>
                            <option value="care_of" {{ ($order->customer_type ?? '') == 'care_of' ? 'selected' : '' }}>Care of</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- CUSTOMER TYPE END -->

            <!-- CLIENT / DEALER / DISTRIBUTOR / END-TO-END CUSTOMER / CARE OF START -->

            <!-- DEALERS SELECTION START -->
            <div class="col-md-4 mb-4 {{ ($order->customer_type ?? 'dealer') != 'dealer' ? 'd-none' : '' }}" id="dealer_select_div">
                <x-forms.label fieldId="client_id" fieldLabel="Select Dealer" fieldRequired="true"></x-forms.label>
                <div class="select-others height-35 rounded">
                    <select class="form-control select-picker" data-live-search="true" data-size="8" name="client_id" id="client_id">
                        <option value="">-- Select Dealer --</option>
                        @foreach ($dealers as $dlr)
                            <option value="{{ $dlr->id }}" {{ $order->client_id == $dlr->id ? 'selected' : '' }}>{{ mb_ucwords($dlr->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- DEALERS SELECTION END -->

            <!-- DISTRIBUTORS SELECTION START -->
            <div class="col-md-4 mb-4 {{ ($order->customer_type ?? '') != 'distributor' ? 'd-none' : '' }}" id="distributor_select_div">
                <x-forms.label fieldId="distributor_id" fieldLabel="Select Distributor" fieldRequired="true"></x-forms.label>
                <div class="select-others height-35 rounded">
                    <select class="form-control select-picker" data-live-search="true" data-size="8" name="distributor_id" id="distributor_id">
                        <option value="">-- Select Distributor --</option>
                        @foreach ($distributors as $dst)
                            <option value="{{ $dst->id }}" {{ $order->distributor_id == $dst->id ? 'selected' : '' }}>{{ mb_ucwords($dst->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- DISTRIBUTORS SELECTION END -->

            <!-- CARE OF SELECTION START -->
            <div class="col-md-4 mb-4 {{ ($order->customer_type ?? '') != 'care_of' ? 'd-none' : '' }}" id="care_of_select_div">
                <x-forms.label fieldId="care_of_id" fieldLabel="Select Employee (Care of)" fieldRequired="true"></x-forms.label>
                <div class="select-others height-35 rounded">
                    <select class="form-control select-picker" data-live-search="true" data-size="8" name="care_of_id" id="care_of_id">
                        <option value="">-- Select Employee --</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $order->care_of_id == $emp->id ? 'selected' : '' }}>{{ mb_ucwords($emp->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- CARE OF SELECTION END -->

            <!-- END TO END CUSTOMER FIELDS START -->
            <div class="col-md-12 mb-4 {{ ($order->customer_type ?? '') != 'end_to_end' ? 'd-none' : '' }}" id="end_to_end_customer_div">
                <div class="card border-0 bg-light p-3">
                    <h6 class="f-15 text-dark font-weight-bold mb-3">End to End Customer Details</h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <x-forms.text fieldId="custom_customer_name" fieldName="custom_customer_name" fieldLabel="Customer Name" fieldPlaceholder="Enter Customer Name" fieldRequired="true" :fieldValue="$order->custom_customer_name"></x-forms.text>
                        </div>
                        <div class="col-md-4 mb-2">
                            <x-forms.text fieldId="custom_customer_number" fieldName="custom_customer_number" fieldLabel="Phone Number (Optional)" fieldPlaceholder="Enter Phone Number" :fieldValue="$order->custom_customer_number"></x-forms.text>
                        </div>
                        <div class="col-md-4 mb-2">
                            <x-forms.text fieldId="custom_customer_address" fieldName="custom_customer_address" fieldLabel="Address (Optional)" fieldPlaceholder="Enter Address" :fieldValue="$order->custom_customer_address"></x-forms.text>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TO END CUSTOMER FIELDS END -->

            <!-- PROJECT AND GENERATED BY REMOVED -->

            @if (!in_array('client', user_roles()))
            <!-- Order Status -->
            <div class="col-md-4">
                <x-forms.label fieldId="status" :fieldLabel="__('app.status')" :fieldRequired="true" class="mt-0"></x-forms.label>
                @if ((in_array('admin', user_roles()) || user()->permission('edit_order') == 'all') && in_array($order->status, ['pending', 'processing']))
                    <select class="form-control select-picker" name="status" id="status">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-warning'></i> Pending">Pending</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-primary'></i> Approved">Approved</option>
                        <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-danger'></i> Rejected">Rejected</option>
                    </select>
                @else
                    <input type="hidden" name="status" value="{{ $order->status }}">
                    <div class="mt-2">
                        @if ($order->status == 'pending')
                            <i class='fa fa-circle mr-2 text-warning'></i> Pending
                        @elseif ($order->status == 'processing')
                            <i class='fa fa-circle mr-2 text-primary'></i> Approved
                        @elseif ($order->status == 'completed')
                            <i class='fa fa-circle mr-2 text-success'></i> Completed
                        @elseif ($order->status == 'canceled')
                            <i class='fa fa-circle mr-2 text-danger'></i> Rejected
                        @else
                            <i class='fa fa-circle mr-2 text-muted'></i> {{ ucfirst($order->status) }}
                        @endif
                    </div>
                @endif
            </div>
            @endif

            <!-- EXCHANGE RATE START -->
            <div class="col-md-3">
                <x-forms.label fieldId="exchange_rate" :fieldLabel="__('modules.currencySettings.exchangeRate')" fieldRequired="true"></x-forms.label>
                <input type="number" id="exchange_rate" name="exchange_rate"
                    class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                    value="{{$order->exchange_rate ?? $companyCurrency->exchange_rate}}" readonly>
                <small id="currency_exchange" class="form-text text-muted">( {{company()->currency->currency_code}} @lang('app.to') {{company()->currency->currency_code}} )</small>
            </div>
            <!-- EXCHANGE RATE END -->

            <!-- CALCULATE TAX START -->
            <div class="col-md-3">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="calculate_tax" :fieldLabel="__('modules.invoices.calculateTax')"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="calculate_tax" id="calculate_tax">
                            <option value="after_discount" {{ $order->calculate_tax == 'after_discount' ? 'selected' : '' }}>After Discount</option>
                            <option value="before_discount" {{ $order->calculate_tax == 'before_discount' ? 'selected' : '' }}>Before Discount</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- CALCULATE TAX END -->
        </div>

        <hr class="m-0 border-top-grey">

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

        <div id="sortable">
            @foreach ($order->items as $key => $item)
                <!-- DESKTOP DESCRIPTION TABLE START -->
                <div class="d-flex px-4 py-3 c-inv-desc item-row">

                    <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                        <table width="100%">
                            <tbody>
                                <tr class="text-dark-grey font-weight-bold f-14">
                                    <td width="{{ $invoiceSetting->hsn_sac_code_show ? '40%' : '50%' }}"
                                        class="border-0 inv-desc-mbl btlr">@lang('app.description')</td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td width="10%" class="border-0" align="right">@lang("app.hsnSac")</td>
                                    @endif
                                    <td width="10%" class="border-0" align="right" id="type">
                                        @lang('modules.invoices.qty')
                                    </td>
                                    <td width="10%" class="border-0" align="right">
                                        @lang("modules.invoices.unitPrice")</td>
                                    <td width="13%" class="border-0" align="right">@lang('modules.invoices.tax')
                                    </td>
                                    <td width="17%" class="border-0 bblr-mbl" align="right">
                                        @lang('modules.invoices.amount')</td>
                                </tr>
                                <tr>
                                    <td class="border-bottom-0 btrr-mbl btlr">
                                        <input type="text" class="form-control f-14 border-0 w-100 item_name" readonly
                                            name="item_name[]" placeholder="@lang('modules.expenses.itemName')"
                                            value="{{ $item->item_name }}">
                                    </td>
                                    <td class="border-bottom-0 d-block d-lg-none d-md-none">
                                        <textarea class="f-14 border-0 w-100 mobile-description"
                                            placeholder="@lang('placeholders.invoices.description')" readonly
                                            name="item_summary[]">{{ $item->item_summary }}</textarea>
                                    </td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td class="border-bottom-0">
                                            <input type="text" class="f-14 border-0 w-100 text-right hsn_sac_code"
                                                value="{{ $item->hsn_sac_code }}" name="hsn_sac_code[]">
                                        </td>
                                    @endif
                                    <td class="border-bottom-0">
                                        <input type="number" min="1"
                                            class="form-control f-14 border-0 w-100 text-right quantity mt-3"
                                            value="{{ $item->quantity }}" name="quantity[]">
                                        @if (!is_null($item->product_id) && $item->product_id != 0)
                                            @php
                                                $availableStock = \App\Models\Inventory::where('product_id', $item->product_id)->sum('quantity');
                                            @endphp
                                            <span class="text-dark-grey float-right border-0 f-12">{{ $item->unit->unit_type }}</span>
                                            <div class="text-muted f-11 mt-1 text-left">Available: {{ (float) $availableStock }}</div>
                                            <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                            <input type="hidden" name="unit_id[]" value="{{ $item->unit_id }}">
                                        @else
                                            <select class="text-dark-grey float-right border-0 f-12" name="unit_id[]">
                                                @foreach ($units as $unit)
                                                    <option
                                                    @if ($item->unit_id == $unit->id) selected @endif
                                                    value="{{ $unit->id }}">{{ $unit->unit_type }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="product_id[]" value="">
                                        @endif
                                    </td>
                                    <td class="border-bottom-0">
                                        <input type="number" min="0" step="0.01"
                                            class="f-14 border-0 w-100 text-right cost_per_item" placeholder="0.00"
                                            value="{{ $item->unit_price }}" name="cost_per_item[]">
                                    </td>
                                    <td class="border-bottom-0">
                                        <input class="form-control height-35 f-14 border-0 w-100 text-right bg-additional-grey "                            value="{{ strtoupper($item->tax_list) ?: '--' }}" readonly>
                                        <div class="select-others  d-none height-35 rounded border-0">
                                            <select id="multiselect{{ $key }}"
                                                name="taxes[{{ $key }}][]" multiple="multiple"
                                                class="select-picker type customSequence border-0" data-size="3">
                                                @foreach ($taxes as $tax)
                                                    <option data-rate="{{ $tax->rate_percent }}" data-tax-text="{{ strtoupper($tax->tax_name) .':'. $tax->rate_percent }}%" @if (isset($item->taxes) && array_search($tax->id, json_decode($item->taxes)) !== false) selected @endif
                                                        value="{{ $tax->id }}">{{ strtoupper($tax->tax_name) }}:
                                                        {{ $tax->rate_percent }}%</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td rowspan="2" align="right" valign="top" class="bg-amt-grey btrr-bbrr">
                                        <span
                                            class="amount-html">{{ number_format((float) $item->amount, 2, '.', '') }}</span>
                                        <input type="hidden" class="amount" name="amount[]"
                                            value="{{ $item->amount }}">
                                    </td>
                                </tr>
                                <tr class="d-none d-md-block d-lg-table-row">
                                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}"
                                        class="dash-border-top bblr">
                                        <textarea class="f-14 border-0 w-100 desktop-description" name="item_summary[]"
                                            placeholder="@lang('placeholders.invoices.description')" readonly>{{ $item->item_summary }}</textarea>
                                    </td>
                                    <td class="border-left-0">
                                        <input type="file"
                                        class="dropify"
                                        name="invoice_item_image[]"
                                        data-allowed-file-extensions="png jpg jpeg"
                                        data-messages-default="test"
                                        data-height="70"
                                        data-id="{{ $item->id }}"
                                        id="{{ $item->id }}"
                                        data-default-file="{{ $item->orderItemImage ? $item->orderItemImage->external_link : '' }}"
                                        disabled
                                        />
                                        <input type="hidden" name="invoice_item_image_url[]" value="{{ $item->orderItemImage ? $item->orderItemImage->external_link : '' }}">
                                        <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <a href="javascript:;"
                            class="d-flex align-items-center justify-content-center ml-3 remove-item"><i
                                class="fa fa-times-circle f-20 text-lightest"></i></a>
                    </div>
                </div>
                <!-- DESKTOP DESCRIPTION TABLE END -->
            @endforeach
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
                                        <td width="30%" class="border-top-0 sub-total">
                                            {{ number_format((float) $order->sub_total, 2, '.', '') }}</td>
                                        <input type="hidden" class="sub-total-field" name="sub_total"
                                            value="{{ $order->sub_total }}">
                                    </tr>
                                    {{-- {{in_array('client', user_roles()) ? 'd-none' : ''}} --}}
                                    <tr class="">
                                        <td width="30%" class="text-dark-grey">@lang('modules.invoices.discount')
                                        </td>
                                        <td width="30%" style="padding: 5px;">
                                            <table width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td width="50%" class="c-inv-sub-padding">
                                                            <input type="number" min="0" name="discount_value" {{in_array('client', user_roles()) ? 'readonly' : ''}}
                                                                class="form-control f-14 border-0 w-100 text-right discount_value"
                                                                placeholder="0" value="{{ $order->discount }}">
                                                        </td>
                                                        <td width="50%" align="left" class="c-inv-sub-padding">
                                                            @if (in_array('client', user_roles()))
                                                                @if ($order->discount_type == 'percent')
                                                                    %
                                                                @else
                                                                    @lang('modules.invoices.amount')
                                                                @endif
                                                            @endif
                                                            <div class="select-others select-tax height-35 rounded border-0 {{in_array('client', user_roles()) ? 'd-none' : ''}}">

                                                                <select class="form-control select-picker"
                                                                    id="discount_type" name="discount_type">
                                                                    <option @if ($order->discount_type == 'percent') selected @endif
                                                                        value="percent">%</option>
                                                                    <option @if ($order->discount_type == 'fixed') selected @endif
                                                                        value="fixed">
                                                                        @lang('modules.invoices.amount')</option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td><span
                                                id="discount_amount">{{ number_format((float) $order->discount, 2, '.', '') }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('modules.invoices.tax')</td>
                                        <td colspan="2" class="p-0">
                                            <table width="100%" id="invoice-taxes">
                                                <tr>
                                                    <td colspan="2"><span class="tax-percent">0.00</span></td>
                                                </tr>
                                            </table>
                                        </td>

                                    </tr>
                                    <tr class="bg-amt-grey f-16 f-w-500">
                                        <td colspan="2">@lang('modules.invoices.total')</td>
                                        <td><span
                                                class="total">{{ number_format((float) $order->total, 2, '.', '') }}</span>
                                        </td>
                                        <input type="hidden" class="total-field" name="total"
                                            value="{{ round($order->total, 2) }}">
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
                            <option value="0" {{ $order->sale_type == 0 ? 'selected' : '' }}>Credit Sale</option>
                            <option value="1" {{ $order->sale_type == 1 ? 'selected' : '' }}>Cash Sale</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- SALE TYPE END -->

            <!-- WAREHOUSE START -->
            <input type="hidden" name="warehouse_id" value="{{ $order->warehouse_id }}">
            <!-- WAREHOUSE END -->

            <!-- PAYMENT OPTIONS FOR CASH SALE START -->
            <div class="col-md-3 {{ $order->sale_type == 1 ? '' : 'd-none' }}" id="payment_gateway_div">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="gateway" fieldLabel="Payment Gateway" fieldRequired="true"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="gateway" id="gateway">
                            <option value="Offline" {{ $order->gateway == 'Offline' ? 'selected' : '' }}>Offline</option>
                            @if ($paymentGateway && $paymentGateway->stripe_status === 'active')
                                <option value="stripe" {{ $order->gateway == 'stripe' ? 'selected' : '' }}>Stripe</option>
                            @endif
                            @if ($paymentGateway && $paymentGateway->paypal_status === 'active')
                                <option value="paypal" {{ $order->gateway == 'paypal' ? 'selected' : '' }}>PayPal</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- OFFLINE METHOD START -->
            <div class="col-md-3 {{ $order->sale_type == 1 && $order->gateway == 'Offline' ? '' : 'd-none' }}" id="offline_methods_div">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="offline_methods" fieldLabel="Offline Payment Methods" fieldRequired="true"></x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="offline_methods" id="offline_methods">
                            @foreach ($offlineMethods as $method)
                                <option value="{{ $method->id }}" {{ $order->offline_method_id == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- TRANSACTION ID START -->
            <div class="col-md-3 {{ $order->sale_type == 1 && $order->gateway == 'Offline' ? '' : 'd-none' }}" id="transaction_id_div">
                <div class="form-group mb-4">
                    <x-forms.text fieldId="transaction_id" fieldName="transaction_id" fieldLabel="Transaction ID / Cheque No." fieldPlaceholder="Enter Receipt/Cheque reference no." fieldValue="{{ $order->transaction_id }}"></x-forms.text>
                </div>
            </div>

            <!-- PAYMENT SLIP FILE UPLOAD START -->
            <div class="col-md-3 {{ $order->sale_type == 1 && $order->gateway == 'Offline' ? '' : 'd-none' }}" id="payment_slip_div">
                <div class="form-group mb-4">
                    <x-forms.label fieldId="payment_slip" fieldLabel="Attach Slip / Receipt"></x-forms.label>
                    <input type="file" name="payment_slip" id="payment_slip" class="form-control height-35 f-15">
                    @if ($order->file)
                        <div class="mt-2">
                            <a href="{{ asset_url('order-files/' . $order->file) }}" target="_blank" class="text-primary f-13"><i class="fa fa-download mr-1"></i> Download Attached Slip</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- SALE TYPE & PAYMENT DETAILS BOTTOM END -->

        <!-- NOTE AND TERMS AND CONDITIONS START -->
        <div class="d-flex flex-wrap px-lg-4 px-md-4 px-3 py-3">
            <div class="col-md-6 col-sm-12 c-inv-note-terms p-0 mb-lg-0 mb-md-0 mb-3">
                <label class="f-14 text-dark-grey mb-12 text-capitalize w-100"
                    for="usr">@lang('app.clientNote')</label>
                <textarea class="form-control" name="note" id="note" rows="4"
                    placeholder="@lang('placeholders.invoices.note')">{{ $order->note }}</textarea>
            </div>
            @if (in_array('admin', user_roles()) || user()->permission('edit_order') == 'all')
                <div class="col-md-6 col-sm-12 c-inv-note-terms pl-md-4 p-0 mb-lg-0 mb-md-0 mb-3">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize w-100"
                        for="remarks">HOD Remarks / Admin Note</label>
                    <textarea class="form-control" name="remarks" id="remarks" rows="4"
                        placeholder="Enter remarks for approval/rejection">{{ $order->remarks }}</textarea>
                </div>
            @elseif ($order->remarks)
                <div class="col-md-6 col-sm-12 c-inv-note-terms pl-md-4 p-0 mb-lg-0 mb-md-0 mb-3">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize w-100"
                        for="remarks">HOD Remarks / Admin Note</label>
                    <div class="form-control bg-additional-grey height-auto min-height-100 py-2">
                        {!! nl2br(e($order->remarks)) !!}
                    </div>
                </div>
            @endif
        </div>
        <!-- NOTE AND TERMS AND CONDITIONS END -->

        <!-- CANCEL SAVE SEND START -->
        <x-form-actions class="c-inv-btns">

            <div class="d-flex">
                <x-forms.button-primary class="save-form mr-3" icon="check">@lang('app.save')
                </x-forms.button-primary>
            </div>

            <x-forms.button-cancel :link="route('orders.index')" class="border-0">@lang('app.cancel')
            </x-forms.button-cancel>
        </x-form-actions>
        <!-- CANCEL SAVE SEND END -->

    </x-form>
    <!-- FORM END -->
</div>
<!-- CREATE ORDER END -->

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
            if (val == 'dealer') {
                $('#dealer_select_div').removeClass('d-none');
                $('#distributor_select_div, #end_to_end_customer_div, #care_of_select_div').addClass('d-none');
                var dealerId = $('#client_id_dealer').val();
                $('#client_id').val(dealerId);
                if (dealerId) changeClient(dealerId);
            } else if (val == 'distributor') {
                $('#distributor_select_div').removeClass('d-none');
                $('#dealer_select_div, #end_to_end_customer_div, #care_of_select_div').addClass('d-none');
                var distId = $('#client_id_distributor').val();
                $('#client_id').val(distId);
                if (distId) changeClient(distId);
            } else if (val == 'end_to_end') {
                $('#end_to_end_customer_div').removeClass('d-none');
                $('#dealer_select_div, #distributor_select_div, #care_of_select_div').addClass('d-none');
                $('#client_id').val('');
                $('#client_billing_address').html('<span class="text-lightest">End to End Customer</span>');
                $('#client_shipping_address').html('<span class="text-lightest">End to End Customer</span>');
            } else if (val == 'care_of') {
                $('#care_of_select_div').removeClass('d-none');
                $('#dealer_select_div, #distributor_select_div, #end_to_end_customer_div').addClass('d-none');
                $('#client_id').val('');
                $('#client_billing_address').html('<span class="text-lightest">Care of (Employee)</span>');
                $('#client_shipping_address').html('<span class="text-lightest">Care of (Employee)</span>');
            }
        });

        $('#client_id_dealer').change(function() {
            var id = $(this).val();
            $('#client_id').val(id);
            if (id) changeClient(id);
        });

        $('#client_id_distributor').change(function() {
            var id = $(this).val();
            $('#client_id').val(id);
            if (id) changeClient(id);
        });

        function changeClient(id) {
            var url = "{{ route('clients.project_list', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#saveOrderForm',
                type: "POST",
                blockUI: true,
                data: {
                    _token: token
                },
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
                container: '#saveOrderForm',
                type: "POST",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#client_billing_address').html(nl2br(response.data.clientDetails
                            .address));
                        $('#add-shipping-field').addClass('d-none');
                        $('#client_shipping_address').removeClass('d-none');

                        if (response.data.clientDetails.shipping_address === null) {
                            var addShippingLink =
                                `<a href="javascript:;" class="text-capitalize" id="show-shipping-field"><i class="f-12 mr-2 fa fa-plus"></i>
                                    @lang("app.addShippingAddress")</a>`;
                            $('#client_shipping_address').html(addShippingLink);
                        } else {
                            $('#client_shipping_address').html(nl2br(response.data
                                .clientDetails
                                .shipping_address));
                        }
                    }
                }
            });

        });

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
            var currencyId = $('#currency_id').val();

            $.easyAjax({
                url: "{{ route('orders.add_item') }}",
                type: "GET",
                data: {
                    id: id,
                    currencyId: currencyId
                },
                blockUI: true,
                success: function(response) {
                    if($('input[name="item_name[]"]').val() == ''){
                        $("#sortable .item-row").remove();
                    }
                    $(response.view).hide().appendTo("#sortable").fadeIn(500);
                    calculateTotal();

                    var noOfRows = $(document).find('#sortable .item-row').length;
                    var i = $(document).find('.item_name').length - 1;
                    var itemRow = $(document).find('#sortable .item-row:nth-child(' + noOfRows +
                        ') select.type');
                    itemRow.attr('id', 'multiselect' + i);
                    itemRow.attr('name', 'taxes[' + i + '][]');
                    $(document).find('#multiselect' + i).selectpicker();

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

        $('#saveOrderForm').on('click', '.remove-item', function() {
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
                calculateTotal();
            });
        });

        $('.save-form').click(function() {

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
                url: "{{ route('orders.update', $order->id) }}",
                container: '#saveOrderForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                disableButton: true,
                buttonSelector: '.save-form',
                data: $('#saveOrderForm').serialize()
            })
        });

        $('#saveOrderForm').on('keyup', '.quantity,.cost_per_item,.item_name, .discount_value', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveOrderForm').on('change', '.type, #discount_type, #calculate_tax', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveOrderForm').on('input', '.quantity', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        calculateTotal();

        // Disable already added products in dropdown on page load
        $('input[name="product_id[]"]').each(function() {
            var id = $(this).val();
            if (id) {
                var option = $('#add-products option[value="' + id + '"]');
                if (option.length) {
                    var originalText = option.text().replace(' (Selected)', '');
                    option.text(originalText + ' (Selected)');
                    option.attr('data-content', originalText + ' <span class="badge badge-secondary">Selected</span>');
                    option.prop('disabled', true);
                }
            }
        });
        $('#add-products').selectpicker('refresh');

        // Cleanup modal backdrops and reset body classes on Turbo page transitions
        document.addEventListener('turbo:before-cache', function cleanup() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            document.removeEventListener('turbo:before-cache', cleanup);
        });

        init(RIGHT_MODAL);

    });
</script>
