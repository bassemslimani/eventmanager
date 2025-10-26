<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'type',
        'total_records',
        'processed',
        'failed',
        'errors',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'processed_at' => 'datetime',
        'total_records' => 'integer',
        'processed' => 'integer',
        'failed' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
