<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- PWA -->
    @php
        $pwaSettings = companyOrGlobalSetting();
        // Force fresh database query to bypass session/laravel cache for branding
        $pwaSettings = ($pwaSettings && $pwaSettings->id) ? \App\Models\Company::find($pwaSettings->id) : \App\Models\GlobalSetting::first();
        $pwaFavicon = $pwaSettings->favicon_url ?? asset('favicon.png');
        $pwaAppName = $pwaSettings->app_name ?? ($pwaSettings->global_app_name ?? config('app.name'));
    @endphp
    <link rel="manifest" href="/manifest.json?v={{ optional($pwaSettings)->updated_at?->timestamp ?? time() }}">
    <link rel="apple-touch-icon" href="{{ $pwaFavicon }}?v={{ optional($pwaSettings)->updated_at?->timestamp ?? time() }}">
    <meta name="apple-mobile-web-app-title" content="{{ $pwaAppName }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/png" href="{{ $pwaFavicon }}?v={{ optional($pwaSettings)->updated_at?->timestamp ?? time() }}">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}">

    <!-- Simple Line Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/simple-line-icons.css') }}">

    <!-- Datepicker -->
    <link rel="stylesheet" href="{{ asset('vendor/css/datepicker.min.css') }}">

    <!-- TimePicker -->
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-timepicker.min.css') }}">

    <!-- Select Plugin -->
    <link rel="stylesheet" href="{{ asset('vendor/css/select2.min.css') }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-icons.css') }}">

    @stack('datatable-styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.bootstrap4.min.css') }}">

    <!-- Template CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ mix('css/main.css') }}">

    <title>@lang($pageTitle)</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <!-- <meta name="msapplication-TileImage" content="{{ companyOrGlobalSetting()->favicon_url }}"> -->
    <meta name="theme-color" content="#ffffff">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @if(isset($activeSettingMenu) || request()->routeIs('dashboard*') || request()->routeIs('tasks.show') || request()->routeIs('tasks.edit') || request()->routeIs('tickets.*'))
        <meta name="turbo-visit-control" content="reload">
        <meta name="turbo-cache-control" content="no-cache">
        <style>
            .preloader-container {
                margin-left: 510px;
                width: calc(100% - 510px)
            }

            .blur-code {
                filter: blur(3px);

            }

            .purchase-code {
                transition: filter .2s ease-out;
                margin-right: 4px;
            }
        </style>
    @endisset

    @stack('styles')

    <style>
        :root {
            --fc-border-color: #E8EEF3;
            --fc-button-text-color: #99A5B5;
            --fc-button-border-color: #99A5B5;
            --fc-button-bg-color: #ffffff;
            --fc-button-active-bg-color: #171f29;
            --fc-today-bg-color: #f2f4f7;
        }

        .fc a[data-navlink] {
            color: #99a5b5;
        }
        .ql-editor p{
            line-height: 1.42;
        }
    </style>

    {{-- Custom theme styles --}}
    @if (!user()->dark_theme)
        @include('sections.theme_css')
    @endif

    @if (file_exists(public_path() . '/css/app-custom.css'))
        <link href="{{ asset('css/app-custom.css') }}" rel="stylesheet">
    @endif

    <style>
        /* Hide preloader when turbo handles the page */
        html[data-turbo-preview] .preloader-container {
            display: none !important;
        }

        .notice-popup-container {
            max-height: 450px;
            overflow-y: auto;
            text-align: left;
            padding: 10px;
            border-top: 1px solid #f1f1f1;
            margin-top: 15px;
        }

        .notice-popup-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .notice-swal-title {
            font-size: 20px !important;
            font-weight: 600 !important;
            color: #28313c !important;
        }

        .notice-swal-popup {
            border-radius: 12px !important;
            padding-bottom: 20px !important;
        }

        .notice-swal-container .swal2-icon {
            border: none !important;
            font-size: 24px !important;
        }

        .notice-popup-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
    </style>

    {{-- Disable Turbo Caching globally to prevent zombie JS objects --}}
    <meta name="turbo-cache-control" content="no-cache">
    <script src="https://unpkg.com/@hotwired/turbo@8.0.4/dist/turbo.es2017-umd.js"></script>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/modernizr.min.js') }}"></script>

    {{-- Timepicker --}}
    <script src="{{ asset('vendor/jquery/bootstrap-timepicker.min.js') }}"></script>

    @includeif('sections.push-setting-include')

    {{-- Include file for widgets if exist --}}
    @includeif('sections.custom_script')


    <script>
        var checkMiniSidebar = localStorage.getItem("mini-sidebar");
    </script>

</head>


<body id="body" class="{{ user()->dark_theme ? 'dark-theme' : '' }} {{ user()->rtl ? 'rtl' : '' }}">
<script>
    if (checkMiniSidebar == "yes" || checkMiniSidebar == "") {
        $('body').addClass('sidebar-toggled');
    }
</script>
{{-- include topbar --}}
@include('sections.topbar')

{{-- include sidebar menu --}}
@include('sections.sidebar')

<!-- BODY WRAPPER START -->
<div class="body-wrapper clearfix">


    <!-- MAIN CONTAINER START -->
    <section class="main-container bg-additional-grey mb-5 mb-sm-0" id="fullscreen" @if(isset($activeSettingMenu) || request()->routeIs('dashboard*')) data-turbo="false" @endif>

        <div class="preloader-container d-flex justify-content-center align-items-center">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
        </div>

        @yield('filter-section')

        <x-app-title class="d-block d-lg-none" :pageTitle="__($pageTitle)"></x-app-title>

        @yield('content')

    </section>
    <!-- MAIN CONTAINER END -->
</div>
<!-- BODY WRAPPER END -->
@include('sections.modals')

<!-- Global Required Javascript -->
<script src="{{ mix('js/main.js') }}"></script>
<script>
    // Translation of default values for the select picker box.
    $.fn.selectpicker.Constructor.DEFAULTS.noneSelectedText = "@lang('placeholders.noneSelectedText')";
    $.fn.selectpicker.Constructor.DEFAULTS.noneResultsText = "@lang('placeholders.noneResultsText')";
    $.fn.selectpicker.Constructor.DEFAULTS.selectAllText = "@lang('placeholders.selectAllText')";
    $.fn.selectpicker.Constructor.DEFAULTS.deselectAllText = "@lang('placeholders.deselectAllText')";

    var MODAL_DEFAULT = '#myModalDefault';
    var MODAL_LG = '#myModal';
    var MODAL_XL = '#myModalXl';
    var MODAL_HEADING = '#modelHeading';
    var RIGHT_MODAL = '#task-detail-1';
    var RIGHT_MODAL_CONTENT = '#right-modal-content';
    var RIGHT_MODAL_TITLE = '#right-modal-title';
    var company = @json(companyOrGlobalSetting());
    var pusher_setting = @json(pusher_settings());
    var message_setting = @json(message_setting());
    var SEARCH_KEYWORD = "{{ request('search_keyword') }}";
    var MOMENTJS_TIME_FORMAT = "{{ (companyOrGlobalSetting()->time_format == 'h:i A') ? 'hh:mm A' : ( (companyOrGlobalSetting()->time_format == 'h:i a') ? 'hh:mm a' : 'H:mm') }}";

    var datepickerConfig = {
        formatter: (input, date, instance) => {
            input.value = moment(date).format('{{ companyOrGlobalSetting()->moment_date_format }}')
        },
        showAllDates: true,
        customDays: {!!  json_encode(\App\Models\GlobalSetting::getDaysOfWeek())!!},
        customMonths: {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        customOverlayMonths: {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        overlayButton: "@lang('app.submit')",
        overlayPlaceholder: "@lang('app.enterYear')",
        startDay: parseInt("{{ attendance_setting()?->week_start_from }}")
    };

    var daterangeConfig = {
        "@lang('app.today')": [moment(), moment()],
        "@lang('app.last30Days')": [moment().subtract(29, 'days'), moment()],
        "@lang('app.thisMonth')": [moment().startOf('month'), moment().endOf('month')],
        "@lang('app.lastMonth')": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        "@lang('app.last90Days')": [moment().subtract(89, 'days'), moment()],
        "@lang('app.last6Months')": [moment().subtract(6, 'months'), moment()],
        "@lang('app.last1Year')": [moment().subtract(1, 'years'), moment()]
    };

    var daterangeLocale = {
        "format": "{{ companyOrGlobalSetting()->moment_date_format }}",
        "customRangeLabel": "@lang('app.customRange')",
        "separator": " @lang('app.to') ",
        "applyLabel": "@lang('app.apply')",
        "cancelLabel": "@lang('app.cancel')",
        "monthNames": {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        "daysOfWeek": {!!  json_encode(\App\Models\GlobalSetting::getDaysOfWeek())!!},
        "firstDay": parseInt("{{ attendance_setting()?->week_start_from }}")
    };

    var dropifyMessages = {
        default: "@lang('app.dragDrop')",
        replace: "@lang('app.dragDropReplace')",
        remove: "@lang('app.remove')",
        error: "@lang('messages.errorOccured')",
    };

    var DROPZONE_FILE_ALLOW = "{{ global_setting()->allowed_file_types }}";
    var DROPZONE_MAX_FILESIZE = "{{ global_setting()->allowed_file_size }}";
    var DROPZONE_MAX_FILES = "{{ global_setting()->allow_max_no_of_files }}";

    Dropzone.prototype.defaultOptions.dictFallbackMessage = "{{ __('modules.projectTemplate.dropFallbackMessage') }}";
    Dropzone.prototype.defaultOptions.dictFallbackText = "{{ __('modules.projectTemplate.dropFallbackText') }}";
    Dropzone.prototype.defaultOptions.dictFileTooBig = "{{ __('modules.projectTemplate.dropFileTooBig') }}";
    Dropzone.prototype.defaultOptions.dictInvalidFileType = "{{ __('modules.projectTemplate.dropInvalidFileType') }}";
    Dropzone.prototype.defaultOptions.dictResponseError = "{{ __('modules.projectTemplate.dropResponseError') }}";
    Dropzone.prototype.defaultOptions.dictCancelUpload = "{{ __('modules.projectTemplate.dropCancelUpload') }}";
    Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = "{{ __('modules.projectTemplate.dropCancelUploadConfirmation') }}";
    Dropzone.prototype.defaultOptions.dictRemoveFile = "{{ __('modules.projectTemplate.dropRemoveFile') }}";
    Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "{{ __('modules.projectTemplate.dropMaxFilesExceeded') }}";
    Dropzone.prototype.defaultOptions.dictDefaultMessage = "{{ __('modules.projectTemplate.dropFile') }}";
    Dropzone.prototype.defaultOptions.timeout = 0;

    $('#datatableRange').on('apply.daterangepicker', (event, picker) => {
        cb(picker.startDate, picker.endDate);
        $('#datatableRange').val(picker.startDate.format('{{ companyOrGlobalSetting()->moment_date_format }}') +
            ' @lang("app.to") ' + picker.endDate.format(
                '{{ companyOrGlobalSetting()->moment_date_format }}'));
    });

    $('#datatableRange2').on('apply.daterangepicker', (event, picker) => {
        cb(picker.startDate, picker.endDate);
        $('#datatableRange2').val(picker.startDate.format('{{ companyOrGlobalSetting()->moment_date_format }}') +
            ' @lang("app.to") ' + picker.endDate.format(
                '{{ companyOrGlobalSetting()->moment_date_format }}'));
    });

    function cb(start, end) {
        $('#datatableRange, #datatableRange2').val(start.format('{{ companyOrGlobalSetting()->moment_date_format }}') +
            ' @lang("app.to") ' + end.format(
                '{{ companyOrGlobalSetting()->moment_date_format }}'));
            $('#reset-filters, #reset-filters-2').removeClass('d-none');

    }

</script>

<!-- Scripts -->
<script>
    window.Laravel = {!! json_encode([
    'csrfToken' => csrf_token(),
    'user' => user(),
]) !!};
</script>

<!-- Core Vendor JS (Moved to layout for Turbo stability) -->
<script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>

@stack('scripts')

<script>
    $(window).on('load', function () {
        // Fallback for direct loads without Turbo
        init();
        $(".preloader-container").fadeOut("slow", function () {
            $(this).removeClass("d-flex");
        });
    });

    if (!window.turboListenersAttached) {
        document.addEventListener("turbo:load", function() {
            // console.log("Turbo Load: Resetting mobile menus and overlays");
            init();

            // Login-based Popup Notice Feature
            @if(isset($unreadPopupNotices) && $unreadPopupNotices->count() > 0)
                (function() {
                    const notices = @json($unreadPopupNotices);
                    let currentIndex = 0;

                    function showNotice(index) {
                        if (index >= notices.length) return;

                        const notice = notices[index];
                        const isLast = (index === notices.length - 1);
                        const imageHtml = notice.image_url ? `<div class="notice-popup-image mb-3"><img src="${notice.image_url}" alt="${notice.heading}"></div>` : '';

                        Swal.fire({
                            title: notice.heading,
                            html: `${imageHtml}<div class="notice-popup-container ql-editor">${notice.description}</div>`,
                            iconHtml: '<i class="fa fa-bullhorn text-info"></i>',
                            showCancelButton: !isLast,
                            confirmButtonText: isLast ? "@lang('app.close')" : "@lang('app.next') <i class='fa fa-arrow-right ml-2'></i>",
                            cancelButtonText: "@lang('app.close')",
                            customClass: {
                                container: 'notice-swal-container',
                                popup: 'notice-swal-popup',
                                header: 'notice-swal-header',
                                title: 'notice-swal-title',
                                content: 'notice-swal-content',
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-secondary ml-2'
                            },
                            buttonsStyling: false,
                            width: '650px',
                            allowOutsideClick: false,
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown animate__faster'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp animate__faster'
                            }
                        }).then((result) => {
                            // Mark as read via AJAX
                            $.easyAjax({
                                url: "{{ route('notices.mark_read', ':id') }}".replace(':id', notice.id),
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function() {
                                    if (result.isConfirmed && !isLast) {
                                        showNotice(index + 1);
                                    }
                                }
                            });
                        });
                    }

                    // Only show once per page lifecycle to prevent repeat on back/forward if already dismissed
                    if (!window.noticesShownThisSession) {
                        showNotice(0);
                        window.noticesShownThisSession = true;
                    }
                })();
            @endif
            $(".preloader-container").fadeOut("fast", function () {
                $(this).removeClass("d-flex");
            });

            // Fix stuck sidebar hover/active states
            $('.main-sidebar .nav-item').removeClass('hover');
            $('.main-sidebar .accordionItemHeading').removeClass('hover');

            // -------------------------------------------------------
            // TURBO SIDEBAR ACTIVE STATE SYNC
            // The sidebar is data-turbo-permanent so Laravel's server-
            // rendered `active` class is never refreshed by Turbo.
            // On every navigation we recompute it from the current URL.
            // -------------------------------------------------------
            (function syncSidebarActive() {
                var currentPath = window.location.pathname;

                // 1. Reset all active states
                $('.main-sidebar .accordionItemHeading').removeClass('active');
                $('.main-sidebar .accordionItemContent a').removeClass('active');
                $('.main-sidebar .accordionItem').removeClass('open');
                // Also reset direct nav-item links (non-accordion)
                $('.main-sidebar a.nav-item').removeClass('active');

                // 2. Mark matching direct links (single-item menu entries)
                $('.main-sidebar a.nav-item[href]').each(function() {
                    var linkPath = this.pathname; // browser parses full href automatically
                    if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
                        $(this).addClass('active');
                    }
                });

                // 3. Mark matching sub-menu links and open their parent accordion
                $('.main-sidebar .accordionItemContent a[href]').each(function() {
                    var linkPath = this.pathname;
                    if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
                        $(this).addClass('active');
                        // Open the parent accordion and mark heading active
                        var $accordionItem = $(this).closest('.accordionItem');
                        $accordionItem.addClass('open');
                        $accordionItem.find('> .accordionItemHeading').addClass('active');
                    }
                });
            })();

            // Robust closing of all mobile overlays
            const closeOverlays = () => {
                $("#mobile_menu_collapse, #mobile_close_panel").removeClass("toggled");
                $("#mob-admin-dash, #close-admin-overlay, #mob-settings-sidebar, #close-settings-overlay, #ticket-detail-contact, #close-tickets-overlay, #mob-client-detail, #close-client-overlay, #hide-project-menues, #mob-project-menu, #close-project-overlay, #more_filter").removeClass("in toggled");
                
                // Safety wrapper for global close functions
                const callIfExists = (fnName) => {
                    if (typeof window[fnName] === 'function') {
                        try { window[fnName](); } catch (e) { console.warn("Overlay catch:", fnName, e); }
                    }
                };

                callIfExists('closeMobileMenu');
                callIfExists('closeMoreFilter');
                callIfExists('closeAdminDashboard');
                callIfExists('closeSettingsSidebar');
                callIfExists('closeTicketsSidebar');
                callIfExists('closeClientDetail');
                callIfExists('closeProjectSidebar');
            };

            closeOverlays();

            // Re-apply desktop mini-sidebar state
            if (typeof checkMiniSidebar !== 'undefined' && (checkMiniSidebar == "yes" || checkMiniSidebar == "")) {
                if (!$('body').hasClass('sidebar-toggled')) {
                    $('body').addClass('sidebar-toggled');
                }
            }
        });

        /* --- GLOBAL FILTER OVERRIDES (Fills gaps in main.js) --- */
        window.openMoreFilter = function() {
            var $filter = $("#more_filter");
            if ($filter.length > 0) {
                $filter.addClass("in");
            }
        };

        window.closeMoreFilter = function() {
            var $filter = $("#more_filter");
            if ($filter.length > 0) {
                $filter.removeClass("in");
            }
        };

        document.addEventListener("turbo:visit", function() {
            $(".preloader-container").addClass("d-flex").show();
            // Close immediately on visit to prevent ghosting
            $("#mobile_menu_collapse, #mobile_close_panel").removeClass("toggled");
            if (typeof closeMobileMenu === 'function') closeMobileMenu();
        });

        document.addEventListener("turbo:before-cache", function() {
            // CRITICAL: Close all menus before Turbo snapshots the page
            // This prevents the menu from appearing "open" when navigating back/forward
            $("#mobile_menu_collapse, #mobile_close_panel").removeClass("toggled");
            $("#mob-admin-dash, #close-admin-overlay, #mob-settings-sidebar, #close-settings-overlay, #ticket-detail-contact, #close-tickets-overlay, #mob-client-detail, #close-client-overlay, #hide-project-menues, #mob-project-menu, #close-project-overlay, #more_filter").removeClass("in toggled");

            // DESTROY Select2 & Selectpicker to prevent double DOM wrapping
            if ($.fn.selectpicker) {
                $('.selectpicker').selectpicker('destroy');
            }
            if ($.fn.select2) {
                $('.select2, .f-select2').select2('destroy');
            }

            // DESTROY DataTables memory leaks and DOM corruption
            if ($.fn.dataTable) {
                $('.dataTable').DataTable().destroy();
                // CRITICAL: Clear the global DataTables registry to prevent "Processing" hangs on the next page
                window.LaravelDataTables = {}; 
            }

            // DESTROY Dropzone instances
            if (typeof Dropzone !== 'undefined' && Dropzone.instances && Dropzone.instances.length > 0) {
                Dropzone.instances.forEach(function(dz) {
                    dz.destroy();
                });
            }

            // DESTROY Quill editors & reset global array tracker
            if (typeof window.quillArray === 'object') {
                $.each(window.quillArray, function(id, instance) {
                    if (typeof destory_editor === 'function') {
                        destory_editor(id);
                    }
                });
                window.quillArray = {}; 
            }

            // FORCE RESET BODY CLASSES (Crucial for Back navigation responsiveness)
            // Bootstrap often leaves these stuck if we navigate during a modal transition
            $('body').removeClass('modal-open sidebar-toggled').css('padding-right', '');
            $('.modal').removeClass('show').hide(); // Force hide any lingering modals

            // DESTROY Tooltips & Popovers to prevent orphaned ghosts on the next page
            if ($.fn.tooltip) {
                $('[data-toggle="tooltip"]').tooltip('dispose');
            }
            if ($.fn.popover) {
                $('[data-toggle="popover"]').popover('dispose');
            }

            // Force hide any persistent preloader
            $(".preloader-container").removeClass("d-flex").hide();

            // Force detach any ghost DOM elements attached globally
            $('.daterangepicker, .modal-backdrop, .dz-hidden-input, .select2-container').remove();
        });

        // Hide preloader when the new page is ready
        document.addEventListener("turbo:load", function() {
            $(".preloader-container").removeClass("d-flex").hide();

            // SYNC SIDEBAR ACTIVE STATE (Required because sidebar is data-turbo-permanent)
            const currentUrl = window.location.href.split(/[?#]/)[0];
            
            // 1. Remove active states from everywhere
            $('.sidebar-menu .active').removeClass('active');
            
            // 2. Find and highlight the current link
            $('.sidebar-menu a').each(function() {
                const linkUrl = this.href.split(/[?#]/)[0];
                if (linkUrl === currentUrl || linkUrl.replace(/\/$/, "") === currentUrl.replace(/\/$/, "")) {
                    $(this).addClass('active');
                    
                    // 3. Handle Parent Accordion (Work, Reports, etc.)
                    const $parentAccordion = $(this).closest('.accordionItem');
                    if ($parentAccordion.length) {
                        $parentAccordion.removeClass('closeIt'); // Open the menu
                        $parentAccordion.find('.accordionItemHeading').addClass('active'); // Highlight parent
                    }
                }
            });
        });

        window.turboListenersAttached = true;
    }

    // Force close mobile menu immediately when any link inside it is clicked
    $('body').on('click', '.sidebar-menu a:not(.accordionItemHeading)', function() {
        if (typeof closeMobileMenu === 'function') {
            closeMobileMenu();
        }
    });

    /* --- UNIVERSAL GLOBAL FILTER BUS --- */
    // Handles filter changes for ALL modules. Lives on document, immune to Turbo body swaps.
    // 'changed.bs.select' is the Bootstrap-Select (selectpicker) event — MUST be here or dropdowns are silent.
    $(document).on('change keyup changed.bs.select',
        '.filter-box select, #more_filter select, .filter-box input[type="text"], .filter-box input[type="number"], #search-text-field',
        function() {
            var $resetBtn = $('#reset-filters');
            if ($resetBtn.length > 0) {
                var isDirty = false;
                $('.filter-box select, #more_filter select').each(function() {
                    var val = $(this).val();
                    if (val && val !== 'all' && val !== 'not finished' && val !== 'deadline' && val !== 'start_date' && val !== 'created_at') {
                        isDirty = true;
                    }
                });
                if ($('#search-text-field').val() !== '') isDirty = true;
                if (isDirty) $resetBtn.removeClass('d-none');
            }

            // Trigger the table refresh if the module has a showTable function
            if (typeof window.showTable === 'function') {
                window.showTable();
            }
        }
    );

    $('body').on('click', '.view-notification', function (event) {
        event.preventDefault();
        const id = $(this).data('notification-id');
        const href = $(this).attr('href');

        $.easyAjax({
            url: "{{ route('mark_single_notification_read') }}",
            type: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                'id': id
            },
            success: function () {
                if (typeof href !== 'undefined') {
                    window.location = href;
                }
            }
        });
    });

    $('body').on('click', '.img-lightbox', function () {
        const imageUrl = $(this).data('image-url');
        const url = "{{ route('front.public.show_image').'?image_url=' }}" + encodeURIComponent(imageUrl);
        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_XL, url);
    });

    function updateOnesignalPlayerId(userId) {
        $.easyAjax({
            url: '{{ route('profile.update_onesignal_id') }}',
            type: 'POST',
            data: {
                'userId': userId,
                '_token': '{{ csrf_token() }}'
            }
        })
    }

    /* --- GLOBAL UI SYNCHRONIZATION HELPER --- */
    window.syncGlobalStats = function(data) {
        if (!data) return;

        // Sync Notification Count
        if (typeof data.unreadNotificationCount !== 'undefined') {
            const $badge = $('.unread-notifications-count');
            if (data.unreadNotificationCount > 0) {
                if ($badge.length > 0) {
                    $badge.html(data.unreadNotificationCount).removeClass('d-none');
                } else {
                    // If badge doesn't exist, we might need to inject it into the bell icon
                    $('.show-user-notifications').append('<span class="badge badge-primary unread-notifications-count active-timer-count position-absolute">' + data.unreadNotificationCount + '</span>');
                }
            } else {
                $badge.addClass('d-none').remove();
            }
        }

        // Sync Global Timer Clock
        if (typeof data.clockHtml !== 'undefined' && data.clockHtml !== '') {
            $('#timer-clock').html(data.clockHtml);
        }

        // Sync Active Timer Count (Badge on the timer icon)
        if (typeof data.activeTimerCount !== 'undefined') {
            const $timerBadge = $('#show-active-timer .active-timer-count');
            if (data.activeTimerCount > 0) {
                $timerBadge.html(data.activeTimerCount).removeClass('d-none');
            } else {
                $timerBadge.addClass('d-none');
            }
        }
    };

    if (SEARCH_KEYWORD !== '' && $('#search-text-field').length > 0) {
        $('#search-text-field').val(SEARCH_KEYWORD);
        $('#reset-filters').removeClass('d-none');
    }

    $('body').on('click', '.show-hide-purchase-code', function () {
        $('> .icon', this).toggleClass('fa-eye-slash fa-eye');
        $(this).siblings('span').toggleClass('blur-code ');
    });

    /* --- GLOBAL TOPBAR & SIDEBAR DELEGATED LISTENERS --- */
    $('body').on('click', '#show-active-timer', function () {
        const url = "{{ route('timelogs.show_active_timer') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_XL, url);
    });

    $('body').on('click', '#start-timer-modal', function () {
        const url = "{{ route('timelogs.show_timer') }}";
        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_XL, url);
    });

    $('body').on('click', '.open-search', function () {
        const url = "{{ route('search.index') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.show-user-notifications', function () {
        const openStatus = $(this).attr('aria-expanded');
        if (typeof openStatus == "undefined" || openStatus == "false") {
            const token = '{{ csrf_token() }}';
            $.easyAjax({
                type: 'POST',
                url: "{{ route('show_notifications') }}",
                container: "#notification-list",
                blockUI: true,
                data: { '_token': token },
                success: function (data) {
                    if (data.status === 'success') {
                        $('#notification-list').html(data.html);
                    }
                }
            });
        }
    });

    $('body').on('click', '.mark-notification-read', function () {
        const token = '{{ csrf_token() }}';
        $.easyAjax({
            type: 'POST',
            url: "{{ route('mark_notification_read') }}",
            blockUI: true,
            data: { '_token': token },
            success: function (data) {
                if (data.status === 'success') {
                    $('#notification-list').html('');
                    $('.unread-notifications-count').remove();
                    // Turbo-friendly refresh
                    if (window.Turbo) {
                        Turbo.visit(window.location.href, { action: "replace" });
                    } else {
                        window.location.reload();
                    }
                }
            }
        });
    });

    $('body').on('click', '.invite-member', function() {
        const url = "{{ route('employees.invite_member') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('change', '#dark-theme-toggle', function() {
        const darkTheme = ($(this).is(':checked')) ? '1' : '0';
        $.easyAjax({
            type: 'POST',
            url: "{{ route('profile.dark_theme') }}",
            blockUI: true,
            data: { '_token': '{{ csrf_token() }}', 'darkTheme': darkTheme },
            success: function(response) {
                if (response.status === 'success') {
                    if (window.Turbo) {
                        Turbo.visit(window.location.href, { action: "replace" });
                    } else {
                        window.location.reload();
                    }
                }
            }
        });
    });

</script>

<script>
    window.quillArray = window.quillArray || {};
    var quillArray = window.quillArray;

    function quillImageLoad(ID) {
        const quillContainer = document.querySelector(ID);
        quillArray[ID] = new Quill(ID, {
            modules: {
                toolbar: [
                    // [{ align: '' }, { align: 'center' }, { align: 'right' }, { align: 'justify' }],
                    [{
                        header: [1, 2, 3, 4, 5, false]
                    }],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['image', 'code-block', 'link','video'],
                    [{
                        'direction': 'rtl'
                    }],
                    ['clean']
                ],
                clipboard: {
                    matchVisual: false
                },
                "emoji-toolbar": true,
                "emoji-textarea": true,
                "emoji-shortname": true,
            },
            theme: 'snow',
            bounds: quillContainer
        });
        const toolbar = quillArray[ID].getModule('toolbar');
        if (toolbar) {
            toolbar.addHandler('image', selectLocalImage);
        }
    }
        function destory_editor(selector){
            if($(selector)[0])
            {
                var content = $(selector).find('.ql-editor').html();
                $(selector).html(content);

                $(selector).siblings('.ql-toolbar').remove();
                $(selector + " *[class*='ql-']").removeClass (function (index, class_name) {
                return (class_name.match (/(^|\s)ql-\S+/g) || []).join(' ');
                });

                $(selector + "[class*='ql-']").removeClass (function (index, class_name) {
                return (class_name.match (/(^|\s)ql-\S+/g) || []).join(' ');
                });
            }
            else
            {
                console.error('editor not exists');
            }
        }
    function quillMention(atValues,ID) {
        const mentionItemTemplate = '<div class="mention-item"> <img src="{image}" class="align-self-start mr-3 taskEmployeeImg rounded">{name}</div>';

        const customRenderItem = function(item, searchTerm) {
            const html = mentionItemTemplate.replace('{image}', item.image).replace('{name}', item.value);
            return html;
        }
        let placeholder;
        if (ID === '#submitTexts') {
            placeholder = "@lang('placeholders.message')";
        } else {
            placeholder = '';
        }

        var quillEditor = new Quill(ID, {
            placeholder: placeholder,
            modules: {
                magicUrl: {
                    urlRegularExpression: /(https?:\/\/[\S]+)|(www.[\S]+)|(tel:[\S]+)/g,
                    globalRegularExpression: /(https?:\/\/|www\.|tel:)[\S]+/g,
                },
                mention: {
                    allowedChars: /^[A-Za-z\sÅÄÖåäö]*$/,
                    mentionDenotationChars: ["@", "#"],
                    source: function(searchTerm, renderList, mentionChar) {
                    let values;
                    if (mentionChar === "@") {
                        values = atValues;
                    } else {
                        values = hashValues;
                    }

                    if (searchTerm.length === 0) {
                        renderList(values, searchTerm);

                    } else {
                        const matches = [];
                        for (i = 0; i < values.length; i++)
                        if (
                            ~values[i].value
                            .toLowerCase()
                            .indexOf(searchTerm.toLowerCase())
                        )
                            matches.push(values[i]);
                        renderList(matches, searchTerm);
                    }
                    },
                    renderItem: customRenderItem,

                },

            },
            theme: 'snow'
        });
    }
     /**
     * click to open user profile
     *
     */
    window.addEventListener('mention-clicked', function ({ value }) {
    if (value?.link) {
        window.open(value.link, value?.target ?? '_blank');
    }
    });
    /**
     * Step1. select local image
     *
     */
    function selectLocalImage() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.click();

        // Listen upload local image and save to server
        input.onchange = () => {
            const file = input.files[0];

            // file type is only image.
            if (/^image\//.test(file.type)) {
                saveToServer(file);
            } else {
                console.warn('You could only upload images.');
            }
        };
    }

    /**
     * Step2. save to server
     *
     * @param {File} file
     */
    function saveToServer(file) {
        const fd = new FormData();
        fd.append('image', file);
        $.ajax({
            type: 'POST',
            url: "{{ route('image.store') }}",
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: fd,
            contentType: false,
            processData: false,
            success: function (response) {
                insertToEditor(response)
            },
        });
    }

    function insertToEditor(url) {
        // push image url to rich editor.
        $.each(quillArray, function (key, quill) {
            try {
                let range = quill.getSelection();
                quill.insertEmbed(range.index, 'image', url);
            } catch (err) {
            }
        });
    }
</script>

<script>
    $('body').on('click', '#pause-timer-btn, .pause-active-timer', function () {
        const id = $(this).data('time-id');
        let url = "{{ route('timelogs.pause_timer', ':id') }}";
        url = url.replace(':id', id);
        const token = '{{ csrf_token() }}';
        $.easyAjax({
            url: url,
            blockUI: true,
            type: "POST",
            disableButton: true,
            buttonSelector: "#pause-timer-btn",
            data: {
                timeId: id,
                _token: token
            },
            success: function (response) {
                if (response.status === 'success') {
                    if ($('#myActiveTimer').length > 0) {
                        $(MODAL_XL + ' .modal-content').html(response.html);

                        if ($('#allTasks-table').length) {
                            window.LaravelDataTables["allTasks-table"].draw(false);
                        }
                    }

                    if ($('#allTasks-table').length) {
                        window.LaravelDataTables["allTasks-table"].draw(false);
                    }

                    $('#timer-clock').html(response.clockHtml);
                }
            }
        })
    });

    $('body').on('click', '#resume-timer-btn, .resume-active-timer', function () {
        const id = $(this).data('time-id');
        let url = "{{ route('timelogs.resume_timer', ':id') }}";
        url = url.replace(':id', id);
        const token = '{{ csrf_token() }}';
        $.easyAjax({
            url: url,
            blockUI: true,
            type: "POST",
            disableButton: true,
            buttonSelector: "#resume-timer-btn",
            data: {
                timeId: id,
                _token: token
            },
            success: function (response) {
                if (response.status === 'success') {
                    if ($('#myActiveTimer').length > 0) {
                        $(MODAL_XL + ' .modal-content').html(response.html);
                    }

                    $('#timer-clock').html(response.clockHtml);
                    if ($('#allTasks-table').length) {
                        window.LaravelDataTables["allTasks-table"].draw(false);
                    }
                }
            }
        })
    });

    $('body').on('click', '.stop-active-timer', function () {
        const id = $(this).data('time-id');
        let url = "{{ route('timelogs.stop_timer', ':id') }}";
        url = url.replace(':id', id);
        const token = '{{ csrf_token() }}';
        $.easyAjax({
            url: url,
            type: "POST",
            data: {
                timeId: id,
                _token: token
            },
            success: function (response) {
                if ($('#myActiveTimer').length > 0) {
                    $(MODAL_XL + ' .modal-content').html(response.html);
                }

                if (response.activeTimerCount > 0) {
                    $('#show-active-timer .active-timer-count').html(response.activeTimerCount);
                } else {
                    $('#show-active-timer .active-timer-count').addClass('d-none');
                }

                $('#timer-clock').html('');
                if ($('#allTasks-table').length) {
                    window.LaravelDataTables["allTasks-table"].draw(false);
                }

            }
        })

    });

</script>

@if (in_array('messages', user_modules()))
<script>
    function newMessageNotificationPlay() { var audio = new Audio("{{ asset('message-notification.mp3') }}"); audio.play(); }

    function checkNewMessage() {
        var url = "{{ route('messages.check_new_message') }}";
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: url,
            type: "POST",
            data: {
                '_token': token,
            },
            success: function (response) {
                if (response.new_message_count > 0) {
                    newMessageNotificationPlay();
                    Swal.fire({
                        icon: 'info',
                        text: 'New message received.',

                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,

                        customClass: {
                            confirmButton: "btn btn-primary",
                        },
                        showClass: {
                            popup: "swal2-noanimation",
                            backdrop: "swal2-noanimation",
                        },
                    });
                }
            }
        });
    }

    if (message_setting.send_sound_notification == 1 && !(pusher_setting.status === 1 && pusher_setting.messages === 1)) {
        window.setInterval(function () {
            checkNewMessage()
        }, 10000); // Check messages every 10 seconds
    }

    </script>
@endif

<!-- PWA Service Worker Registration -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('{{ asset("sw.js") }}').then(function(registration) {
                console.log('PWA ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
                console.log('PWA ServiceWorker registration failed: ', err);
            });
        });
    }
</script>
</body>

</html>
