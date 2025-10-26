# 🚀 PHASE 2: Quick Start Guide

## Current Status: Phase 1 Complete ✅ → Starting Phase 2

---

## 📋 PHASE 2 CHECKLIST

Copy this checklist to your new chat:

```
PHASE 2: BACKEND DEVELOPMENT

[ ] Step 1: Update Event.php model with relationships
[ ] Step 2: Update Attendee.php model with relationships
[ ] Step 3: Update BadgeTemplate.php model with relationships
[ ] Step 4: Update CheckIn.php model with relationships
[ ] Step 5: Update ImportLog.php model with relationships
[ ] Step 6: Create all 6 controllers
[ ] Step 7: Update routes/web.php
[ ] Step 8: Create BadgeTemplateSeeder
[ ] Step 9: Update DatabaseSeeder
[ ] Step 10: Run php artisan db:seed
[ ] Step 11: Test /dashboard route
[ ] Step 12: Verify all routes with php artisan route:list
```

---

## 🎯 STEP-BY-STEP INSTRUCTIONS

### **STEP 1-5: Update Models**

Open each model file and replace the class content:

#### 1. app/Models/Event.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name', 'name_ar', 'date', 'location', 'location_ar',
        'description', 'description_ar', 'theme_settings', 'status',
    ];

    protected $casts = [
        'date' => 'date',
        'theme_settings' => 'array',
    ];

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }
}
```

#### 2. app/Models/Attendee.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attendee extends Model
{
    protected $fillable = [
        'event_id', 'type', 'name', 'name_ar', 'email', 'mobile',
        'company', 'company_ar', 'category', 'role', 'department',
        'qr_code', 'qr_uuid', 'badge_url', 'badge_generated_at',
        'checked_in_at', 'checked_in_by', 'welcome_email_sent_at',
        'preferences', 'metadata',
    ];

    protected $casts = [
        'badge_generated_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'welcome_email_sent_at' => 'datetime',
        'preferences' => 'array',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attendee) {
            if (empty($attendee->qr_uuid)) {
                $attendee->qr_uuid = Str::uuid();
            }
            if (empty($attendee->qr_code)) {
                $attendee->qr_code = 'QR-' . strtoupper(Str::random(10));
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
```

#### 3. app/Models/BadgeTemplate.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeTemplate extends Model
{
    protected $fillable = [
        'type', 'name', 'background_image', 'overlay_color',
        'overlay_opacity', 'glass_effect', 'gradient_direction',
        'font_family', 'layout_config', 'css_overrides', 'is_active',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'glass_effect' => 'boolean',
        'is_active' => 'boolean',
        'overlay_opacity' => 'integer',
    ];
}
```

#### 4. app/Models/CheckIn.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    protected $fillable = [
        'attendee_id', 'event_id', 'scanned_by', 'scanned_at',
        'location', 'device_info', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'device_info' => 'array',
    ];

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
```

#### 5. app/Models/ImportLog.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id', 'file_name', 'file_path', 'type',
        'total_records', 'processed', 'failed',
        'errors', 'status', 'processed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'processed_at' => 'datetime',
        'total_records' => 'integer',
        'processed' => 'integer',
        'failed' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

### **STEP 6: Create Controllers**

Run these commands in terminal:

```bash
php artisan make:controller DashboardController
php artisan make:controller AttendeeController --resource
php artisan make:controller BadgeController
php artisan make:controller CheckInController
php artisan make:controller ImportController
php artisan make:controller EventController --resource
```

Then add the code to each:

**app/Http/Controllers/DashboardController.php** - See PROJECT_STATUS.md Section 2.2

**app/Http/Controllers/AttendeeController.php** - See PROJECT_STATUS.md Section 2.2

---

### **STEP 7: Update Routes**

Replace the content of **routes/web.php** with:

```php
<?php

use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Events
    Route::resource('events', EventController::class);

    // Attendees
    Route::resource('attendees', AttendeeController::class);

    // Import
    Route::get('import', [ImportController::class, 'index'])->name('import.index');
    Route::post('import/upload', [ImportController::class, 'upload'])->name('import.upload');

    // Badges
    Route::get('badges', [BadgeController::class, 'index'])->name('badges.index');
    Route::post('badges/generate/{attendee}', [BadgeController::class, 'generate'])->name('badges.generate');

    // Check-in
    Route::get('check-in', [CheckInController::class, 'index'])->name('checkin.index');
    Route::post('check-in/scan', [CheckInController::class, 'scan'])->name('checkin.scan');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
```

---

### **STEP 8-9: Create Seeders**

Create **database/seeders/BadgeTemplateSeeder.php**:

```php
<?php

namespace Database\Seeders;

use App\Models\BadgeTemplate;
use Illuminate\Database\Seeder;

class BadgeTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'type' => 'exhibitor',
                'name' => 'Exhibitor Badge',
                'overlay_color' => '#10B981',
                'overlay_opacity' => 85,
                'glass_effect' => true,
                'gradient_direction' => '135deg',
                'font_family' => 'Inter',
                'is_active' => true,
            ],
            [
                'type' => 'guest',
                'name' => 'Guest Badge',
                'overlay_color' => '#3B82F6',
                'overlay_opacity' => 85,
                'glass_effect' => true,
                'gradient_direction' => '135deg',
                'font_family' => 'Inter',
                'is_active' => true,
            ],
            [
                'type' => 'organizer',
                'name' => 'Organizer Badge',
                'overlay_color' => '#EAB308',
                'overlay_opacity' => 85,
                'glass_effect' => true,
                'gradient_direction' => '135deg',
                'font_family' => 'Inter',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            BadgeTemplate::create($template);
        }
    }
}
```

Update **database/seeders/DatabaseSeeder.php**:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BadgeTemplateSeeder::class,
        ]);
    }
}
```

---

### **STEP 10: Run Seeders**

```bash
php artisan db:seed
```

You should see:
```
INFO  Seeding database.

INFO  Running: Database\Seeders\BadgeTemplateSeeder
```

---

### **STEP 11-12: Test Everything**

**Start servers:**
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

**Test routes:**
```bash
php artisan route:list
```

You should see routes for:
- dashboard
- attendees.*
- events.*
- badges.*
- checkin.*
- import.*

**Visit in browser:**
- http://localhost:8000
- Login/Register
- Should redirect to /dashboard

---

## ✅ PHASE 2 COMPLETE WHEN:

- [ ] All models updated with relationships
- [ ] All 6 controllers created with code
- [ ] routes/web.php updated
- [ ] Badge templates seeded (3 records in database)
- [ ] Can visit /dashboard without errors
- [ ] Route list shows all defined routes

---

## 🐛 Troubleshooting

**If you get errors:**

1. **Clear cache:**
   ```bash
   php artisan optimize:clear
   ```

2. **Check database:**
   - Visit phpMyAdmin: http://localhost/phpmyadmin
   - Database 'qrmh' should exist
   - Table 'badge_templates' should have 3 rows

3. **Check models:**
   - All 5 models should have `protected $fillable` arrays
   - All relationships should be defined

4. **Check controllers:**
   - Should all be in app/Http/Controllers/
   - DashboardController and AttendeeController must have full code

---

## 📚 Reference Files

- **Detailed code:** PROJECT_STATUS.md (Section 2)
- **Full implementation guide:** PROJECT_SETUP.md
- **Quick reference:** README.md

---

## 🎯 NEXT PHASE

After Phase 2 is complete, move to **Phase 3: Vue Components**

You'll create:
- Dashboard with Bento grid
- Attendees DataTable
- Forms for Create/Edit
- Modern UI components

---

## 💡 TIPS

1. **Copy code carefully** - Make sure to get all the use statements at the top
2. **Test after each step** - Don't wait until the end
3. **Use route:list** - Verify routes are registered
4. **Check database** - Confirm seeders worked
5. **Clear cache** - If something doesn't work, clear cache first

---

**🎉 Ready to start Phase 2? Let's build the backend!**

**Project Progress: 25% → 50% (after Phase 2)**
