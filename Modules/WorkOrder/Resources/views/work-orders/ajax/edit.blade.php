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
                        <x-forms.datepicker fieldId="delivery_date" :fieldLabel="__('workorder::modules.workOrder.deliveryDate')"
                            fieldName="delivery_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$workOrder->delivery_date ? $workOrder->delivery_date->format(company()->date_format) : ''" />
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
                        <div class="row p-10 item-row border-top-grey">
                            <div class="col-md-3"><div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">Item Name <sup>*</sup></label>
                                <input type="text" class="form-control height-35 f-14" name="item_name[]" value="{{ $item->item_name }}" required>
                            </div></div>
                            <div class="col-md-2"><div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">Qty <sup>*</sup></label>
                                <input type="number" step="0.01" class="form-control height-35 f-14 item-qty" name="quantity[]" value="{{ $item->quantity }}" required>
                            </div></div>
                            <div class="col-md-1"><div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">Unit</label>
                                <input type="text" class="form-control height-35 f-14" name="unit[]" value="{{ $item->unit }}">
                            </div></div>
                            <div class="col-md-2"><div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">Rate <sup>*</sup></label>
                                <input type="number" step="0.01" class="form-control height-35 f-14 item-rate" name="rate[]" value="{{ $item->rate }}" required>
                            </div></div>
                            <div class="col-md-2"><div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">Tax Type</label>
                                <select class="form-control height-35 f-14 item-tax-type" name="tax_type[]">
                                    <option value="exclusive" @selected($item->tax_type == 'exclusive')>Exclusive</option>
                                    <option value="inclusive" @selected($item->tax_type == 'inclusive')>Inclusive</option>
                                </select>
                            </div></div>
                            <div class="col-md-1"><div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">Tax %</label>
                                <input type="number" step="0.01" class="form-control height-35 f-14 item-tax-pct" name="tax_percent[]" value="{{ $item->tax_percent }}">
                            </div></div>
                            <div class="col-md-1 d-flex align-items-center pt-3">
                                <div>
                                    <label class="f-14 text-dark-grey mb-12">Total</label>
                                    <p class="mb-0 f-14 font-weight-bold item-total-display">{{ number_format($item->total, 2) }}</p>
                                </div>
                            </div>
                            <div class="col-12 text-right mt-1">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-wo-item"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-wo-item" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="fa fa-plus mr-1"></i> @lang('workorder::modules.workOrder.addItem')
                    </button>
                </div>

                {{-- ── SUMMARY ─────────────────────────────────────────────── --}}
                <div class="row p-20 border-top-grey">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-sm">
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
    datepicker('#wo_date',       { position: 'bl', ...datepickerConfig });
    datepicker('#delivery_date', { position: 'bl', ...datepickerConfig });

    $('#add-wo-item').click(function () {
        var html = `<div class="row p-10 item-row border-top-grey">
            <div class="col-md-3"><div class="form-group"><label class="f-14 text-dark-grey mb-12">Item Name <sup>*</sup></label>
                <input type="text" class="form-control height-35 f-14" name="item_name[]" required></div></div>
            <div class="col-md-2"><div class="form-group"><label class="f-14 text-dark-grey mb-12">Qty <sup>*</sup></label>
                <input type="number" step="0.01" class="form-control height-35 f-14 item-qty" name="quantity[]" value="1" required></div></div>
            <div class="col-md-1"><div class="form-group"><label class="f-14 text-dark-grey mb-12">Unit</label>
                <input type="text" class="form-control height-35 f-14" name="unit[]"></div></div>
            <div class="col-md-2"><div class="form-group"><label class="f-14 text-dark-grey mb-12">Rate <sup>*</sup></label>
                <input type="number" step="0.01" class="form-control height-35 f-14 item-rate" name="rate[]" value="0" required></div></div>
            <div class="col-md-2"><div class="form-group"><label class="f-14 text-dark-grey mb-12">Tax Type</label>
                <select class="form-control height-35 f-14 item-tax-type" name="tax_type[]">
                    <option value="exclusive">Exclusive</option><option value="inclusive">Inclusive</option>
                </select></div></div>
            <div class="col-md-1"><div class="form-group"><label class="f-14 text-dark-grey mb-12">Tax %</label>
                <input type="number" step="0.01" class="form-control height-35 f-14 item-tax-pct" name="tax_percent[]" value="0"></div></div>
            <div class="col-md-1 d-flex align-items-center pt-3"><div>
                <label class="f-14 text-dark-grey mb-12">Total</label>
                <p class="mb-0 f-14 font-weight-bold item-total-display">0.00</p></div></div>
            <div class="col-12 text-right mt-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-wo-item"><i class="fa fa-times"></i></button>
            </div></div>`;
        $('#wo-item-list').append(html);
    });

    $('body').on('click', '.remove-wo-item', function () { $(this).closest('.item-row').remove(); recalculate(); });
    $('body').on('input change', '.item-qty,.item-rate,.item-tax-pct,.item-tax-type,#wo-discount,#discount-type', recalculate);

    function recalculate() {
        var subTotal = 0, totalTax = 0;
        $('.item-row').each(function () {
            var qty = parseFloat($(this).find('.item-qty').val()) || 0;
            var rate = parseFloat($(this).find('.item-rate').val()) || 0;
            var taxPct = parseFloat($(this).find('.item-tax-pct').val()) || 0;
            var taxType = $(this).find('.item-tax-type').val();
            var lineBase = qty * rate;
            var taxAmt = taxType === 'exclusive' ? lineBase * (taxPct / 100) : lineBase - (lineBase / (1 + taxPct / 100));
            var lineTotal = taxType === 'exclusive' ? lineBase + taxAmt : lineBase;
            subTotal += lineBase; totalTax += taxAmt;
            $(this).find('.item-total-display').text(lineTotal.toFixed(2));
        });
        var discount = parseFloat($('#wo-discount').val()) || 0;
        var discountAmt = $('#discount-type').val() === 'percent' ? subTotal * (discount / 100) : discount;
        $('#wo-subtotal').text(subTotal.toFixed(2));
        $('#wo-tax-total').text(totalTax.toFixed(2));
        $('#wo-grand-total').text((subTotal + totalTax - discountAmt).toFixed(2));
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

    init(RIGHT_MODAL);
});
</script>
