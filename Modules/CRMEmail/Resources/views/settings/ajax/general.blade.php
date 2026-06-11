<div class="s-b-n-content p-20">

    <form id="updateCrmEmailGeneralSettings"
          action="{{ route('crm-email-settings.update', 1) }}"
          method="POST">
        @csrf
        @method('PUT')

        {{-- Default Sender Name --}}
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-1 w-100" for="from_name">
                Default Sender Name
                <span class="text-muted f-12 d-block">Used when a campaign template does not define its own sender name.</span>
            </label>
            <input type="text"
                   class="form-control height-35 f-14"
                   id="from_name"
                   name="from_name"
                   value="{{ old('from_name', $setting->from_name) }}"
                   placeholder="{{ company()->company_name }}">
        </div>

        {{-- Default Sender Email --}}
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-1 w-100" for="from_email">
                Default Sender Email
                <span class="text-muted f-12 d-block">Used when a campaign template does not define its own sender email.</span>
            </label>
            <input type="email"
                   class="form-control height-35 f-14"
                   id="from_email"
                   name="from_email"
                   value="{{ old('from_email', $setting->from_email) }}"
                   placeholder="{{ config('mail.from.address') }}">
        </div>

        {{-- Unsubscribe Footer Toggle --}}
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-1">Unsubscribe Footer</label>
            <div class="d-flex align-items-center">
                <input type="hidden" name="unsubscribe_footer" value="no">
                <div class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="unsubscribe_footer"
                           name="unsubscribe_footer"
                           value="yes"
                           {{ old('unsubscribe_footer', $setting->unsubscribe_footer) === 'yes' ? 'checked' : '' }}>
                    <label class="custom-control-label f-14" for="unsubscribe_footer">
                        Append an unsubscribe link footer to every campaign email
                    </label>
                </div>
            </div>
        </div>

        {{-- Footer Text --}}
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-1 w-100" for="footer_text">
                Footer Text
                <span class="text-muted f-12 d-block">Optional text shown below the unsubscribe link (e.g. company address).</span>
            </label>
            <textarea class="form-control f-14"
                      id="footer_text"
                      name="footer_text"
                      rows="3"
                      placeholder="© {{ date('Y') }} {{ company()->company_name }}. All rights reserved.">{{ old('footer_text', $setting->footer_text) }}</textarea>
        </div>

        <button type="submit" id="saveCrmEmailGeneralSettings" class="btn btn-primary f-14">
            <i class="fa fa-check mr-1"></i> Save Settings
        </button>

    </form>

</div>

<script>
    $('#updateCrmEmailGeneralSettings').on('submit', function (e) {
        e.preventDefault();
        $.easyAjax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            disableButton: true,
            buttonSelector: '#saveCrmEmailGeneralSettings',
            blockUI: true,
            success: function (response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl;
                }
            }
        });
    });
</script>
