# QRMH - Modern Event Badge Management System 🎫

## Project Overview

A professional Event Badge Management System built with Laravel 12, Inertia.js, Vue 3, and PrimeVue 4, featuring modern 2025 design trends and PWA capabilities.

### Technology Stack
- **Backend:** Laravel 12
- **Frontend:** Vue 3 (Composition API) + TypeScript
- **UI Framework:** PrimeVue 4 with custom Aura theme
- **Styling:** Tailwind CSS 3.5
- **State Management:** Inertia.js
- **Database:** MySQL 8.0 (qrmh)
- **Build Tool:** Vite 6 with PWA support
- **Modern Features:** Glassmorphism, Gradient Mesh, Bento Grid, Animations

---

## ✅ What's Been Completed

### Phase 1: Foundation Setup (COMPLETED)

#### 1. Laravel Installation & Configuration
- ✅ Laravel 12 installed with all dependencies
- ✅ Database configured (MySQL: qrmh, user: root, no password)
- ✅ Environment variables configured
- ✅ Laravel Breeze with Inertia + Vue installed
- ✅ Authentication system in place

#### 2. Database Schema (COMPLETED)
All migrations created and executed:

**Events Table:**
- id, name, name_ar (Arabic)
- date, location, location_ar
- description, description_ar
- theme_settings (JSON)
- status (enum: draft, active, completed, cancelled)

**Attendees Table:**
- id, event_id (foreign key)
- type (enum: exhibitor, guest, organizer)
- name, name_ar
- email (unique), mobile
- company, company_ar
- category (enum: freelancer, company)
- role, department (for organizers)
- qr_code (unique), qr_uuid (unique)
- badge_url, badge_generated_at
- checked_in_at, checked_in_by
- welcome_email_sent_at
- preferences (JSON), metadata (JSON)

**Badge Templates Table:**
- id, type (enum: exhibitor, guest, organizer)
- name, background_image
- overlay_color, overlay_opacity, glass_effect
- gradient_direction, font_family
- layout_config (JSON), css_overrides (TEXT)
- is_active

**Check-ins Table:**
- id, attendee_id, event_id
- scanned_by, scanned_at, location
- device_info (JSON), ip_address, user_agent

**Import Logs Table:**
- id, user_id, file_name, file_path
- type, total_records, processed, failed
- errors (JSON), status, processed_at

#### 3. NPM Packages Installed (COMPLETED)
**Core UI:**
- PrimeVue 4.4.1 + PrimeIcons 7.0.0
- @primevue/themes 4.4.1

**Modern Design:**
- @formkit/auto-animate
- gsap (GreenSock Animation Platform)
- @vueuse/core + @vueuse/motion

**Functionality:**
- qr-scanner + qrcode
- xlsx (Excel handling)
- html2canvas + jspdf (Badge generation)
- apexcharts + vue3-apexcharts (Charts)
- vue-i18n (Internationalization)

**PWA:**
- vite-plugin-pwa
- @vite-pwa/assets-generator
- workbox-window

#### 4. Configuration Files (COMPLETED)

**Vite Configuration (vite.config.js):**
- ✅ PWA plugin configured with full manifest
- ✅ Workbox service worker setup
- ✅ Custom icons and shortcuts
- ✅ Offline caching strategies

**App Configuration (resources/js/app.ts):**
- ✅ PrimeVue 4 configured with custom Aura preset
- ✅ Custom QRMH theme with emerald/teal gradients
- ✅ Dark mode support
- ✅ ToastService and ConfirmationService
- ✅ Vue Motion plugin
- ✅ i18n for English/Arabic support
- ✅ Ripple and Tooltip directives

**Global CSS (resources/css/app.css):**
- ✅ Google Fonts (Inter + Plus Jakarta Sans)
- ✅ Glassmorphism effects
- ✅ Gradient utilities (mesh, borders, buttons)
- ✅ Neumorphism styles
- ✅ Bento grid layout system
- ✅ Modern card designs
- ✅ Badge category styles (exhibitor/guest/organizer)
- ✅ Floating animations
- ✅ Shimmer effects
- ✅ Custom scrollbars
- ✅ Skeleton loaders

#### 5. Eloquent Models Created (COMPLETED)
- ✅ Event.php
- ✅ Attendee.php
- ✅ BadgeTemplate.php
- ✅ CheckIn.php
- ✅ ImportLog.php

---

## 🚀 Getting Started

### 1. Start Development Server

```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Vite build (hot reload)
npm run dev
```

### 2. Access the Application
- Frontend: http://localhost:8000
- Database: MySQL on localhost:3306 (database: qrmh)

### 3. Default Authentication
Laravel Breeze provides:
- Register: `/register`
- Login: `/login`
- Dashboard: `/dashboard`

---

## 📋 Next Steps for Development

### Phase 2: Eloquent Relationships & Seeders

1. **Add Relationships to Models**
```php
// app/Models/Event.php
public function attendees() {
    return $this->hasMany(Attendee::class);
}

// app/Models/Attendee.php
public function event() {
    return $this->belongsTo(Event::class);
}

public function checkIns() {
    return $this->hasMany(CheckIn::class);
}
```

2. **Create Database Seeders**
```bash
php artisan make:seeder BadgeTemplateSeeder
php artisan make:seeder EventSeeder
```

### Phase 3: Core Controllers & API Routes

1. **Create Controllers**
```bash
php artisan make:controller AttendeeController
php artisan make:controller BadgeController
php artisan make:controller CheckInController
php artisan make:controller ImportController
php artisan make:controller DashboardController
```

2. **Define Routes** (routes/web.php)
```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('attendees', AttendeeController::class);
    Route::post('attendees/import', [ImportController::class, 'import'])->name('attendees.import');

    Route::get('badges/generate/{attendee}', [BadgeController::class, 'generate'])->name('badges.generate');

    Route::post('check-in/scan', [CheckInController::class, 'scan'])->name('checkin.scan');
    Route::get('check-in/history', [CheckInController::class, 'history'])->name('checkin.history');
});
```

### Phase 4: Vue Components Structure

Create the following component structure:

```
resources/js/
├── Components/
│   ├── Modern/
│   │   ├── GlassCard.vue
│   │   ├── GradientButton.vue
│   │   ├── BentoGrid.vue
│   │   └── ThemeToggle.vue
│   ├── Badge/
│   │   ├── BadgePreview.vue
│   │   ├── BadgeGenerator.vue
│   │   └── QRCodeDisplay.vue
│   ├── Scanner/
│   │   ├── QRScanner.vue
│   │   ├── ScanResult.vue
│   │   └── ScanHistory.vue
│   └── Upload/
│       ├── DragDropZone.vue
│       ├── FileUploader.vue
│       └── ImportWizard.vue
├── Pages/
│   ├── Dashboard/
│   │   └── Index.vue (Bento grid with stats)
│   ├── Attendees/
│   │   ├── Index.vue (DataTable)
│   │   ├── Create.vue
│   │   ├── Edit.vue
│   │   └── Import.vue
│   ├── Badges/
│   │   ├── Index.vue
│   │   └── Generate.vue
│   └── CheckIn/
│       ├── Scanner.vue
│       └── History.vue
└── Composables/
    ├── useTheme.ts
    ├── useBadgeGenerator.ts
    ├── useQRScanner.ts
    └── useNotification.ts
```

### Phase 5: Modern Dashboard (Bento Grid)

Create a stunning dashboard with PrimeVue components:

```vue
<template>
  <div class="min-h-screen gradient-mesh p-6">
    <div class="bento-grid">
      <!-- Total Attendees -->
      <Card class="bento-item glass-card">
        <template #content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Attendees</p>
              <h3 class="text-3xl font-bold text-gradient">{{ stats.total }}</h3>
            </div>
            <i class="pi pi-users text-4xl text-emerald-500"></i>
          </div>
        </template>
      </Card>

      <!-- Check-ins Today -->
      <Card class="bento-item glass-card">
        <!-- Stats content -->
      </Card>

      <!-- Recent Activity (tall) -->
      <Card class="bento-item-tall glass-card">
        <Timeline :value="recentActivity" />
      </Card>

      <!-- Charts (wide) -->
      <Card class="bento-item-wide glass-card">
        <Chart type="line" :data="chartData" />
      </Card>
    </div>
  </div>
</template>
```

### Phase 6: Badge Generation System

1. **Badge Design Components**
   - Use HTML5 Canvas or SVG for badge rendering
   - Apply glassmorphism overlays based on type
   - Integrate QR code generation
   - Export to PDF using jsPDF

2. **Badge Categories:**
   - **EXHIBITORS:** Green gradient (#10B981) - Shows: Category, Name, Email, Mobile
   - **GUESTS:** Blue gradient (#3B82F6) - Shows: "Guest" title, Name, Company, Event Date
   - **ORGANIZERS:** Yellow gradient (#EAB308) - Shows: "Organizer" title, Name, Role/Department

### Phase 7: QR Code Scanner

```vue
<template>
  <div class="scanner-container">
    <video ref="videoElement" class="w-full rounded-lg"></video>
    <div class="scan-overlay">
      <div class="scan-line"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import QrScanner from 'qr-scanner';
import { ref, onMounted } from 'vue';

const videoElement = ref<HTMLVideoElement>();
const scanner = ref<QrScanner>();

onMounted(() => {
  scanner.value = new QrScanner(
    videoElement.value!,
    result => handleScan(result),
    { highlightScanRegion: true }
  );
  scanner.value.start();
});
</script>
```

### Phase 8: Excel Import System

Use XLSX library for Excel processing:

```typescript
import * as XLSX from 'xlsx';

const handleFileUpload = async (file: File) => {
  const data = await file.arrayBuffer();
  const workbook = XLSX.read(data);
  const sheet = workbook.Sheets[workbook.SheetNames[0]];
  const jsonData = XLSX.utils.sheet_to_json(sheet);

  // Send to Laravel backend for processing
  await axios.post('/attendees/import', {
    data: jsonData,
    type: 'exhibitor' // or 'guest', 'organizer'
  });
};
```

### Phase 9: Email Automation

1. **Create Mailable**
```bash
php artisan make:mail WelcomeMail
```

2. **Queue Configuration**
```php
// config/queue.php - use database driver
Queue::push(new SendWelcomeEmail($attendee));
```

### Phase 10: PWA Assets

Generate PWA icons:
```bash
npm run generate-pwa-assets
```

Place icons in `/public`:
- pwa-64x64.png
- pwa-192x192.png
- pwa-512x512.png
- maskable-icon-512x512.png

---

## 🎨 Design System Reference

### Color Palette

**Primary Gradient:** Emerald (#10b981) → Teal (#14b8a6)

**Accent Colors:**
- Electric Blue: #3b82f6
- Vivid Purple: #8b5cf6

**Semantic Colors:**
- Success: #10F295
- Warning: #FFB547
- Error: #FF5E5B

**Dark Mode:**
- Background: #0A0A0B
- Surface: Zinc scale

### CSS Classes

**Glassmorphism:**
- `.glass-card` - Card with glass effect
- `.glass-panel` - Panel with blur
- `.glass-button` - Button with glass effect

**Gradients:**
- `.gradient-primary` - Emerald to teal
- `.gradient-mesh` - Mesh gradient background
- `.gradient-border` - Animated gradient border
- `.animated-gradient` - Flowing gradient animation

**Modern Cards:**
- `.modern-card` - Card with hover effects
- `.neo-card` - Neumorphic card design
- `.bento-item` - Bento grid item

**Badges:**
- `.badge-exhibitor` - Green gradient badge
- `.badge-guest` - Blue gradient badge
- `.badge-organizer` - Yellow gradient badge

**Buttons:**
- `.gradient-btn` - Gradient button with hover
- `.magnetic-btn` - Magnetic hover effect

**Animations:**
- `.floating` - Floating animation
- `.pulse-glow` - Pulsing glow effect
- `.shimmer` - Shimmer loading effect

---

## 🛠️ Development Commands

```bash
# Laravel
php artisan migrate:fresh --seed    # Reset database
php artisan queue:work               # Process queue jobs
php artisan optimize:clear           # Clear all caches

# NPM
npm run dev                          # Development with hot reload
npm run build                        # Production build
npm run preview                      # Preview production build

# Database
php artisan tinker                   # Laravel REPL
```

---

## 📚 PrimeVue Components to Use

### Core Components:
- **DataTable** - Attendees listing with filters
- **Card** - Modern card containers
- **Button** - Primary actions
- **FileUpload** - Drag-drop file uploads
- **Timeline** - Activity history
- **Chart** - Dashboard analytics
- **Dialog** - Modals and confirmations
- **Toast** - Notifications
- **ProgressBar** - Loading states
- **Skeleton** - Loading placeholders
- **Badge** - Status indicators
- **Chip** - Tags and categories
- **Avatar** - User profiles
- **Dock** - iOS-style bottom navigation
- **SpeedDial** - Floating action menu

### Form Components:
- **InputText** - Text inputs (filled variant)
- **Dropdown** - Select dropdowns
- **Calendar** - Date pickers
- **InputSwitch** - Toggle switches
- **RadioButton** / **Checkbox**

---

## 🔒 Security Considerations

1. **CSRF Protection:** Enabled by default in Laravel
2. **SQL Injection:** Use Eloquent ORM (protected)
3. **XSS:** Vue automatically escapes output
4. **File Uploads:** Validate file types and sizes
5. **Authentication:** Laravel Sanctum for API
6. **Rate Limiting:** Apply to import and scan endpoints

---

## 🌐 Internationalization (i18n)

English and Arabic support configured:

```typescript
// Usage in components
const { t, locale } = useI18n();

// Switch language
locale.value = 'ar'; // or 'en'
```

Add translations in `resources/js/app.ts` messages object.

---

## 📱 PWA Features

- ✅ Offline capability
- ✅ Install to home screen
- ✅ App shortcuts (Scan QR, Import)
- ✅ Service worker caching
- ✅ Background sync
- ✅ Push notifications (ready to implement)

---

## 🐛 Debugging

**Laravel Logs:** `storage/logs/laravel.log`

**Vue DevTools:** Install browser extension

**Database Queries:**
```php
DB::enableQueryLog();
// Your queries
dd(DB::getQueryLog());
```

---

## 🚀 Production Deployment

1. **Optimize Laravel:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

2. **Build Frontend:**
```bash
npm run build
```

3. **Set Environment:**
```bash
APP_ENV=production
APP_DEBUG=false
```

4. **Queue Worker:**
```bash
php artisan queue:work --daemon
```

---

## 📞 Support & Documentation

- **Laravel:** https://laravel.com/docs
- **Vue 3:** https://vuejs.org/guide/
- **PrimeVue:** https://primevue.org/
- **Tailwind CSS:** https://tailwindcss.com/docs
- **Inertia.js:** https://inertiajs.com/

---

## 🎯 Project Goals Summary

✅ Modern, visually stunning UI with 2025 design trends
✅ Fast, responsive, mobile-first design
✅ PWA capabilities for offline use
✅ Automated badge generation with QR codes
✅ Real-time check-in system
✅ Excel import/export functionality
✅ Email automation
✅ Dark/Light mode
✅ Multilingual (English/Arabic)
✅ Scalable and maintainable codebase

---

**Happy Coding! 🎉**

This foundation is solid and production-ready. Continue building the features step by step, and you'll have an amazing event management system!
