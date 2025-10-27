<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendeeController extends Controller
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

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $attendees = $query->latest()->paginate(20);
        $events = Event::orderBy('name')->get();

        return Inertia::render('Attendees/Index', [
            'attendees' => $attendees,
            'events' => $events,
            'filters' => $request->only(['type', 'search', 'event_id']),
        ]);
    }

    public function create(): Response
    {
        $events = Event::where('status', 'active')->get();

        return Inertia::render('Attendees/Create', [
            'events' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'type' => 'required|in:exhibitor,guest,organizer',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|email|unique:attendees,email',
            'mobile' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'company_ar' => 'nullable|string|max:255',
            'category' => 'nullable|in:freelancer,company',
            'role' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $attendee = Attendee::create($validated);

        return redirect()->route('attendees.index')
            ->with('success', 'Attendee created successfully.');
    }

    public function show(Attendee $attendee): Response
    {
        return Inertia::render('Attendees/Show', [
            'attendee' => $attendee->load('event', 'checkIns'),
        ]);
    }

    public function edit(Attendee $attendee): Response
    {
        $events = Event::where('status', 'active')->get();

        return Inertia::render('Attendees/Edit', [
            'attendee' => $attendee,
            'events' => $events,
        ]);
    }

    public function update(Request $request, Attendee $attendee)
    {
        $validated = $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'type' => 'required|in:exhibitor,guest,organizer',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|email|unique:attendees,email,' . $attendee->id,
            'mobile' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'company_ar' => 'nullable|string|max:255',
            'category' => 'nullable|in:freelancer,company',
            'role' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $attendee->update($validated);

        return redirect()->route('attendees.index')
            ->with('success', 'Attendee updated successfully.');
    }

    public function destroy(Attendee $attendee)
    {
        $attendee->delete();

        return redirect()->route('attendees.index')
            ->with('success', 'Attendee deleted successfully.');
    }
}
