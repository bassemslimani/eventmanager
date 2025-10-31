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
            // High quality QR code (600px) for premium badge quality
            $qrCode = \Endroid\QrCode\Builder\Builder::create()
                ->data($attendee->qr_uuid)
                ->size(600) // Premium quality matching single downloads
                ->margin(10)
                ->build();

            // Convert to base64 data URL for DomPDF
            $qrCodeDataUrl = 'data:image/png;base64,' . base64_encode($qrCode->getString());

            Log::info("QR Code generated as PNG (600x600) for attendee: {$attendee->id}");

            // Compress template background image to reduce PDF size
            $compressedTemplate = $this->compressTemplateImage($template->front_template);

            // Compress event logo if exists
            $compressedLogo = null;
            if ($event->logo) {
                $compressedLogo = $this->compressImage($event->logo, 1000, 1000, 92);
            }

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
                'compressedTemplate' => $compressedTemplate,
                'compressedLogo' => $compressedLogo,
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

    /**
     * Compress template background image for high-quality PDFs
     * Target: Premium quality badges matching single downloads (~1.4MB)
     */
    private function compressTemplateImage(?string $templatePath): ?string
    {
        if (!$templatePath) {
            return null;
        }

        try {
            $fullPath = storage_path('app/public/' . $templatePath);

            if (!file_exists($fullPath)) {
                Log::warning("Template image not found: {$fullPath}");
                return null;
            }

            // Premium quality compression at 1600px width, 90% quality
            return $this->compressImage($templatePath, 1600, 2400, 90);

        } catch (\Exception $e) {
            Log::error("Failed to compress template image: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Compress an image and return base64 data URL
     *
     * @param string $imagePath Path relative to storage/app/public
     * @param int $maxWidth Maximum width in pixels
     * @param int $maxHeight Maximum height in pixels
     * @param int $quality JPEG quality (1-100)
     * @return string|null Base64 data URL
     */
    private function compressImage(string $imagePath, int $maxWidth, int $maxHeight, int $quality): ?string
    {
        try {
            $fullPath = storage_path('app/public/' . $imagePath);

            if (!file_exists($fullPath)) {
                return null;
            }

            // Get image info
            $imageInfo = getimagesize($fullPath);
            if (!$imageInfo) {
                return null;
            }

            [$width, $height, $type] = $imageInfo;

            // Create image resource based on type
            $source = null;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($fullPath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($fullPath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($fullPath);
                    break;
                default:
                    Log::warning("Unsupported image type for compression: {$type}");
                    return null;
            }

            if (!$source) {
                return null;
            }

            // Calculate new dimensions maintaining aspect ratio
            $ratio = min($maxWidth / $width, $maxHeight / $height);

            // Only resize if image is larger than max dimensions
            if ($ratio < 1) {
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            // Create new image with resampled dimensions
            $resized = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG
            if ($type === IMAGETYPE_PNG) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Resample the image
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Capture output to base64
            ob_start();
            if ($type === IMAGETYPE_PNG) {
                // PNG with compression level 6 (balance between size and quality)
                imagepng($resized, null, 6);
                $mimeType = 'image/png';
            } else {
                // Convert to JPEG with specified quality
                imagejpeg($resized, null, $quality);
                $mimeType = 'image/jpeg';
            }
            $imageData = ob_get_clean();

            // Clean up
            imagedestroy($source);
            imagedestroy($resized);

            // Return as base64 data URL
            $base64 = base64_encode($imageData);

            Log::info("Image compressed: Original {$width}x{$height}, New {$newWidth}x{$newHeight}, Size: " . strlen($imageData) . " bytes");

            return "data:{$mimeType};base64,{$base64}";

        } catch (\Exception $e) {
            Log::error("Failed to compress image {$imagePath}: " . $e->getMessage());
            return null;
        }
    }
}
