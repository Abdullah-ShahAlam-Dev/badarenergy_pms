@if (in_array(\Modules\CRMEmail\Entities\CrmEmailSetting::MODULE_NAME, user_modules()) && in_array('admin', user_roles()))
    <a href="{{ route('crm-email-settings.index') }}"
       class="d-flex align-items-center text-dark-grey py-2 px-4 f-14
              {{ request()->routeIs('crm-email-settings.*') ? 'active bg-additional-grey' : '' }}">
        <i class="side-icon bi bi-envelope-gear mr-2"></i>
        @lang('CRM Email')
    </a>
@endif
