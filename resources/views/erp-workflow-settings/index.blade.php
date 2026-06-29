@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header px-4 py-3 border-bottom-grey">
                    <h4 class="mb-0 f-21 font-weight-normal text-capitalize">ERP Workflow Settings</h4>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                <form id="editSettings" class="ajax-form">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="stock_out_trigger" fieldLabel="Stock Out Trigger"
                                            fieldName="stock_out_trigger">
                                <option value="invoice_approval" @if ($stockOutTrigger === 'invoice_approval') selected @endif>Invoice Approval (Legacy)</option>
                                <option value="delivery_order_dispatch" @if ($stockOutTrigger === 'delivery_order_dispatch') selected @endif>Delivery Order Dispatch (Decoupled)</option>
                            </x-forms.select>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.text fieldId="serial_prefix" fieldLabel="Serial Number Prefix"
                                          fieldName="serial_prefix" :fieldValue="$serialPrefix" required="true" />
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.number fieldId="serial_digit_length" fieldLabel="Serial Number Digit Length"
                                            fieldName="serial_digit_length" :fieldValue="$serialDigitLength" required="true" min="3" max="15" />
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="serial_include_year" fieldLabel="Include Year in Serial"
                                            fieldName="serial_include_year">
                                <option value="1" @if ($serialIncludeYear) selected @endif>Yes</option>
                                <option value="0" @if (!$serialIncludeYear) selected @endif>No</option>
                            </x-forms.select>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="approvals_delivery_order" fieldLabel="Require Delivery Order Supervisor Approval"
                                            fieldName="approvals_delivery_order">
                                <option value="1" @if ($approvalsDeliveryOrder) selected @endif>Yes</option>
                                <option value="0" @if (!$approvalsDeliveryOrder) selected @endif>No</option>
                            </x-forms.select>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="barcode_type" fieldLabel="Default Barcode Type"
                                            fieldName="barcode_type">
                                <option value="code128" @if ($barcodeType === 'code128') selected @endif>Code 128 (Barcode)</option>
                                <option value="qrcode" @if ($barcodeType === 'qrcode') selected @endif>QR Code</option>
                                <option value="pdf417" @if ($barcodeType === 'pdf417') selected @endif>PDF417</option>
                            </x-forms.select>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.text fieldId="shipment_prefix" fieldLabel="Shipment Number Prefix"
                                          fieldName="shipment_prefix" :fieldValue="$shipmentPrefix" required="true" />
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.number fieldId="shipment_digit_length" fieldLabel="Shipment Number Digit Length"
                                            fieldName="shipment_digit_length" :fieldValue="$shipmentDigitLength" required="true" min="3" max="15" />
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="shipment_include_year" fieldLabel="Include Year in Shipment Number"
                                            fieldName="shipment_include_year">
                                <option value="1" @if ($shipmentIncludeYear) selected @endif>Yes</option>
                                <option value="0" @if (!$shipmentIncludeYear) selected @endif>No</option>
                            </x-forms.select>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="allow_manual_shipment_number" fieldLabel="Allow Manual Shipment Number Entry"
                                            fieldName="allow_manual_shipment_number">
                                <option value="1" @if ($allowManualShipmentNumber) selected @endif>Yes</option>
                                <option value="0" @if (!$allowManualShipmentNumber) selected @endif>No</option>
                            </x-forms.select>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.text fieldId="intake_prefix" fieldLabel="Intake Voucher Number Prefix"
                                          fieldName="intake_prefix" :fieldValue="$intakePrefix" required="true" />
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.number fieldId="intake_digit_length" fieldLabel="Intake Voucher Number Digit Length"
                                            fieldName="intake_digit_length" :fieldValue="$intakeDigitLength" required="true" min="3" max="15" />
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="intake_include_year" fieldLabel="Include Year in Intake Voucher Number"
                                            fieldName="intake_include_year">
                                <option value="1" @if ($intakeIncludeYear) selected @endif>Yes</option>
                                <option value="0" @if (!$intakeIncludeYear) selected @endif>No</option>
                            </x-forms.select>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="approvals_stock_intake" fieldLabel="Require Stock Intake Supervisor Approval"
                                            fieldName="approvals_stock_intake">
                                <option value="1" @if ($approvalsStockIntake) selected @endif>Yes</option>
                                <option value="0" @if (!$approvalsStockIntake) selected @endif>No</option>
                            </x-forms.select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="w-100 border-top-grey set-btns">
                <x-setting-form-actions>
                    <x-forms.button-primary id="save-erp-settings" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                </x-setting-form-actions>
            </div>
        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        $('body').on('click', '#save-erp-settings', function () {
            const url = "{{ route('erp-workflow-settings.update', ['erp_workflow_setting' => 1]) }}";

            $.easyAjax({
                url: url,
                container: '#editSettings',
                type: "POST",
                disableButton: true,
                buttonSelector: "#save-erp-settings",
                data: $('#editSettings').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        // Standard refresh / success notification
                    }
                }
            });
        });
    </script>
@endpush
