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
use App\Models\EmailCampaign;
use App\Models\Attendee;

class CampaignEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $campaign;
    public $attendee;
    public $event;
    public $campaignAttachments;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $campaign, Attendee $attendee)
    {
        $this->campaign = $campaign;
        $this->attendee = $attendee;
        $this->event = $attendee->event; // Load the event for the logo
        $this->campaignAttachments = $campaign->attachments ?? [];
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
            subject: $this->campaign->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.campaign',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if (!empty($this->campaignAttachments)) {
            foreach ($this->campaignAttachments as $file) {
                $filePath = storage_path('app/public/' . $file);
                if (file_exists($filePath)) {
                    $attachments[] = Attachment::fromPath($filePath);
                }
            }
        }

        return $attachments;
    }
}
