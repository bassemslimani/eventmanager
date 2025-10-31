<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailCampaign;
use App\Models\CampaignRecipient;
use App\Models\Attendee;
use App\Models\Event;
use App\Jobs\ProcessCampaign;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    /**
     * Display a listing of campaigns
     */
    public function index()
    {
        $campaigns = EmailCampaign::with('creator')
            ->latest()
            ->paginate(20);

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns
        ]);
    }

    /**
     * Show the form for creating a new campaign
     */
    public function create()
    {
        $events = Event::orderBy('name')->get();
        $categories = ['exhibitor', 'visitor', 'guest', 'speaker', 'organizer'];

        return Inertia::render('Campaigns/Create', [
            'events' => $events,
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'filters' => 'nullable',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // Max 10MB per file
        ]);

        // Parse filters from JSON string if needed
        $filters = [];
        if (!empty($validated['filters'])) {
            $filters = is_string($validated['filters'])
                ? json_decode($validated['filters'], true)
                : $validated['filters'];
        }

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('campaigns', 'public');
                $attachmentPaths[] = $path;
            }
        }

        // Create campaign
        $campaign = EmailCampaign::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'filters' => $filters,
            'attachments' => $attachmentPaths,
            'created_by' => Auth::id(),
            'status' => 'draft',
        ]);

        // Get filtered attendees and create recipients
        $attendees = $this->getFilteredAttendees($filters)->get();

        foreach ($attendees as $attendee) {
            CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'attendee_id' => $attendee->id,
                'status' => 'pending',
            ]);
        }

        // Update recipients count
        $campaign->update(['recipients_count' => $attendees->count()]);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign created successfully with ' . $attendees->count() . ' recipients.');
    }

    /**
     * Display the specified campaign
     */
    public function show(EmailCampaign $campaign)
    {
        $campaign->load('creator');

        $recipients = $campaign->recipients()
            ->with('attendee')
            ->paginate(50);

        $stats = [
            'total' => $campaign->recipients_count,
            'sent' => $campaign->sent_count,
            'failed' => $campaign->failed_count,
            'pending' => $campaign->recipients_count - $campaign->sent_count - $campaign->failed_count,
        ];

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
            'recipients' => $recipients,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified campaign
     */
    public function edit(EmailCampaign $campaign)
    {
        if (!$campaign->canEdit()) {
            return back()->with('error', 'Campaign cannot be edited in its current status.');
        }

        $events = Event::orderBy('name')->get();
        $categories = ['exhibitor', 'visitor', 'guest', 'speaker', 'organizer'];

        return Inertia::render('Campaigns/Edit', [
            'campaign' => $campaign,
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified campaign
     */
    public function update(Request $request, EmailCampaign $campaign)
    {
        if (!$campaign->canEdit()) {
            return back()->with('error', 'Campaign cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'filters' => 'nullable|array',
        ]);

        $campaign->update($validated);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign updated successfully.');
    }

    /**
     * Remove the specified campaign
     */
    public function destroy(EmailCampaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    /**
     * Send the campaign
     */
    public function send(EmailCampaign $campaign)
    {
        if (!$campaign->canSend()) {
            return back()->with('error', 'Campaign cannot be sent in its current status.');
        }

        if ($campaign->recipients_count === 0) {
            return back()->with('error', 'Campaign has no recipients.');
        }

        // Dispatch job to process campaign
        ProcessCampaign::dispatch($campaign);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign is being sent. Emails will be delivered shortly.');
    }

    /**
     * Preview recipients count based on filters
     */
    public function previewRecipients(Request $request)
    {
        $filters = $request->input('filters', []);
        $attendees = $this->getFilteredAttendees($filters);

        return response()->json([
            'count' => $attendees->count(),
            'preview' => $attendees->take(10)->get(['id', 'name', 'email', 'type'])
        ]);
    }

    /**
     * Get filtered attendees based on criteria
     */
    protected function getFilteredAttendees($filters)
    {
        $query = Attendee::query();

        // Filter by event
        if (!empty($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        }

        // Filter by category/type
        if (!empty($filters['categories'])) {
            $query->whereIn('type', $filters['categories']);
        }

        // Filter by registration date
        if (!empty($filters['registered_after'])) {
            $query->where('created_at', '>=', $filters['registered_after']);
        }

        if (!empty($filters['registered_before'])) {
            $query->where('created_at', '<=', $filters['registered_before']);
        }

        // Only attendees with email addresses
        $query->whereNotNull('email');

        return $query;
    }
}
