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
        $categories = ['exhibitor', 'guest', 'organizer', 'visitor'];
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
        \Log::info('Badge template create request', [
            'has_file' => $request->hasFile('front_template'),
            'elements_type' => gettype($request->input('elements')),
        ]);

        $validated = $request->validate([
            'category' => 'required|in:exhibitor,guest,organizer,visitor',
            'front_template' => 'nullable|image|max:5120',
            'elements' => 'nullable',  // Allow both string and array
            'terms_and_conditions' => 'nullable|string',
            'badge_size' => 'nullable|string',
            'badge_width' => 'nullable|integer|min:100|max:1000',
            'badge_height' => 'nullable|integer|min:100|max:1500',
            'badge_width_cm' => 'nullable|numeric|min:5|max:15',
            'badge_height_cm' => 'nullable|numeric|min:8|max:20',
            'measurement_unit' => 'nullable|string',
            'font_family' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'show_qr_code' => 'nullable|boolean',
            'show_logo' => 'nullable|boolean',
            'show_category_badge' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle template upload (single A4 template)
        if ($request->hasFile('front_template')) {
            $validated['front_template'] = $request->file('front_template')
                ->store('badge-templates/' . $event->id, 'public');
        }

        // Ensure elements structure
        if (isset($validated['elements'])) {
            if (is_string($validated['elements'])) {
                $validated['elements'] = json_decode($validated['elements'], true);
            }
            \Log::info('Decoded elements on create', ['elements' => $validated['elements']]);
        } else {
            // Set default elements if not provided
            $validated['elements'] = EventBadgeTemplate::getDefaultElements();
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
        // Log incoming request data for debugging
        \Log::info('Badge template update request', [
            'has_file' => $request->hasFile('front_template'),
            'elements_type' => gettype($request->input('elements')),
        ]);

        $validated = $request->validate([
            'front_template' => 'nullable|image|max:5120',
            'elements' => 'nullable',  // Allow both string and array
            'terms_and_conditions' => 'nullable|string',
            'badge_size' => 'nullable|string',
            'badge_width' => 'nullable|integer|min:100|max:1000',
            'badge_height' => 'nullable|integer|min:100|max:1500',
            'badge_width_cm' => 'nullable|numeric|min:5|max:15',
            'badge_height_cm' => 'nullable|numeric|min:8|max:20',
            'measurement_unit' => 'nullable|string',
            'font_family' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'show_qr_code' => 'nullable|boolean',
            'show_logo' => 'nullable|boolean',
            'show_category_badge' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle template upload (single A4 template)
        if ($request->hasFile('front_template')) {
            // Delete old file
            if ($badgeTemplate->front_template) {
                Storage::disk('public')->delete($badgeTemplate->front_template);
            }
            $validated['front_template'] = $request->file('front_template')
                ->store('badge-templates/' . $event->id, 'public');
        } else {
            // IMPORTANT: Don't update front_template if no new file uploaded
            // This preserves the existing template
            unset($validated['front_template']);
        }

        // Ensure elements structure
        if (isset($validated['elements'])) {
            if (is_string($validated['elements'])) {
                $validated['elements'] = json_decode($validated['elements'], true);
            }
            \Log::info('Decoded elements', ['elements' => $validated['elements']]);
        }

        $badgeTemplate->update($validated);

        \Log::info('Badge template updated', ['id' => $badgeTemplate->id]);

        return redirect()->route('events.badge-designer.index', $event)
            ->with('success', 'Badge template updated successfully.');
    }

    public function destroy(Event $event, EventBadgeTemplate $badgeTemplate)
    {
        // Delete template file
        if ($badgeTemplate->front_template) {
            Storage::disk('public')->delete($badgeTemplate->front_template);
        }

        $badgeTemplate->delete();

        return redirect()->route('events.badge-designer.index', $event)
            ->with('success', 'Badge template deleted successfully.');
    }

    public function preview(Event $event, EventBadgeTemplate $badgeTemplate)
    {
        return response()->json([
            'template' => $badgeTemplate,
            'template_url' => $badgeTemplate->front_template ? Storage::url($badgeTemplate->front_template) : null,
        ]);
    }

    /**
     * Show visual drag-and-drop designer for creating a badge template
     */
    public function visual(Event $event, string $category): Response
    {
        return Inertia::render('Events/BadgeDesigner/Designer', [
            'event' => $event,
            'category' => $category,
            'template' => null,
        ]);
    }

    /**
     * Show visual drag-and-drop designer for editing a badge template
     */
    public function visualEdit(Event $event, EventBadgeTemplate $badgeTemplate): Response
    {
        return Inertia::render('Events/BadgeDesigner/Designer', [
            'event' => $event,
            'category' => $badgeTemplate->category,
            'template' => $badgeTemplate,
        ]);
    }
}
