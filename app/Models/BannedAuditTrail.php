<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['banned_id', 'user_id', 'action', 'field', 'old_value', 'new_value', 'created_at'])]
class BannedAuditTrail extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function banned(): BelongsTo
    {
        return $this->belongsTo(Banned::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
