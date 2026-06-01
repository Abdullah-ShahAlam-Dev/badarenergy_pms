<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageGroupMember extends BaseModel
{
    protected $table = 'message_group_members';

    protected $guarded = ['id'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(MessageGroup::class, 'message_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
