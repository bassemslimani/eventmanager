<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Display list of users for event managers
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get events based on role
        if ($user->isAdmin()) {
            $events = Event::with(['managers', 'agents'])->get();
        } else {
            $events = $user->managedEvents()->with(['managers', 'agents'])->get();
        }

        return Inertia::render('UserManagement/Index', [
            'events' => $events,
            'userRole' => $user->role,
        ]);
    }

    /**
     * Show form to create agent for a specific event
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $events = Event::where('status', 'active')->get();
        } else {
            $events = $user->managedEvents()->where('status', 'active')->get();
        }

        return Inertia::render('UserManagement/Create', [
            'events' => $events,
            'userRole' => $user->role,
        ]);
    }

    /**
     * Store a new agent/event manager
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:event_manager,agent',
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:events,id',
        ]);

        // Check if current user can manage the selected events
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            $managedEventIds = $currentUser->managedEvents()->pluck('events.id')->toArray();
            $requestedEventIds = $validated['event_ids'];

            foreach ($requestedEventIds as $eventId) {
                if (!in_array($eventId, $managedEventIds)) {
                    return back()->withErrors(['event_ids' => 'You can only assign users to events you manage.']);
                }
            }
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Attach to events
        foreach ($validated['event_ids'] as $eventId) {
            $user->events()->attach($eventId, ['role' => $validated['role']]);
        }

        return redirect()->route('users.index')
            ->with('success', ucfirst($validated['role']) . ' created successfully.');
    }

    /**
     * Show users for a specific event
     */
    public function eventUsers(Event $event): Response
    {
        $user = auth()->user();

        // Check if user can manage this event
        if (!$user->isAdmin() && !$user->canManageEvent($event->id)) {
            abort(403, 'Unauthorized to manage this event.');
        }

        $managers = $event->managers;
        $agents = $event->agents;

        return Inertia::render('UserManagement/EventUsers', [
            'event' => $event,
            'managers' => $managers,
            'agents' => $agents,
            'userRole' => $user->role,
        ]);
    }

    /**
     * Remove user from event
     */
    public function detach(Request $request, Event $event, User $user)
    {
        $currentUser = $request->user();

        // Check if current user can manage this event
        if (!$currentUser->isAdmin() && !$currentUser->canManageEvent($event->id)) {
            abort(403, 'Unauthorized to manage this event.');
        }

        $event->users()->detach($user->id);

        return back()->with('success', 'User removed from event successfully.');
    }

    /**
     * Delete user (admin only)
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can delete users.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
