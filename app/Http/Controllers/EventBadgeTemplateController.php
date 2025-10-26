<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventBadgeTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventBadgeTemplateController extends Controller
{
    public function index(Event $event): Response
    {
        $templates = $event->badgeTemplates()->get();

        // Ensure all categories have templates
        $categories = ['exhibitor', 'guest', 'organizer', 'vip'];
        $existingCategories = $templates->pluck('category')->toArray();

        foreach ($categories as $category) {
            if (!in_array($category, $existingCategories)) {
                $templates->push((object)[
                    'id' => null,
                    'category' => $category,
                    'front_template' => null,
                    'back_template' => null,
                    'is_active' => false,
                ]);
            }
        }

        return Inertia::render('Events/BadgeDesigner/Index', [
            'event' => $event,
            'templates' => $templates,
        ]);
    }

    public function create(Event $event, string $category): Response
    {
        return Inertia::render('Events/BadgeDesigner/Create', [
            'event' => $event,
            'category' => $category,
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'category' => 'required|in:exhibitor,guest,organizer,vip',
            'front_template' => 'nullable|image|max:5120',
            'back_template' => 'nullable|image|max:5120',
            'front_layout' => 'nullable|array',
            'back_layout' => 'nullable|array',
            'terms_and_conditions' => 'nullable|string',
            'badge_size' => 'nullable|string',
            'badge_width' => 'nullable|integer|min:100|max:1000',
            'badge_height' => 'nullable|integer|min:100|max:1500',
            'font_family' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'show_qr_code' => 'nullable|boolean',
            'show_logo' => 'nullable|boolean',
            'show_category_badge' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle front template upload
        if ($request->hasFile('front_template')) {
            $validated['front_template'] = $request->file('front_template')
                ->store('badge-templates/' . $event->id, 'public');
        }

        // Handle back template upload
        if ($request->hasFile('back_template')) {
            $validated['back_template'] = $request->file('back_template')
                ->store('badge-templates/' . $event->id, 'public');
        }

        // Set default layout if not provided
        if (!isset($validated['front_layout'])) {
            $validated['front_layout'] = EventBadgeTemplate::getDefaultLayout();
        }

        if (!isset($validated['back_layout'])) {
            $validated['back_layout'] = EventBadgeTemplate::getDefaultLayout();
        }

        $event->badgeTemplates()->create($validated);

        return redirect()->route('events.badge-designer.index', $event)
            ->with('success', 'Badge template created successfully.');
    }

    public function edit(Event $event, EventBadgeTemplate $badgeTemplate): Response
    {
        return Inertia::render('Events/BadgeDesigner/Edit', [
            'event' => $event,
            'template' => $badgeTemplate,
        ]);
    }

    public function update(Request $request, Event $event, EventBadgeTemplate $badgeTemplate)
    {
        $validated = $request->validate([
            'front_template' => 'nullable|image|max:5120',
            'back_template' => 'nullable|image|max:5120',
            'front_layout' => 'nullable|array',
            'back_layout' => 'nullable|array',
            'terms_and_conditions' => 'nullable|string',
            'badge_size' => 'nullable|string',
            'badge_width' => 'nullable|integer|min:100|max:1000',
            'badge_height' => 'nullable|integer|min:100|max:1500',
            'font_family' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'show_qr_code' => 'nullable|boolean',
            'show_logo' => 'nullable|boolean',
            'show_category_badge' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle front template upload
        if ($request->hasFile('front_template')) {
            // Delete old file
            if ($badgeTemplate->front_template) {
                Storage::disk('public')->delete($badgeTemplate->front_template);
            }
            $validated['front_template'] = $request->file('front_template')
                ->store('badge-templates/' . $event->id, 'public');
        }

        // Handle back template upload
        if ($request->hasFile('back_template')) {
            // Delete old file
            if ($badgeTemplate->back_template) {
                Storage::disk('public')->delete($badgeTemplate->back_template);
            }
            $validated['back_template'] = $request->file('back_template')
                ->store('badge-templates/' . $event->id, 'public');
        }

        $badgeTemplate->update($validated);

        return redirect()->route('events.badge-designer.index', $event)
            ->with('success', 'Badge template updated successfully.');
    }

    public function destroy(Event $event, EventBadgeTemplate $badgeTemplate)
    {
        // Delete template files
        if ($badgeTemplate->front_template) {
            Storage::disk('public')->delete($badgeTemplate->front_template);
        }
        if ($badgeTemplate->back_template) {
            Storage::disk('public')->delete($badgeTemplate->back_template);
        }

        $badgeTemplate->delete();

        return redirect()->route('events.badge-designer.index', $event)
            ->with('success', 'Badge template deleted successfully.');
    }

    public function preview(Event $event, EventBadgeTemplate $badgeTemplate)
    {
        return response()->json([
            'template' => $badgeTemplate,
            'front_url' => $badgeTemplate->front_template ? Storage::url($badgeTemplate->front_template) : null,
            'back_url' => $badgeTemplate->back_template ? Storage::url($badgeTemplate->back_template) : null,
        ]);
    }
}
