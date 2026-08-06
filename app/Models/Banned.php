<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['fullname', 'address', 'source', 'license', 'description', 'created_by', 'date_created'])]
class Banned extends Model
{
    protected $table = 'banned';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'date_created' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(BannedAuditTrail::class, 'banned_id')->latest('created_at');
    }
}
