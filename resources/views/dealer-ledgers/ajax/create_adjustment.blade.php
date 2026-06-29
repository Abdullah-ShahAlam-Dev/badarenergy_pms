<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">Post Adjustment Voucher</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="post-adjustment-form">
        <div class="row">
            <!-- Dealer Selection -->
            <div class="col-md-6">
                <div class="form-group my-3">
                    <x-forms.label fieldId="dealer_id" fieldRequired="true" fieldLabel="Select Dealer"></x-forms.label>
                    <select class="form-control select-picker" name="dealer_id" id="dealer_id" data-live-search="true" data-size="8">
                        @foreach ($dealers as $dealer)
                            <option value="{{ $dealer->id }}">{{ $dealer->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Voucher Type -->
            <div class="col-md-6">
                <div class="form-group my-3">
                    <x-forms.label fieldId="type" fieldRequired="true" fieldLabel="Voucher Type"></x-forms.label>
                    <select class="form-control select-picker" name="type" id="type">
                        <option value="adjustment">Adjustment Voucher</option>
                        <option value="opening_balance">Opening Balance Setup</option>
                        <option value="write_off">Write-Off (Bad Debt)</option>
                    </select>
                </div>
            </div>

            <!-- Entry Type (Debit/Credit) -->
            <div class="col-md-6">
                <div class="form-group my-3">
                    <x-forms.label fieldId="entry_type" fieldRequired="true" fieldLabel="Transaction Direction"></x-forms.label>
                    <select class="form-control select-picker" name="entry_type" id="entry_type">
                        <option value="debit">Debit (+ Increase Outstanding)</option>
                        <option value="credit">Credit (- Decrease Outstanding)</option>
                    </select>
                </div>
            </div>

            <!-- Date -->
            <div class="col-md-6">
                <div class="form-group my-3">
                    <x-forms.text fieldId="adjustment_date" fieldLabel="Date" fieldRequired="true" 
                                  fieldName="date" placeholder="Select Date" autocomplete="off" />
                </div>
            </div>

            <!-- Amount -->
            <div class="col-md-6">
                <div class="form-group my-3">
                    <x-forms.number fieldName="amount" fieldId="amount" fieldLabel="Amount (PKR)" fieldRequired="true" min="0.01" step="0.01" />
                </div>
            </div>

            <!-- Remarks -->
            <div class="col-md-12">
                <div class="form-group my-3">
                    <x-forms.textarea fieldLabel="Remarks / Description" fieldName="remarks" fieldId="remarks" />
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">Cancel</x-forms.button-cancel>
    <x-forms.button-primary id="btn-save-adjustment" icon="check">Post Voucher</x-forms.button-primary>
</div>

<script>
    $(document).ready(function() {
        $(".select-picker").selectpicker();

        datepicker('#adjustment_date', {
            position: 'bl',
            formatter: (input, date, instance) => {
                input.value = moment(date).format('{{ company()->date_format_js }}');
            }
        });

        // Set default date
        $('#adjustment_date').val(moment().format('{{ company()->date_format_js }}'));

        $('#btn-save-adjustment').click(function() {
            const url = "{{ route('ledgers.store_adjustment') }}";
            $.easyAjax({
                url: url,
                container: '#post-adjustment-form',
                type: "POST",
                blockUI: true,
                data: $('#post-adjustment-form').serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(MODAL_LG).modal('hide');
                        if (typeof window.LaravelDataTables !== 'undefined' && window.LaravelDataTables["dealer-ledger-summary-table"]) {
                            window.LaravelDataTables["dealer-ledger-summary-table"].draw();
                        } else {
                            window.location.reload();
                        }
                    }
                }
            });
        });
    });
</script>
