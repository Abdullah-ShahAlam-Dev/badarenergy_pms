<div class="card" id="general-panel">
    <div class="card-header">
        <h4 class="card-title">@lang('General Settings')</h4>
    </div>

    <div class="card-body">
        <x-form id="updateCrmEmailGeneralSettings" method="PUT" :action="route('crm-email-settings.update', 1)">

            {{-- Default Sender Name --}}
            <div class="form-group">
                <label class="f-14 text-dark-grey mb-1 w-100" for="from_name">
                    @lang('Default Sender Name')
                    <span class="text-muted f-12"> — used when no template sender name is configured</span>
                </label>
                <input type="text"
                       class="form-control height-35 f-14"
                       id="from_name"
                       name="from_name"
                       value="{{ old('from_name', $setting->from_name) }}"
                       placeholder="{{ company()->company_name }}">
            </div>

            {{-- Default Sender Email --}}
            <div class="form-group">
                <label class="f-14 text-dark-grey mb-1 w-100" for="from_email">
                    @lang('Default Sender Email')
                    <span class="text-muted f-12"> — used when no template sender email is configured</span>
                </label>
                <input type="email"
                       class="form-control height-35 f-14"
                       id="from_email"
                       name="from_email"
                       value="{{ old('from_email', $setting->from_email) }}"
                       placeholder="{{ config('mail.from.address') }}">
            </div>

            {{-- Unsubscribe Footer --}}
            <div class="form-group">
                <label class="f-14 text-dark-grey mb-1">@lang('Unsubscribe Footer')</label>
                <div class="d-flex align-items-center">
                    <div class="custom-control custom-switch mr-3">
                        <input type="hidden" name="unsubscribe_footer" value="no">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="unsubscribe_footer"
                               name="unsubscribe_footer"
                               value="yes"
                               {{ old('unsubscribe_footer', $setting->unsubscribe_footer) === 'yes' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="unsubscribe_footer">
                            @lang('Append unsubscribe link to every campaign email')
                        </label>
                    </div>
                </div>
            </div>

            {{-- Footer Text --}}
            <div class="form-group">
                <label class="f-14 text-dark-grey mb-1 w-100" for="footer_text">
                    @lang('Footer Text')
                    <span class="text-muted f-12"> — optional custom text shown below the unsubscribe link</span>
                </label>
                <textarea class="form-control f-14"
                          id="footer_text"
                          name="footer_text"
                          rows="3"
                          placeholder="© {{ date('Y') }} {{ company()->company_name }}. All rights reserved.">{{ old('footer_text', $setting->footer_text) }}</textarea>
            </div>

            <x-forms.button-cancel :link="route('crm-email-settings.index')" class="mr-3" />
            <button type="submit" id="saveGeneralSettings" class="btn btn-primary">
                <i class="fa fa-check mr-1"></i> @lang('app.save')
            </button>

        </x-form>
    </div>
</div>

@push('scripts')
<script>
    $('#updateCrmEmailGeneralSettings').on('submit', function(e) {
        e.preventDefault();
        $.easyAjax({
            url: $(this).attr('action'),
            type: 'PUT',
            data: $(this).serialize(),
            disableButton: true,
            buttonSelector: '#saveGeneralSettings',
            blockUI: true,
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl;
                }
            }
        });
    });
</script>
@endpush
