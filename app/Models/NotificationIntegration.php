<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Support\Facades\Crypt;

class NotificationIntegration extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];
    
    // We cast credentials to array so we can easily mutate it
    protected $casts = [
        // 'credentials' => 'array',
    ];

    public function setCredentialsAttribute($value)
    {
        $this->attributes['credentials'] = $value ? Crypt::encryptString(json_encode($value)) : null;
    }

    public function getCredentialsAttribute($value)
    {
        return $value ? json_decode(Crypt::decryptString($value), true) : null;
    }
}
