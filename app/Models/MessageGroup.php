<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageGroup extends BaseModel
{
    use HasCompany;
    use HasFactory;

    protected $table = 'message_groups';

    protected $guarded = ['id'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_group_members', 'message_group_id', 'user_id')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(UserChat::class, 'message_group_id')->orderBy('created_at', 'asc');
    }

    public function latestMessage(): BelongsTo
    {
        // Return latest message relationship
        return $this->belongsTo(UserChat::class, 'id', 'message_group_id')
            ->orderBy('id', 'desc');
    }
}
