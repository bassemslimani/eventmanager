<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\EventBadgeTemplate;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    /**
     * Generate a badge PDF for an attendee
     * Returns the path to the generated PDF in storage
     */
    public function generateBadgePDF(Attendee $attendee): ?string
    {
        try {
            Log::info("Starting badge PDF generation for attendee: {$attendee->id}");

            // Load event data
            $event = $attendee->event;

            if (!$event) {
                Log::error("No event found for attendee {$attendee->id}");
                return null;
            }

            // Get the EventBadgeTemplate for this attendee's event and category
            $template = EventBadgeTemplate::where('event_id', $attendee->event_id)
                ->where('category', $attendee->type)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                Log::error("No active badge template found for attendee {$attendee->id}, event {$event->id}, type {$attendee->type}");
                return null;
            }

            Log::info("Found badge template ID: {$template->id} for attendee: {$attendee->id}");

            // Generate QR code as PNG using endroid/qr-code (works with GD)
            $qrCode = \Endroid\QrCode\Builder\Builder::create()
                ->data($attendee->qr_uuid)
                ->size(800)
                ->margin(10)
                ->build();

            // Convert to base64 data URL for DomPDF
            $qrCodeDataUrl = 'data:image/png;base64,' . base64_encode($qrCode->getString());

            Log::info("QR Code generated as PNG for attendee: {$attendee->id}");

            // Get badge dimensions
            $badgeWidthCm = $template->badge_width_cm ?? 8.5;
            $badgeHeightCm = $template->badge_height_cm ?? 12.5;

            Log::info("Generating designed badge PDF for attendee: {$attendee->id}, using template elements");

            // Generate PDF using DomPDF with the designed template
            $pdf = \PDF::loadView('pdfs.badge-designed', [
                'attendee' => $attendee,
                'event' => $event,
                'template' => $template,
                'elements' => $template->elements,
                'qrCode' => $qrCodeDataUrl,
                'badgeWidthCm' => $badgeWidthCm,
                'badgeHeightCm' => $badgeHeightCm,
            ]);

            // Set paper size
            $pdf->setPaper([0, 0, $badgeWidthCm * 28.35, $badgeHeightCm * 28.35], 'portrait');

            // Save PDF to storage
            $fileName = 'badge_' . $attendee->id . '_' . time() . '.pdf';
            $path = 'badges/generated/' . $fileName;

            Log::info("Saving designed badge PDF to storage: {$path}");

            // Ensure directory exists
            $directory = dirname($path);
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            Storage::put($path, $pdf->output());

            if (Storage::exists($path)) {
                $size = Storage::size($path);
                Log::info("Badge PDF successfully saved: {$path}, Size: {$size} bytes");
                return $path;
            } else {
                Log::error("Badge PDF was not saved successfully: {$path}");
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Failed to generate badge PDF for attendee {$attendee->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Get or generate a badge PDF for an attendee
     * Checks if a recent PDF exists, otherwise generates a new one
     */
    public function getOrGenerateBadgePDF(Attendee $attendee): ?string
    {
        // For now, always generate a fresh PDF
        // In the future, we could check for existing PDFs and reuse them if they're recent
        return $this->generateBadgePDF($attendee);
    }
}
