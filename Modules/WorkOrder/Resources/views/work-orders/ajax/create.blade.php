<div class="row">
    <div class="col-sm-12">
        <x-form id="save-work-order-form" method="POST">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('workorder::modules.workOrder.createWorkOrder')
                </h4>

                {{-- ── BASIC INFORMATION ─────────────────────────────────────── --}}
                <div class="row p-20">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="wo_number" :fieldLabel="__('workorder::modules.workOrder.workOrderNo')"
                            fieldName="wo_number" :fieldValue="$nextWoNumber" fieldReadOnly="true" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="wo_date" :fieldLabel="__('workorder::modules.workOrder.woDate')"
                            fieldName="wo_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="now()->format(company()->date_format)" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="delivery_date" :fieldLabel="__('workorder::modules.workOrder.deliveryDate')"
                            fieldName="delivery_date" :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <x-forms.select fieldId="event_id" :fieldLabel="__('workorder::modules.workOrder.event')"
                            fieldName="event_id" fieldRequired="true" search="true">
                            <option value="">-- Select Event --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->event_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.select fieldId="vendor_id" :fieldLabel="__('workorder::modules.workOrder.vendor')"
                            fieldName="vendor_id" fieldRequired="true" search="true">
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }} ({{ $vendor->company_name }})</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="work_category" :fieldLabel="__('workorder::modules.workOrder.workCategory')"
                            fieldName="work_category" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="venue" :fieldLabel="__('workorder::modules.workOrder.venue')"
                            fieldName="venue" />
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <x-forms.number fieldId="no_of_days" :fieldLabel="__('workorder::modules.workOrder.noOfDays')"
                            fieldName="no_of_days" fieldValue="1" />
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <x-forms.select fieldId="priority" :fieldLabel="__('workorder::modules.workOrder.priority')"
                            fieldName="priority">
                            <option value="low">@lang('workorder::modules.workOrder.low')</option>
                            <option value="medium" selected>@lang('workorder::modules.workOrder.medium')</option>
                            <option value="high">@lang('workorder::modules.workOrder.high')</option>
                            <option value="urgent">@lang('workorder::modules.workOrder.urgent')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea fieldId="description" :fieldLabel="__('workorder::modules.workOrder.description')"
                            fieldName="description" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="remarks" :fieldLabel="__('workorder::modules.workOrder.remarks')"
                            fieldName="remarks" />
                    </div>
                </div>

                {{-- ── ITEMS ─────────────────────────────────────────────────── --}}
                <div class="border-top-grey p-20">
                    <h5 class="f-15 f-w-500 mb-3">Items / Services</h5>
                    <div id="wo-item-list">
                        {{-- First row always present --}}
                        <div class="row p-10 item-row border-top-grey">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">@lang('workorder::modules.workOrder.itemName') <sup class="f-14">*</sup></label>
                                    <input type="text" class="form-control height-35 f-14" name="item_name[]" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">@lang('workorder::modules.workOrder.quantity') <sup class="f-14">*</sup></label>
                                    <input type="number" step="0.01" class="form-control height-35 f-14 item-qty" name="quantity[]" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">@lang('workorder::modules.workOrder.unit')</label>
                                    <input type="text" class="form-control height-35 f-14" name="unit[]">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">@lang('workorder::modules.workOrder.rate') <sup class="f-14">*</sup></label>
                                    <input type="number" step="0.01" class="form-control height-35 f-14 item-rate" name="rate[]" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">@lang('workorder::modules.workOrder.taxType')</label>
                                    <select class="form-control height-35 f-14 item-tax-type" name="tax_type[]">
                                        <option value="exclusive">@lang('workorder::modules.workOrder.exclusive')</option>
                                        <option value="inclusive">@lang('workorder::modules.workOrder.inclusive')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">@lang('workorder::modules.workOrder.taxPercent')</label>
                                    <input type="number" step="0.01" class="form-control height-35 f-14 item-tax-pct" name="tax_percent[]" value="0">
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-center pt-3">
                                <div>
                                    <label class="f-14 text-dark-grey mb-12">Total</label>
                                    <p class="mb-0 f-14 font-weight-bold item-total-display">0.00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-wo-item" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="fa fa-plus mr-1"></i> @lang('workorder::modules.workOrder.addItem')
                    </button>
                </div>

                {{-- ── SUMMARY ───────────────────────────────────────────────── --}}
                <div class="row p-20 border-top-grey">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-sm">
                            <tr><td class="text-right">@lang('workorder::modules.workOrder.subTotal')</td><td class="text-right font-weight-bold" id="wo-subtotal">0.00</td></tr>
                            <tr>
                                <td class="text-right">
                                    @lang('workorder::modules.workOrder.discount')
                                    <select name="discount_type" class="form-control form-control-sm d-inline-block w-auto ml-1" id="discount-type">
                                        <option value="percent">%</option>
                                        <option value="fixed">Fixed</option>
                                    </select>
                                </td>
                                <td class="text-right"><input type="number" step="0.01" name="discount" id="wo-discount" class="form-control form-control-sm text-right" value="0"></td>
                            </tr>
                            <tr><td class="text-right">@lang('workorder::modules.workOrder.taxAmount')</td><td class="text-right font-weight-bold" id="wo-tax-total">0.00</td></tr>
                            <tr class="border-top-grey"><td class="text-right f-16 font-weight-bold">@lang('workorder::modules.workOrder.grandTotal')</td><td class="text-right f-16 font-weight-bold" id="wo-grand-total">0.00</td></tr>
                        </table>
                    </div>
                </div>

                {{-- ── TERMS & CONDITIONS ────────────────────────────────────── --}}
                <div class="row p-20 border-top-grey">
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="terms_conditions" :fieldLabel="__('workorder::modules.workOrder.termsConditions')"
                            fieldName="terms_conditions" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="special_instructions" :fieldLabel="__('workorder::modules.workOrder.specialInstructions')"
                            fieldName="special_instructions" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.checkbox fieldId="approval_required" :fieldLabel="__('workorder::modules.workOrder.approvalRequired')"
                            fieldName="approval_required" fieldValue="1" :checked="true" />
                    </div>
                </div>

                {{-- ── SUBMIT ────────────────────────────────────────────────── --}}
                <div class="p-20 border-top-grey">
                    <x-forms.button-primary id="save-wo-btn" icon="check">@lang('app.save')</x-forms.button-primary>
                    <a href="{{ route('work-orders.index') }}" class="btn btn-secondary ml-2">@lang('app.cancel')</a>
                </div>
            </div>
        </x-form>
    </div>
</div>

<script>
$(document).ready(function () {
    // ── Datepickers ────────────────────────────────────────────────────────────
    datepicker('#wo_date',       { position: 'bl', ...datepickerConfig });
    datepicker('#delivery_date', { position: 'bl', ...datepickerConfig });

    // ── Item row template ──────────────────────────────────────────────────────
    $('#add-wo-item').click(function () {
        var html = `<div class="row p-10 item-row border-top-grey">
            <div class="col-md-3"><div class="form-group">
                <label class="f-14 text-dark-grey mb-12">Item Name <sup>*</sup></label>
                <input type="text" class="form-control height-35 f-14" name="item_name[]" required>
            </div></div>
            <div class="col-md-2"><div class="form-group">
                <label class="f-14 text-dark-grey mb-12">Qty <sup>*</sup></label>
                <input type="number" step="0.01" class="form-control height-35 f-14 item-qty" name="quantity[]" value="1" required>
            </div></div>
            <div class="col-md-1"><div class="form-group">
                <label class="f-14 text-dark-grey mb-12">Unit</label>
                <input type="text" class="form-control height-35 f-14" name="unit[]">
            </div></div>
            <div class="col-md-2"><div class="form-group">
                <label class="f-14 text-dark-grey mb-12">Rate <sup>*</sup></label>
                <input type="number" step="0.01" class="form-control height-35 f-14 item-rate" name="rate[]" value="0" required>
            </div></div>
            <div class="col-md-2"><div class="form-group">
                <label class="f-14 text-dark-grey mb-12">Tax Type</label>
                <select class="form-control height-35 f-14 item-tax-type" name="tax_type[]">
                    <option value="exclusive">Exclusive</option>
                    <option value="inclusive">Inclusive</option>
                </select>
            </div></div>
            <div class="col-md-1"><div class="form-group">
                <label class="f-14 text-dark-grey mb-12">Tax %</label>
                <input type="number" step="0.01" class="form-control height-35 f-14 item-tax-pct" name="tax_percent[]" value="0">
            </div></div>
            <div class="col-md-1 d-flex align-items-center pt-3">
                <div>
                    <label class="f-14 text-dark-grey mb-12">Total</label>
                    <p class="mb-0 f-14 font-weight-bold item-total-display">0.00</p>
                </div>
            </div>
            <div class="col-12 text-right mt-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-wo-item"><i class="fa fa-times"></i></button>
            </div>
        </div>`;
        $('#wo-item-list').append(html);
    });

    $('body').on('click', '.remove-wo-item', function () {
        $(this).closest('.item-row').remove();
        recalculate();
    });

    // ── Real-time calculation ──────────────────────────────────────────────────
    $('body').on('input change', '.item-qty, .item-rate, .item-tax-pct, .item-tax-type', function () {
        recalculate();
    });
    $('body').on('input change', '#wo-discount, #discount-type', function () {
        recalculate();
    });

    function recalculate() {
        var subTotal = 0, totalTax = 0;
        $('.item-row').each(function () {
            var qty      = parseFloat($(this).find('.item-qty').val()) || 0;
            var rate     = parseFloat($(this).find('.item-rate').val()) || 0;
            var taxPct   = parseFloat($(this).find('.item-tax-pct').val()) || 0;
            var taxType  = $(this).find('.item-tax-type').val();
            var lineBase = qty * rate;
            var taxAmt   = 0, lineTotal = 0;

            if (taxType === 'exclusive') {
                taxAmt    = lineBase * (taxPct / 100);
                lineTotal = lineBase + taxAmt;
            } else {
                taxAmt    = lineBase - (lineBase / (1 + taxPct / 100));
                lineTotal = lineBase;
            }
            subTotal += lineBase;
            totalTax += taxAmt;
            $(this).find('.item-total-display').text(lineTotal.toFixed(2));
        });

        var discount     = parseFloat($('#wo-discount').val()) || 0;
        var discountType = $('#discount-type').val();
        var discountAmt  = discountType === 'percent' ? subTotal * (discount / 100) : discount;
        var grandTotal   = subTotal + totalTax - discountAmt;

        $('#wo-subtotal').text(subTotal.toFixed(2));
        $('#wo-tax-total').text(totalTax.toFixed(2));
        $('#wo-grand-total').text(grandTotal.toFixed(2));
    }

    // ── Submit ─────────────────────────────────────────────────────────────────
    $('#save-wo-btn').click(function () {
        $.easyAjax({
            url: "{{ route('work-orders.store') }}",
            container: '#save-work-order-form',
            type: 'POST',
            disableButton: true,
            blockUI: true,
            buttonSelector: '#save-wo-btn',
            data: $('#save-work-order-form').serialize(),
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
