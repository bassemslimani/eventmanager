<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Attendee;
use App\Mail\BadgeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendBadgeEmail implements ShouldQueue
{
    use Queueable;

    public $attendeeId;
    public $badgePath;

    /**
     * Create a new job instance.
     */
    public function __construct(Attendee $attendee, $badgePath)
    {
        // Store only the ID to ensure we always fetch fresh data
        $this->attendeeId = $attendee->id;
        $this->badgePath = $badgePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Fresh load the attendee to get the latest email address
            $attendee = Attendee::with('event')->find($this->attendeeId);

            if (!$attendee) {
                Log::warning("Attendee {$this->attendeeId} not found");
                return;
            }

            if (!$attendee->email) {
                Log::warning("Attendee {$attendee->id} has no email address");
                return;
            }

            Mail::to($attendee->email)
                ->send(new BadgeEmail($attendee, $this->badgePath));

            Log::info("Badge email sent to {$attendee->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send badge email: " . $e->getMessage());
            throw $e; // Re-throw to trigger queue retry
        }
    }
}
