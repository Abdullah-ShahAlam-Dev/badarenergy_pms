<div class="col-lg-12 cloudinary-form">
    <div class="row">
        <div class="col-lg-12">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('Cloudinary Cloud Name')"
                          fieldName="cloudinary_cloud_name"
                          fieldId="cloudinary_cloud_name"
                          :fieldValue="$cloudinaryKeys->cloud_name ?? '' "
                          fieldPlaceholder="e.g. my-cloud-name"
                          :fieldRequired="true">
            </x-forms.text>
        </div>
        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('Cloudinary API Key')"
                          fieldName="cloudinary_api_key"
                          fieldId="cloudinary_api_key"
                          :fieldValue="$cloudinaryKeys->api_key ?? '' "
                          fieldPlaceholder="e.g. 123456789012345"
                          :fieldRequired="true">
            </x-forms.text>
        </div>

        <div class="col-lg-6">
            <x-forms.label class="mt-3 field" fieldId="password"
                           :fieldLabel="__('Cloudinary API Secret')"
                           :fieldRequired="true">
            </x-forms.label>

            <x-forms.input-group>

                <input type="password"
                       name="cloudinary_api_secret"
                       id="cloudinary_api_secret"
                       class="form-control height-35 f-14 field"
                       value="{{ $cloudinaryKeys->api_secret ?? '' }}">
                <x-slot name="preappend">
                    <button type="button" data-toggle="tooltip"
                            data-original-title="{{ __('messages.viewKey') }}"
                            class="btn btn-outline-secondary border-grey height-35 toggle-password">
                        <i class="fa fa-eye"></i>
                    </button>
                </x-slot>
            </x-forms.input-group>
        </div>
    </div>
</div>
