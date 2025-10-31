<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\CheckIn;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Mail\CheckInWelcomeEmail;

class CheckInController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('CheckIn/Scanner');
    }

    public function scan(Request $request)
    {
        try {
            \Log::info('QR Code Scan Request', [
                'qr_code' => $request->qr_code,
                'user_id' => auth()->id(),
            ]);

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
                \Log::warning('Attendee not found', [
                    'qr_code' => $validated['qr_code'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Attendee not found. Please check the QR code.',
                ], 404);
            }

            // Check if already checked in
            if ($attendee->checked_in_at) {
                \Log::info('Attendee already checked in', [
                    'attendee_id' => $attendee->id,
                    'checked_in_at' => $attendee->checked_in_at,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Attendee already checked in at ' . $attendee->checked_in_at->format('H:i'),
                    'attendee' => $attendee,
                ], 200);
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

            // Send welcome email to attendee
            try {
                Mail::to($attendee->email)->send(new CheckInWelcomeEmail($attendee->fresh()));
                \Log::info('Check-in welcome email sent', [
                    'attendee_id' => $attendee->id,
                    'attendee_email' => $attendee->email,
                ]);
            } catch (\Exception $e) {
                // Log email error but don't fail the check-in
                \Log::error('Failed to send check-in welcome email', [
                    'attendee_id' => $attendee->id,
                    'attendee_email' => $attendee->email,
                    'error' => $e->getMessage(),
                ]);
            }

            \Log::info('Check-in successful', [
                'attendee_id' => $attendee->id,
                'attendee_name' => $attendee->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!',
                'attendee' => $attendee->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in scan', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error processing check-in scan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
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

        // Send welcome email to attendee
        try {
            Mail::to($attendee->email)->send(new CheckInWelcomeEmail($attendee->fresh()));
            \Log::info('Check-in welcome email sent (manual)', [
                'attendee_id' => $attendee->id,
                'attendee_email' => $attendee->email,
            ]);
        } catch (\Exception $e) {
            // Log email error but don't fail the check-in
            \Log::error('Failed to send check-in welcome email (manual)', [
                'attendee_id' => $attendee->id,
                'attendee_email' => $attendee->email,
                'error' => $e->getMessage(),
            ]);
        }

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

    public function clearAll(Request $request, Event $event)
    {
        try {
            // Ensure user is admin
            if (!$request->user() || !$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can clear check-ins.',
                ], 403);
            }

            // Validate password
            $request->validate([
                'password' => 'required|string',
            ]);

            // Verify admin password
            if (!Hash::check($request->password, $request->user()->password)) {
                throw ValidationException::withMessages([
                    'password' => 'The provided password is incorrect.',
                ]);
            }

            // Count check-ins before clearing
            $checkInCount = CheckIn::where('event_id', $event->id)->count();
            $attendeeCount = Attendee::where('event_id', $event->id)
                ->whereNotNull('checked_in_at')
                ->count();

            // Clear check-in records
            CheckIn::where('event_id', $event->id)->delete();

            // Reset attendee check-in status
            Attendee::where('event_id', $event->id)
                ->update([
                    'checked_in_at' => null,
                    'checked_in_by' => null,
                ]);

            \Log::info('All check-ins cleared for event', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'admin_id' => $request->user()->id,
                'admin_email' => $request->user()->email,
                'check_ins_cleared' => $checkInCount,
                'attendees_reset' => $attendeeCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared {$checkInCount} check-in records for {$attendeeCount} attendees.",
                'cleared_count' => $checkInCount,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error clearing check-ins', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
