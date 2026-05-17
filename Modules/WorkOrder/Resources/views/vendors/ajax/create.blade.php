<div class="row">
    <div class="col-sm-12">
        <x-form id="save-vendor-form" method="POST">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('workorder::modules.vendor.addVendor')
                </h4>
                <div class="row p-20">
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="vendor_name" :fieldLabel="__('workorder::modules.vendor.vendorName')"
                            fieldName="vendor_name" fieldRequired="true" />
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="company_name" :fieldLabel="__('workorder::modules.vendor.companyName')"
                            fieldName="company_name" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="designation" :fieldLabel="__('workorder::modules.vendor.designation')"
                            fieldName="designation" />
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
                                            value="{{ $item->phonecode }}" @if($item->phonecode == 92) selected @endif>{{ $item->phonecode }}
                                    </option>
                                @endforeach
                            </x-forms.select>

                            <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                name="mobile" id="mobile">
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
                                            value="{{ $item->phonecode }}" @if($item->phonecode == 92) selected @endif>{{ $item->phonecode }}
                                    </option>
                                @endforeach
                            </x-forms.select>

                            <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                name="alternate_mobile" id="alternate_mobile">
                        </x-forms.input-group>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="email" :fieldLabel="__('workorder::modules.vendor.email')"
                            fieldName="email" fieldType="email" />
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="category" :fieldLabel="__('workorder::modules.vendor.category')"
                            fieldName="category" />
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="cnic" :fieldLabel="__('workorder::modules.vendor.cnic')"
                            fieldName="cnic" />
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="ntn" :fieldLabel="__('workorder::modules.vendor.ntn')"
                            fieldName="ntn" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="office_address" :fieldLabel="__('workorder::modules.vendor.officeAddress')"
                            fieldName="office_address" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="bank_details" :fieldLabel="__('workorder::modules.vendor.bankDetails')"
                            fieldName="bank_details" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="notes" :fieldLabel="__('workorder::modules.vendor.notes')"
                            fieldName="notes" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('workorder::modules.vendor.status')" fieldName="status">
                            <option value="active">@lang('workorder::modules.vendor.active')</option>
                            <option value="inactive">@lang('workorder::modules.vendor.inactive')</option>
                        </x-forms.select>
                    </div>
                </div>
                <div class="p-20 border-top-grey">
                    <x-forms.button-primary id="save-vendor-btn" icon="check">@lang('app.save')</x-forms.button-primary>
                    <a href="{{ route('vendors.index') }}" class="btn btn-secondary ml-2">@lang('app.cancel')</a>
                </div>
            </div>
        </x-form>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#save-vendor-btn').click(function () {
        $.easyAjax({
            url: "{{ route('vendors.store') }}",
            container: '#save-vendor-form',
            type: 'POST',
            disableButton: true,
            blockUI: true,
            buttonSelector: '#save-vendor-btn',
            data: $('#save-vendor-form').serialize(),
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
