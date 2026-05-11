<div class="col-lg-12 p-4">

    {{-- Page description --}}
    <x-alert type="info" icon="bell">
        Control which administrators receive notifications for each event type.
        Setting a notification to <strong>Only Involved Admins</strong> means only admins who are directly assigned,
        mentioned, or managing that specific record will be notified stopping the CEO and other global admins
        from receiving notifications for tasks and events they are not involved in.
    </x-alert>

    <form id="adminNotifForm">
        @csrf

        <div class="table-responsive mt-4">
            <table class="table custom-table-border text-left">
                <thead>
                    <tr class="bg-amt-grey">
                        <th class="f-13 py-3 pl-3" width="40%">Notification Event</th>
                        <th class="f-13 py-3 text-center" width="20%">
                            Behavior
                            <i class="fa fa-question-circle text-lightest f-11 ml-1" 
                               data-toggle="popover" data-placement="top" data-trigger="hover"
                               data-content="Select 'All Admins' to notify everyone in the admin list. Select 'Involved Admins' to notify only those linked to the record (Assignees, Mentioned users, etc.)."></i>
                        </th>
                        <th class="f-13 py-3" width="40%">
                            Restrict To (Optional)
                            <i class="fa fa-question-circle text-lightest f-11 ml-1" 
                               data-toggle="popover" data-placement="top" data-trigger="hover"
                               data-content="Optional: Choose specific admins to notify. If you select someone here, ONLY they will receive the notification (further filtered by the Behavior selection)."></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($emailSettings as $emailSetting)
                        <tr class="notification-row">
                            <td class="pl-3 py-3 f-14 text-dark">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-circle f-10 mr-2 {{ $emailSetting->send_to_admins == 'involved' ? 'text-primary' : 'text-success' }}"></i>
                                    @php
                                        $slug = str_slug($emailSetting->setting_name);
                                        $translatedName = __('modules.emailNotification.' . $slug);
                                        // Fallback if translation is missing or returns the key
                                        if ($translatedName == 'modules.emailNotification.' . $slug) {
                                            $translatedName = str_replace(['-', '_'], ' ', $emailSetting->setting_name);
                                        }
                                    @endphp
                                    {{ $translatedName }}
                                    @if($emailSetting->send_to_admins == 'involved')
                                        <span class="badge badge-info ml-2 f-11">Smart Filter</span>
                                    @endif
                                </div>
                                <p class="f-11 text-lightest mb-0 mt-1 pl-3">
                                    @if($emailSetting->send_to_admins == 'all')
                                        All admins will receive this notification.
                                    @else
                                        Only admins directly involved in the record will be notified.
                                    @endif
                                </p>
                            </td>
                            <td class="py-3 text-center">
                                <select class="form-control select-picker admin-notif-select"
                                        name="send_to_admins[{{ $emailSetting->id }}]"
                                        id="send_to_admins_{{ $emailSetting->id }}"
                                        data-row="{{ $emailSetting->id }}">
                                    <option value="all" data-content="<i class='fa fa-users mr-2'></i> All Admins" @if($emailSetting->send_to_admins == 'all') selected @endif>
                                        All Admins
                                    </option>
                                    <option value="involved" data-content="<i class='fa fa-filter mr-2'></i> Involved Admins" @if($emailSetting->send_to_admins == 'involved') selected @endif>
                                        Involved Admins
                                    </option>
                                </select>
                            </td>
                            <td class="py-3">
                                <select class="form-control select-picker"
                                        name="allowed_admin_ids[{{ $emailSetting->id }}][]"
                                        multiple
                                        data-actions-box="true"
                                        data-live-search="true"
                                        data-size="5"
                                        title="Everyone (Default)">
                                    @php
                                        $selectedAdmins = json_decode($emailSetting->allowed_admin_ids) ?: [];
                                    @endphp
                                    @foreach($allAdmins as $admin)
                                        <option value="{{ $admin->id }}" 
                                                data-content="<div class='d-flex align-items-center'><img src='{{ $admin->image_url }}' class='mr-2 taskEmployeeImg rounded-circle'> {{ $admin->name }}</div>"
                                                @if(in_array($admin->id, $selectedAdmins)) selected @endif>
                                            {{ $admin->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </form>

</div>

<!-- Buttons -->
<div class="w-100 border-top-grey set-btns">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-admin-notif-btn" class="mr-3" icon="check">
            @lang('app.save')
        </x-forms.button-primary>
    </x-setting-form-actions>
</div>

<script>
    $(function () {
        $('[data-toggle="popover"]').popover();
    });

    // Live preview: update the description text when the dropdown changes
    $('body').on('change', '.admin-notif-select', function () {
        const val = $(this).val();
        const row = $(this).closest('tr.notification-row');
        const dot = row.find('.fa-circle');
        const badge = row.find('.badge');
        const desc = row.find('p');

        if (val === 'involved') {
            dot.removeClass('text-success').addClass('text-primary');
            desc.text('Only admins directly involved in the record will be notified.');
            if (!badge.length) {
                row.find('.d-flex').append('<span class="badge badge-info ml-2 f-11">Smart Filter</span>');
            }
        } else {
            dot.removeClass('text-primary').addClass('text-success');
            desc.text('All admins will receive this notification.');
            row.find('.badge').remove();
        }
    });

    // Save
    $('body').on('click', '#save-admin-notif-btn', function () {
        $.easyAjax({
            url: "{{ route('admin-notification-settings.update') }}",
            type: "POST",
            container: '#editSettings',
            blockUI: true,
            messagePosition: "inline",
            data: $('#adminNotifForm').serialize(),
        });
    });
</script>
