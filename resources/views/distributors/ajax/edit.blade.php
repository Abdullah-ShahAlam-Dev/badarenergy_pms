<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-distributor-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    Edit Distributor Details
                </h4>

                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.text fieldId="name" fieldLabel="Distributor Name" fieldName="name" fieldRequired="true" :fieldValue="$distributor->name" placeholder="Enter distributor name" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="company_name" fieldLabel="Company Name" fieldName="company_name" :fieldValue="$distributor->company_name" placeholder="Enter company name" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="email" fieldLabel="Email" fieldName="email" :fieldValue="$distributor->email" placeholder="e.g. distributor@example.com" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="phone" fieldLabel="Phone Number" fieldName="phone" :fieldValue="$distributor->phone" placeholder="Enter phone number" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="tax_number" fieldLabel="Tax Number" fieldName="tax_number" :fieldValue="$distributor->tax_number" placeholder="Enter tax / NTN number" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="status" fieldLabel="Status" fieldName="status">
                            <option value="active" {{ $distributor->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="deactive" {{ $distributor->status == 'deactive' ? 'selected' : '' }}>Deactive</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-6">
                        <x-forms.textarea fieldId="address" fieldLabel="Billing Address" fieldName="address" :fieldValue="$distributor->address" placeholder="Enter billing address" />
                    </div>

                    <div class="col-md-6">
                        <x-forms.textarea fieldId="shipping_address" fieldLabel="Shipping Address" fieldName="shipping_address" :fieldValue="$distributor->shipping_address" placeholder="Enter shipping address" />
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea fieldId="note" fieldLabel="Note / Remarks" fieldName="note" :fieldValue="$distributor->note" placeholder="Enter note or remarks" />
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-distributor-form" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('distributors.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#update-distributor-form').click(function() {
            const url = "{{ route('distributors.update', $distributor->id) }}";
            var data = $('#edit-distributor-data-form').serialize();

            $.easyAjax({
                url: url,
                container: '#edit-distributor-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-distributor-form",
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        if (typeof window.LaravelDataTables["distributors-table"] !== 'undefined') {
                            window.LaravelDataTables["distributors-table"].draw(false);
                            if (typeof closeTaskDetail === 'function') {
                                closeTaskDetail();
                            } else if ($(RIGHT_MODAL).hasClass('in')) {
                                document.getElementById('right-modal-content').innerHTML = '';
                                $(RIGHT_MODAL).removeClass('in');
                                $('.bg-overlay').remove();
                            }
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });
    });
</script>
