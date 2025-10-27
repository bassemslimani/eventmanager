<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body',
        'filters',
        'attachments',
        'status',
        'scheduled_at',
        'sent_at',
        'recipients_count',
        'sent_count',
        'failed_count',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'attachments' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user who created the campaign
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all recipients for this campaign
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }

    /**
     * Get pending recipients
     */
    public function pendingRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id')->where('status', 'pending');
    }

    /**
     * Check if campaign can be edited
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'failed']);
    }

    /**
     * Check if campaign can be sent
     */
    public function canSend(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'failed']);
    }
}
