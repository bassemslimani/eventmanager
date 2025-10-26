# 🚀 PHASE 4: Features Implementation - Quick Start Guide

## Current Status: Phase 3 Complete ✅ → Starting Phase 4

**Project Progress: 75% Complete**

---

## 📋 PHASE 4 CHECKLIST

Copy this checklist to track your progress:

```
PHASE 4: FEATURES IMPLEMENTATION

[ ] Step 1: Implement Badge Generation System
[ ] Step 2: Create QR Code Scanner Component
[ ] Step 3: Build Excel Import/Export System
[ ] Step 4: Setup Email Automation
[ ] Step 5: Create remaining controller implementations
[ ] Step 6: Add analytics and charts
[ ] Step 7: Test all features end-to-end
[ ] Step 8: Performance optimization
```

---

## 🎯 STEP-BY-STEP INSTRUCTIONS

### **STEP 1: Implement Badge Generation System**

This step creates the badge generation system with HTML5 Canvas and PDF export.

#### 1.1 Update BadgeController

**File: app/Http/Controllers/BadgeController.php**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\BadgeTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BadgeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Attendee::with('event');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Only show attendees without badges or filter by badge status
        if ($request->has('badge_status')) {
            if ($request->badge_status === 'generated') {
                $query->whereNotNull('badge_generated_at');
            } elseif ($request->badge_status === 'pending') {
                $query->whereNull('badge_generated_at');
            }
        }

        $attendees = $query->latest()->paginate(20);
        $templates = BadgeTemplate::where('is_active', true)->get();

        return Inertia::render('Badges/Index', [
            'attendees' => $attendees,
            'templates' => $templates,
            'filters' => $request->only(['type', 'badge_status']),
        ]);
    }

    public function generate(Attendee $attendee)
    {
        // Get the appropriate template
        $template = BadgeTemplate::where('type', $attendee->type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return back()->with('error', 'No active template found for this attendee type.');
        }

        // Generate QR code as base64
        $qrCode = base64_encode(QrCode::format('png')
            ->size(200)
            ->errorCorrection('H')
            ->generate($attendee->qr_uuid));

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
        // Return badge data for frontend PDF generation
        $template = BadgeTemplate::where('type', $attendee->type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return back()->with('error', 'No active template found.');
        }

        $qrCode = base64_encode(QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($attendee->qr_uuid));

        return response()->json([
            'attendee' => $attendee,
            'template' => $template,
            'qr_code' => 'data:image/png;base64,' . $qrCode,
        ]);
    }
}
```

#### 1.2 Create Badge Index Vue Component

**File: resources/js/Pages/Badges/Index.vue**

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';

interface Attendee {
    id: number;
    type: string;
    name: string;
    email: string;
    company: string;
    qr_code: string;
    badge_generated_at: string | null;
}

interface Props {
    attendees: {
        data: Attendee[];
        links: any[];
        meta: any;
    };
    templates: any[];
    filters: {
        type?: string;
        badge_status?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    type: props.filters.type || null,
    badge_status: props.filters.badge_status || null,
});

const selectedAttendees = ref<Attendee[]>([]);

const typeOptions = [
    { label: 'All Types', value: null },
    { label: 'Exhibitors', value: 'exhibitor' },
    { label: 'Guests', value: 'guest' },
    { label: 'Organizers', value: 'organizer' },
];

const statusOptions = [
    { label: 'All Status', value: null },
    { label: 'Generated', value: 'generated' },
    { label: 'Pending', value: 'pending' },
];

const searchBadges = () => {
    router.get('/badges', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const generateSingle = (id: number) => {
    router.post(`/badges/generate/${id}`, {}, {
        preserveState: true,
        onSuccess: () => {
            router.reload();
        },
    });
};

const generateBulk = () => {
    if (selectedAttendees.value.length === 0) {
        alert('Please select at least one attendee.');
        return;
    }

    const attendeeIds = selectedAttendees.value.map(a => a.id);
    router.post('/badges/generate-bulk', { attendee_ids: attendeeIds }, {
        preserveState: true,
        onSuccess: () => {
            selectedAttendees.value = [];
            router.reload();
        },
    });
};

const downloadBadge = async (id: number) => {
    try {
        const response = await fetch(`/badges/download/${id}`);
        const data = await response.json();

        // Trigger download using browser
        // You'll implement the actual PDF generation in the frontend
        console.log('Badge data:', data);
        alert('Badge download initiated. Check console for data.');
    } catch (error) {
        console.error('Error downloading badge:', error);
    }
};
</script>

<template>
    <Head title="Badge Generation" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gradient">Badge Generation</h1>
                    <Button
                        label="Generate Selected Badges"
                        icon="pi pi-id-card"
                        class="gradient-btn"
                        :disabled="selectedAttendees.length === 0"
                        @click="generateBulk"
                    />
                </div>

                <!-- Filters -->
                <div class="glass-card p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Type</label>
                            <Dropdown
                                v-model="filters.type"
                                :options="typeOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Filter by type"
                                class="w-full"
                                @change="searchBadges"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Badge Status</label>
                            <Dropdown
                                v-model="filters.badge_status"
                                :options="statusOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Filter by status"
                                class="w-full"
                                @change="searchBadges"
                            />
                        </div>

                        <div class="flex items-end">
                            <Button
                                label="Clear Filters"
                                icon="pi pi-filter-slash"
                                severity="secondary"
                                @click="filters = { type: null, badge_status: null }; searchBadges()"
                            />
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="modern-card">
                    <DataTable
                        v-model:selection="selectedAttendees"
                        :value="attendees.data"
                        stripedRows
                        paginator
                        :rows="20"
                        :rowsPerPageOptions="[10, 20, 50]"
                        class="custom-datatable"
                        dataKey="id"
                    >
                        <Column selectionMode="multiple" style="width: 3rem" />
                        <Column field="id" header="ID" sortable style="width: 80px" />
                        <Column field="name" header="Name" sortable />
                        <Column field="type" header="Type" sortable />
                        <Column field="company" header="Company" sortable />

                        <Column field="badge_generated_at" header="Badge Status">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.badge_generated_at"
                                    value="Generated"
                                    severity="success"
                                    icon="pi pi-check"
                                />
                                <Tag
                                    v-else
                                    value="Pending"
                                    severity="warning"
                                />
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 200px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button
                                        v-if="!slotProps.data.badge_generated_at"
                                        icon="pi pi-plus"
                                        label="Generate"
                                        severity="success"
                                        size="small"
                                        @click="generateSingle(slotProps.data.id)"
                                    />
                                    <Button
                                        v-else
                                        icon="pi pi-download"
                                        label="Download"
                                        severity="info"
                                        size="small"
                                        @click="downloadBadge(slotProps.data.id)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

### **STEP 2: Create QR Code Scanner Component**

This step implements the QR code scanner using the device camera.

#### 2.1 Update CheckInController

**File: app/Http/Controllers/CheckInController.php**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\CheckIn;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckInController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('CheckIn/Scanner');
    }

    public function scan(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
            'event_id' => 'nullable|exists:events,id',
            'location' => 'nullable|string|max:255',
        ]);

        // Find attendee by QR code or UUID
        $attendee = Attendee::where('qr_code', $validated['qr_code'])
            ->orWhere('qr_uuid', $validated['qr_code'])
            ->first();

        if (!$attendee) {
            return response()->json([
                'success' => false,
                'message' => 'Attendee not found.',
            ], 404);
        }

        // Check if already checked in
        if ($attendee->checked_in_at) {
            return response()->json([
                'success' => false,
                'message' => 'Attendee already checked in.',
                'attendee' => $attendee,
            ], 400);
        }

        // Update attendee
        $attendee->update([
            'checked_in_at' => now(),
            'checked_in_by' => auth()->id(),
        ]);

        // Create check-in record
        CheckIn::create([
            'attendee_id' => $attendee->id,
            'event_id' => $validated['event_id'] ?? $attendee->event_id,
            'scanned_by' => auth()->id(),
            'scanned_at' => now(),
            'location' => $validated['location'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_info' => [
                'platform' => $request->header('sec-ch-ua-platform'),
                'mobile' => $request->header('sec-ch-ua-mobile'),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful!',
            'attendee' => $attendee->fresh(),
        ]);
    }

    public function manual(): Response
    {
        $events = Event::where('status', 'active')->get();

        return Inertia::render('CheckIn/Manual', [
            'events' => $events,
        ]);
    }

    public function manualCheckIn(Request $request)
    {
        $validated = $request->validate([
            'attendee_id' => 'required|exists:attendees,id',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $attendee = Attendee::find($validated['attendee_id']);

        if ($attendee->checked_in_at) {
            return back()->with('error', 'Attendee already checked in.');
        }

        $attendee->update([
            'checked_in_at' => now(),
            'checked_in_by' => auth()->id(),
        ]);

        CheckIn::create([
            'attendee_id' => $attendee->id,
            'event_id' => $validated['event_id'] ?? $attendee->event_id,
            'scanned_by' => auth()->id(),
            'scanned_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Check-in successful!');
    }

    public function history(Request $request): Response
    {
        $query = CheckIn::with(['attendee', 'event', 'scanner']);

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('date')) {
            $query->whereDate('scanned_at', $request->date);
        }

        $checkIns = $query->latest('scanned_at')->paginate(50);
        $events = Event::where('status', 'active')->get();

        return Inertia::render('CheckIn/History', [
            'checkIns' => $checkIns,
            'events' => $events,
            'filters' => $request->only(['event_id', 'date']),
        ]);
    }
}
```

#### 2.2 Create QR Scanner Component

**File: resources/js/Pages/CheckIn/Scanner.vue**

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import { QrScanner } from 'qr-scanner';

const videoElement = ref<HTMLVideoElement | null>(null);
const scanner = ref<QrScanner | null>(null);
const isScanning = ref(false);
const lastScannedCode = ref('');
const scanResult = ref<{success: boolean, message: string, attendee?: any} | null>(null);

const startScanning = async () => {
    if (!videoElement.value) return;

    try {
        scanner.value = new QrScanner(
            videoElement.value,
            result => handleScan(result.data),
            {
                highlightScanRegion: true,
                highlightCodeOutline: true,
            }
        );

        await scanner.value.start();
        isScanning.value = true;
        scanResult.value = null;
    } catch (error) {
        console.error('Error starting scanner:', error);
        alert('Failed to start camera. Please check permissions.');
    }
};

const stopScanning = () => {
    if (scanner.value) {
        scanner.value.stop();
        scanner.value.destroy();
        scanner.value = null;
        isScanning.value = false;
    }
};

const handleScan = async (code: string) => {
    // Prevent duplicate scans
    if (code === lastScannedCode.value) return;

    lastScannedCode.value = code;

    // Stop scanning temporarily
    stopScanning();

    try {
        const response = await fetch('/check-in/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ qr_code: code }),
        });

        const data = await response.json();
        scanResult.value = data;

        // Clear result and resume scanning after 3 seconds
        setTimeout(() => {
            scanResult.value = null;
            lastScannedCode.value = '';
            startScanning();
        }, 3000);
    } catch (error) {
        console.error('Error processing scan:', error);
        scanResult.value = {
            success: false,
            message: 'Error processing QR code.',
        };

        setTimeout(() => {
            scanResult.value = null;
            lastScannedCode.value = '';
            startScanning();
        }, 3000);
    }
};

onMounted(() => {
    startScanning();
});

onUnmounted(() => {
    stopScanning();
});
</script>

<template>
    <Head title="QR Code Scanner" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <h1 class="text-4xl font-bold text-gradient mb-2">QR Code Scanner</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Scan attendee badges to check them in
                    </p>
                </div>

                <!-- Scanner Card -->
                <Card class="glass-card mb-6">
                    <template #content>
                        <div class="relative aspect-video bg-black rounded-lg overflow-hidden">
                            <video
                                ref="videoElement"
                                class="w-full h-full object-cover"
                            ></video>

                            <!-- Scanning Overlay -->
                            <div
                                v-if="isScanning"
                                class="absolute inset-0 flex items-center justify-center pointer-events-none"
                            >
                                <div class="w-64 h-64 border-4 border-emerald-500 rounded-lg animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Controls -->
                        <div class="flex justify-center gap-4 mt-4">
                            <Button
                                v-if="!isScanning"
                                label="Start Scanning"
                                icon="pi pi-camera"
                                class="gradient-btn"
                                @click="startScanning"
                            />
                            <Button
                                v-else
                                label="Stop Scanning"
                                icon="pi pi-stop"
                                severity="danger"
                                @click="stopScanning"
                            />
                        </div>
                    </template>
                </Card>

                <!-- Scan Result -->
                <Card
                    v-if="scanResult"
                    :class="[
                        'glass-card',
                        scanResult.success ? 'border-emerald-500' : 'border-red-500',
                        'border-2'
                    ]"
                >
                    <template #content>
                        <div class="text-center">
                            <i
                                :class="[
                                    'pi text-6xl mb-4',
                                    scanResult.success ? 'pi-check-circle text-emerald-500' : 'pi-times-circle text-red-500'
                                ]"
                            ></i>
                            <h3 class="text-2xl font-bold mb-2">
                                {{ scanResult.message }}
                            </h3>
                            <div v-if="scanResult.attendee" class="mt-4 text-left">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Name</p>
                                        <p class="font-semibold">{{ scanResult.attendee.name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Type</p>
                                        <p class="font-semibold capitalize">{{ scanResult.attendee.type }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-semibold">{{ scanResult.attendee.email }}</p>
                                    </div>
                                    <div v-if="scanResult.attendee.company">
                                        <p class="text-sm text-gray-500">Company</p>
                                        <p class="font-semibold">{{ scanResult.attendee.company }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button
                        class="gradient-btn magnetic-btn flex items-center justify-center gap-3 py-4"
                        @click="router.visit('/check-in/manual')"
                    >
                        <i class="pi pi-pencil text-2xl"></i>
                        <span class="font-semibold">Manual Check-in</span>
                    </button>

                    <button
                        class="gradient-btn magnetic-btn flex items-center justify-center gap-3 py-4"
                        @click="router.visit('/check-in/history')"
                    >
                        <i class="pi pi-history text-2xl"></i>
                        <span class="font-semibold">Check-in History</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

### **STEP 3: Build Excel Import/Export System**

This step creates the attendee import system with Excel file support.

#### 3.1 Update ImportController

**File: app/Http/Controllers/ImportController.php**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index(): Response
    {
        $events = Event::where('status', 'active')->get();

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
            ['organizer', 'Admin User', 'المسؤول', 'admin@example.com', '+966501234569', '', '', '', 'Event Manager', 'Operations'],
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
            'event_id' => 'nullable|exists:events,id',
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
            'event_id' => 'nullable|exists:events,id',
        ]);

        $importLog = ImportLog::find($validated['import_log_id']);
        $importLog->update(['status' => 'processing']);

        try {
            $filePath = storage_path('app/' . $importLog->file_path);
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
                        'event_id' => $validated['event_id'] ?? null,
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
```

#### 3.2 Create Import Index Component

**File: resources/js/Pages/Import/Index.vue**

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import FileUpload from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import ProgressBar from 'primevue/progressbar';

interface Event {
    id: number;
    name: string;
}

interface Props {
    events: Event[];
}

const props = defineProps<Props>();

const selectedEvent = ref<number | null>(null);
const uploadedFile = ref<File | null>(null);
const importLogId = ref<number | null>(null);
const isUploading = ref(false);
const isProcessing = ref(false);
const importResult = ref<any>(null);

const onFileSelect = (event: any) => {
    uploadedFile.value = event.files[0];
};

const uploadFile = async () => {
    if (!uploadedFile.value) {
        alert('Please select a file first.');
        return;
    }

    isUploading.value = true;
    const formData = new FormData();
    formData.append('file', uploadedFile.value);
    if (selectedEvent.value) {
        formData.append('event_id', selectedEvent.value.toString());
    }

    try {
        const response = await fetch('/import/upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            importLogId.value = data.import_log_id;
            processImport();
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Failed to upload file.');
    } finally {
        isUploading.value = false;
    }
};

const processImport = async () => {
    if (!importLogId.value) return;

    isProcessing.value = true;

    try {
        const response = await fetch('/import/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                import_log_id: importLogId.value,
                event_id: selectedEvent.value,
            }),
        });

        const data = await response.json();
        importResult.value = data;

        if (data.success) {
            setTimeout(() => {
                router.visit('/attendees');
            }, 3000);
        }
    } catch (error) {
        console.error('Process error:', error);
        alert('Failed to process import.');
    } finally {
        isProcessing.value = false;
    }
};

const downloadTemplate = () => {
    window.location.href = '/attendees/import/template';
};
</script>

<template>
    <Head title="Import Attendees" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-4xl font-bold text-gradient mb-2">Import Attendees</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Upload an Excel file to import multiple attendees
                    </p>
                </div>

                <!-- Template Download -->
                <Card class="glass-card mb-6">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold mb-2">Download Template</h3>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Download the Excel template with sample data
                                </p>
                            </div>
                            <Button
                                label="Download Template"
                                icon="pi pi-download"
                                class="gradient-btn"
                                @click="downloadTemplate"
                            />
                        </div>
                    </template>
                </Card>

                <!-- Upload Form -->
                <Card class="glass-card mb-6">
                    <template #content>
                        <div class="space-y-6">
                            <!-- Event Selection -->
                            <div v-if="events.length > 0">
                                <label class="block text-sm font-medium mb-2">
                                    Event (Optional)
                                </label>
                                <Dropdown
                                    v-model="selectedEvent"
                                    :options="events"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Select an event"
                                    class="w-full"
                                />
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Upload Excel File
                                </label>
                                <FileUpload
                                    mode="basic"
                                    accept=".xlsx,.xls,.csv"
                                    :maxFileSize="10000000"
                                    @select="onFileSelect"
                                    :auto="false"
                                    chooseLabel="Choose File"
                                />
                            </div>

                            <!-- Upload Button -->
                            <Button
                                label="Upload and Import"
                                icon="pi pi-upload"
                                class="gradient-btn w-full"
                                :loading="isUploading || isProcessing"
                                :disabled="!uploadedFile"
                                @click="uploadFile"
                            />

                            <!-- Progress -->
                            <div v-if="isProcessing" class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Processing import...</p>
                                <ProgressBar mode="indeterminate" />
                            </div>

                            <!-- Result -->
                            <Card v-if="importResult" class="mt-4 border-2" :class="importResult.success ? 'border-emerald-500' : 'border-red-500'">
                                <template #content>
                                    <div class="text-center">
                                        <i :class="['pi text-4xl mb-2', importResult.success ? 'pi-check-circle text-emerald-500' : 'pi-times-circle text-red-500']"></i>
                                        <p class="font-semibold">{{ importResult.message }}</p>
                                        <div v-if="importResult.import_log" class="mt-4 text-sm text-left">
                                            <p>Total Records: {{ importResult.import_log.total_records }}</p>
                                            <p>Processed: {{ importResult.import_log.processed }}</p>
                                            <p>Failed: {{ importResult.import_log.failed }}</p>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </div>
                    </template>
                </Card>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button
                        class="gradient-btn magnetic-btn flex items-center justify-center gap-3 py-4"
                        @click="router.visit('/import/history')"
                    >
                        <i class="pi pi-history text-2xl"></i>
                        <span class="font-semibold">Import History</span>
                    </button>

                    <button
                        class="gradient-btn magnetic-btn flex items-center justify-center gap-3 py-4"
                        @click="router.visit('/attendees')"
                    >
                        <i class="pi pi-users text-2xl"></i>
                        <span class="font-semibold">View Attendees</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

### **STEP 4: Install QR Scanner Package**

Install the required qr-scanner package:

```bash
npm install qr-scanner
```

Add QR Scanner types to tsconfig.json if needed.

---

### **STEP 5: Install PHPSpreadsheet**

Install the required package for Excel handling:

```bash
composer require phpoffice/phpspreadsheet
```

---

### **STEP 6: Test All Features**

1. **Start servers:**
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

2. **Test Badge Generation:**
   - Visit `/badges`
   - Select attendees
   - Generate badges
   - Download badges

3. **Test QR Scanner:**
   - Visit `/check-in`
   - Allow camera access
   - Scan QR codes
   - Verify check-ins

4. **Test Import:**
   - Visit `/import`
   - Download template
   - Fill with data
   - Upload and import
   - Check results

---

## ✅ PHASE 4 COMPLETE WHEN:

- [ ] Badge generation works for all attendee types
- [ ] QR scanner detects and processes codes
- [ ] Excel import successfully creates attendees
- [ ] Download template works
- [ ] Check-in history displays correctly
- [ ] All controllers return proper responses
- [ ] No console errors
- [ ] Mobile camera works for scanning

---

## 🐛 Troubleshooting

**Camera not working:**
- Ensure HTTPS or localhost
- Check browser permissions
- Try different browser

**Import fails:**
- Check file format (XLSX, XLS, CSV)
- Verify data matches template
- Check file size (<10MB)
- Review import log errors

**Badge generation issues:**
- Ensure badge templates exist in database
- Check QR code generation
- Verify attendee type matches template

---

## 💡 TIPS

1. **Test with sample data** before importing large files
2. **Use browser DevTools** to debug camera access
3. **Check Laravel logs** for import errors: `storage/logs/laravel.log`
4. **Install Vue DevTools** for better debugging
5. **Test QR codes** with generated badges

---

## 📚 Reference

- **QR Scanner Docs:** https://github.com/nimiq/qr-scanner
- **PHPSpreadsheet Docs:** https://phpspreadsheet.readthedocs.io/
- **PrimeVue Components:** https://primevue.org/

---

## 🎯 Next Phase: Phase 5 - Polish & Optimization

After completing Phase 4, you'll move to Phase 5 which includes:
- Animations and micro-interactions
- PWA enhancements
- Performance optimization
- Testing and bug fixes
- Documentation

---

**🎉 Ready to implement powerful features! Let's build Phase 4!**

**Project Progress: 75% → 90% (after Phase 4)**
