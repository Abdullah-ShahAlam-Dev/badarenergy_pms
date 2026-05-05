<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class EmailTemplate extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];
}

