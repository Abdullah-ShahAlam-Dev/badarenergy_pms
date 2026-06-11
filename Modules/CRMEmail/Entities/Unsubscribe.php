<?php

namespace Modules\CRMEmail\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class Unsubscribe extends BaseModel
{
    use HasCompany;

    protected $table = 'crm_unsubscribes';

    protected $fillable = [
        'company_id',
        'email',
    ];
}
