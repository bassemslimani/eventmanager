<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $events = Event::latest()->paginate(20);

        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Events/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'location_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,active,completed,cancelled',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('event-logos', 'public');
        }

        $event = Event::create($validated);

        return redirect()->route('events.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): Response
    {
        $event->load(['attendees', 'checkIns']);

        return Inertia::render('Events/Show', [
            'event' => $event,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event): Response
    {
        return Inertia::render('Events/Edit', [
            'event' => $event,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        // Log incoming request data for debugging
        \Log::info('Event update request data:', [
            'event_id' => $event->id,
            'old_date' => $event->date,
            'new_date' => $request->input('date'),
            'all_data' => $request->except('logo')
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'location_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,active,completed,cancelled',
        ]);

        \Log::info('Event update validated data:', $validated);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($event->logo) {
                Storage::disk('public')->delete($event->logo);
            }
            $validated['logo'] = $request->file('logo')->store('event-logos', 'public');
        }

        $event->update($validated);

        \Log::info('Event after update:', [
            'event_id' => $event->id,
            'updated_date' => $event->date,
        ]);

        return redirect()->route('events.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }
}
