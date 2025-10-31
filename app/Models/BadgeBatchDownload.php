<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeBatchDownload extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'total_attendees',
        'processed_attendees',
        'successful_badges',
        'failed_badges',
        'zip_file_path',
        'filters',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->total_attendees == 0) {
            return 0;
        }

        return (int) (($this->processed_attendees / $this->total_attendees) * 100);
    }

    /**
     * Check if batch is complete
     */
    public function isComplete(): bool
    {
        return in_array($this->status, ['completed', 'failed']);
    }

    /**
     * Check if zip file is ready for download
     */
    public function isReadyForDownload(): bool
    {
        return $this->status === 'completed' &&
               $this->zip_file_path &&
               \Storage::disk('local')->exists($this->zip_file_path);
    }
}
