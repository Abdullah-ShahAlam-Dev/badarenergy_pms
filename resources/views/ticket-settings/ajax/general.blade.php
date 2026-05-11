<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    <x-form id="editSettings" method="PUT">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group my-3">
                    <x-forms.label fieldId="ticket_closing_restriction"
                        :fieldLabel="__('modules.tickets.closingRestriction')"
                        :fieldHelp="__('modules.tickets.closingRestrictionHelp')">
                    </x-forms.label>
                    <div class="d-flex">
                        <x-forms.radio fieldId="closing-restriction-enabled" :fieldLabel="__('app.enabled')" fieldValue="enabled"
                            fieldName="ticket_closing_restriction" :checked="company()->ticket_closing_restriction == 'enabled'">
                        </x-forms.radio>
                        <x-forms.radio fieldId="closing-restriction-disabled" :fieldLabel="__('app.disabled')" fieldValue="disabled"
                            fieldName="ticket_closing_restriction" :checked="company()->ticket_closing_restriction == 'disabled'">
                        </x-forms.radio>
                    </div>
                </div>
            </div>
        </div>

        <x-setting-form-actions>
            <x-forms.button-primary id="save-general-settings" class="mr-3" icon="check">@lang('app.save')
            </x-forms.button-primary>
        </x-setting-form-actions>
    </x-form>
</div>

<script>
    $('#save-general-settings').click(function() {
        var url = "{{ route('ticket-settings.update', company()->id) }}";

        $.easyAjax({
            url: url,
            container: '#editSettings',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-general-settings",
            data: $('#editSettings').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });
</script>
