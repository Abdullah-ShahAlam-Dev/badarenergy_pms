@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>

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
