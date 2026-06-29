<?php

namespace App\Observers;

use App\Models\ErpWorkflowSetting;
use App\Facades\WorkflowConfig;

class ErpWorkflowSettingObserver
{
    /**
     * Handle the ErpWorkflowSetting "saved" event.
     */
    public function saved(ErpWorkflowSetting $setting): void
    {
        WorkflowConfig::clearCache($setting->company_id);
    }

    /**
     * Handle the ErpWorkflowSetting "deleted" event.
     */
    public function deleted(ErpWorkflowSetting $setting): void
    {
        WorkflowConfig::clearCache($setting->company_id);
    }
}
