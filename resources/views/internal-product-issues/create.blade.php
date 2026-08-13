@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <x-form id="saveInternalIssueForm">
                    <div class="add-client bg-white rounded">
                        <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                            Issue Product Internally (Care Of / Office / Vehicle)
                        </h4>

                        <div class="form-body">
                            <div class="row p-20">

                                <!-- Care Of Person / Employee -->
                                <div class="col-md-4">
                                    <x-forms.select fieldId="care_of_id" fieldLabel="Issue To (Person / Employee / Care Of)"
                                        fieldName="care_of_id" fieldRequired="true" search="true">
                                        <option value="">-- Select Person / Employee --</option>
                                        @foreach ($careOfUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </x-forms.select>
                                </div>

                                <!-- Dispatch Warehouse -->
                                <div class="col-md-4">
                                    <x-forms.select fieldId="warehouse_id" fieldLabel="Dispatch Warehouse"
                                        fieldName="warehouse_id" fieldRequired="true" search="true">
                                        <option value="">-- Select Warehouse --</option>
                                        @foreach ($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </x-forms.select>
                                </div>

                                <!-- Purpose / Use Case -->
                                <div class="col-md-4">
                                    <x-forms.text fieldId="purpose" fieldLabel="Purpose / Use Case"
                                        fieldName="purpose"
                                        fieldPlaceholder="e.g. Office generator battery, Company Vehicle" />
                                </div>

                                <!-- Vehicle Number -->
                                <div class="col-md-6 mt-2">
                                    <x-forms.text fieldId="vehicle_number" fieldLabel="Vehicle Number (Optional)"
                                        fieldName="vehicle_number"
                                        fieldPlaceholder="e.g. LEB-2026" />
                                </div>

                                <!-- Driver / Transport Name -->
                                <div class="col-md-6 mt-2">
                                    <x-forms.text fieldId="driver_name" fieldLabel="Driver / Transport Name (Optional)"
                                        fieldName="driver_name"
                                        fieldPlaceholder="e.g. Ali Raza" />
                                </div>

                            </div>

                            <!-- Products to Issue Table -->
                            <div class="row p-20 border-top-grey">
                                <div class="col-md-12">
                                    <h5 class="mb-3 f-16 text-dark font-weight-bold">Products to Issue</h5>
                                    
                                    <table class="table table-bordered" id="issue-items-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="50%">Product <span class="text-danger">*</span></th>
                                                <th width="25%">Quantity <span class="text-danger">*</span></th>
                                                <th width="20%">Unit Value (Cost/Price)</th>
                                                <th width="5%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows Appended Here -->
                                        </tbody>
                                    </table>

                                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-issue-item-btn">
                                        <i class="fa fa-plus mr-1"></i> Add Product
                                    </button>
                                </div>
                            </div>

                        </div>

                        <x-form-actions>
                            <x-forms.button-primary id="submitInternalIssue" class="mr-3" icon="paper-plane">
                                Process Product Issue
                            </x-forms.button-primary>
                            <x-forms.button-cancel :link="route('internal-product-issues.index')" class="border-0">
                                @lang('app.cancel')
                            </x-forms.button-cancel>
                        </x-form-actions>

                    </div>
                </x-form>
            </div>
        </div>
    </div>

    <!-- Template for dynamic row generation -->
    <script type="text/html" id="issue-row-template">
        <tr class="issue-row">
            <td>
                <select class="form-control select-picker product-select" name="product_id[]" data-live-search="true" required>
                    <option value="">-- Select Product --</option>
                    @foreach ($products as $prod)
                        <option value="{{ $prod->id }}">
                            {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control item-quantity" name="quantity[]" min="0.01" step="any" value="1.00" required>
            </td>
            <td>
                <input type="number" class="form-control item-price" name="unit_price[]" min="0" step="any" value="0.00">
            </td>
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
            var rowTemplate = $('#issue-row-template').html();

            // Add first initial row
            addRow();

            // Add row on click
            $('#add-issue-item-btn').click(function() {
                addRow();
            });

            function addRow() {
                $('#issue-items-table tbody').append(rowTemplate);
                var lastRow = $('#issue-items-table tbody tr').last();
                lastRow.find('.select-picker').selectpicker();
            }

            // Remove row on click
            $('body').on('click', '.delete-row-btn', function() {
                if ($('#issue-items-table tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        text: 'At least one product is required for internal issue.'
                    });
                }
            });

            // Submit form via easyAjax
            $('#submitInternalIssue').click(function() {
                var url = "{{ route('internal-product-issues.store') }}";
                var data = $('#saveInternalIssueForm').serialize();

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: data,
                    container: '#saveInternalIssueForm',
                    messagePosition: 'inline',
                    disableButton: true,
                    buttonSelector: '#submitInternalIssue',
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
