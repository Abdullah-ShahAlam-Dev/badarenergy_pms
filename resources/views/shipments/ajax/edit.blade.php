<div class="row">
    <div class="col-sm-12">
        <x-form id="editShipmentForm">
            @method('PUT')
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    Edit Shipment - {{ $shipment->shipment_number }}
                </h4>

                <div class="form-body">
                    <div class="row p-20">

                        <!-- Shipment Number -->
                        <div class="col-md-6">
                            @if ($allowManualShipmentNumber)
                                <x-forms.text fieldId="shipment_number" fieldLabel="Shipment Number"
                                    fieldName="shipment_number" fieldRequired="true"
                                    :fieldValue="$shipment->shipment_number"
                                    fieldPlaceholder="e.g. SH-2026-0001">
                                </x-forms.text>
                            @else
                                <x-forms.text fieldId="shipment_number" fieldLabel="Shipment Number (Auto Generated)"
                                    fieldName="shipment_number"
                                    :fieldValue="$shipment->shipment_number"
                                    readOnly="true">
                                </x-forms.text>
                            @endif
                        </div>

                        <!-- Container Number -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="container_number" fieldLabel="Container Number (Optional)"
                                fieldName="container_number"
                                :fieldValue="$shipment->container_number"
                                fieldPlaceholder="e.g. CO-12345">
                            </x-forms.text>
                        </div>

                        <!-- Bill of Lading -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="bill_of_lading" fieldLabel="Bill of Lading (Optional)"
                                fieldName="bill_of_lading"
                                :fieldValue="$shipment->bill_of_lading"
                                fieldPlaceholder="e.g. BL-98765">
                            </x-forms.text>
                        </div>

                        <!-- Manufacturing Reference -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="manufacturing_ref" fieldLabel="Manufacturing Reference (Optional)"
                                fieldName="manufacturing_ref"
                                :fieldValue="$shipment->manufacturing_ref"
                                fieldPlaceholder="e.g. MFR-CN-888">
                            </x-forms.text>
                        </div>

                        <!-- Port of Origin -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="port_of_origin" fieldLabel="Port of Origin"
                                fieldName="port_of_origin" fieldRequired="true"
                                :fieldValue="$shipment->port_of_origin">
                            </x-forms.text>
                        </div>

                        <!-- Port of Discharge -->
                        <div class="col-md-6">
                            <x-forms.text fieldId="port_of_discharge" fieldLabel="Port of Discharge"
                                fieldName="port_of_discharge" fieldRequired="true"
                                :fieldValue="$shipment->port_of_discharge">
                            </x-forms.text>
                        </div>

                        <!-- ETA -->
                        <div class="col-md-6">
                            <x-forms.datepicker fieldId="eta" fieldRequired="true"
                                fieldLabel="ETA" fieldName="eta"
                                :fieldValue="$shipment->eta ? $shipment->eta->format(company()->date_format) : ''"
                                fieldPlaceholder="Select Date" />
                        </div>

                        <!-- Arrival Date -->
                        <div class="col-md-6">
                            <x-forms.datepicker fieldId="arrival_date"
                                fieldLabel="Arrival Date" fieldName="arrival_date"
                                :fieldValue="$shipment->arrival_date ? $shipment->arrival_date->format(company()->date_format) : ''"
                                fieldPlaceholder="Select Date" />
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <x-forms.select fieldId="status" fieldLabel="Status"
                                fieldName="status" fieldRequired="true">
                                <option value="in_transit" @if($shipment->status === 'in_transit') selected @endif>In Transit</option>
                                <option value="port_customs" @if($shipment->status === 'port_customs') selected @endif>Port Customs</option>
                                <option value="warehouse_receiving" @if($shipment->status === 'warehouse_receiving') selected @endif>Warehouse Receiving</option>
                                <option value="completed" @if($shipment->status === 'completed') selected @endif>Completed</option>
                                <option value="cancelled" @if($shipment->status === 'cancelled') selected @endif>Cancelled</option>
                            </x-forms.select>
                        </div>

                        <!-- Remarks -->
                        <div class="col-lg-12 col-md-12">
                            <x-forms.textarea fieldId="remarks" fieldLabel="Remarks (Optional)"
                                fieldName="remarks"
                                :fieldValue="$shipment->remarks"
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
        var url  = "{{ route('shipments.update', [$shipment->id]) }}";
        var data = $('#editShipmentForm').serialize();

        $.easyAjax({
            type: 'POST',
            url:  url,
            data: data,
            container: '#editShipmentForm',
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
