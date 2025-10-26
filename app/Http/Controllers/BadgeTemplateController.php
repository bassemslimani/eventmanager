<?php

namespace App\Http\Controllers;

use App\Models\BadgeTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class BadgeTemplateController extends Controller
{
    public function index(): Response
    {
        $templates = BadgeTemplate::latest()->get();

        return Inertia::render('BadgeTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BadgeTemplates/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:exhibitor,guest,organizer',
            'name' => 'required|string|max:255',
            'background_image' => 'nullable|image|max:2048',
            'overlay_color' => 'nullable|string|max:7',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'glass_effect' => 'nullable|boolean',
            'gradient_direction' => 'nullable|string',
            'font_family' => 'nullable|string',
            'layout_config' => 'nullable|array',
            'css_overrides' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            $path = $request->file('background_image')->store('badge-backgrounds', 'public');
            $validated['background_image'] = $path;
        }

        $validated['is_active'] = $validated['is_active'] ?? false;

        BadgeTemplate::create($validated);

        return redirect()->route('badge-templates.index')
            ->with('success', 'Badge template created successfully.');
    }

    public function edit(BadgeTemplate $badgeTemplate): Response
    {
        return Inertia::render('BadgeTemplates/Edit', [
            'template' => $badgeTemplate,
        ]);
    }

    public function update(Request $request, BadgeTemplate $badgeTemplate)
    {
        $validated = $request->validate([
            'type' => 'required|in:exhibitor,guest,organizer',
            'name' => 'required|string|max:255',
            'background_image' => 'nullable|image|max:2048',
            'overlay_color' => 'nullable|string|max:7',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'glass_effect' => 'nullable|boolean',
            'gradient_direction' => 'nullable|string',
            'font_family' => 'nullable|string',
            'layout_config' => 'nullable|array',
            'css_overrides' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            // Delete old image if exists
            if ($badgeTemplate->background_image) {
                Storage::disk('public')->delete($badgeTemplate->background_image);
            }

            $path = $request->file('background_image')->store('badge-backgrounds', 'public');
            $validated['background_image'] = $path;
        }

        $badgeTemplate->update($validated);

        return redirect()->route('badge-templates.index')
            ->with('success', 'Badge template updated successfully.');
    }

    public function destroy(BadgeTemplate $badgeTemplate)
    {
        // Delete background image if exists
        if ($badgeTemplate->background_image) {
            Storage::disk('public')->delete($badgeTemplate->background_image);
        }

        $badgeTemplate->delete();

        return redirect()->route('badge-templates.index')
            ->with('success', 'Badge template deleted successfully.');
    }

    public function toggleActive(BadgeTemplate $badgeTemplate)
    {
        $badgeTemplate->update([
            'is_active' => !$badgeTemplate->is_active,
        ]);

        return back()->with('success', 'Template status updated successfully.');
    }
}
