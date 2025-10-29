<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\CampaignRecipient;
use App\Mail\CampaignEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCampaignEmail implements ShouldQueue
{
    use Queueable;

    public $recipientId;

    /**
     * Create a new job instance.
     */
    public function __construct(CampaignRecipient $recipient)
    {
        // Store only the ID to ensure we always fetch fresh data
        $this->recipientId = $recipient->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Fresh load the recipient and attendee to get the latest email address
            $recipient = CampaignRecipient::with('campaign', 'attendee')->find($this->recipientId);

            if (!$recipient) {
                Log::warning("Campaign recipient {$this->recipientId} not found");
                return;
            }
            $campaign = $recipient->campaign;
            $attendee = $recipient->attendee;

            if (!$attendee->email) {
                $recipient->update([
                    'status' => 'failed',
                    'error' => 'No email address'
                ]);
                return;
            }

            Mail::to($attendee->email)
                ->send(new CampaignEmail($campaign, $attendee));

            $recipient->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            // Update campaign counts
            $campaign->increment('sent_count');

            Log::info("Campaign email sent to {$attendee->email} for campaign {$campaign->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send campaign email: " . $e->getMessage());

            // Reload recipient in case it doesn't exist
            $recipient = CampaignRecipient::with('campaign')->find($this->recipientId);

            if ($recipient) {
                $recipient->update([
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ]);

                // Update campaign failed count
                $recipient->campaign->increment('failed_count');
            }

            throw $e;
        }
    }
}
