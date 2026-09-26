<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmActivity extends Model
{
    protected $fillable = [
        'taman_id',
        'user_id',
        'type',
        'title',
        'detail',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
