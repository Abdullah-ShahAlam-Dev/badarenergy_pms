<?php

namespace Modules\GatePass\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatePassAttachment extends BaseModel
{
    protected $table = 'gate_pass_attachments';

    protected $fillable = [
        'gate_pass_request_id',
        'file_name',
        'hash_name',
        'size',
        'external_link'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(GatePassRequest::class, 'gate_pass_request_id');
    }
}
