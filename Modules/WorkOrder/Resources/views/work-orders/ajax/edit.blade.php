<div class="row">
    <div class="col-sm-12">
        <x-form id="update-work-order-form" method="POST">
            <input type="hidden" name="_method" value="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('workorder::modules.workOrder.editWorkOrder') — {{ $workOrder->wo_number }}
                </h4>

                <div class="row p-20">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="wo_number_disp" :fieldLabel="__('workorder::modules.workOrder.workOrderNo')"
                            fieldName="wo_number_disp" :fieldValue="$workOrder->wo_number" fieldReadOnly="true" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="wo_date" :fieldLabel="__('workorder::modules.workOrder.woDate')"
                            fieldName="wo_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$workOrder->wo_date ? $workOrder->wo_date->format(company()->date_format) : ''" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12" for="completion_date_time">
                                @lang('workorder::modules.workOrder.completionDateTime')
                            </label>
                            <input type="datetime-local" class="form-control height-35 f-14"
                                id="completion_date_time" name="completion_date_time"
                                value="{{ $workOrder->completion_date_time ? $workOrder->completion_date_time->format('Y-m-d\TH:i') : '' }}">
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <x-forms.select fieldId="event_id" :fieldLabel="__('workorder::modules.workOrder.event')"
                            fieldName="event_id" fieldRequired="true" search="true">
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected($workOrder->event_id == $event->id)>{{ $event->event_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.select fieldId="vendor_id" :fieldLabel="__('workorder::modules.workOrder.vendor')"
                            fieldName="vendor_id" fieldRequired="true" search="true">
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected($workOrder->vendor_id == $vendor->id)>{{ $vendor->vendor_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="work_category" :fieldLabel="__('workorder::modules.workOrder.workCategory')"
                            fieldName="work_category" :fieldValue="$workOrder->work_category" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="venue" :fieldLabel="__('workorder::modules.workOrder.venue')"
                            fieldName="venue" :fieldValue="$workOrder->venue" />
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <x-forms.number fieldId="no_of_days" :fieldLabel="__('workorder::modules.workOrder.noOfDays')"
                            fieldName="no_of_days" :fieldValue="$workOrder->no_of_days" />
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <x-forms.select fieldId="priority" :fieldLabel="__('workorder::modules.workOrder.priority')" fieldName="priority">
                            @foreach(['low','medium','high','urgent'] as $p)
                                <option value="{{ $p }}" @selected($workOrder->priority == $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea fieldId="description" :fieldLabel="__('workorder::modules.workOrder.description')"
                            fieldName="description" :fieldValue="$workOrder->description" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="remarks" :fieldLabel="__('workorder::modules.workOrder.remarks')"
                            fieldName="remarks" :fieldValue="$workOrder->remarks" />
                    </div>
                </div>

                {{-- ── ITEMS ─────────────────────────────────────────────── --}}
                <div class="border-top-grey p-20">
                    <h5 class="f-15 f-w-500 mb-3">Items / Services</h5>
                    <div id="wo-item-list">
                        @foreach($workOrder->items as $item)
                        @php $isWithoutAmt = (bool)$item->without_amount; @endphp
                        <div class="row p-10 item-row border-top-grey">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Product <sup class="f-14">*</sup></label>
                                    <select class="form-control height-35 f-14 selectpicker item-product" name="product_id[]" data-live-search="true" required>
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" @selected($item->product_id == $product->id) data-rate="{{ $product->price }}" data-unit="{{ $product->unit ? $product->unit->unit_type : '' }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Qty <sup class="f-14">*</sup></label>
                                    <input type="number" step="0.01" class="form-control height-35 f-14 item-qty" name="quantity[]" value="{{ $item->quantity }}" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Unit</label>
                                    <input type="text" class="form-control height-35 f-14 item-unit" name="unit[]" value="{{ $item->unit }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">SQM From</label>
                                    <input type="number" step="0.01" class="form-control height-35 f-14 item-sqm-from" name="sqm_from[]" value="{{ $item->sqm_from }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">SQM To</label>
                                    <input type="number" step="0.01" class="form-control height-35 f-14 item-sqm-to" name="sqm_to[]" value="{{ $item->sqm_to }}">
                                </div>
                            </div>
                            {{-- Without Price toggle --}}
                            @php $switchId = 'without_amount_' . $loop->index . '_' . now()->timestamp; @endphp
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Without Price</label>
                                    <div class="custom-control custom-switch pt-2">
                                        <input type="hidden" name="without_amount[]" value="{{ $isWithoutAmt ? '1' : '0' }}" class="item-without-amount-hidden">
                                        <input type="checkbox" class="custom-control-input item-without-amount-chk" id="{{ $switchId }}" {{ $isWithoutAmt ? 'checked' : '' }}>
                                        <label class="custom-control-label f-14 cursor-pointer" for="{{ $switchId }}"></label>
                                    </div>
                                </div>
                            </div>
                            {{-- Payment fields wrapper --}}
                            <div class="col-md-2 payment-fields-wrapper {{ $isWithoutAmt ? 'd-none' : '' }}">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Rate <sup class="f-14">*</sup></label>
                                    <input type="number" step="0.01" class="form-control height-35 f-14 item-rate" name="rate[]" value="{{ $item->rate }}">
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-center pt-3 payment-fields-wrapper {{ $isWithoutAmt ? 'd-none' : '' }}">
                                <div>
                                    <label class="f-14 text-dark-grey mb-12">Total</label>
                                    <p class="mb-0 f-14 font-weight-bold item-total-display">{{ number_format($item->total, 2) }}</p>
                                </div>
                            </div>
                            
                            {{-- Line break to prevent layout shift when payment fields are hidden --}}
                            <div class="w-100"></div>
                            
                            <div class="col-md-2 payment-fields-wrapper {{ $isWithoutAmt ? 'd-none' : '' }}">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Tax Name</label>
                                    <select class="form-control height-35 f-14 item-tax-name" name="tax_name[]">
                                        <option value="" @selected(empty($item->tax_name))>None</option>
                                        <option value="GST" @selected($item->tax_name == 'GST')>GST</option>
                                        <option value="SST" @selected($item->tax_name == 'SST')>SST</option>
                                        <option value="Other" @selected($item->tax_name == 'Other')>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 payment-fields-wrapper {{ $isWithoutAmt ? 'd-none' : '' }}">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Tax Mode</label>
                                    <select class="form-control height-35 f-14 item-tax-type" name="tax_type[]">
                                        <option value="exclusive" @selected($item->tax_type == 'exclusive')>Exclusive</option>
                                        <option value="amount" @selected($item->tax_type == 'amount')>Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2.5 payment-fields-wrapper {{ $isWithoutAmt ? 'd-none' : '' }}">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Tax Method</label>
                                    <select class="form-control height-35 f-14 item-tax-method" name="tax_method[]">
                                        <option value="percent" @selected($item->tax_method == 'percent')>Percentage</option>
                                        <option value="fixed" @selected($item->tax_method == 'fixed')>Fixed Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1.5 payment-fields-wrapper {{ $isWithoutAmt ? 'd-none' : '' }}">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Tax Value</label>
                                    <input type="number" step="0.01" class="form-control height-35 f-14 item-tax-pct" name="tax_percent[]" value="{{ $item->tax_percent }}">
                                </div>
                            </div>
                            {{-- Expected Completion --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Expected Completion</label>
                                    <input type="datetime-local" class="form-control height-35 f-14"
                                        name="item_completion_date_time[]"
                                        value="{{ $item->completion_date_time ? $item->completion_date_time->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>
                            <div class="col-md-1 text-right pt-4">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-wo-item mt-2"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-wo-item" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="fa fa-plus mr-1"></i> @lang('workorder::modules.workOrder.addItem')
                    </button>
                    @if(user()->permission('add_product') != 'none')
                        <button type="button" class="btn btn-outline-success btn-sm mt-2 ml-2" id="add-product-button">
                            <i class="fa fa-plus mr-1"></i> Add Product
                        </button>
                    @endif
                </div>

                {{-- ── SUMMARY ─────────────────────────────────────────────── --}}
                <div class="row p-20 border-top-grey">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-sm">
                            <tr><td class="text-right">Total SQM</td><td class="text-right font-weight-bold" id="wo-sqm-total">0.00</td></tr>
                            <tr><td class="text-right">Sub Total</td><td class="text-right font-weight-bold" id="wo-subtotal">{{ number_format($workOrder->sub_total, 2) }}</td></tr>
                            <tr>
                                <td class="text-right">
                                    Discount
                                    <select name="discount_type" class="form-control form-control-sm d-inline-block w-auto ml-1" id="discount-type">
                                        <option value="percent" @selected($workOrder->discount_type == 'percent')>%</option>
                                        <option value="fixed" @selected($workOrder->discount_type == 'fixed')>Fixed</option>
                                    </select>
                                </td>
                                <td class="text-right"><input type="number" step="0.01" name="discount" id="wo-discount" class="form-control form-control-sm text-right" value="{{ $workOrder->discount }}"></td>
                            </tr>
                            <tr><td class="text-right">Tax Amount</td><td class="text-right font-weight-bold" id="wo-tax-total">{{ number_format($workOrder->tax_amount, 2) }}</td></tr>
                            <tr class="border-top-grey"><td class="text-right f-16 font-weight-bold">Grand Total</td><td class="text-right f-16 font-weight-bold" id="wo-grand-total">{{ number_format($workOrder->grand_total, 2) }}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="row p-20 border-top-grey">
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="terms_conditions" :fieldLabel="__('workorder::modules.workOrder.termsConditions')"
                            fieldName="terms_conditions" :fieldValue="$workOrder->terms_conditions" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="special_instructions" :fieldLabel="__('workorder::modules.workOrder.specialInstructions')"
                            fieldName="special_instructions" :fieldValue="$workOrder->special_instructions" />
                    </div>
                </div>

                <div class="p-20 border-top-grey">
                    <x-forms.button-primary id="update-wo-btn" icon="check">@lang('app.update')</x-forms.button-primary>
                    <a href="{{ route('work-orders.index') }}" class="btn btn-secondary ml-2">@lang('app.cancel')</a>
                </div>
            </div>
        </x-form>
    </div>
</div>

<script>
$(document).ready(function () {
    $('.selectpicker').selectpicker();
    
    datepicker('#wo_date', { position: 'bl', ...datepickerConfig });

    // Generate product options
    var productOptions = `<option value="">-- Select Product --</option>`;
    @foreach($products as $product)
        productOptions += `<option value="{{ $product->id }}" data-rate="{{ $product->price }}" data-unit="{{ $product->unit ? $product->unit->unit_type : '' }}">{{ addslashes($product->name) }}</option>`;
    @endforeach

    // ── Item row HTML template (for dynamic cloning) ───────────────────────────
    function newItemRowHtml() {
        var rowId = 'without_amount_' + $('.item-row').length + '_' + Date.now();
        return `<div class="row p-10 item-row border-top-grey">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Product <sup>*</sup></label>
                    <select class="form-control height-35 f-14 selectpicker item-product" name="product_id[]" data-live-search="true" required>
                        ${productOptions}
                    </select>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Qty <sup>*</sup></label>
                    <input type="number" step="0.01" class="form-control height-35 f-14 item-qty" name="quantity[]" value="1" required>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Unit</label>
                    <input type="text" class="form-control height-35 f-14 item-unit" name="unit[]">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">SQM From</label>
                    <input type="number" step="0.01" class="form-control height-35 f-14 item-sqm-from" name="sqm_from[]" value="0">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">SQM To</label>
                    <input type="number" step="0.01" class="form-control height-35 f-14 item-sqm-to" name="sqm_to[]" value="0">
                </div>
            </div>
            {{-- Without Price toggle --}}
            <div class="col-md-2">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Without Price</label>
                    <div class="custom-control custom-switch pt-2">
                        <input type="hidden" name="without_amount[]" value="0" class="item-without-amount-hidden">
                        <input type="checkbox" class="custom-control-input item-without-amount-chk" id="${rowId}">
                        <label class="custom-control-label f-14 cursor-pointer" for="${rowId}"></label>
                    </div>
                </div>
            </div>
            <div class="col-md-2 payment-fields-wrapper">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Rate <sup>*</sup></label>
                    <input type="number" step="0.01" class="form-control height-35 f-14 item-rate" name="rate[]" value="0">
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-center pt-3 payment-fields-wrapper">
                <div>
                    <label class="f-14 text-dark-grey mb-12">Total</label>
                    <p class="mb-0 f-14 font-weight-bold item-total-display">0.00</p>
                </div>
            </div>
            
            <div class="w-100"></div>
            
            <div class="col-md-2 payment-fields-wrapper">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Tax Name</label>
                    <select class="form-control height-35 f-14 item-tax-name" name="tax_name[]">
                        <option value="">None</option>
                        <option value="GST">GST</option>
                        <option value="SST">SST</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2 payment-fields-wrapper">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Tax Mode</label>
                    <select class="form-control height-35 f-14 item-tax-type" name="tax_type[]">
                        <option value="exclusive">Exclusive</option>
                        <option value="amount">Amount</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2.5 payment-fields-wrapper">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Tax Method</label>
                    <select class="form-control height-35 f-14 item-tax-method" name="tax_method[]">
                        <option value="percent">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>
            </div>
            <div class="col-md-1.5 payment-fields-wrapper">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Tax Value</label>
                    <input type="number" step="0.01" class="form-control height-35 f-14 item-tax-pct" name="tax_percent[]" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12">Expected Completion</label>
                    <input type="datetime-local" class="form-control height-35 f-14" name="item_completion_date_time[]">
                </div>
            </div>
            <div class="col-md-1 text-right pt-4">
                <button type="button" class="btn btn-sm btn-outline-danger remove-wo-item mt-2"><i class="fa fa-times"></i></button>
            </div>
        </div>`;
    }

    $('#add-wo-item').click(function () {
        $('#wo-item-list').append(newItemRowHtml());
        $('select[name="product_id[]"]').last().selectpicker();
    });

    $('body').on('click', '.remove-wo-item', function () { $(this).closest('.item-row').remove(); recalculate(); });

    // ── Product change auto-population ─────────────────────────────────────────
    $('body').on('change', '.item-product', function () {
        var $row = $(this).closest('.item-row');
        var $selectedOption = $(this).find('option:selected');
        var rate = $selectedOption.data('rate') || 0;
        var unit = $selectedOption.data('unit') || '';
        
        $row.find('.item-rate').val(rate);
        $row.find('.item-unit').val(unit);
        recalculate();
    });

    // ── Without Amount toggle ──────────────────────────────────────────────────
    $('body').on('change', '.item-without-amount-chk', function () {
        var $row = $(this).closest('.item-row');
        var isChecked = $(this).is(':checked');
        $row.find('.item-without-amount-hidden').val(isChecked ? '1' : '0');
        $row.find('.payment-fields-wrapper').toggleClass('d-none', isChecked);
        if (isChecked) {
            $row.find('.item-rate').val(0);
            $row.find('.item-tax-pct').val(0);
            $row.find('.item-total-display').text('0.00');
        }
        recalculate();
    });

    $('body').on('input change', '.item-qty, .item-rate, .item-tax-pct, .item-tax-type, .item-tax-method, .item-sqm-from, .item-sqm-to, #wo-discount, #discount-type', recalculate);

    function recalculate() {
        var subTotal = 0, totalTax = 0, totalSqm = 0;
        $('.item-row').each(function () {
            var qty      = parseFloat($(this).find('.item-qty').val()) || 0;
            var sqmFrom  = parseFloat($(this).find('.item-sqm-from').val()) || 0;
            var sqmTo    = parseFloat($(this).find('.item-sqm-to').val()) || 0;
            var rowSqm   = Math.max(0, sqmTo - sqmFrom) * qty;
            totalSqm    += rowSqm;

            if ($(this).find('.item-without-amount-chk').is(':checked')) {
                $(this).find('.item-total-display').text('0.00');
                return;
            }
            var rate      = parseFloat($(this).find('.item-rate').val()) || 0;
            var taxPct    = parseFloat($(this).find('.item-tax-pct').val()) || 0;
            var taxType   = $(this).find('.item-tax-type').val();
            var taxMethod = $(this).find('.item-tax-method').val();
            var lineBase  = qty * rate;
            var taxAmt    = 0, lineTotal = 0;

            if (taxMethod === 'fixed') {
                taxAmt = taxPct;
            } else {
                taxAmt = lineBase * (taxPct / 100);
            }

            if (taxType === 'exclusive') {
                lineTotal = lineBase + taxAmt;
            } else {
                lineTotal = lineBase;
            }
            subTotal += lineBase;
            totalTax += taxAmt;
            $(this).find('.item-total-display').text(lineTotal.toFixed(2));
        });

        var discount     = parseFloat($('#wo-discount').val()) || 0;
        var discountAmt  = $('#discount-type').val() === 'percent' ? subTotal * (discount / 100) : discount;
        var grandTotal   = subTotal + totalTax - discountAmt;

        $('#wo-sqm-total').text(totalSqm.toFixed(2));
        $('#wo-subtotal').text(subTotal.toFixed(2));
        $('#wo-tax-total').text(totalTax.toFixed(2));
        $('#wo-grand-total').text(grandTotal.toFixed(2));
    }

    // ── Add Product modal flow ─────────────────────────────────────────────────
    $('#add-product-button').click(function() {
        var url = "{{ route('products.create') }}?redirect_url=no";
        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
        $.easyAjax({
            url: url,
            type: "GET",
            blockUI: true,
            success: function (response) {
                if (response.status === 'success') {
                    $(MODAL_XL + ' .modal-content').html(response.html);
                    $(MODAL_XL).modal({
                        show: true,
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            }
        });
    });

    window.getProductOptions = function(response) {
        $.easyAjax({
            url: "{{ route('products.options') }}",
            type: "GET",
            success: function (optionsResponse) {
                var options = '<option value="">-- Select Product --</option>' + optionsResponse.products;
                // Update all product select elements
                $('select[name="product_id[]"]').each(function() {
                    var currentVal = $(this).val();
                    $(this).html(options);
                    $(this).val(currentVal);
                });
                $('select[name="product_id[]"]').selectpicker('refresh');
                
                // Select the newly created product in the last select picker if empty
                if (response && response.productID) {
                    var lastSelect = $('select[name="product_id[]"]').last();
                    if (!lastSelect.val()) {
                        lastSelect.val(response.productID).selectpicker('refresh');
                        // Populate rate/unit for it
                        var $selectedOption = lastSelect.find('option:selected');
                        var rate = $selectedOption.data('rate') || 0;
                        var unit = $selectedOption.data('unit') || '';
                        var $row = lastSelect.closest('.item-row');
                        $row.find('.item-rate').val(rate);
                        $row.find('.item-unit').val(unit);
                    }
                }

                // Close XL modal
                $(MODAL_XL).modal('hide');
                
                // Update internal options variable for new rows
                productOptions = options;
                recalculate();
            }
        });
    }

    $('#update-wo-btn').click(function () {
        $.easyAjax({
            url: "{{ route('work-orders.update', $workOrder->id) }}",
            container: '#update-work-order-form',
            type: 'POST',
            disableButton: true,
            blockUI: true,
            buttonSelector: '#update-wo-btn',
            data: $('#update-work-order-form').serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    if ($(RIGHT_MODAL).hasClass('in')) {
                        document.getElementById('right-modal-content').innerHTML = '';
                        $(RIGHT_MODAL).modal('hide');
                    }
                    window.location.href = response.redirectUrl;
                }
            }
        });
    });

    // Run recalculation on page load to initialize Total SQM
    recalculate();

    init(RIGHT_MODAL);
});
</script>
