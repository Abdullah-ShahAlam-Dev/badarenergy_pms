<div class="row">
    <div class="col-sm-12">
        <x-form id="update-vendor-form" method="POST">
            <input type="hidden" name="_method" value="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('workorder::modules.vendor.editVendor') — {{ $vendor->vendor_name }}
                </h4>
                <div class="row p-20">
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="vendor_name" :fieldLabel="__('workorder::modules.vendor.vendorName')"
                            fieldName="vendor_name" :fieldValue="$vendor->vendor_name" fieldRequired="true" />
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="company_name" :fieldLabel="__('workorder::modules.vendor.companyName')"
                            fieldName="company_name" :fieldValue="$vendor->company_name" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="designation" :fieldLabel="__('workorder::modules.vendor.designation')"
                            fieldName="designation" :fieldValue="$vendor->designation" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="mobile"
                            :fieldLabel="__('workorder::modules.vendor.mobile')"></x-forms.label>
                        <x-forms.input-group style="margin-top:-4px">
                            <x-forms.select fieldId="country_phonecode" fieldName="country_phonecode"
                                search="true">
                                @foreach ($countries as $item)
                                    <option data-tokens="{{ $item->name }}"
                                            data-content="{{$item->flagSpanCountryCode()}}"
                                            value="{{ $item->phonecode }}" @if($item->phonecode == ($vendor->country_phonecode ?? 92)) selected @endif>{{ $item->phonecode }}
                                    </option>
                                @endforeach
                            </x-forms.select>

                            <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                name="mobile" id="mobile" value="{{ $vendor->mobile }}">
                        </x-forms.input-group>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="alternate_mobile"
                            :fieldLabel="__('workorder::modules.vendor.alternateMobile')"></x-forms.label>
                        <x-forms.input-group style="margin-top:-4px">
                            <x-forms.select fieldId="alternate_country_phonecode" fieldName="alternate_country_phonecode"
                                search="true">
                                @foreach ($countries as $item)
                                    <option data-tokens="{{ $item->name }}"
                                            data-content="{{$item->flagSpanCountryCode()}}"
                                            value="{{ $item->phonecode }}" @if($item->phonecode == ($vendor->alternate_country_phonecode ?? 92)) selected @endif>{{ $item->phonecode }}
                                    </option>
                                @endforeach
                            </x-forms.select>

                            <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                name="alternate_mobile" id="alternate_mobile" value="{{ $vendor->alternate_mobile }}">
                        </x-forms.input-group>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="email" :fieldLabel="__('workorder::modules.vendor.email')"
                            fieldName="email" fieldType="email" :fieldValue="$vendor->email" />
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="category" :fieldLabel="__('workorder::modules.vendor.category')"
                            fieldName="category" :fieldValue="$vendor->category" />
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="cnic" :fieldLabel="__('workorder::modules.vendor.cnic')"
                            fieldName="cnic" :fieldValue="$vendor->cnic" />
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="ntn" :fieldLabel="__('workorder::modules.vendor.ntn')"
                            fieldName="ntn" :fieldValue="$vendor->ntn" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="office_address" :fieldLabel="__('workorder::modules.vendor.officeAddress')"
                            fieldName="office_address" :fieldValue="$vendor->office_address" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="bank_details" :fieldLabel="__('workorder::modules.vendor.bankDetails')"
                            fieldName="bank_details" :fieldValue="$vendor->bank_details" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="notes" :fieldLabel="__('workorder::modules.vendor.notes')"
                            fieldName="notes" :fieldValue="$vendor->notes" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('workorder::modules.vendor.status')" fieldName="status">
                            <option value="active" @selected($vendor->status == 'active')>Active</option>
                            <option value="inactive" @selected($vendor->status == 'inactive')>Inactive</option>
                        </x-forms.select>
                    </div>
                </div>
                <div class="p-20 border-top-grey">
                    <x-forms.button-primary id="update-vendor-btn" icon="check">@lang('app.update')</x-forms.button-primary>
                    <a href="{{ route('vendors.index') }}" class="btn btn-secondary ml-2">@lang('app.cancel')</a>
                </div>
            </div>
        </x-form>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#update-vendor-btn').click(function () {
        $.easyAjax({
            url: "{{ route('vendors.update', $vendor->id) }}",
            container: '#update-vendor-form',
            type: 'POST',
            disableButton: true,
            blockUI: true,
            buttonSelector: '#update-vendor-btn',
            data: $('#update-vendor-form').serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    if ($(RIGHT_MODAL).hasClass('in')) {
                        document.getElementById('right-modal-content').innerHTML = '';
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
