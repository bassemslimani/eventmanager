<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attendee extends Model
{
    protected $fillable = [
        'event_id',
        'type',
        'name',
        'name_ar',
        'email',
        'mobile',
        'company',
        'company_ar',
        'category',
        'role',
        'department',
        'qr_code',
        'qr_uuid',
        'badge_url',
        'badge_generated_at',
        'checked_in_at',
        'checked_in_by',
        'welcome_email_sent_at',
        'preferences',
        'metadata',
    ];

    protected $casts = [
        'badge_generated_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'welcome_email_sent_at' => 'datetime',
        'preferences' => 'array',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attendee) {
            if (empty($attendee->qr_uuid)) {
                $attendee->qr_uuid = Str::uuid();
            }
            if (empty($attendee->qr_code)) {
                $attendee->qr_code = 'QR-' . strtoupper(Str::random(10));
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
