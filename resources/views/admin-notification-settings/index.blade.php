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
                data: $('#editSettings').serialize(),
            });
        });
    </script>
@endpush
