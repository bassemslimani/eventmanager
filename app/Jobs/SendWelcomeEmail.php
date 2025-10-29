<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Attendee;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public $attendeeId;

    /**
     * Create a new job instance.
     */
    public function __construct(Attendee $attendee)
    {
        // Store only the ID to ensure we always fetch fresh data
        $this->attendeeId = $attendee->id;
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
                ->send(new WelcomeEmail($attendee));

            Log::info("Welcome email sent to {$attendee->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email: " . $e->getMessage());
            throw $e; // Re-throw to trigger queue retry
        }
    }
}
