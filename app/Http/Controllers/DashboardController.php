<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\CheckIn;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Agents see simplified scanner-only dashboard
        if ($user->isAgent()) {
            return $this->agentDashboard($user);
        }

        // Event managers see their events only
        if ($user->isEventManager()) {
            return $this->eventManagerDashboard($user);
        }

        // Admins see everything
        return $this->adminDashboard();
    }

    /**
     * Admin dashboard with all stats
     */
    private function adminDashboard(): Response
    {
        $stats = [
            'total_attendees' => Attendee::count(),
            'total_exhibitors' => Attendee::where('type', 'exhibitor')->count(),
            'total_guests' => Attendee::where('type', 'guest')->count(),
            'total_organizers' => Attendee::where('type', 'organizer')->count(),
            'checked_in_today' => CheckIn::whereDate('scanned_at', today())->count(),
            'badges_generated' => Attendee::whereNotNull('badge_generated_at')->count(),
            'active_events' => Event::where('status', 'active')->count(),
        ];

        $recentCheckIns = CheckIn::with(['attendee', 'scanner'])
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        $upcomingEvents = Event::where('status', 'active')
            ->where('date', '>=', today())
            ->orderBy('date')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recentCheckIns' => $recentCheckIns,
            'upcomingEvents' => $upcomingEvents,
            'userRole' => 'admin',
        ]);
    }

    /**
     * Event manager dashboard - only their events
     */
    private function eventManagerDashboard($user): Response
    {
        $eventIds = $user->managedEvents()->pluck('events.id');

        $stats = [
            'total_attendees' => Attendee::whereIn('event_id', $eventIds)->count(),
            'total_exhibitors' => Attendee::whereIn('event_id', $eventIds)->where('type', 'exhibitor')->count(),
            'total_guests' => Attendee::whereIn('event_id', $eventIds)->where('type', 'guest')->count(),
            'total_organizers' => Attendee::whereIn('event_id', $eventIds)->where('type', 'organizer')->count(),
            'checked_in_today' => CheckIn::whereIn('event_id', $eventIds)->whereDate('scanned_at', today())->count(),
            'badges_generated' => Attendee::whereIn('event_id', $eventIds)->whereNotNull('badge_generated_at')->count(),
            'managed_events' => $eventIds->count(),
        ];

        $recentCheckIns = CheckIn::with(['attendee', 'scanner'])
            ->whereIn('event_id', $eventIds)
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        $managedEvents = $user->managedEvents()
            ->where('status', 'active')
            ->where('date', '>=', today())
            ->orderBy('date')
            ->get();

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recentCheckIns' => $recentCheckIns,
            'upcomingEvents' => $managedEvents,
            'userRole' => 'event_manager',
        ]);
    }

    /**
     * Agent dashboard - scanner only view
     */
    private function agentDashboard($user): Response
    {
        $eventIds = $user->events()->pluck('events.id');

        // Get today's check-in count for agent's events
        $todayCheckIns = CheckIn::whereIn('event_id', $eventIds)
            ->whereDate('scanned_at', today())
            ->count();

        $myEvents = $user->events()
            ->where('status', 'active')
            ->get();

        return Inertia::render('Dashboard/AgentDashboard', [
            'todayCheckIns' => $todayCheckIns,
            'myEvents' => $myEvents,
            'userRole' => 'agent',
        ]);
    }
}
