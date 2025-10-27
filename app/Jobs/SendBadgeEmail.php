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

    public $attendee;
    public $badgePath;

    /**
     * Create a new job instance.
     */
    public function __construct(Attendee $attendee, $badgePath)
    {
        $this->attendee = $attendee;
        $this->badgePath = $badgePath;
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
                ->send(new BadgeEmail($this->attendee, $this->badgePath));

            Log::info("Badge email sent to {$this->attendee->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send badge email to {$this->attendee->email}: " . $e->getMessage());
            throw $e; // Re-throw to trigger queue retry
        }
    }
}
