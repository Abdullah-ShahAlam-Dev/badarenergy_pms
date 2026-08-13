@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <x-form id="saveAssemblyWithdrawalForm">
                    <div class="add-client bg-white rounded">
                        <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                            Withdraw Assembly Parts (Raw Stock)
                        </h4>

                        <div class="form-body">
                            <div class="row p-20">

                                <!-- Withdrawal Ref # -->
                                <div class="col-md-4">
                                    <x-forms.text fieldId="assembly_number" fieldLabel="Withdrawal Reference #"
                                        fieldName="assembly_number" :fieldValue="$nextAssemblyNumber" readonly="true" />
                                </div>

                                <!-- Warehouse Selector -->
                                <div class="col-md-4">
                                    <x-forms.select fieldId="warehouse_id" fieldLabel="Source Warehouse"
                                        fieldName="warehouse_id" fieldRequired="true" search="true">
                                        <option value="">-- Select Warehouse --</option>
                                        @foreach ($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </x-forms.select>
                                </div>

                                <!-- User Name -->
                                <div class="col-md-4">
                                    <x-forms.text fieldId="user_name" fieldLabel="Stock Withdrawn By"
                                        fieldName="user_name" :fieldValue="auth()->user()->name" fieldReadOnly="true" />
                                </div>

                                <!-- Remarks -->
                                <div class="col-lg-12 col-md-12 mt-2">
                                    <x-forms.textarea fieldId="notes" fieldLabel="Withdrawal Notes / Purpose"
                                        fieldName="notes"
                                        fieldPlaceholder="e.g. Taking 17 cases and 50 cells for battery assembly...">
                                    </x-forms.textarea>
                                </div>

                            </div>

                            <!-- Assembly Component Items Area -->
                            <div class="row p-20 border-top-grey">
                                <div class="col-md-12">
                                    <h5 class="mb-3 f-16 text-dark font-weight-bold">Assembly Parts to Withdraw</h5>
                                    
                                    <table class="table table-bordered" id="assembly-parts-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="65%">Assembly Component / Part <span class="text-danger">*</span></th>
                                                <th width="30%">Quantity Withdrawn <span class="text-danger">*</span></th>
                                                <th width="5%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows Will Be Appended Here -->
                                        </tbody>
                                    </table>

                                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-row-btn">
                                        <i class="fa fa-plus mr-1"></i> Add Component Row
                                    </button>
                                </div>
                            </div>

                        </div>

                        <x-form-actions>
                            <x-forms.button-primary id="submitAssemblyWithdrawal" class="mr-3" icon="check">
                                Withdraw & Deduct Stock Instantly
                            </x-forms.button-primary>
                            <x-forms.button-cancel :link="route('assembly-orders.index')" class="border-0">
                                @lang('app.cancel')
                            </x-forms.button-cancel>
                        </x-form-actions>

                    </div>
                </x-form>
            </div>
        </div>
    </div>

    <!-- Template for dynamic row generation -->
    <script type="text/html" id="part-row-template">
        <tr class="part-row">
            <td>
                <select class="form-control select-picker part-select" name="raw_product_id[]" data-live-search="true" required>
                    <option value="">-- Select Assembly Part --</option>
                    @foreach ($products as $prod)
                        <option value="{{ $prod->id }}">
                            {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control part-quantity" name="quantity_required[]" min="0.01" step="any" value="1.00" required>
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
            var rowTemplate = $('#part-row-template').html();

            // Add first initial row
            addRow();

            // Add row on click
            $('#add-row-btn').click(function() {
                addRow();
            });

            function addRow() {
                $('#assembly-parts-table tbody').append(rowTemplate);
                var lastRow = $('#assembly-parts-table tbody tr').last();
                lastRow.find('.select-picker').selectpicker();
            }

            // Remove row on click
            $('body').on('click', '.delete-row-btn', function() {
                if ($('#assembly-parts-table tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Assembly withdrawal must have at least one component part.'
                    });
                }
            });

            // Submit form via easyAjax
            $('#submitAssemblyWithdrawal').click(function() {
                var url = "{{ route('assembly-orders.store') }}";
                var data = $('#saveAssemblyWithdrawalForm').serialize();

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: data,
                    container: '#saveAssemblyWithdrawalForm',
                    messagePosition: 'inline',
                    disableButton: true,
                    buttonSelector: '#submitAssemblyWithdrawal',
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
