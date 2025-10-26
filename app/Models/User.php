<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an event manager
     */
    public function isEventManager(): bool
    {
        return $this->role === 'event_manager';
    }

    /**
     * Check if user is an agent
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Get the events that this user manages or has access to
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the events that this user manages as Event Manager
     */
    public function managedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->wherePivot('role', 'event_manager')
            ->withTimestamps();
    }

    /**
     * Check if user can access a specific event
     */
    public function canAccessEvent($eventId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->events()->where('events.id', $eventId)->exists();
    }

    /**
     * Check if user can manage a specific event
     */
    public function canManageEvent($eventId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isEventManager()) {
            return $this->managedEvents()->where('events.id', $eventId)->exists();
        }

        return false;
    }
}
