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

    public $recipient;

    /**
     * Create a new job instance.
     */
    public function __construct(CampaignRecipient $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $recipient = $this->recipient->load('campaign', 'attendee');
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

            $this->recipient->update([
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);

            // Update campaign failed count
            $this->recipient->campaign->increment('failed_count');

            throw $e;
        }
    }
}
