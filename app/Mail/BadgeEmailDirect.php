<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Attendee;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class BadgeEmailDirect extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $attendee;
    public $event;
    public $badgePdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Attendee $attendee)
    {
        $this->attendee = $attendee;
        $this->event = $attendee->event;

        // Generate badge PDF
        $this->badgePdfPath = $this->generateBadgePDF($attendee);
    }

    /**
     * Generate badge PDF for attachment
     */
    protected function generateBadgePDF($attendee)
    {
        try {
            // Generate QR code as SVG
            $renderer = new ImageRenderer(
                new RendererStyle(300),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCode = $writer->writeString($attendee->qr_uuid);

            // Load event data
            $event = $attendee->event;

            // Generate PDF using DomPDF
            $pdf = \PDF::loadView('pdfs.badge', [
                'attendee' => $attendee,
                'event' => $event,
                'qrCode' => $qrCode,
            ]);

            // Save PDF to temporary storage
            $fileName = 'badge_' . $attendee->id . '_' . time() . '.pdf';
            $path = 'badges/temp/' . $fileName;

            Storage::put($path, $pdf->output());

            return $path;
        } catch (\Exception $e) {
            \Log::error("Failed to generate badge PDF: " . $e->getMessage());
            return null;
        }
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
        // If PDF was generated successfully, attach it
        if ($this->badgePdfPath && Storage::exists($this->badgePdfPath)) {
            return [
                Attachment::fromStorage($this->badgePdfPath)
                    ->as($this->attendee->name . '_Badge.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
