<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'date',
        'location',
        'location_ar',
        'description',
        'description_ar',
        'logo',
        'theme_settings',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'theme_settings' => 'array',
    ];

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * Get all users (managers and agents) assigned to this event
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get event managers for this event
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->wherePivot('role', 'event_manager')
            ->withTimestamps();
    }

    /**
     * Get agents for this event
     */
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->wherePivot('role', 'agent')
            ->withTimestamps();
    }

    /**
     * Get badge templates for this event
     */
    public function badgeTemplates(): HasMany
    {
        return $this->hasMany(EventBadgeTemplate::class);
    }
}
