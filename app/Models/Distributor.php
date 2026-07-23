<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Distributor extends BaseModel
{
    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if (company()) {
                $model->company_id = company()->id;
            }
        });
    }

    protected $table = 'distributors';

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'company_name',
        'address',
        'shipping_address',
        'tax_number',
        'status',
        'note'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'distributor_id');
    }
}
