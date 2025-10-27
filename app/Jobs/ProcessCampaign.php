<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\EmailCampaign;
use Illuminate\Support\Facades\Log;

class ProcessCampaign implements ShouldQueue
{
    use Queueable;

    public $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update campaign status
            $this->campaign->update(['status' => 'sending']);

            // Get all pending recipients
            $recipients = $this->campaign->recipients()
                ->where('status', 'pending')
                ->get();

            // Queue individual send jobs
            foreach ($recipients as $recipient) {
                SendCampaignEmail::dispatch($recipient);
            }

            Log::info("Queued {$recipients->count()} emails for campaign {$this->campaign->id}");

            // Update campaign status to sent
            $this->campaign->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process campaign {$this->campaign->id}: " . $e->getMessage());

            $this->campaign->update(['status' => 'failed']);

            throw $e;
        }
    }
}
