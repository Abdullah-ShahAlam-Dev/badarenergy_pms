<div class="row">
    <div class="col-sm-12">
        <x-form id="update-gate-pass-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    Edit Gate Pass Request: {{ $gatePass->request_number }}</h4>
                <div class="row p-20">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="request_number" :fieldLabel="__('gatepass::modules.gatePass.requestNumber')"
                            fieldName="request_number" :fieldValue="$gatePass->request_number" fieldReadOnly="true" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="request_date" :fieldLabel="__('gatepass::modules.gatePass.requestDate')"
                            fieldName="request_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$gatePass->request_date->format(company()->date_format)" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="department_id" :fieldLabel="__('app.department')" fieldName="department_id"
                            search="true">
                            <option value="">--</option>
                            @foreach ($departments as $team)
                                <option value="{{ $team->id }}" {{ $gatePass->department_id == $team->id ? 'selected' : '' }}>{{ $team->team_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="type" :fieldLabel="__('gatepass::modules.gatePass.type')" fieldName="type">
                            <option value="out" {{ $gatePass->type == 'out' ? 'selected' : '' }}>Gate Pass Out</option>
                            <option value="in" {{ $gatePass->type == 'in' ? 'selected' : '' }}>Gate Pass In</option>
                        </x-forms.select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="return_type" :fieldLabel="__('gatepass::modules.gatePass.returnType')" fieldName="return_type">
                            <option value="non-returnable" {{ $gatePass->return_type == 'non-returnable' ? 'selected' : '' }}>Non-Returnable</option>
                            <option value="returnable" {{ $gatePass->return_type == 'returnable' ? 'selected' : '' }}>Returnable</option>
                        </x-forms.select>
                    </div>
                    <div class="col-lg-4 col-md-6 {{ $gatePass->return_type != 'returnable' ? 'd-none' : '' }}" id="expected_return_date_container">
                        <x-forms.datepicker fieldId="expected_return_date" :fieldLabel="__('gatepass::modules.gatePass.expectedReturnDate')"
                            fieldName="expected_return_date" :fieldPlaceholder="__('placeholders.date')" 
                            :fieldValue="$gatePass->expected_return_date ? $gatePass->expected_return_date->format(company()->date_format) : ''" />
                    </div>

                    <div class="col-md-6">
                        <x-forms.text fieldId="from_location" :fieldLabel="__('gatepass::modules.gatePass.fromLocation')"
                            fieldName="from_location" :fieldValue="$gatePass->from_location" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="to_location" :fieldLabel="__('gatepass::modules.gatePass.toLocation')"
                            fieldName="to_location" :fieldValue="$gatePass->to_location" />
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea fieldId="purpose" :fieldLabel="__('gatepass::modules.gatePass.purpose')"
                            fieldName="purpose" fieldRequired="true" :fieldValue="$gatePass->purpose" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="vehicle_number" :fieldLabel="__('gatepass::modules.gatePass.vehicleNumber')"
                            fieldName="vehicle_number" :fieldValue="$gatePass->vehicle_number" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="driver_name" :fieldLabel="__('gatepass::modules.gatePass.driverName')"
                            fieldName="driver_name" :fieldValue="$gatePass->driver_name" />
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey border-bottom-grey">
                    @lang('gatepass::modules.gatePass.itemDetails')</h4>
                
                <div id="item-list">
                    @foreach($gatePass->items as $index => $item)
                        <div class="row p-20 item-row {{ $index > 0 ? 'border-top-grey' : '' }}">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Product <sup class="f-14">*</sup></label>
                                    <select class="form-control height-35 f-14 selectpicker" name="product_id[]" data-live-search="true" required>
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Quantity <sup class="f-14">*</sup></label>
                                    <input type="number" class="form-control height-35 f-14" name="quantity[]" value="{{ $item->quantity }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Unit</label>
                                    <input type="text" class="form-control height-35 f-14" name="unit[]" value="{{ $item->unit }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Serial/Asset Tag</label>
                                    <input type="text" class="form-control height-35 f-14" name="serial_number[]" value="{{ $item->serial_number }}">
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-center pt-3">
                                @if($index > 0)
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item mt-2">
                                        <i class="fa fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-20 pb-20">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">
                        <i class="fa fa-plus mr-1"></i> Add Another Item
                    </button>
                    @if(user()->permission('add_product') != 'none')
                        <button type="button" class="btn btn-outline-success btn-sm ml-2" data-toggle="modal" data-target="#quickAddProductModal">
                            <i class="fa fa-plus mr-1"></i> Create Product Master
                        </button>
                    @endif
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-gate-pass-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('gate-pass.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

                @if(user()->permission('add_product') != 'none')
                <!-- Quick Add Product Modal -->
                <div class="modal fade" id="quickAddProductModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
                    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-dark">Create Product Master</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body text-left">
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Product Name <sup class="f-14">*</sup></label>
                                    <input type="text" class="form-control height-35 f-14" id="quick_product_name" required>
                                </div>
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Price <sup class="f-14">*</sup></label>
                                    <input type="number" class="form-control height-35 f-14" id="quick_product_price" value="0" required>
                                </div>
                                <div class="form-group">
                                    <label class="f-14 text-dark-grey mb-12">Purchase Allowed</label>
                                    <select class="form-control height-35 f-14" id="quick_purchase_allow">
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary btn-sm" id="save-quick-product">Save Product</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        datepicker('#request_date', {
            position: 'bl',
            ...datepickerConfig
        });
        datepicker('#expected_return_date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#return_type').change(function() {
            if ($(this).val() == 'returnable') {
                $('#expected_return_date_container').removeClass('d-none');
            } else {
                $('#expected_return_date_container').addClass('d-none');
            }
        });

        // Generate product options
        var productOptions = `<option value="">-- Select Product --</option>`;
        @foreach($products as $product)
            productOptions += `<option value="{{ $product->id }}">{{ addslashes($product->name) }}</option>`;
        @endforeach

        $('#add-item').unbind().click(function() {
            var html = `
                <div class="row p-20 item-row border-top-grey">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="f-14 text-dark-grey mb-12">Product <sup class="f-14">*</sup></label>
                            <select class="form-control height-35 f-14 selectpicker" name="product_id[]" data-live-search="true" required>
                                ${productOptions}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="f-14 text-dark-grey mb-12">Quantity <sup class="f-14">*</sup></label>
                            <input type="number" class="form-control height-35 f-14" name="quantity[]" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="f-14 text-dark-grey mb-12">Unit</label>
                            <input type="text" class="form-control height-35 f-14" name="unit[]" placeholder="e.g. Pcs">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="f-14 text-dark-grey mb-12">Serial/Asset Tag</label>
                            <input type="text" class="form-control height-35 f-14" name="serial_number[]">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center pt-3">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item mt-2">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>`;
            $('#item-list').append(html);
            $('select[name="product_id[]"]').selectpicker('refresh');
        });

        $('body').on('click', '.remove-item', function() {
            $(this).closest('.item-row').remove();
        });

        @if(user()->permission('add_product') != 'none')
        $('#save-quick-product').click(function() {
            var name = $('#quick_product_name').val();
            var price = $('#quick_product_price').val();
            var purchaseAllow = $('#quick_purchase_allow').val();

            if (!name || !price) {
                alert('Please fill out all required fields.');
                return;
            }

            $.easyAjax({
                url: "{{ route('products.store') }}",
                type: "POST",
                disableButton: true,
                buttonSelector: "#save-quick-product",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name,
                    price: price,
                    purchase_allow: purchaseAllow,
                    default_image: 0
                },
                success: function(response) {
                    if (response.status == 'success') {
                        // Append newly created product to all product dropdowns
                        var safeName = name.replace(/'/g, "\\'").replace(/"/g, '\\"');
                        var optionHtml = `<option value="${response.productID}">${name}</option>`;
                        productOptions += `<option value="${response.productID}">${safeName}</option>`;
                        
                        $('select[name="product_id[]"]').each(function() {
                            var currentVal = $(this).val();
                            $(this).append(optionHtml);
                            $(this).val(currentVal);
                        });
                        $('select[name="product_id[]"]').selectpicker('refresh');
                        
                        // Select the new product in the last row if its value is currently empty
                        var lastSelect = $('select[name="product_id[]"]').last();
                        if (!lastSelect.val()) {
                            lastSelect.val(response.productID).selectpicker('refresh');
                        }

                        $('#quickAddProductModal').modal('hide');
                        $('#quick_product_name').val('');
                        $('#quick_product_price').val('0');
                        $('#quick_purchase_allow').val('yes');
                    }
                }
            });
        });
        @endif

        $('#update-gate-pass-form').click(function() {
            const url = "{{ route('gate-pass.update', $gatePass->id) }}";
            $.easyAjax({
                url: url,
                container: '#update-gate-pass-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-gate-pass-form",
                data: $('#update-gate-pass-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(RIGHT_MODAL).hasClass('in')) {
                            document.getElementById('right-modal-content').innerHTML = "";
                            $(RIGHT_MODAL).modal('hide');
                        }
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
