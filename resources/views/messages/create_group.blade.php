<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">Create Message Group</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createGroupForm">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <x-forms.text fieldId="group_name" :fieldLabel="__('modules.projects.projectName') . ' / Group Title'"
                        fieldName="group_name" fieldPlaceholder="Enter group title" fieldRequired="true">
                    </x-forms.text>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <x-forms.select fieldId="selectGroupMembers" :fieldLabel="__('modules.messages.chooseMember') . 's'"
                        fieldName="user_ids[]" search="true" fieldRequired="true" :multiple="true">
                        @foreach ($employees as $item)
                            <x-user-option :user="$item" :pill="true"/>
                        @endforeach
                    </x-forms.select>
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-group-btn" icon="check">Create Group</x-forms.button-primary>
</div>

<script>
    $('#selectGroupMembers').selectpicker({
        actionsBox: true,
        selectAllText: "@lang('placeholders.selectAllText')",
        deselectAllText: "@lang('placeholders.deselectAllText')"
    });

    $('#save-group-btn').click(function() {
        var url = "{{ route('message-groups.store') }}";
        $.easyAjax({
            url: url,
            container: '#createGroupForm',
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-group-btn",
            type: "POST",
            data: $('#createGroupForm').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    $(MODAL_LG).modal('hide');
                    // Reload the conversation user/group list in the sidebar
                    fetchUserList();
                }
            }
        })
    });

    init('#createGroupForm');
</script>
