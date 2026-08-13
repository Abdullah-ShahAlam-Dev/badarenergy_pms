@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <x-form id="saveStockIntakeForm">
                    <div class="add-client bg-white rounded">
                        <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                            Create Stock Intake Voucher
                        </h4>

                        <div class="form-body">
                            <div class="row p-20">

                                <!-- Shipment Selector -->
                                <div class="col-md-4">
                                    <x-forms.select fieldId="shipment_id" fieldLabel="Select Shipment (Optional)"
                                        fieldName="shipment_id" search="true">
                                        <option value="">-- No Linked Shipment --</option>
                                        <option value="create_new">+ Create New Shipment In This Intake</option>
                                        @foreach ($shipments as $shp)
                                            <option value="{{ $shp->id }}">
                                                {{ $shp->shipment_number }} (BL: {{ $shp->bill_of_lading ?: 'N/A' }}, Container: {{ $shp->container_number ?: 'N/A' }})
                                            </option>
                                        @endforeach
                                    </x-forms.select>
                                </div>

                                <!-- Warehouse Selector -->
                                <div class="col-md-4">
                                    <x-forms.select fieldId="warehouse_id" fieldLabel="Select Destination Warehouse"
                                        fieldName="warehouse_id" fieldRequired="true" search="true">
                                        @foreach ($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </x-forms.select>
                                </div>

                                <!-- Intake Date -->
                                <div class="col-md-4">
                                    <x-forms.datepicker fieldId="intake_date" fieldRequired="true"
                                        fieldLabel="Intake Date" fieldName="intake_date"
                                        :fieldValue="\Carbon\Carbon::now(company()->timezone)->format(company()->date_format)"
                                        fieldPlaceholder="Select Date" />
                                </div>

                                <!-- Intake Type -->
                                <div class="col-md-4">
                                    <x-forms.select fieldId="intake_type" fieldLabel="Intake Type"
                                        fieldName="intake_type" fieldRequired="true">
                                        <option value="direct">Direct Intake (Bane-Banaye / Ready-Made)</option>
                                        <option value="assembly">Assembled Product (Assembly Line Output)</option>
                                    </x-forms.select>
                                </div>

                                <!-- INLINE NEW SHIPMENT FORM CARD (COLLAPSIBLE) -->
                                <div class="col-md-12 d-none mt-2 mb-3" id="new_shipment_card">
                                    <div class="card bg-light border border-primary rounded p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="f-15 font-weight-bold text-primary mb-0">
                                                <i class="fa fa-ship mr-1"></i> Inline New Shipment Details
                                            </h5>
                                            <span class="badge badge-primary">Auto Generated Shipment Number</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <x-forms.text fieldId="container_number" fieldLabel="Container #" fieldName="container_number" fieldPlaceholder="e.g. MSKU-998811" />
                                            </div>
                                            <div class="col-md-3">
                                                <x-forms.text fieldId="bill_of_lading" fieldLabel="Bill of Lading (BL #)" fieldName="bill_of_lading" fieldPlaceholder="e.g. BL-554433" />
                                            </div>
                                            <div class="col-md-3">
                                                <x-forms.text fieldId="manufacturing_ref" fieldLabel="OEM Batch / Ref #" fieldName="manufacturing_ref" fieldPlaceholder="e.g. BATCH-2026-01" />
                                            </div>
                                            <div class="col-md-3">
                                                <x-forms.select fieldId="shipment_status" fieldLabel="Shipment Status" fieldName="shipment_status">
                                                    <option value="arrived" selected>Arrived</option>
                                                    <option value="in_transit">In Transit</option>
                                                    <option value="customs_clearance">Customs Clearance</option>
                                                    <option value="dispatched">Dispatched</option>
                                                </x-forms.select>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <x-forms.text fieldId="port_of_origin" fieldLabel="Port of Origin" fieldName="port_of_origin" fieldPlaceholder="e.g. Ningbo, China" />
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <x-forms.text fieldId="port_of_discharge" fieldLabel="Port of Discharge" fieldName="port_of_discharge" fieldPlaceholder="e.g. Karachi, Pakistan" />
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <x-forms.datepicker fieldId="eta" fieldLabel="Estimated Arrival (ETA)" fieldName="eta" fieldPlaceholder="Select Date" />
                                            </div>
                                            <div class="col-md-12 mt-2">
                                                <x-forms.textarea fieldId="shipment_remarks" fieldLabel="Shipment Remarks" fieldName="shipment_remarks" fieldPlaceholder="Enter any remarks regarding shipping line, supplier..." />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Remarks -->
                                <div class="col-lg-12 col-md-12 mt-2">
                                    <x-forms.textarea fieldId="remarks" fieldLabel="Intake Remarks (Optional)"
                                        fieldName="remarks"
                                        fieldPlaceholder="Enter any remarks regarding the quality, packaging, or arrival logs...">
                                    </x-forms.textarea>
                                </div>

                            </div>

                            <!-- Items Table Area -->
                            <div class="row p-20 border-top-grey">
                                <div class="col-md-12">
                                    <h5 class="mb-3 f-16 text-dark font-weight-bold">Stock Items Details</h5>
                                    
                                    <table class="table table-bordered" id="intake-items-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="70%">Product</th>
                                                <th width="25%">Qty Received</th>
                                                <th width="5%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows Will Be Appended Here -->
                                        </tbody>
                                    </table>

                                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-row-btn">
                                        <i class="fa fa-plus mr-1"></i> Add Product Row
                                    </button>
                                </div>
                            </div>

                        </div>

                        <x-form-actions>
                            <x-forms.button-primary id="saveStockIntake" class="mr-3" icon="check">
                                Save Stock Intake Voucher
                            </x-forms.button-primary>
                            <x-forms.button-cancel :link="route('stock-intakes.index')" class="border-0">
                                @lang('app.cancel')
                            </x-forms.button-cancel>
                        </x-form-actions>

                    </div>
                </x-form>
            </div>
        </div>
    </div>

    <!-- Template for dynamic row generation -->
    <script type="text/html" id="item-row-template">
        <tr class="item-row">
            <td>
                <select class="form-control select-picker product-select" name="items[__INDEX__][product_id]" data-live-search="true" required>
                    <option value="">-- Choose Product --</option>
                    @foreach ($products as $prod)
                        <option value="{{ $prod->id }}" 
                                data-is-serialized="{{ $prod->is_serialized ? 1 : 0 }}">
                            {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <input type="hidden" class="quantity-declared" name="items[__INDEX__][quantity_declared]" value="1">
            <td>
                <input type="number" class="form-control quantity-received" name="items[__INDEX__][quantity_received]" min="0" step="any" required>
            </td>
            <input type="hidden" name="items[__INDEX__][unit_cost]" value="0.00">

            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm delete-row-btn" title="Delete Row">
                    <i class="fa fa-times"></i>
                </button>
            </td>
        </tr>
    </script>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var rowIndex = 0;
            var rowTemplate = $('#item-row-template').html();

            // Toggle inline shipment creation card
            $('#shipment_id').change(function() {
                if ($(this).val() === 'create_new') {
                    $('#new_shipment_card').removeClass('d-none');
                    dp('#eta');
                } else {
                    $('#new_shipment_card').addClass('d-none');
                }
            });

            // Append initial row on page load
            addRow();

            // Click action to add a row
            $('#add-row-btn').click(function() {
                addRow();
            });

            // Automatically synchronize quantity_declared with quantity_received
            $('body').on('input change', '.quantity-received', function() {
                var row = $(this).closest('tr');
                var val = parseFloat($(this).val()) || 0;
                row.find('.quantity-declared').val(Math.max(0.01, val));
            });

            function addRow() {
                var html = rowTemplate.replace(/__INDEX__/g, rowIndex);
                $('#intake-items-table tbody').append(html);
                
                // Initialize selectpicker on the newly appended row select
                var lastRow = $('#intake-items-table tbody tr').last();
                lastRow.find('.select-picker').selectpicker();

                rowIndex++;
            }

            // Remove a row action
            $('body').on('click', '.delete-row-btn', function() {
                if ($('#intake-items-table tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Stock Intake must have at least one product row.'
                    });
                }
            });



            // Save Stock Intake action handler
            $('#saveStockIntake').click(function() {
                var url = "{{ route('stock-intakes.store') }}";
                var data = $('#saveStockIntakeForm').serialize();

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: data,
                    container: '#saveStockIntakeForm',
                    messagePosition: 'inline',
                    disableButton: true,
                    buttonSelector: '#saveStockIntake',
                    error: function(jqXHR) {
                        // Reset buttons and unblock UI (standard easyAjax behavior)
                        $.easyUnblockUI('#saveStockIntakeForm');
                        var btn = $('#saveStockIntake');
                        btn.html(btn.attr('data-prev-text'));
                        btn.prop("disabled", false);
                        
                        try {
                            var response = JSON.parse(jqXHR.responseText);
                            if (response && response.errors) {
                                // Clear previous errors
                                $('.invalid-feedback').remove();
                                $('.is-invalid').removeClass('is-invalid');
                                $('.form-group').removeClass('has-error');
                                $('.item-row').removeClass('table-danger');
                                
                                // Loop through errors
                                var errorMessages = [];
                                $.each(response.errors, function(key, messages) {
                                    var cleanMessage = messages[0];
                                    // Remove Laravel internal dot-notation fields to show friendly text
                                    cleanMessage = cleanMessage.replace(/items\.\d+\.quantity_declared/gi, 'quantity declared')
                                                               .replace(/items\.\d+\.quantity_received/gi, 'received quantity')
                                                               .replace(/items\.\d+\.product_id/gi, 'product')
                                                               .replace(/items\.\d+\.unit_cost/gi, 'unit cost')
                                                               .replace(/quantity_declared/gi, 'quantity declared')
                                                               .replace(/quantity_received/gi, 'received quantity')
                                                               .replace(/product_id/gi, 'product')
                                                               .replace(/unit_cost/gi, 'unit cost');
                                    
                                    errorMessages.push(cleanMessage);
                                    
                                    // Map items.0.quantity_received -> name="items[0][quantity_received]"
                                    var inputName = key;
                                    if (key.indexOf('.') !== -1) {
                                        var parts = key.split('.');
                                        if (parts.length >= 3) {
                                            inputName = parts[0] + '[' + parts[1] + ']';
                                            for (var i = 2; i < parts.length; i++) {
                                                inputName += '[' + parts[i] + ']';
                                            }
                                        }
                                    }
                                    
                                    var inputElement = $('[name="' + inputName + '"]');
                                    if (inputElement.length > 0) {
                                        inputElement.addClass('is-invalid');
                                        
                                        var errorPlacementElement = inputElement;
                                        if (inputElement.closest('.bootstrap-select').length > 0) {
                                            errorPlacementElement = inputElement.closest('.bootstrap-select');
                                        }
                                        
                                        // Append validation error next to the input
                                        if (errorPlacementElement.next('.invalid-feedback').length === 0) {
                                            errorPlacementElement.after('<div class="invalid-feedback d-block f-12 mt-1">' + cleanMessage + '</div>');
                                        }
                                    }
                                    
                                    // Fallback for general fields (like warehouse_id)
                                    if (inputElement.length === 0) {
                                        var fallbackEl = $('#' + key);
                                        if (fallbackEl.length > 0) {
                                            fallbackEl.addClass('is-invalid');
                                            var grp = fallbackEl.closest('.form-group');
                                            grp.addClass('has-error');
                                            
                                            var errorPlacementElement = fallbackEl;
                                            if (fallbackEl.closest('.bootstrap-select').length > 0) {
                                                errorPlacementElement = fallbackEl.closest('.bootstrap-select');
                                            }
                                            
                                            if (errorPlacementElement.next('.invalid-feedback').length === 0) {
                                                errorPlacementElement.after('<div class="invalid-feedback d-block f-12 mt-1">' + cleanMessage + '</div>');
                                            }
                                        }
                                    }
                                });
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            });
        });
    </script>
@endpush
