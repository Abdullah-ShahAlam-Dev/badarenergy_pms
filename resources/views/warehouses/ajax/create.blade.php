<div class="row">
    <div class="col-sm-12">
        <x-form id="addWarehouseForm">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.add') @lang('modules.warehouse.warehouse')
                </h4>

                <div class="form-body">
                    <div class="row p-20">

                        <!-- Name -->
                        <div class="col-lg-6 col-md-6">
                            <x-forms.text fieldId="name" :fieldLabel="__('modules.warehouse.name')"
                                fieldName="name" fieldRequired="true"
                                :fieldPlaceholder="__('placeholders.name')">
                            </x-forms.text>
                        </div>

                        <!-- Code -->
                        <div class="col-lg-6 col-md-6">
                            <x-forms.text fieldId="code" :fieldLabel="__('modules.warehouse.code')"
                                fieldName="code" fieldRequired="true"
                                :fieldPlaceholder="__('modules.warehouse.codePlaceholder')">
                            </x-forms.text>
                        </div>

                        <!-- Type -->
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="type" :fieldLabel="__('modules.warehouse.type')"
                                fieldName="type" fieldRequired="true">
                                @foreach ($warehouseTypes as $type)
                                    <option value="{{ $type }}">@lang('modules.warehouse.type' . ucfirst($type))</option>
                                @endforeach
                            </x-forms.select>
                        </div>

                        <!-- Active Status -->
                        <div class="col-lg-6 col-md-6 d-flex align-items-end pb-3">
                            <div class="d-flex align-items-center">
                                <x-forms.checkbox fieldId="is_active" :fieldLabel="__('modules.warehouse.isActive')"
                                    fieldName="is_active" :checked="true">
                                </x-forms.checkbox>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-lg-12 col-md-12">
                            <x-forms.textarea fieldId="address" :fieldLabel="__('modules.warehouse.address')"
                                fieldName="address"
                                :fieldPlaceholder="__('modules.warehouse.addressPlaceholder')">
                            </x-forms.textarea>
                        </div>

                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="saveWarehouse" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('warehouses.index')" class="border-0">
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

    $('#saveWarehouse').click(function() {
        var url  = "{{ route('warehouses.store') }}";
        var data = $('#addWarehouseForm').serialize();

        $.easyAjax({
            type: 'POST',
            url:  url,
            data: data,
            container: '#addWarehouseForm',
            messagePosition: 'inline',
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl;
                }
            }
        });
    });
</script>
