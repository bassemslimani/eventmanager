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
