<div class="s-b-n-content p-20">

    <form id="updateCrmEmailSendingSettings"
          action="{{ route('crm-email-settings.update', 1) }}"
          method="POST">
        @csrf
        @method('PUT')

        {{-- Throttle Per Minute --}}
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-1 w-100" for="throttle_per_minute">
                Email Throttle (emails per minute)
                <span class="text-muted f-12 d-block">
                    Maximum campaign emails dispatched per minute.
                    Lower this if your SMTP provider enforces rate limits.
                </span>
            </label>
            <div class="input-group" style="max-width: 260px;">
                <input type="number"
                       class="form-control height-35 f-14"
                       id="throttle_per_minute"
                       name="throttle_per_minute"
                       min="1"
                       max="1000"
                       value="{{ old('throttle_per_minute', $setting->throttle_per_minute ?? 60) }}">
                <div class="input-group-append">
                    <span class="input-group-text f-12">/ min</span>
                </div>
            </div>
        </div>

        {{-- Track Opens --}}
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-1">Track Email Opens</label>
            <div class="custom-control custom-switch">
                <input type="hidden" name="track_opens" value="no">
                <input type="checkbox"
                       class="custom-control-input"
                       id="track_opens"
                       name="track_opens"
                       value="yes"
                       {{ old('track_opens', $setting->track_opens) === 'yes' ? 'checked' : '' }}>
                <label class="custom-control-label f-14" for="track_opens">
                    Insert a tracking pixel to detect when recipients open the email
                </label>
            </div>
        </div>

        {{-- Track Clicks --}}
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-1">Track Link Clicks</label>
            <div class="custom-control custom-switch">
                <input type="hidden" name="track_clicks" value="no">
                <input type="checkbox"
                       class="custom-control-input"
                       id="track_clicks"
                       name="track_clicks"
                       value="yes"
                       {{ old('track_clicks', $setting->track_clicks) === 'yes' ? 'checked' : '' }}>
                <label class="custom-control-label f-14" for="track_clicks">
                    Rewrite links in campaign emails to track click-through rates
                </label>
            </div>
        </div>

        <div class="alert alert-info f-12 mt-3">
            <i class="fa fa-info-circle mr-1"></i>
            <strong>Sender name &amp; email</strong> per individual campaign can also be configured in the Email Template.
            Values from the <em>General</em> tab are used only when a template doesn't define its own sender.
        </div>

        <button type="submit" id="saveCrmEmailSendingSettings" class="btn btn-primary f-14">
            <i class="fa fa-check mr-1"></i> Save Settings
        </button>

    </form>

</div>

<script>
    $('#updateCrmEmailSendingSettings').on('submit', function (e) {
        e.preventDefault();
        $.easyAjax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            disableButton: true,
            buttonSelector: '#saveCrmEmailSendingSettings',
            blockUI: true,
            success: function (response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl;
                }
            }
        });
    });
</script>
