<div class="row">
    <div class="col-sm-12">
        <x-form id="save-payment-data-form">
            @method('PUT')
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.payments.paymentDetails')</h4>
                <div class="row p-20">

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="client_id" :fieldLabel="'Dealer / Client'"
                            fieldName="client_id" search="true" fieldRequired="true">
                            <option value="">--</option>
                            @foreach ($clients as $c)
                                <option value="{{ $c->id }}" @if(($payment->customer_id ?? ($payment->invoice ? $payment->invoice->client_id : null)) == $c->id) selected @endif>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="salesperson_id" :fieldLabel="'Salesperson'"
                            fieldName="salesperson_id" search="true" fieldRequired="true">
                            <option value="">--</option>
                            @foreach ($salespersons as $s)
                                <option value="{{ $s->id }}" @if($payment->salesperson_id == $s->id) selected @endif>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="payment_invoice_id" :fieldLabel="__('app.invoice')"
                            fieldName="invoice_id" search="true">
                            <option value="">-- Overall Ledger Balance (Balance-wise) --</option>
                            @foreach ($invoices as $inv)
                                @php
                                    $paidAmount = $inv->amountPaid();
                                    $currentPaid = ($payment->invoice_id == $inv->id) ? $payment->amount : 0;
                                    $due = max($inv->total - ($paidAmount - $currentPaid), 0);
                                @endphp
                                <option data-currency-code="{{$inv->currency->currency_code}}" data-currency-id="{{ $inv->currency_id }}"
                                    @if ($payment->invoice_id == $inv->id) selected @endif
                                    data-content="{{ $inv->invoice_number . ' - ' . __('app.total') . ': <span class=\'text-dark f-w-500 mr-2\'>' . currency_format($inv->total, $inv->currency->id) . ' </span>' . __('modules.invoices.due') . ': <span class=\'text-red\'>' . currency_format($due, $inv->currency->id) . '</span>' }}"
                                    value="{{ $inv->id }}">{{ $inv->invoice_number }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.datepicker fieldId="paid_on" :fieldLabel="__('modules.payments.paidOn')"
                            fieldName="paid_on" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="($payment->paid_on ? $payment->paid_on->format(company()->date_format) : '')" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.number fieldId="amount" :fieldLabel="__('modules.invoices.amount')" fieldName="amount"
                            :fieldValue="$payment->amount" :fieldPlaceholder="__('placeholders.price')"
                            fieldRequired="true" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <input type="hidden" id="currency_id" name="currency_id" value="{{$payment->currency_id}}">
                        <x-forms.select fieldId="currency" :fieldLabel="__('app.currency')" fieldName="currency"
                            search="true">
                            @foreach ($currencies as $currency)
                                <option @if ($currency->id == $payment->currency_id) selected @endif value="{{ $currency->id }}" data-currency-code="{{ $currency->currency_code }}">
                                    {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="exchange_rate" :fieldLabel="__('modules.currencySettings.exchangeRate')"
                        fieldName="exchange_rate" fieldRequired="true" :fieldValue="$payment->exchange_rate" :fieldReadOnly="($companyCurrency->id == $payment->currency_id)"
                        :fieldHelp="( company()->currency->currency_code.' '.__('app.to').' '.$payment->currency->currency_code )" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="transaction_id" :fieldLabel="__('modules.payments.transactionId')"
                            fieldName="transaction_id" :fieldPlaceholder="__('placeholders.payments.transactionId')"
                            :fieldValue="$payment->transaction_id" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="payment_gateway_id" :fieldLabel="__('modules.payments.paymentGateway')" fieldName="gateway"
                        search="true">
                            <option value="">--</option>
                            <option @if ($payment->gateway == 'Offline') selected @endif value="Offline">{{ __('modules.offlinePayment.offlinePayment') }}</option>
                            @if ($paymentGateway->paypal_status == 'active')
                                <option @if ($payment->gateway == 'paypal') selected @endif value="paypal">{{ __('app.paypal') }}</option>
                            @endif
                            @if ($paymentGateway->stripe_status == 'active')
                                <option @if ($payment->gateway == 'stripe') selected @endif value="stripe">{{ __('app.stripe') }}</option>
                            @endif
                            @if ($paymentGateway->razorpay_status == 'active')
                                <option @if ($payment->gateway == 'stripe') selected @endif @if ($payment->gateway == 'paypal') selected @endif value="razorpay">{{ __('app.razorpay') }}</option>
                            @endif
                            @if ($paymentGateway->paystack_status == 'active')
                                <option @if ($payment->gateway == 'paystack') selected @endif value="paystack">{{ __('app.paystack') }}</option>
                            @endif
                            @if ($paymentGateway->mollie_status == 'active')
                                <option @if ($payment->gateway == 'mollie') selected @endif value="mollie">{{ __('app.mollie') }}</option>
                            @endif
                            @if ($paymentGateway->payfast_status == 'active')
                                <option @if ($payment->gateway == 'payfast') selected @endif value="payfast">{{ __('app.payfast') }}</option>
                            @endif
                            @if ($paymentGateway->authorize_status == 'active')
                                <option @if ($payment->gateway == 'authorize') selected @endif value="authorize">{{ __('app.authorize') }}</option>
                            @endif
                            @if ($paymentGateway->square_status == 'active')
                                <option @if ($payment->gateway == 'square') selected @endif value="square">{{ __('app.square') }}</option>
                            @endif
                            @if ($paymentGateway->flutterwave_status == 'active')
                                <option @if ($payment->gateway == 'flutterwave') selected @endif value="flutterwave">{{ __('app.flutterwave') }}</option>
                            @endif
                        </x-forms.select>
                    </div>

                    @if ($payment->gateway=='Offline')
                    <div class="col-md-3" id = "add_offline">
                        <x-forms.select fieldId="add_offline_methods" :fieldLabel="__('modules.payments.offlinePaymentMethod')" fieldName="offline_methods"
                        search="true">
                        @foreach ($methods as $method)
                        @if($method->status == 'yes')
                        <option @if($payment->offline_method_id == $method->id) selected @endif value={{$method->id}}>{{$method->name}}</option>
                        @endif
                        @endforeach
                        </x-forms.select>
                    </div>
                    @endif

                    <div class="col-md-3 d-none" id = "add_offline">
                        <x-forms.select fieldId="add_offline_methods" :fieldLabel="__('modules.payments.offlinePaymentMethod')" fieldName="offline_methods"
                        search="true">
                        </x-forms.select>
                    </div>

                    @if($linkPaymentPermission == 'all')
                        <div class="col-md-3">
                            <x-forms.select fieldId="bank_account_id" :fieldLabel="__('app.menu.bankaccount')" fieldName="bank_account_id"
                                search="true">
                                <option value="">--</option>
                                @if($viewBankAccountPermission != 'none')
                                    @foreach ($bankDetails as $bankDetail)
                                        <option value="{{ $bankDetail->id }}" @if($bankDetail->id == $payment->bank_account_id) selected @endif>@if($bankDetail->type == 'bank')
                                            {{ $bankDetail->bank_name }} | @endif
                                            {{ mb_ucwords($bankDetail->account_name) }}
                                        </option>
                                    @endforeach
                                @endif
                            </x-forms.select>
                        </div>
                    @endif

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-dark-green f-10'></i> {{ __('app.complete') }}"
                                @if ($payment->status == 'complete') selected
                                @endif
                                value="complete">@lang('app.complete')</option>
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-yellow f-10'></i> {{ __('app.pending') }}"
                                @if ($payment->status == 'pending') selected
                                @endif
                                value="pending">@lang('app.pending')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-12">
                        <x-forms.file allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.receipt')" fieldName="bill"
                            fieldId="bill" :fieldValue="($payment->bill ? $payment->file_url : '')" :popover="__('messages.fileFormat.multipleImageFile')" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.remark')"
                                fieldName="remarks" fieldId="remarks" :fieldValue="$payment->remarks"
                                :fieldPlaceholder="__('placeholders.payments.remark')" />
                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-payment-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('payments.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        datepicker('#paid_on', {
            position: 'bl',
            dateSelected: new Date("{{ $payment->paid_on ? str_replace('-', '/', $payment->paid_on) : str_replace('-', '/', now()) }}"),
            ...datepickerConfig
        });

        $('#payment_invoice_id').change(function() {
            var companyCurrency = '{{ $companyCurrency->id }}';
            var companyCurrencyName = "{{$companyCurrency->currency_code}}";

            if ($('#payment_invoice_id').val() != '') {

                var curId = $('#payment_invoice_id option:selected').attr('data-currency-id');
                var currentCurrencyName = $('#payment_invoice_id option:selected').attr('data-currency-code');
                $('#currency').removeAttr('disabled');
                $('#currency').selectpicker('refresh');
                $('#currency').val(curId);
                $('#currency_id').val(curId);
                $('#currency').prop('disabled', true);
                $('#currency').selectpicker('refresh');
            } else {
                $('#currency').prop('disabled', false);
                $('#currency').selectpicker('refresh');
                var currentCurrencyName = $('#currency option:selected').attr('data-currency-code');
            }

            $('#exchange_rateHelp').html('( '+companyCurrencyName+' @lang('app.to') '+currentCurrencyName+' )');

            var url = "{{ route('payments.account_list') }}";
            var curId = $('#currency_id').val();
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#save-payment-data-form',
                type: "GET",
                blockUI: true,
                data: {
                    _token: token,
                    'curId' :curId
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#bank_account_id').html(response.data);
                        $('#bank_account_id').selectpicker('refresh');
                        $('#exchange_rate').val(response.exchangeRate);
                        if(curId != undefined && curId != companyCurrency){
                            $('#exchange_rate').prop('readonly', false);
                        } else {
                            $('#exchange_rate').prop('readonly', true);
                        }
                    }
                }
            });
        });

        $('#save-payment-form').click(function() {
            const url = "{{ route('payments.update', $payment->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-payment-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-payment-form",
                file: true,
                data: $('#save-payment-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $('#client_id').change(function() {
            var id = $(this).val();
            if (id == '') {
                $('#payment_invoice_id').html('<option value="">-- Overall Ledger Balance (Balance-wise) --</option>');
                $('#payment_invoice_id').selectpicker('refresh');
                return;
            }

            var url = "{{ route('payments.client_invoices', ':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                type: "GET",
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        $('#payment_invoice_id').html(response.data);
                        $('#payment_invoice_id').selectpicker('refresh');
                    }
                }
            });
        });

        $('#currency').change(function() {
            var curId = $(this).val();
            $('#currency_id').val(curId);

            var companyCurrencyName = "{{$companyCurrency->currency_code}}";
            var currentCurrencyName = $('#currency option:selected').attr('data-currency-code');
            var companyCurrency = '{{ $companyCurrency->id }}';

            if(curId == companyCurrency){
                $('#exchange_rate').prop('readonly', true);
            } else{
                $('#exchange_rate').prop('readonly', false);
            }

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: "{{ route('payments.account_list') }}",
                type: "GET",
                blockUI: true,
                data: { 'curId' : curId , _token: token},
                success: function(response) {
                    if (response.status == 'success') {
                        $('#bank_account_id').html(response.data);
                        $('#bank_account_id').selectpicker('refresh');
                        $('#exchange_rate').val(response.exchangeRate);
                        $('#exchange_rateHelp').html('( '+companyCurrencyName+' @lang('app.to') '+currentCurrencyName+' )');
                    }
                }
            });
        });


        $('#payment_gateway_id').on('change', function() {
            var val = $(this).val();
            if (val == 'Offline'){
                var url = "{{ route('offline.methods') }}"
                $.easyAjax({
                url : url,
                type : "GET",
                success: function (response) {
                    if (response.status == 'success'){
                        $('#add_offline').removeClass('d-none');
                        var options = [];
                        var rData = [];
                        rData = response.data;
                            $.each(rData, function (index, value) {
                            var selectData = '';
                            if(value.status=='yes'){
                            selectData = '<option value="' + value.id + '">' + value.name + '</option>';
                            }
                            options.push(selectData);
                        });
                        $('#add_offline_methods').html(
                            options);
                        $('#add_offline_methods').selectpicker('refresh');
                    }

                }

                });

            }
            else
            {
                $('#add_offline').addClass('d-none');
            }
        });

        function ucWord(str){
            str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                return letter.toUpperCase();
            });
            return str;
        }


        init(RIGHT_MODAL);
    });
</script>
