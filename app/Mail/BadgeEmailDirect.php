<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Attendee;
use App\Services\BadgeService;
use Illuminate\Support\Facades\Storage;

class BadgeEmailDirect extends Mailable
{
    use Queueable, SerializesModels;

    public $attendee;
    public $event;
    private $badgePdfPath = null;

    /**
     * Create a new message instance.
     */
    public function __construct(Attendee $attendee)
    {
        $this->attendee = $attendee;
        $this->event = $attendee->event;
    }

    /**
     * Generate badge PDF for attachment using BadgeService
     */
    protected function generateBadgePDF($attendee)
    {
        $badgeService = new BadgeService();
        return $badgeService->generateBadgePDF($attendee);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎫 Your Event Badge - ' . $this->event->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.badge',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Generate PDF at send time (not in constructor)
        if ($this->badgePdfPath === null) {
            \Log::info("Generating badge PDF at send time for attendee: {$this->attendee->id}");
            $this->badgePdfPath = $this->generateBadgePDF($this->attendee);
        }

        // If PDF was generated successfully, attach it
        if ($this->badgePdfPath && Storage::exists($this->badgePdfPath)) {
            \Log::info("Attaching badge PDF: {$this->badgePdfPath}");
            return [
                Attachment::fromStorage($this->badgePdfPath)
                    ->as($this->attendee->name . '_Badge.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        \Log::error("No badge PDF to attach for attendee: {$this->attendee->id}");
        return [];
    }
}
