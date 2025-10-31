<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\BadgeTemplate;
use App\Models\BadgeBatchDownload;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class BadgeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Attendee::with('event');

        // Filter by event
        if ($request->has('event_id') && $request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Only show attendees without badges or filter by badge status
        if ($request->has('badge_status') && $request->badge_status) {
            if ($request->badge_status === 'generated') {
                $query->whereNotNull('badge_generated_at');
            } elseif ($request->badge_status === 'pending') {
                $query->whereNull('badge_generated_at');
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Get per_page from request, default to 20, max 100
        $perPage = $request->input('per_page', 20);
        $perPage = min((int)$perPage, 100); // Cap at 100 to prevent performance issues

        $attendees = $query->latest()->paginate($perPage)->withQueryString();
        $templates = BadgeTemplate::where('is_active', true)->get();
        $events = Event::orderBy('name')->get();

        return Inertia::render('Badges/Index', [
            'attendees' => $attendees,
            'templates' => $templates,
            'events' => $events,
            'filters' => $request->only(['type', 'badge_status', 'event_id', 'search', 'per_page']),
        ]);
    }

    public function generate(Attendee $attendee)
    {
        // Get the event-based badge template
        if (!$attendee->event_id) {
            return back()->with('error', 'Attendee must be assigned to an event.');
        }

        $template = \App\Models\EventBadgeTemplate::where('event_id', $attendee->event_id)
            ->where('category', $attendee->type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return back()->with('error', 'No active badge template found for this attendee category in this event. Please configure badges in the Event Badge Designer.');
        }

        // Generate QR code as SVG (doesn't require Imagick or GD)
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($attendee->qr_uuid);

        // Update attendee
        $attendee->update([
            'badge_generated_at' => now(),
        ]);

        return back()->with('success', 'Badge generated successfully.');
    }

    public function generateBulk(Request $request)
    {
        $validated = $request->validate([
            'attendee_ids' => 'required|array',
            'attendee_ids.*' => 'exists:attendees,id',
        ]);

        $attendees = Attendee::whereIn('id', $validated['attendee_ids'])->get();
        $generated = 0;

        foreach ($attendees as $attendee) {
            $template = BadgeTemplate::where('type', $attendee->type)
                ->where('is_active', true)
                ->first();

            if ($template) {
                $attendee->update([
                    'badge_generated_at' => now(),
                ]);
                $generated++;
            }
        }

        return back()->with('success', "Generated {$generated} badges successfully.");
    }

    public function download(Attendee $attendee)
    {
        // Get the event-based badge template
        if (!$attendee->event_id) {
            return response()->json(['error' => 'Attendee must be assigned to an event.'], 400);
        }

        $attendee->load('event');
        $event = $attendee->event;

        $template = \App\Models\EventBadgeTemplate::where('event_id', $attendee->event_id)
            ->where('category', $attendee->type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return response()->json(['error' => 'No active badge template found for this attendee.'], 404);
        }

        // Generate QR code as SVG
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($attendee->qr_uuid);

        // Prepare URLs
        $frontTemplateUrl = $template->front_template ? asset('storage/' . $template->front_template) : null;
        $eventLogoUrl = $event->logo ? asset('storage/' . $event->logo) : null;

        return response()->json([
            'success' => true,
            'attendee' => [
                'name' => $attendee->name,
                'name_ar' => $attendee->name_ar,
                'company' => $attendee->company,
                'company_ar' => $attendee->company_ar,
                'type' => $attendee->type,
                'email' => $attendee->email,
                'qr_uuid' => $attendee->qr_uuid,
            ],
            'event' => [
                'name' => $event->name,
                'name_ar' => $event->name_ar,
                'logo_url' => $eventLogoUrl,
                'date' => $event->date->format('Y-m-d'),
                'location' => $event->location,
            ],
            'template' => [
                'front_template_url' => $frontTemplateUrl,
                'badge_width' => $template->badge_width,
                'badge_height' => $template->badge_height,
                'badge_width_cm' => $template->badge_width_cm ?? 8.5,
                'badge_height_cm' => $template->badge_height_cm ?? 12.5,
                'font_family' => $template->font_family,
                'primary_color' => $template->primary_color,
                'secondary_color' => $template->secondary_color,
                'terms_and_conditions' => $template->terms_and_conditions,
                'elements' => $template->elements,
                'show_qr_code' => $template->show_qr_code,
                'show_logo' => $template->show_logo,
                'show_category_badge' => $template->show_category_badge,
            ],
            'qr_code_svg' => $qrCodeSvg,
        ]);
    }

    /**
     * Send badge via email
     */
    public function sendEmail(Request $request, Attendee $attendee)
    {
        if (!$attendee->email) {
            return back()->with('error', 'Attendee does not have an email address.');
        }

        if (!$attendee->event_id) {
            return back()->with('error', 'Attendee must be assigned to an event.');
        }

        if (!$attendee->badge_generated_at) {
            return back()->with('error', 'Badge must be generated first before sending via email.');
        }

        // Load the attendee's event
        $attendee->load('event');

        // Dispatch job to send email (with badge data, will generate PDF in job)
        \App\Jobs\SendBadgeEmailDirect::dispatch($attendee);

        return back()->with('success', 'Badge email is being sent to ' . $attendee->email . '!');
    }

    /**
     * Send multiple badges via email
     */
    public function sendEmailBulk(Request $request)
    {
        $validated = $request->validate([
            'attendee_ids' => 'required|array',
            'attendee_ids.*' => 'exists:attendees,id',
        ]);

        $attendees = Attendee::with('event')
            ->whereIn('id', $validated['attendee_ids'])
            ->whereNotNull('badge_generated_at')
            ->whereNotNull('email')
            ->get();

        if ($attendees->isEmpty()) {
            return back()->with('error', 'No generated badges with email addresses found for selected attendees.');
        }

        $sent = 0;
        foreach ($attendees as $attendee) {
            \App\Jobs\SendBadgeEmailDirect::dispatch($attendee);
            $sent++;
        }

        return back()->with('success', "Badge emails are being sent to {$sent} attendee(s)!");
    }

    /**
     * Generate all badges based on current filters
     */
    public function generateAll(Request $request)
    {
        $query = Attendee::query();

        // Apply same filters as index
        if ($request->has('event_id') && $request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Only get pending badges
        $query->whereNull('badge_generated_at');

        $attendees = $query->get();

        if ($attendees->isEmpty()) {
            return back()->with('error', 'No pending badges found to generate.');
        }

        $generated = 0;
        foreach ($attendees as $attendee) {
            // Check if attendee has an event and template exists
            if ($attendee->event_id) {
                $template = \App\Models\EventBadgeTemplate::where('event_id', $attendee->event_id)
                    ->where('category', $attendee->type)
                    ->where('is_active', true)
                    ->first();

                if ($template) {
                    $attendee->update([
                        'badge_generated_at' => now(),
                    ]);
                    $generated++;
                }
            }
        }

        return back()->with('success', "Generated {$generated} badges successfully.");
    }

    /**
     * Download multiple badges as ZIP file
     */
    public function downloadBulk(Request $request)
    {
        $validated = $request->validate([
            'attendee_ids' => 'required|array',
            'attendee_ids.*' => 'exists:attendees,id',
        ]);

        $attendees = Attendee::with('event')
            ->whereIn('id', $validated['attendee_ids'])
            ->whereNotNull('badge_generated_at')
            ->get();

        if ($attendees->isEmpty()) {
            return back()->with('error', 'No generated badges found for selected attendees.');
        }

        // Create temporary directory for PDFs
        $tempDir = storage_path('app/temp/badges_' . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $badgeService = new \App\Services\BadgeService();
        $generatedFiles = [];

        try {
            // Generate PDFs for each attendee
            foreach ($attendees as $attendee) {
                $pdfPath = $badgeService->generateBadgePDF($attendee);

                if ($pdfPath && \Storage::disk('local')->exists($pdfPath)) {
                    $fileName = $attendee->name . '_Badge.pdf';
                    // Sanitize filename
                    $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);

                    $destinationPath = $tempDir . '/' . $fileName;
                    // BadgeService uses 'local' disk which points to storage/app/private
                    $sourcePath = \Storage::disk('local')->path($pdfPath);

                    if (file_exists($sourcePath)) {
                        copy($sourcePath, $destinationPath);
                        $generatedFiles[] = $destinationPath;
                    }
                }
            }

            if (empty($generatedFiles)) {
                return back()->with('error', 'Failed to generate any badge PDFs.');
            }

            // Create ZIP file
            $zipPath = storage_path('app/temp/badges_' . time() . '.zip');
            $zip = new \ZipArchive();

            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                return back()->with('error', 'Failed to create ZIP file.');
            }

            foreach ($generatedFiles as $file) {
                $zip->addFile($file, basename($file));
            }

            $zip->close();

            // Schedule cleanup after download
            register_shutdown_function(function () use ($tempDir, $generatedFiles, $zipPath) {
                // Clean up temporary PDF files
                foreach ($generatedFiles as $file) {
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                }
                // Remove temp directory
                if (is_dir($tempDir)) {
                    @rmdir($tempDir);
                }
                // Remove ZIP file
                if (file_exists($zipPath)) {
                    @unlink($zipPath);
                }
            });

            // Return download
            return response()->download($zipPath, 'badges_' . date('Y-m-d_His') . '.zip');

        } catch (\Exception $e) {
            \Log::error('Bulk badge download failed: ' . $e->getMessage());

            // Cleanup on error
            foreach ($generatedFiles as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
            if (is_dir($tempDir)) {
                @rmdir($tempDir);
            }

            return back()->with('error', 'Failed to download badges: ' . $e->getMessage());
        }
    }

    /**
     * Start a batch download job for all attendees with filters
     */
    public function startBatchDownload(Request $request)
    {
        // Get current filters
        $filters = $request->only(['event_id', 'type', 'search']);

        // Count attendees with current filters
        $query = Attendee::whereNotNull('badge_generated_at');

        if (!empty($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $attendeeCount = $query->count();

        if ($attendeeCount === 0) {
            return back()->with('error', 'No generated badges found with the current filters.');
        }

        // Create batch download record
        $batchDownload = BadgeBatchDownload::create([
            'user_id' => auth()->id(),
            'event_id' => $filters['event_id'] ?? null,
            'status' => 'pending',
            'filters' => $filters,
        ]);

        // Dispatch the job
        \App\Jobs\GenerateBadgeBatchZip::dispatch($batchDownload);

        return response()->json([
            'success' => true,
            'message' => "Batch download started for {$attendeeCount} attendees. You can track the progress and download when ready.",
            'batch_id' => $batchDownload->id,
        ]);
    }

    /**
     * Get all batch downloads for the current user
     */
    public function getBatchDownloads(Request $request)
    {
        $batchDownloads = BadgeBatchDownload::where('user_id', auth()->id())
            ->with('event:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'event_name' => $batch->event?->name,
                    'status' => $batch->status,
                    'total_attendees' => $batch->total_attendees,
                    'processed_attendees' => $batch->processed_attendees,
                    'successful_badges' => $batch->successful_badges,
                    'failed_badges' => $batch->failed_badges,
                    'progress_percentage' => $batch->getProgressPercentage(),
                    'is_ready_for_download' => $batch->isReadyForDownload(),
                    'error_message' => $batch->error_message,
                    'started_at' => $batch->started_at?->toIso8601String(),
                    'completed_at' => $batch->completed_at?->toIso8601String(),
                    'created_at' => $batch->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'batches' => $batchDownloads,
        ]);
    }

    /**
     * Check status of a specific batch download
     */
    public function checkBatchStatus($batchId)
    {
        $batch = BadgeBatchDownload::where('id', $batchId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$batch) {
            return response()->json(['error' => 'Batch download not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'batch' => [
                'id' => $batch->id,
                'status' => $batch->status,
                'total_attendees' => $batch->total_attendees,
                'processed_attendees' => $batch->processed_attendees,
                'successful_badges' => $batch->successful_badges,
                'failed_badges' => $batch->failed_badges,
                'progress_percentage' => $batch->getProgressPercentage(),
                'is_ready_for_download' => $batch->isReadyForDownload(),
                'error_message' => $batch->error_message,
                'completed_at' => $batch->completed_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Download a completed batch ZIP file
     */
    public function downloadBatch($batchId)
    {
        $batch = BadgeBatchDownload::where('id', $batchId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$batch) {
            return back()->with('error', 'Batch download not found.');
        }

        if (!$batch->isReadyForDownload()) {
            return back()->with('error', 'Batch download is not ready yet or has failed.');
        }

        $zipPath = Storage::disk('local')->path($batch->zip_file_path);

        if (!file_exists($zipPath)) {
            return back()->with('error', 'ZIP file not found. It may have been cleaned up.');
        }

        return response()->download($zipPath, 'badges_batch_' . $batch->id . '.zip');
    }

    /**
     * Delete a batch download and its ZIP file
     */
    public function deleteBatch($batchId)
    {
        $batch = BadgeBatchDownload::where('id', $batchId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$batch) {
            return response()->json(['error' => 'Batch download not found.'], 404);
        }

        // Delete ZIP file if it exists
        if ($batch->zip_file_path && Storage::disk('local')->exists($batch->zip_file_path)) {
            Storage::disk('local')->delete($batch->zip_file_path);
        }

        // Delete the record
        $batch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Batch download deleted successfully.',
        ]);
    }
}
