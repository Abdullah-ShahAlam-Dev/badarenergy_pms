<div class="card" id="email-panel">
    <div class="card-header">
        <h4 class="card-title">@lang('Email & Sending Settings')</h4>
    </div>

    <div class="card-body">
        <x-form id="updateCrmEmailSendingSettings" method="PUT" :action="route('crm-email-settings.update', 1)">

            {{-- Throttle Per Minute --}}
            <div class="form-group">
                <label class="f-14 text-dark-grey mb-1 w-100" for="throttle_per_minute">
                    @lang('Email Throttle (per minute)')
                </label>
                <p class="text-muted f-12 mb-2">
                    Controls the maximum number of campaign emails dispatched per minute.
                    Lower this value if your SMTP provider enforces rate limits.
                </p>
                <div class="input-group" style="max-width: 250px;">
                    <input type="number"
                           class="form-control height-35 f-14"
                           id="throttle_per_minute"
                           name="throttle_per_minute"
                           min="1"
                           max="1000"
                           value="{{ old('throttle_per_minute', $setting->throttle_per_minute ?? 60) }}">
                    <div class="input-group-append">
                        <span class="input-group-text f-12">emails / min</span>
                    </div>
                </div>
            </div>

            {{-- Track Opens --}}
            <div class="form-group">
                <label class="f-14 text-dark-grey mb-1">@lang('Track Email Opens')</label>
                <div class="custom-control custom-switch">
                    <input type="hidden" name="track_opens" value="no">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="track_opens"
                           name="track_opens"
                           value="yes"
                           {{ old('track_opens', $setting->track_opens) === 'yes' ? 'checked' : '' }}>
                    <label class="custom-control-label" for="track_opens">
                        @lang('Insert a tracking pixel to detect when recipients open the email')
                    </label>
                </div>
            </div>

            {{-- Track Clicks --}}
            <div class="form-group">
                <label class="f-14 text-dark-grey mb-1">@lang('Track Link Clicks')</label>
                <div class="custom-control custom-switch">
                    <input type="hidden" name="track_clicks" value="no">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="track_clicks"
                           name="track_clicks"
                           value="yes"
                           {{ old('track_clicks', $setting->track_clicks) === 'yes' ? 'checked' : '' }}>
                    <label class="custom-control-label" for="track_clicks">
                        @lang('Rewrite links in campaign emails to track click-through rates')
                    </label>
                </div>
            </div>

            <div class="alert alert-info f-12 mt-3">
                <i class="fa fa-info-circle mr-1"></i>
                The <strong>sender email / name</strong> for individual campaigns is also configurable per Email Template.
                The values on the <em>General Settings</em> tab are used only when a template does not define its own sender.
            </div>

            <x-forms.button-cancel :link="route('crm-email-settings.index', ['tab' => 'email'])" class="mr-3" />
            <button type="submit" id="saveSendingSettings" class="btn btn-primary">
                <i class="fa fa-check mr-1"></i> @lang('app.save')
            </button>

        </x-form>
    </div>
</div>

@push('scripts')
<script>
    $('#updateCrmEmailSendingSettings').on('submit', function(e) {
        e.preventDefault();
        $.easyAjax({
            url: $(this).attr('action'),
            type: 'PUT',
            data: $(this).serialize(),
            disableButton: true,
            buttonSelector: '#saveSendingSettings',
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
