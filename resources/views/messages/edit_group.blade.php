<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">Manage Group Members</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="editGroupMembersForm">
        @method('PUT')
        <div class="row">
            <div class="col-md-12 mb-3">
                <p class="f-13 text-dark-grey font-weight-bold mb-0">Group Title:</p>
                <p class="f-15 text-indigo font-weight-normal mb-0">{{ $group->group_name }}</p>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <x-forms.select fieldId="selectGroupMembersEdit" :fieldLabel="__('modules.messages.chooseMember') . 's'"
                        fieldName="user_ids[]" search="true" fieldRequired="true" :multiple="true">
                        @foreach ($employees as $item)
                            <x-user-option :user="$item" :pill="true" :selected="in_array($item->id, $currentMemberIds)"/>
                        @endforeach
                    </x-forms.select>
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-group-members-btn" icon="check">Save Members</x-forms.button-primary>
</div>

<script>
    $('#selectGroupMembersEdit').selectpicker();

    $('#save-group-members-btn').click(function() {
        var url = "{{ route('message-groups.update', $group->id) }}";
        $.easyAjax({
            url: url,
            container: '#editGroupMembersForm',
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-group-members-btn",
            type: "POST",
            data: $('#editGroupMembersForm').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    $(MODAL_LG).modal('hide');
                    // Reload conversation list and fetch messages to see updated system message
                    fetchUserList();
                    fetchUserMessages();
                }
            }
        })
    });

    init('#editGroupMembersForm');
</script>
