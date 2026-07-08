<div class="row">
    <div class="col-sm-12">
        <x-form id="addShipmentForm">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    Add Shipment
                </h4>

                <div class="form-body">
                    <div class="row p-20">

                        <!-- Shipment Number -->
                        <div class="col-md-6">
                            @if ($allowManualShipmentNumber)
                                <x-forms.text fieldId="shipment_number" fieldLabel="Shipment Number"
                                    fieldName="shipment_number" fieldRequired="true"
                                    fieldPlaceholder="e.g. SH-2026-0001">
                                </x-forms.text>
                            @else
                                <x-forms.text fieldId="shipment_number" fieldLabel="Shipment Number (Auto Generated)"
                                    fieldName="shipment_number"
                                    :fieldValue="$autoShipmentNumber"
                                    readOnly="true">
                                </x-forms.text>
                            @endif
                        </div>

                        <!-- Container Number -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="container_number" fieldLabel="Container Number (Optional)"
                                fieldName="container_number"
                                fieldPlaceholder="e.g. CO-12345">
                            </x-forms.text>
                        </div>

                        <!-- Bill of Lading -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="bill_of_lading" fieldLabel="Bill of Lading (Optional)"
                                fieldName="bill_of_lading"
                                fieldPlaceholder="e.g. BL-98765">
                            </x-forms.text>
                        </div>

                        <!-- Manufacturing Reference -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="manufacturing_ref" fieldLabel="Manufacturing Reference (Optional)"
                                fieldName="manufacturing_ref"
                                fieldPlaceholder="e.g. MFR-CN-888">
                            </x-forms.text>
                        </div>

                        <!-- Port of Origin -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="port_of_origin" fieldLabel="Port of Origin"
                                fieldName="port_of_origin" fieldRequired="true"
                                fieldValue="China Port">
                            </x-forms.text>
                        </div>

                        <!-- Port of Discharge -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="port_of_discharge" fieldLabel="Port of Discharge"
                                fieldName="port_of_discharge" fieldRequired="true"
                                fieldValue="Karachi">
                            </x-forms.text>
                        </div>

                        <!-- ETA -->
                        <div class="col-md-6">
                            <x-forms.datepicker fieldId="eta" fieldRequired="true"
                                fieldLabel="ETA" fieldName="eta"
                                :fieldValue="\Carbon\Carbon::now(company()->timezone)->format(company()->date_format)"
                                fieldPlaceholder="Select Date" />
                        </div>

                        <!-- Arrival Date -->
                        <div class="col-md-6">
                            <x-forms.datepicker fieldId="arrival_date"
                                fieldLabel="Arrival Date" fieldName="arrival_date"
                                fieldValue=""
                                fieldPlaceholder="Select Date" />
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <x-forms.select fieldId="status" fieldLabel="Status"
                                fieldName="status" fieldRequired="true">
                                <option value="in_transit">In Transit</option>
                                <option value="port_customs">Port Customs</option>
                                <option value="warehouse_receiving">Warehouse Receiving</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </x-forms.select>
                        </div>

                        <!-- Remarks -->
                        <div class="col-lg-12 col-md-12">
                            <x-forms.textarea fieldId="remarks" fieldLabel="Remarks (Optional)"
                                fieldName="remarks"
                                fieldPlaceholder="Enter any shipping details or customs notes...">
                            </x-forms.textarea>
                        </div>

                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="saveShipment" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('shipments.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        init(RIGHT_MODAL);
    });

    $('#saveShipment').click(function() {
        var url  = "{{ route('shipments.store') }}";
        var data = $('#addShipmentForm').serialize();

        $.easyAjax({
            type: 'POST',
            url:  url,
            data: data,
            container: '#addShipmentForm',
            messagePosition: 'inline',
            disableButton: true,
            buttonSelector: "#saveShipment",
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl;
                }
            }
        });
    });
</script>
