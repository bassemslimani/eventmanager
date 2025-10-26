<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\BadgeTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class BadgeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Attendee::with('event');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Only show attendees without badges or filter by badge status
        if ($request->has('badge_status')) {
            if ($request->badge_status === 'generated') {
                $query->whereNotNull('badge_generated_at');
            } elseif ($request->badge_status === 'pending') {
                $query->whereNull('badge_generated_at');
            }
        }

        $attendees = $query->latest()->paginate(20);
        $templates = BadgeTemplate::where('is_active', true)->get();

        return Inertia::render('Badges/Index', [
            'attendees' => $attendees,
            'templates' => $templates,
            'filters' => $request->only(['type', 'badge_status']),
        ]);
    }

    public function generate(Attendee $attendee)
    {
        // Get the event-based badge template
        if (!$attendee->event_id) {
            return back()->with('error', 'Attendee must be assigned to an event.');
        }

        $template = \App\Models\EventBadgeTemplate::where('event_id', $attendee->event_id)
            ->where('category', $attendee->type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return back()->with('error', 'No active badge template found for this attendee category in this event. Please configure badges in the Event Badge Designer.');
        }

        // Generate QR code as SVG (doesn't require Imagick or GD)
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($attendee->qr_uuid);

        // Update attendee
        $attendee->update([
            'badge_generated_at' => now(),
        ]);

        return back()->with('success', 'Badge generated successfully.');
    }

    public function generateBulk(Request $request)
    {
        $validated = $request->validate([
            'attendee_ids' => 'required|array',
            'attendee_ids.*' => 'exists:attendees,id',
        ]);

        $attendees = Attendee::whereIn('id', $validated['attendee_ids'])->get();
        $generated = 0;

        foreach ($attendees as $attendee) {
            $template = BadgeTemplate::where('type', $attendee->type)
                ->where('is_active', true)
                ->first();

            if ($template) {
                $attendee->update([
                    'badge_generated_at' => now(),
                ]);
                $generated++;
            }
        }

        return back()->with('success', "Generated {$generated} badges successfully.");
    }

    public function download(Attendee $attendee)
    {
        // Get the event-based badge template
        if (!$attendee->event_id) {
            return response()->json(['error' => 'Attendee must be assigned to an event.'], 400);
        }

        $attendee->load('event');
        $event = $attendee->event;

        $template = \App\Models\EventBadgeTemplate::where('event_id', $attendee->event_id)
            ->where('category', $attendee->type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return response()->json(['error' => 'No active badge template found for this attendee.'], 404);
        }

        // Generate QR code as SVG
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($attendee->qr_uuid);

        // Prepare URLs
        $frontTemplateUrl = $template->front_template ? asset('storage/' . $template->front_template) : null;
        $backTemplateUrl = $template->back_template ? asset('storage/' . $template->back_template) : null;
        $eventLogoUrl = $event->logo ? asset('storage/' . $event->logo) : null;

        return response()->json([
            'success' => true,
            'attendee' => [
                'name' => $attendee->name,
                'name_ar' => $attendee->name_ar,
                'company' => $attendee->company,
                'company_ar' => $attendee->company_ar,
                'type' => $attendee->type,
                'email' => $attendee->email,
                'qr_uuid' => $attendee->qr_uuid, // Add QR UUID for manual entry
            ],
            'event' => [
                'name' => $event->name,
                'name_ar' => $event->name_ar,
                'logo_url' => $eventLogoUrl,
                'date' => $event->date->format('Y-m-d'),
                'location' => $event->location,
            ],
            'template' => [
                'front_template_url' => $frontTemplateUrl,
                'back_template_url' => $backTemplateUrl,
                'badge_width' => $template->badge_width,
                'badge_height' => $template->badge_height,
                'font_family' => $template->font_family,
                'primary_color' => $template->primary_color,
                'secondary_color' => $template->secondary_color,
                'terms_and_conditions' => $template->terms_and_conditions,
                'front_layout' => $template->front_layout,
                'back_layout' => $template->back_layout,
                'show_qr_code' => $template->show_qr_code,
                'show_logo' => $template->show_logo,
                'show_category_badge' => $template->show_category_badge,
            ],
            'qr_code_svg' => $qrCodeSvg,
        ]);
    }
}
