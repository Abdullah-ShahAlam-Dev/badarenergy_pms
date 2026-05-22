<?php

namespace App\Models;

use App\Traits\HasCompany;

class NotificationTemplate extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];
    protected $casts = [
        'parameter_mappings' => 'array',
        'is_active' => 'boolean'
    ];
}
