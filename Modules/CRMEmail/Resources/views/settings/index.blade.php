@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu"/>

        <x-setting-card>

            <x-slot name="header">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    CRM Email Settings
                </h4>
            </x-slot>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 general {{ $activeTab === 'general' ? 'active' : '' }}"
                               href="{{ route('crm-email-settings.index') }}"
                               role="tab" aria-controls="nav-crm-email-setting" aria-selected="true"
                               ajax="false">General
                            </a>

                            <a class="nav-item nav-link f-15 email {{ $activeTab === 'email' ? 'active' : '' }}"
                               href="{{ route('crm-email-settings.index') }}?tab=email"
                               role="tab" aria-controls="nav-crm-email-setting" aria-selected="false"
                               ajax="false">Email &amp; Sending
                            </a>
                        </div>
                    </nav>
                </div>
            </x-slot>

            {{-- Tab content rendered server-side --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
<script>
    $('.nav-item').removeClass('active');
    const activeTab = "{{ $activeTab }}";
    $('.' + activeTab).addClass('active');

    $(document).on('click', '#nav-tab .nav-item', function (event) {
        event.preventDefault();

        $('.nav-item').removeClass('active');
        $(this).addClass('active');

        const requestUrl = this.href;

        $.easyAjax({
            url: requestUrl,
            blockUI: true,
            container: '#nav-tabContent',
            historyPush: true,
            success: function (response) {
                if (response.status === 'success') {
                    $('#nav-tabContent .flex-wrap').html(response.html);
                    init('#nav-tabContent');
                }
            }
        });
    });
</script>
@endpush
