@if (in_array('admin', user_roles()) && in_array(\Modules\CRMEmail\Entities\CrmEmailSetting::MODULE_NAME, user_modules()))
    <x-setting-menu-item :active="$activeMenu" menu="crm_email_settings" :href="route('crm-email-settings.index')"
                         :text="__('CRM Email Settings')"/>
@endif
