<div class="row">
    <div class="col-sm-12">
        <x-form id="adjustStockForm">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.inventory.manualAdjustment')
                </h4>

                <div class="form-body">
                    <div class="row p-20">

                        <!-- Product -->
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="product_id" :fieldLabel="__('modules.inventory.product')"
                                fieldName="product_id" fieldRequired="true">
                                <option value="">@lang('modules.inventory.selectProduct')</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-serialized="{{ $product->is_serialized ? '1' : '0' }}" {{ $defaultProductId == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>

                        <!-- Warehouse -->
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="warehouse_id" :fieldLabel="__('modules.inventory.warehouse')"
                                fieldName="warehouse_id" fieldRequired="true">
                                <option value="">@lang('modules.inventory.selectWarehouse')</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $defaultWarehouseId == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} ({{ $warehouse->code }})
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>

                        <!-- Type -->
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="type" :fieldLabel="__('modules.inventory.adjustmentType')"
                                fieldName="type" fieldRequired="true">
                                <option value="in">@lang('modules.inventory.addStock')</option>
                                <option value="out">@lang('modules.inventory.removeStock')</option>
                            </x-forms.select>
                        </div>

                        <!-- Category -->
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="category" :fieldLabel="__('Stock Category')"
                                fieldName="category" fieldRequired="true">
                                <option value="available">Available</option>
                                <option value="faulty">Faulty/Damaged</option>
                                <option value="in_transit">In-Transit</option>
                            </x-forms.select>
                        </div>

                        <!-- Quantity -->
                        <div class="col-lg-6 col-md-6">
                            <x-forms.number fieldId="quantity" :fieldLabel="__('modules.inventory.adjustmentQty')"
                                fieldName="quantity" fieldRequired="true" :fieldValue="1" min="0.01" step="0.01">
                            </x-forms.number>
                        </div>

                        <!-- Serial Numbers -->
                        <div class="col-lg-12 col-md-12 d-none" id="serial-numbers-field">
                            <x-forms.textarea fieldId="serial_numbers" :fieldLabel="__('Serial Numbers (One per line)')"
                                fieldName="serial_numbers"
                                fieldPlaceholder="Scan or type serial numbers here (one per line)">
                            </x-forms.textarea>
                        </div>

                        <!-- Remarks -->
                        <div class="col-lg-12 col-md-12">
                            <x-forms.textarea fieldId="remarks" :fieldLabel="__('modules.inventory.remarks')"
                                fieldName="remarks"
                                :fieldPlaceholder="__('modules.inventory.remarksPlaceholder')">
                            </x-forms.textarea>
                        </div>

                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="saveAdjustment" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('inventory.index')" class="border-0">
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

        function toggleSerialField() {
            var selected = $('#product_id option:selected');
            var isSerialized = selected.data('serialized') == '1';
            if (isSerialized) {
                $('#serial-numbers-field').removeClass('d-none');
            } else {
                $('#serial-numbers-field').addClass('d-none');
                $('#serial_numbers').val('');
            }
        }

        $('#product_id').change(function() {
            toggleSerialField();
        });

        // Trigger on load
        toggleSerialField();
    });

    $('#saveAdjustment').click(function() {
        var url  = "{{ route('inventory.store') }}";
        var data = $('#adjustStockForm').serialize();

        $.easyAjax({
            type: 'POST',
            url:  url,
            data: data,
            container: '#adjustStockForm',
            messagePosition: 'inline',
            success: function(response) {
                if (response.status === 'success') {
                    if (window.location.href.indexOf('inventory') > -1) {
                        if (typeof showTable === 'function') {
                            showTable();
                        } else {
                            window.location.reload();
                        }
                        $(MODAL_RIGHT).modal('hide');
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                }
            }
        });
    });
</script>
