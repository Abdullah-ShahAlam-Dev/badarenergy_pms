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
                                        @foreach ($shipments as $shp)
                                            <option value="{{ $shp->id }}">
                                                {{ $shp->shipment_number }} (ETA: {{ $shp->eta ? $shp->eta->format(company()->date_format) : '-' }})
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

                                <!-- Remarks -->
                                <div class="col-lg-12 col-md-12 mt-2">
                                    <x-forms.textarea fieldId="remarks" fieldLabel="Remarks (Optional)"
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
                                                <th width="50%">Product</th>
                                                <th width="22%">Qty Declared</th>
                                                <th width="23%">Qty Received</th>
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
            <td>
                <input type="number" class="form-control quantity-declared" name="items[__INDEX__][quantity_declared]" min="0.01" step="any" required>
            </td>
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

            // Append initial row on page load
            addRow();

            // Click action to add a row
            $('#add-row-btn').click(function() {
                addRow();
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
