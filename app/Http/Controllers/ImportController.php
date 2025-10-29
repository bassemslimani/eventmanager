<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Admin sees all active events
        if ($user->role === 'admin') {
            $events = Event::where('status', 'active')->get();
        }
        // Event managers and agents only see their assigned events
        else {
            $events = $user->events()
                ->where('status', 'active')
                ->get();
        }

        return Inertia::render('Import/Index', [
            'events' => $events,
        ]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'type', 'name', 'name_ar', 'email', 'mobile',
            'company', 'company_ar', 'category', 'role', 'department'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        // Add sample data
        $sampleData = [
            ['exhibitor', 'John Doe', 'جون دو', 'john@example.com', '+966501234567', 'Tech Corp', 'شركة التقنية', 'company', '', ''],
            ['guest', 'Jane Smith', 'جين سميث', 'jane@example.com', '+966501234568', 'Design Studio', 'استوديو التصميم', '', '', ''],
            ['organizer', 'Admin User', 'المسؤول', 'adminuser@example.com', '+966501234569', '', '', '', 'Event Manager', 'Operations'],
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981'],
            ],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $fileName = 'attendees_import_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'event_id' => 'required|exists:events,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports', 'local');

        // Create import log
        $importLog = ImportLog::create([
            'user_id' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'type' => 'attendees',
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'import_log_id' => $importLog->id,
            'message' => 'File uploaded successfully. Processing...',
        ]);
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'import_log_id' => 'required|exists:import_logs,id',
            'event_id' => 'required|exists:events,id',
        ]);

        $importLog = ImportLog::find($validated['import_log_id']);
        $importLog->update(['status' => 'processing']);

        try {
            // Local disk uses storage/app/private as root
            $filePath = Storage::disk('local')->path($importLog->file_path);
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header row
            $headers = array_shift($rows);

            $totalRecords = count($rows);
            $processed = 0;
            $failed = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = [
                        'event_id' => $validated['event_id'],
                        'type' => $row[0] ?? 'guest',
                        'name' => $row[1],
                        'name_ar' => $row[2] ?? null,
                        'email' => $row[3],
                        'mobile' => $row[4] ?? null,
                        'company' => $row[5] ?? null,
                        'company_ar' => $row[6] ?? null,
                        'category' => $row[7] ?? null,
                        'role' => $row[8] ?? null,
                        'department' => $row[9] ?? null,
                    ];

                    Attendee::create($data);
                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            $importLog->update([
                'total_records' => $totalRecords,
                'processed' => $processed,
                'failed' => $failed,
                'errors' => $errors,
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Import completed. Processed: {$processed}, Failed: {$failed}",
                'import_log' => $importLog,
            ]);
        } catch (\Exception $e) {
            $importLog->update([
                'status' => 'failed',
                'errors' => [$e->getMessage()],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function history(): Response
    {
        $imports = ImportLog::with('user')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return Inertia::render('Import/History', [
            'imports' => $imports,
        ]);
    }
}
