<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Attendee;
use App\Mail\BadgeEmailDirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendBadgeEmailDirect implements ShouldQueue
{
    use Queueable;

    public $attendee;

    /**
     * Create a new job instance.
     */
    public function __construct(Attendee $attendee)
    {
        $this->attendee = $attendee;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->attendee->email) {
                Log::warning("Attendee {$this->attendee->id} has no email address");
                return;
            }

            // Ensure event is loaded
            if (!$this->attendee->event) {
                $this->attendee->load('event');
            }

            Mail::to($this->attendee->email)
                ->send(new BadgeEmailDirect($this->attendee));

            Log::info("Badge email sent to {$this->attendee->email} for event {$this->attendee->event->name}");
        } catch (\Exception $e) {
            Log::error("Failed to send badge email to {$this->attendee->email}: " . $e->getMessage());
            throw $e; // Re-throw to trigger queue retry
        }
    }
}
