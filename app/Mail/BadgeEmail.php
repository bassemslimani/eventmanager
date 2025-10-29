<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Attendee;

class BadgeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $attendee;
    public $badgePath;
    public $event;

    /**
     * Create a new message instance.
     */
    public function __construct(Attendee $attendee, $badgePath)
    {
        $this->attendee = $attendee;
        $this->badgePath = $badgePath;
        $this->event = $attendee->event;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                \App\Models\Setting::get('mail_from_address', config('mail.from.address')),
                \App\Models\Setting::get('mail_from_name', config('mail.from.name'))
            ),
            subject: 'Your Event Badge - ' . $this->event->name,
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
        // Verify the badge file exists before attaching
        if (\Illuminate\Support\Facades\Storage::exists($this->badgePath)) {
            \Log::info("Attaching badge PDF: {$this->badgePath}");
            return [
                Attachment::fromStorage($this->badgePath)
                    ->as($this->attendee->name . '_Badge.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        \Log::error("Badge PDF not found in storage: {$this->badgePath}");
        return [];
    }
}
