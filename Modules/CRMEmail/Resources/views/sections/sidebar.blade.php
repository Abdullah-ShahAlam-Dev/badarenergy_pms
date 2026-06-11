@php
    $viewPermission = user()->permission('view_crm_email');
@endphp

@if (in_array('crm_email', user_modules()) && ($viewPermission == 'all' || $viewPermission == 'added' || in_array('admin', user_roles())))
    <x-menu-item icon="envelope" :text="__('CRM Email')" :active="request()->routeIs('crm-email-templates.*') || request()->routeIs('crm-email-segments.*') || request()->routeIs('crm-email-campaigns.*') || request()->routeIs('crm-email-settings.*')">
        <x-slot name="iconPath">
            <path fill-rule="evenodd" d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
        </x-slot>
        <div class="accordionItemContent pb-2">
            @if (in_array('admin', user_roles()) || $viewPermission != 'none')
                <x-sub-menu-item :link="route('crm-email-templates.index')" :text="__('Email Templates')" />
                <x-sub-menu-item :link="route('crm-email-segments.index')" :text="__('Email Segments')" />
                <x-sub-menu-item :link="route('crm-email-campaigns.index')" :text="__('Campaigns')" />
            @endif
        </div>
    </x-menu-item>
@endif


