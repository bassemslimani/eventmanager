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

            Mail::to($this->attendee->email)
                ->send(new WelcomeEmail($this->attendee));

            Log::info("Welcome email sent to {$this->attendee->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email to {$this->attendee->email}: " . $e->getMessage());
            throw $e; // Re-throw to trigger queue retry
        }
    }
}
