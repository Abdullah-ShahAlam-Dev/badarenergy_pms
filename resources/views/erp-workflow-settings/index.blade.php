@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 active erp-tab-btn" data-tab="general" href="javascript:;">General & Serials</a>
                            <a class="nav-item nav-link f-15 erp-tab-btn" data-tab="shipments" href="javascript:;">Shipments</a>
                            <a class="nav-item nav-link f-15 erp-tab-btn" data-tab="intakes" href="javascript:;">Stock Intakes</a>
                            <a class="nav-item nav-link f-15 erp-tab-btn" data-tab="barcodes" href="javascript:;">Barcodes & Labels</a>
                        </div>
                    </nav>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                <!-- GENERAL & SERIALS TAB -->
                <div class="row erp-tab-content" id="tab-general">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="stock_out_trigger" fieldLabel="Stock Out Trigger"
                                        fieldName="stock_out_trigger" popover="Determines when inventory is deducted. 'Invoice Approval' deducts stock when an invoice is approved. 'Delivery Order Dispatch' decouples inventory, only deducting stock upon actual dispatch of the Delivery Order.">
                            <option value="invoice_approval" @if ($stockOutTrigger === 'invoice_approval') selected @endif>Invoice Approval (Legacy)</option>
                            <option value="delivery_order_dispatch" @if ($stockOutTrigger === 'delivery_order_dispatch') selected @endif>Delivery Order Dispatch (Decoupled)</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="approvals_delivery_order" fieldLabel="Require Delivery Order Supervisor Approval"
                                        fieldName="approvals_delivery_order" popover="If enabled, Delivery Orders must be approved by a supervisor before they can be picked and dispatched.">
                            <option value="1" @if ($approvalsDeliveryOrder) selected @endif>Yes</option>
                            <option value="0" @if (!$approvalsDeliveryOrder) selected @endif>No</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="serial_prefix" fieldLabel="Serial Number Prefix"
                                      fieldName="serial_prefix" :fieldValue="$serialPrefix" required="true" popover="Prefix added to automatically generated product serial numbers (e.g., BE)." />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number fieldId="serial_digit_length" fieldLabel="Serial Number Digit Length"
                                        fieldName="serial_digit_length" :fieldValue="$serialDigitLength" required="true" min="3" max="15" popover="Number of sequential digits to generate for serial numbers (e.g., 6 digits outputs 000001)." />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="serial_include_year" fieldLabel="Include Year in Serial"
                                        fieldName="serial_include_year" popover="Append the last two digits of the current year to the serial number prefix (e.g., BE-26-000001).">
                            <option value="1" @if ($serialIncludeYear) selected @endif>Yes</option>
                            <option value="0" @if (!$serialIncludeYear) selected @endif>No</option>
                        </x-forms.select>
                    </div>
                </div>

                <!-- SHIPMENTS TAB -->
                <div class="row erp-tab-content d-none" id="tab-shipments">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="shipment_prefix" fieldLabel="Shipment Number Prefix"
                                      fieldName="shipment_prefix" :fieldValue="$shipmentPrefix" required="true" popover="Prefix added to automatically generated Shipment numbers (e.g., SHP)." />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number fieldId="shipment_digit_length" fieldLabel="Shipment Number Digit Length"
                                        fieldName="shipment_digit_length" :fieldValue="$shipmentDigitLength" required="true" min="3" max="15" popover="Number of sequential digits to generate for Shipment numbers." />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="shipment_include_year" fieldLabel="Include Year in Shipment Number"
                                        fieldName="shipment_include_year" popover="Append the last two digits of the current year to the Shipment number prefix (e.g., SHP-26-000001).">
                            <option value="1" @if ($shipmentIncludeYear) selected @endif>Yes</option>
                            <option value="0" @if (!$shipmentIncludeYear) selected @endif>No</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="allow_manual_shipment_number" fieldLabel="Allow Manual Shipment Number Entry"
                                        fieldName="allow_manual_shipment_number" popover="If enabled, administrators can manually override and type custom shipment numbers instead of using auto-generation.">
                            <option value="1" @if ($allowManualShipmentNumber) selected @endif>Yes</option>
                            <option value="0" @if (!$allowManualShipmentNumber) selected @endif>No</option>
                        </x-forms.select>
                    </div>
                </div>

                <!-- STOCK INTAKES TAB -->
                <div class="row erp-tab-content d-none" id="tab-intakes">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="intake_prefix" fieldLabel="Intake Voucher Number Prefix"
                                      fieldName="intake_prefix" :fieldValue="$intakePrefix" required="true" popover="Prefix added to automatically generated Stock Intake Voucher numbers (e.g., SIV)." />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number fieldId="intake_digit_length" fieldLabel="Intake Voucher Number Digit Length"
                                        fieldName="intake_digit_length" :fieldValue="$intakeDigitLength" required="true" min="3" max="15" popover="Number of sequential digits to generate for Stock Intake Voucher numbers." />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="intake_include_year" fieldLabel="Include Year in Intake Voucher Number"
                                        fieldName="intake_include_year" popover="Append the last two digits of the current year to the Stock Intake Voucher number prefix (e.g., SIV-26-000001).">
                            <option value="1" @if ($intakeIncludeYear) selected @endif>Yes</option>
                            <option value="0" @if (!$intakeIncludeYear) selected @endif>No</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="approvals_stock_intake" fieldLabel="Require Stock Intake Supervisor Approval"
                                        fieldName="approvals_stock_intake" popover="If enabled, newly received stock vouchers require manual supervisor approval before inventory counts and serials are posted.">
                            <option value="1" @if ($approvalsStockIntake) selected @endif>Yes</option>
                            <option value="0" @if (!$approvalsStockIntake) selected @endif>No</option>
                        </x-forms.select>
                    </div>
                </div>

                <!-- BARCODES TAB -->
                <div class="row erp-tab-content d-none" id="tab-barcodes">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="barcode_type" fieldLabel="Default Barcode Type"
                                         fieldName="barcode_type" popover="Format used for printable product barcode labels.">
                            <option value="code128" @if ($barcodeType === 'code128') selected @endif>Code 128 (Barcode)</option>
                            <option value="qrcode" @if ($barcodeType === 'qrcode') selected @endif>QR Code</option>
                            <option value="pdf417" @if ($barcodeType === 'pdf417') selected @endif>PDF417</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="barcode_terms_url" fieldLabel="Terms & Conditions URL (for QR Code)"
                                      fieldName="barcode_terms_url" :fieldValue="$barcodeTermsUrl" required="true" popover="The website URL encoded in the QR code displayed on printed barcode labels. Typically links to terms and conditions." />
                    </div>
                </div>
            </div>

            <x-slot name="action">
                <!-- Action buttons inside setting form -->
                <div class="d-flex justify-content-start border-top-grey p-4">
                    <x-forms.button-primary id="save-erp-settings" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                </div>
            </x-slot>
        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        // Tab switching handler
        $('body').on('click', '.erp-tab-btn', function (e) {
            e.preventDefault();
            $('.erp-tab-btn').removeClass('active');
            $(this).addClass('active');

            const tab = $(this).data('tab');
            $('.erp-tab-content').addClass('d-none');
            $('#tab-' + tab).removeClass('d-none');
        });

        // Form save handler
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
