<div class="row">
    <div class="col-sm-12">
        <x-form id="save-gate-pass-data-form" method="POST">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('gatepass::modules.gatePass.addRequest')</h4>
                <div class="row p-20">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="request_number" :fieldLabel="__('gatepass::modules.gatePass.requestNumber')"
                            fieldName="request_number" :fieldValue="$nextNumber" fieldReadOnly="true" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="request_date" :fieldLabel="__('gatepass::modules.gatePass.requestDate')"
                            fieldName="request_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="now()->format(company()->date_format)" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="department_id" :fieldLabel="__('app.department')" fieldName="department_id"
                            search="true">
                            <option value="">--</option>
                            @foreach ($departments as $team)
                                <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="type" :fieldLabel="__('gatepass::modules.gatePass.type')" fieldName="type">
                            <option value="out">Gate Pass Out</option>
                            <option value="in">Gate Pass In</option>
                        </x-forms.select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="return_type" :fieldLabel="__('gatepass::modules.gatePass.returnType')" fieldName="return_type">
                            <option value="non-returnable">Non-Returnable</option>
                            <option value="returnable">Returnable</option>
                        </x-forms.select>
                    </div>
                    <div class="col-lg-4 col-md-6 d-none" id="expected_return_date_container">
                        <x-forms.datepicker fieldId="expected_return_date" :fieldLabel="__('gatepass::modules.gatePass.expectedReturnDate')"
                            fieldName="expected_return_date" :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6">
                        <x-forms.text fieldId="from_location" :fieldLabel="__('gatepass::modules.gatePass.fromLocation')"
                            fieldName="from_location" fieldPlaceholder="e.g. Office Main Gate" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="to_location" :fieldLabel="__('gatepass::modules.gatePass.toLocation')"
                            fieldName="to_location" fieldPlaceholder="e.g. Client Site" />
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea fieldId="purpose" :fieldLabel="__('gatepass::modules.gatePass.purpose')"
                            fieldName="purpose" fieldRequired="true" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="vehicle_number" :fieldLabel="__('gatepass::modules.gatePass.vehicleNumber')"
                            fieldName="vehicle_number" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="driver_name" :fieldLabel="__('gatepass::modules.gatePass.driverName')"
                            fieldName="driver_name" />
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey border-bottom-grey">
                    @lang('gatepass::modules.gatePass.itemDetails')</h4>
                
                <div id="item-list">
                    <div class="row p-20 item-row">
                        <div class="col-md-4">
                            <x-forms.text fieldId="item_name_0" :fieldLabel="__('gatepass::modules.gatePass.itemName')"
                                fieldName="item_name[]" fieldRequired="true" />
                        </div>
                        <div class="col-md-2">
                            <x-forms.number fieldId="quantity_0" :fieldLabel="__('gatepass::modules.gatePass.quantity')"
                                fieldName="quantity[]" fieldRequired="true" />
                        </div>
                        <div class="col-md-2">
                            <x-forms.text fieldId="unit_0" :fieldLabel="__('gatepass::modules.gatePass.unit')"
                                fieldName="unit[]" fieldPlaceholder="e.g. Pcs" />
                        </div>
                        <div class="col-md-3">
                            <x-forms.text fieldId="serial_number_0" :fieldLabel="__('gatepass::modules.gatePass.serialNumber')"
                                fieldName="serial_number[]" />
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <!-- Placeholder for alignment -->
                        </div>
                    </div>
                </div>

                <div class="px-20 pb-20">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">
                        <i class="fa fa-plus mr-1"></i> Add Another Item
                    </button>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-gate-pass-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('gate-pass.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
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

        var itemIndex = 1;
        $('#add-item').click(function() {
            var html = `
                <div class="row p-20 item-row border-top-grey">
                    <div class="col-md-4">
                        <x-forms.text fieldId="item_name_${itemIndex}" :fieldLabel="__('gatepass::modules.gatePass.itemName')"
                            fieldName="item_name[]" fieldRequired="true" />
                    </div>
                    <div class="col-md-2">
                        <x-forms.number fieldId="quantity_${itemIndex}" :fieldLabel="__('gatepass::modules.gatePass.quantity')"
                            fieldName="quantity[]" fieldRequired="true" />
                    </div>
                    <div class="col-md-2">
                        <x-forms.text fieldId="unit_${itemIndex}" :fieldLabel="__('gatepass::modules.gatePass.unit')"
                            fieldName="unit[]" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="serial_number_${itemIndex}" :fieldLabel="__('gatepass::modules.gatePass.serialNumber')"
                            fieldName="serial_number[]" />
                    </div>
                    <div class="col-md-1 d-flex align-items-center pt-4">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item mt-2">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>`;
            // Note: Since I'm using Blade components inside JS string, this won't work directly if the components use PHP.
            // I'll rewrite this to use plain HTML for the repeated part.
            itemIndex++;
        });

        // Fixed item repeater logic
        $('#add-item').unbind().click(function() {
            var html = `
                <div class="row p-20 item-row border-top-grey">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="f-14 text-dark-grey mb-12">Item Name <sup class="f-14">*</sup></label>
                            <input type="text" class="form-control height-35 f-14" name="item_name[]" required>
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
                            <input type="text" class="form-control height-35 f-14" name="unit[]">
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
        });

        $('body').on('click', '.remove-item', function() {
            $(this).closest('.item-row').remove();
        });

        $('#save-gate-pass-form').click(function() {
            const url = "{{ route('gate-pass.store') }}";
            $.easyAjax({
                url: url,
                container: '#save-gate-pass-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-gate-pass-form",
                data: $('#save-gate-pass-data-form').serialize(),
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
