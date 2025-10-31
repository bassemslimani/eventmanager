<?php

namespace App\Jobs;

use App\Models\Attendee;
use App\Models\BadgeBatchDownload;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GenerateBadgeBatchZip implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 1; // Don't retry on failure

    /**
     * Create a new job instance.
     */
    public function __construct(
        public BadgeBatchDownload $batchDownload
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Starting badge batch download job for batch ID: {$this->batchDownload->id}");

        try {
            // Update status to processing
            $this->batchDownload->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Build query based on filters
            $filters = $this->batchDownload->filters ?? [];
            $query = Attendee::with('event')->whereNotNull('badge_generated_at');

            // Apply filters
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

            // Get total count
            $totalAttendees = $query->count();

            if ($totalAttendees === 0) {
                $this->batchDownload->update([
                    'status' => 'failed',
                    'error_message' => 'No attendees found with the specified filters.',
                    'completed_at' => now(),
                ]);
                return;
            }

            $this->batchDownload->update([
                'total_attendees' => $totalAttendees,
            ]);

            Log::info("Found {$totalAttendees} attendees to process for batch ID: {$this->batchDownload->id}");

            // Create temporary directory for PDFs
            $tempDir = storage_path('app/temp/batch_badges_' . $this->batchDownload->id);
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $badgeService = new BadgeService();
            $generatedFiles = [];
            $processedCount = 0;
            $successCount = 0;
            $failedCount = 0;

            // Process in chunks of 50 to avoid memory issues
            $chunkSize = 50;
            $query->chunk($chunkSize, function ($attendees) use (
                $badgeService,
                &$generatedFiles,
                &$processedCount,
                &$successCount,
                &$failedCount,
                $tempDir
            ) {
                foreach ($attendees as $attendee) {
                    try {
                        // Generate badge PDF
                        $pdfPath = $badgeService->generateBadgePDF($attendee);

                        if ($pdfPath && Storage::disk('local')->exists($pdfPath)) {
                            // Sanitize filename
                            $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $attendee->name) . '_Badge.pdf';
                            $destinationPath = $tempDir . '/' . $fileName;
                            $sourcePath = Storage::disk('local')->path($pdfPath);

                            if (file_exists($sourcePath)) {
                                copy($sourcePath, $destinationPath);
                                $generatedFiles[] = $destinationPath;
                                $successCount++;

                                // Clean up the source PDF after copying
                                Storage::disk('local')->delete($pdfPath);
                            } else {
                                Log::warning("Source PDF not found for attendee {$attendee->id}: {$sourcePath}");
                                $failedCount++;
                            }
                        } else {
                            Log::warning("Failed to generate PDF for attendee {$attendee->id}");
                            $failedCount++;
                        }
                    } catch (\Exception $e) {
                        Log::error("Error generating badge for attendee {$attendee->id}: " . $e->getMessage());
                        $failedCount++;
                    }

                    $processedCount++;

                    // Update progress every 10 attendees
                    if ($processedCount % 10 === 0) {
                        $this->batchDownload->update([
                            'processed_attendees' => $processedCount,
                            'successful_badges' => $successCount,
                            'failed_badges' => $failedCount,
                        ]);

                        Log::info("Progress for batch {$this->batchDownload->id}: {$processedCount}/{$this->batchDownload->total_attendees} processed");
                    }

                    // Free up memory
                    unset($attendee);
                }

                // Force garbage collection after each chunk
                gc_collect_cycles();
            });

            // Final progress update
            $this->batchDownload->update([
                'processed_attendees' => $processedCount,
                'successful_badges' => $successCount,
                'failed_badges' => $failedCount,
            ]);

            if (empty($generatedFiles)) {
                $this->batchDownload->update([
                    'status' => 'failed',
                    'error_message' => 'Failed to generate any badge PDFs.',
                    'completed_at' => now(),
                ]);

                // Cleanup
                $this->cleanupTempDirectory($tempDir);
                return;
            }

            Log::info("Generating ZIP file for batch {$this->batchDownload->id} with {$successCount} badges");

            // Create ZIP file
            $zipFileName = 'badges_batch_' . $this->batchDownload->id . '_' . date('Y-m-d_His') . '.zip';
            $zipPath = 'batch_downloads/' . $zipFileName;
            $fullZipPath = Storage::disk('local')->path($zipPath);

            // Ensure directory exists
            $zipDir = dirname($fullZipPath);
            if (!file_exists($zipDir)) {
                mkdir($zipDir, 0755, true);
            }

            $zip = new ZipArchive();

            if ($zip->open($fullZipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception('Failed to create ZIP file.');
            }

            foreach ($generatedFiles as $file) {
                $zip->addFile($file, basename($file));
            }

            $zip->close();

            Log::info("ZIP file created successfully for batch {$this->batchDownload->id}: {$zipPath}");

            // Update batch download record
            $this->batchDownload->update([
                'status' => 'completed',
                'zip_file_path' => $zipPath,
                'completed_at' => now(),
            ]);

            // Cleanup temporary PDF files
            $this->cleanupTempDirectory($tempDir);

            Log::info("Badge batch download completed successfully for batch ID: {$this->batchDownload->id}");

        } catch (\Exception $e) {
            Log::error("Badge batch download failed for batch ID {$this->batchDownload->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            $this->batchDownload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            // Cleanup on error
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->cleanupTempDirectory($tempDir);
            }
        }
    }

    /**
     * Clean up temporary directory and files
     */
    private function cleanupTempDirectory(string $tempDir): void
    {
        try {
            if (is_dir($tempDir)) {
                $files = glob($tempDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
                @rmdir($tempDir);
                Log::info("Cleaned up temporary directory: {$tempDir}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to cleanup temp directory {$tempDir}: " . $e->getMessage());
        }
    }
}
