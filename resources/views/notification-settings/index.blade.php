@extends('layouts.app')

@section('content')

    <style>
        .ntfcn-tab-content-right {
            margin-top: 0 !important;
        }
        .ntfcn-tab-content-right h4 {
            height: auto !important;
            margin-bottom: 1.5rem !important;
        }
    </style>

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs" style="position: relative; z-index: 10;">
                    <nav class="tabs border-bottom-grey">
                        <ul class="nav -primary" id="nav-tab" role="tablist">
                            <li>
                                <a class="nav-item nav-link f-15 active email-setting"
                                    href="{{ route('notifications.index') }}" role="tab" aria-controls="nav-ticketAgents"
                                    aria-selected="true">@lang('app.email')
                                </a>
                            </li>
                            <li>
                                <a class="nav-item nav-link f-15 email-templates"
                                    href="{{ route('notifications.index') }}?tab=email-templates" role="tab" aria-controls="nav-emailTemplates"
                                    aria-selected="false" ajax="false">Email Templates
                                </a>
                            </li>
                            <li>
                                <a class="nav-item nav-link f-15 slack-setting"
                                    href="{{ route('notifications.index') }}?tab=slack-setting" role="tab"
                                    aria-controls="nav-ticketTypes" aria-selected="true" ajax="false">@lang('app.slack') <i
                                    class="fa fa-circle ml-1 {{ $slackSettings->status == 'active' ? 'text-light-green' : 'text-red' }}"></i>
                                </a>
                            </li>
                            <li>
                                <a class="nav-item nav-link f-15 push-notification-setting"
                                    href="{{ route('notifications.index') }}?tab=push-notification-setting" role="tab"
                                    aria-controls="nav-ticketTypes" aria-selected="true"
                                    ajax="false">@lang('app.pushNotification')<i
                                    class="fa fa-circle ml-1 {{ $pushSettings->status == 'active' ? 'text-light-green' : 'text-red' }}"></i>
                                </a>
                            </li>
                            <li>
                                <a class="nav-item nav-link f-15 pusher-setting"
                                    href="{{ route('notifications.index') }}?tab=pusher-setting" role="tab"
                                    aria-controls="nav-ticketTypes" aria-selected="true"
                                    ajax="false">@lang('app.menu.pusherSettings')<i
                                    id="pusher-setting-tab" class="fa fa-circle ml-1 {{ $pusherSettings->status == 1 ? 'text-light-green' : 'text-red' }}"></i>
                                </a>
                            </li>
                            <li>
                                <a class="nav-item nav-link f-15 whatsapp-setting"
                                    href="{{ route('notifications.index') }}?tab=whatsapp-setting" role="tab"
                                    aria-controls="nav-whatsappSetting" aria-selected="false"
                                    ajax="false">WhatsApp<i
                                    class="fa fa-circle ml-1 {{ isset($whatsappSetting) && $whatsappSetting->status == 'active' ? 'text-light-green' : 'text-red' }}"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </x-slot>

            {{-- include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        /* manage menu active class */
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

       $("body").on("click", "#editSettings .nav a", function(event) {
            event.preventDefault();

            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: true,
                success: function(response) {
                    if (response.status === "success") {
                        $('#nav-tabContent .flex-wrap').html('');
                        $('#nav-tabContent .flex-wrap').html(response.html);
                        init('#nav-tabContent');
                    }
                }
            });
        });

        $(document).on('change', '#pusher_status', function() {
            $('.pusher_details').toggleClass('d-none');
        });

        $(document).on('change', '#slack_status', function() {
            $('.slack_details').toggleClass('d-none');
        });

        $(document).on('change', '#push_status', function() {
            $('.push_details').toggleClass('d-none');
        });
    </script>

    <script>
        /*******************************************************
         More btn in projects menu Start
         *******************************************************/

        const container = document.querySelector('.tabs');
        const primary = container.querySelector('.-primary');
        const primaryItems = container.querySelectorAll('.-primary > li:not(.-more)');
        container.classList.add('--jsfied'); // insert "more" button and duplicate the list

        primary.insertAdjacentHTML('beforeend', `
        <li class="-more bg-grey">
            <button type="button" class="px-4 h-100 w-100 d-lg-flex d-md-flex align-items-center justify-content-center" aria-haspopup="true" aria-expanded="false">
            More <span>&darr;</span>
            </button>
            <ul class="-secondary" id="hide-project-menues">
            ${primary.innerHTML}
            </ul>
        </li>
        `);
        const secondary = container.querySelector('.-secondary');
        const secondaryItems = secondary.querySelectorAll('li');
        const allItems = container.querySelectorAll('li');
        const moreLi = primary.querySelector('.-more');
        const moreBtn = moreLi.querySelector('button');
        moreBtn.addEventListener('click', e => {
            e.preventDefault();
            container.classList.toggle('--show-secondary');
            moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'));
        }); // adapt tabs

        const doAdapt = () => {
            // reveal all items for the calculation
            allItems.forEach(item => {
                item.classList.remove('--hidden');
            }); // hide items that won't fit in the Primary

            let stopWidth = moreBtn.offsetWidth;
            let hiddenItems = [];
            const primaryWidth = primary.offsetWidth;
            primaryItems.forEach((item, i) => {
                if (primaryWidth >= stopWidth + item.offsetWidth) {
                    stopWidth += item.offsetWidth;
                } else {
                    item.classList.add('--hidden');
                    hiddenItems.push(i);
                }
            }); // toggle the visibility of More button and items in Secondary

            if (!hiddenItems.length) {
                moreLi.classList.add('--hidden');
                container.classList.remove('--show-secondary');
                moreBtn.setAttribute('aria-expanded', false);
            } else {
                secondaryItems.forEach((item, i) => {
                    if (!hiddenItems.includes(i)) {
                        item.classList.add('--hidden');
                    }
                });
            }
        };

        doAdapt(); // adapt immediately on load

        window.addEventListener('resize', doAdapt); // adapt on window resize
        // hide Secondary on the outside click

        document.addEventListener('click', e => {
            let el = e.target;

            while (el) {
                if (el === secondary || el === moreBtn) {
                    return;
                }

                el = el.parentNode;
            }

            container.classList.remove('--show-secondary');
            moreBtn.setAttribute('aria-expanded', false);
        });
        /*******************************************************
         More btn in projects menu End
         *******************************************************/
    </script>
@endpush
