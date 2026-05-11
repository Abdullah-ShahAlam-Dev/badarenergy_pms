<div class="col-lg-12 p-4">

    {{-- Page description --}}
    <x-alert type="info" icon="info-circle">
        Use these rules to stop notification fatigue for executives.
        <ul class="mt-2 mb-0 pl-3">
            <li><strong>Smart Filter</strong>: If enabled, only admins directly involved (Assignees, CCs, etc.) get notified.</li>
            <li><strong>Always Notify (Bypass)</strong>: These admins get the alert even if they aren't involved. (Perfect for the CEO).</li>
            <li><strong>Never Notify (Block)</strong>: These admins are completely silenced for this event, even if they are involved.</li>
        </ul>
    </x-alert>

    <div class="table-responsive mt-4">
            <table class="table custom-table-border text-left">
                <thead>
                    <tr class="bg-amt-grey">
                        <th class="f-13 py-3 pl-3" width="30%">Notification Event</th>
                        <th class="f-13 py-3 text-center" width="16%">
                            Smart Filter
                            <i class="fa fa-question-circle text-lightest f-11 ml-1" 
                               data-toggle="popover" data-placement="top" data-trigger="hover"
                               data-content="Select 'All Admins' to notify everyone. Select 'Involved Admins' to notify only those linked to the record (Assignees, etc.)."></i>
                        </th>
                        <th class="f-13 py-3" width="27%">
                            Always Notify (Bypass)
                            <i class="fa fa-question-circle text-lightest f-11 ml-1" 
                               data-toggle="popover" data-placement="top" data-trigger="hover"
                               data-content="Optional: These admins will ALWAYS receive this notification, even if the Smart Filter would normally block them. Perfect for Main Admins who want to see everything."></i>
                        </th>
                        <th class="f-13 py-3" width="27%">
                            Never Notify (Block)
                            <i class="fa fa-question-circle text-lightest f-11 ml-1" 
                               data-toggle="popover" data-placement="top" data-trigger="hover"
                               data-content="Optional: These admins will NEVER receive this notification, even if they are involved in the task."></i>
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
                            </td>
                            <td class="py-3 text-center">
                                <select class="form-control select-picker admin-notif-select"
                                        name="send_to_admins[{{ $emailSetting->id }}]"
                                        id="send_to_admins_{{ $emailSetting->id }}"
                                        data-row="{{ $emailSetting->id }}">
                                    <option value="all" data-content="<i class='fa fa-users mr-2'></i> All" @if($emailSetting->send_to_admins == 'all') selected @endif>
                                        All
                                    </option>
                                    <option value="involved" data-content="<i class='fa fa-filter mr-2'></i> Involved" @if($emailSetting->send_to_admins == 'involved') selected @endif>
                                        Involved
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
                                        title="No Bypass">
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
                            <td class="py-3">
                                <select class="form-control select-picker"
                                        name="blocked_admin_ids[{{ $emailSetting->id }}][]"
                                        multiple
                                        data-actions-box="true"
                                        data-live-search="true"
                                        data-size="5"
                                        title="None Blocked">
                                    @php
                                        $blockedAdmins = json_decode($emailSetting->blocked_admin_ids) ?: [];
                                    @endphp
                                    @foreach($allAdmins as $admin)
                                        <option value="{{ $admin->id }}" 
                                                data-content="<div class='d-flex align-items-center'><img src='{{ $admin->image_url }}' class='mr-2 taskEmployeeImg rounded-circle'> {{ $admin->name }}</div>"
                                                @if(in_array($admin->id, $blockedAdmins)) selected @endif>
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
</div>

<!-- Buttons -->
<div class="w-100 border-top-grey set-btns">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-admin-notif-btn" class="mr-3" icon="check">
            @lang('app.save')
        </x-forms.button-primary>
    </x-setting-form-actions>
</div>


