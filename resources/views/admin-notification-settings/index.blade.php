@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card method="POST">
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h3 class="mb-0 p-20 f-21 font-weight-normal text-capitalize">
                        <i class="fa fa-bell mr-2 text-primary"></i>
                        @lang('app.menu.adminNotificationSettings')
                    </h3>
                </div>
            </x-slot>

            @include('admin-notification-settings.ajax.index')

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        $(function () {
            $('[data-toggle="popover"]').popover();
        });

        // Live preview: update the status indicator when the dropdown changes
        $('body').on('change', '.admin-notif-select', function () {
            const val = $(this).val();
            const row = $(this).closest('tr.notification-row');
            const dot = row.find('.fa-circle');
            const badge = row.find('.badge');

            if (val === 'involved') {
                dot.removeClass('text-success').addClass('text-primary');
                if (!badge.length) {
                    row.find('.d-flex').append('<span class="badge badge-info ml-2 f-11">Smart Filter</span>');
                }
            } else {
                dot.removeClass('text-primary').addClass('text-success');
                row.find('.badge').remove();
            }
        });

        // Prevent selecting same user in both bypass and block lists
        $('body').on('change', 'select[name^="allowed_admin_ids"], select[name^="blocked_admin_ids"]', function () {
            const row = $(this).closest('tr');
            const bypassSelect = row.find('select[name^="allowed_admin_ids"]');
            const blockSelect = row.find('select[name^="blocked_admin_ids"]');
            
            const bypassVals = bypassSelect.val() || [];
            const blockVals = blockSelect.val() || [];

            // If we just changed bypass, remove those users from block
            if ($(this).is(bypassSelect)) {
                const newBlockVals = blockVals.filter(v => !bypassVals.includes(v));
                if (newBlockVals.length !== blockVals.length) {
                    blockSelect.val(newBlockVals).selectpicker('refresh');
                }
            } 
            // If we just changed block, remove those users from bypass
            else {
                const newBypassVals = bypassVals.filter(v => !blockVals.includes(v));
                if (newBypassVals.length !== bypassVals.length) {
                    bypassSelect.val(newBypassVals).selectpicker('refresh');
                }
            }

            // Optional: Visually disable the options in the other list
            bypassSelect.find('option').prop('disabled', false);
            blockSelect.find('option').prop('disabled', false);

            bypassVals.forEach(v => blockSelect.find(`option[value="${v}"]`).prop('disabled', true));
            blockVals.forEach(v => bypassSelect.find(`option[value="${v}"]`).prop('disabled', true));

            bypassSelect.selectpicker('refresh');
            blockSelect.selectpicker('refresh');
        });

        // Save
        $('body').on('click', '#save-admin-notif-btn', function () {
            $.easyAjax({
                url: "{{ route('admin-notification-settings.update') }}",
                type: "POST",
                container: '#editSettings',
                blockUI: true,
                messagePosition: "inline",
                data: $('#editSettings').serialize(),
            });
        });
    </script>
@endpush
