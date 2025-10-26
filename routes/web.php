<?php

use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BadgeTemplateController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventBadgeTemplateController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - All authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Only Routes
    Route::middleware(['admin'])->group(function () {
        // Events (Admin only)
        Route::resource('events', EventController::class);

        // Event Badge Designer
        Route::get('events/{event}/badge-designer', [EventBadgeTemplateController::class, 'index'])->name('events.badge-designer.index');
        Route::get('events/{event}/badge-designer/create/{category}', [EventBadgeTemplateController::class, 'create'])->name('events.badge-designer.create');
        Route::post('events/{event}/badge-designer', [EventBadgeTemplateController::class, 'store'])->name('events.badge-designer.store');
        Route::get('events/{event}/badge-designer/{badgeTemplate}/edit', [EventBadgeTemplateController::class, 'edit'])->name('events.badge-designer.edit');
        Route::put('events/{event}/badge-designer/{badgeTemplate}', [EventBadgeTemplateController::class, 'update'])->name('events.badge-designer.update');
        Route::delete('events/{event}/badge-designer/{badgeTemplate}', [EventBadgeTemplateController::class, 'destroy'])->name('events.badge-designer.destroy');
        Route::get('events/{event}/badge-designer/{badgeTemplate}/preview', [EventBadgeTemplateController::class, 'preview'])->name('events.badge-designer.preview');

        // User Management (Admin can manage all)
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

    // Event Manager & Admin Routes
    Route::middleware(['event_manager'])->group(function () {
        // User Management for Event Managers
        Route::get('/event-users', [UserManagementController::class, 'index'])->name('event.users.index');
        Route::get('/event-users/create', [UserManagementController::class, 'create'])->name('event.users.create');
        Route::post('/event-users', [UserManagementController::class, 'store'])->name('event.users.store');
        Route::get('/events/{event}/users', [UserManagementController::class, 'eventUsers'])->name('event.users.show');
        Route::delete('/events/{event}/users/{user}', [UserManagementController::class, 'detach'])->name('event.users.detach');

        // Attendees (Event Managers can manage attendees in their events)
        Route::resource('attendees', AttendeeController::class);

        // Badges
        Route::get('badges', [BadgeController::class, 'index'])->name('badges.index');
        Route::post('badges/generate/{attendee}', [BadgeController::class, 'generate'])->name('badges.generate');
        Route::post('badges/generate-bulk', [BadgeController::class, 'generateBulk'])->name('badges.generate.bulk');
        Route::get('badges/download/{attendee}', [BadgeController::class, 'download'])->name('badges.download');

        // Badge Templates
        Route::resource('badge-templates', BadgeTemplateController::class);
        Route::post('badge-templates/{badgeTemplate}/toggle-active', [BadgeTemplateController::class, 'toggleActive'])->name('badge-templates.toggle-active');

        // Import
        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import/upload', [ImportController::class, 'upload'])->name('import.upload');
        Route::post('import/process', [ImportController::class, 'process'])->name('import.process');
        Route::get('import/history', [ImportController::class, 'history'])->name('import.history');
        Route::get('attendees/import/template', [ImportController::class, 'downloadTemplate'])->name('attendees.import.template');
    });

    // Check-in Routes - All users (Agents, Event Managers, Admins)
    Route::get('check-in', [CheckInController::class, 'index'])->name('checkin.index');
    Route::post('check-in/scan', [CheckInController::class, 'scan'])->name('checkin.scan');
    Route::get('check-in/manual', [CheckInController::class, 'manual'])->name('checkin.manual');
    Route::post('check-in/manual', [CheckInController::class, 'manualCheckIn'])->name('checkin.manual.submit');

    // Profile - All users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
