<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErpWorkflowSetting extends BaseModel
{
    use HasCompany;

    protected $table = 'erp_workflow_settings';

    protected $fillable = [
        'company_id',
        'setting_group',
        'setting_key',
        'setting_value',
        'datatype',
        'default_value',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
