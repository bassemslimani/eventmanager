# QRMH - Event Badge Management System 🎫

A modern, professional Event Badge Management System built with Laravel 12, Inertia.js, Vue 3, TypeScript, and PrimeVue 4, featuring 2025 design trends and PWA capabilities.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)
![PrimeVue](https://img.shields.io/badge/PrimeVue-4-41B883?style=for-the-badge)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=for-the-badge&logo=typescript&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Node.js 20+
- MySQL 8.0+
- Composer
- XAMPP (or similar)

### Installation & Running

1. **Start XAMPP MySQL**

2. **Install Dependencies**
```bash
# Install PHP dependencies
composer install

# Install NPM dependencies (already installed)
npm install --legacy-peer-deps
```

3. **Start Development Servers**
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

4. **Access Application**
- Frontend: http://localhost:8000
- Register a user account to get started

## ✨ Features Implemented

### ✅ Foundation (COMPLETED)
- 🎯 Laravel 12 with Breeze authentication
- 🎨 PrimeVue 4 with custom Aura theme (Emerald/Teal)
- 💎 2025 Design Trends (Glassmorphism, Gradients, Bento Grid)
- 📦 PWA Configuration with offline support
- 🗄️ Complete database schema (5 tables)
- 🌍 i18n ready (English/Arabic)
- 🌓 Dark/Light mode support
- ⚡ Vite 6 with hot reload

### 🎨 Modern Design System
All CSS utility classes implemented:
- 💎 Glassmorphism effects
- 🌈 Gradient mesh backgrounds
- 🎭 Neumorphism styles
- 🎪 Bento grid layouts
- ✨ Floating animations
- 💫 Shimmer loading effects
- 🎨 Badge category styles (Green/Blue/Yellow)

### 📊 Database Schema
Complete schema with 5 tables:
- **Events** - Event management with Arabic support
- **Attendees** - 3 types (Exhibitor, Guest, Organizer) with QR codes
- **Badge Templates** - Customizable badge designs
- **Check-ins** - Real-time check-in tracking
- **Import Logs** - Excel import history

## 📁 Project Structure

```
qrmh/
├── app/Models/          # Eloquent models (5 models created)
├── database/migrations/ # All migrations created & executed
├── resources/
│   ├── js/
│   │   ├── app.ts      # PrimeVue + i18n configured
│   │   ├── Components/ # Your components go here
│   │   ├── Pages/      # Inertia pages
│   │   └── Layouts/    # App layouts
│   └── css/
│       └── app.css     # 400+ lines of 2025 design utilities
├── public/build/       # Compiled assets + PWA (service worker)
├── vite.config.js      # Vite + PWA configured
├── PROJECT_SETUP.md    # 📘 Detailed implementation guide
└── README.md           # This file
```

## 🎨 CSS Utility Classes

### Glassmorphism
```html
<div class="glass-card">Frosted glass effect</div>
<div class="glass-panel">Blurred panel</div>
<button class="glass-button">Transparent button</button>
```

### Gradients
```html
<div class="gradient-primary">Emerald to Teal</div>
<div class="gradient-mesh">Animated mesh background</div>
<div class="gradient-border">Animated border</div>
<div class="animated-gradient">Flowing gradient</div>
```

### Modern Cards
```html
<div class="modern-card">Card with hover effects</div>
<div class="neo-card">Neumorphic design</div>
<div class="bento-item">Bento grid item</div>
```

### Badge Categories
```html
<div class="badge-exhibitor">Green - Exhibitors</div>
<div class="badge-guest">Blue - Guests</div>
<div class="badge-organizer">Yellow - Organizers</div>
```

### Animations
```html
<div class="floating">Floating animation</div>
<div class="pulse-glow">Pulsing glow</div>
<div class="shimmer">Shimmer loader</div>
```

## 🛠️ Commands

### Development
```bash
php artisan serve              # Start Laravel (port 8000)
npm run dev                    # Start Vite with HMR
php artisan queue:work         # Process background jobs
```

### Build
```bash
npm run build                  # Production build (already tested ✓)
```

### Database
```bash
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Fresh start
php artisan tinker             # Laravel REPL
```

## 📦 Installed Packages

### Core Frontend (✅ Installed)
- primevue@4.4.1 + primeicons@7.0.0
- vue@3.4.0 + typescript@5.6.3
- @vueuse/core + @vueuse/motion
- gsap (animations)
- @formkit/auto-animate

### Functionality (✅ Installed)
- qr-scanner + qrcode (QR code handling)
- xlsx (Excel import/export)
- html2canvas + jspdf (Badge PDF generation)
- apexcharts + vue3-apexcharts (Charts)
- vue-i18n (Internationalization)

### PWA (✅ Configured)
- vite-plugin-pwa
- workbox-window
- Service worker generated

## 📚 Next Steps

See **[PROJECT_SETUP.md](./PROJECT_SETUP.md)** for complete implementation guide:

### Phase 2: Backend Development
- Add model relationships
- Create controllers (Attendee, Badge, CheckIn, Import)
- Define API routes
- Create seeders for initial data

### Phase 3: Vue Components
- Modern dashboard with Bento grid
- Attendee management (PrimeVue DataTable)
- Badge generator with HTML5 Canvas
- QR code scanner (camera access)
- Excel import wizard
- Dark/Light theme toggle

### Phase 4: Features
- Badge generation (3 categories with glass overlays)
- QR scanning and check-in
- Excel import/export
- Email automation
- Real-time notifications
- Analytics dashboard

## 🎯 Badge Categories

### 1. EXHIBITORS (Green - #10B981)
- Glass overlay with green gradient
- Shows: Category (Freelancer/Company), Name, Email, Mobile
- QR code for check-in

### 2. GUESTS (Blue - #3B82F6)
- Glass overlay with blue gradient
- Shows: "Guest" title, Name, Company, Event Date
- QR code for check-in

### 3. ORGANIZERS (Yellow - #EAB308)
- Glass overlay with yellow gradient
- Shows: "Organizer" title, Name, Role, Department
- QR code for check-in

## 🔐 Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qrmh
DB_USERNAME=root
DB_PASSWORD=
```

## 🌐 Browser Support

- Chrome/Edge (latest) ✓
- Firefox (latest) ✓
- Safari (latest) ✓
- Mobile browsers ✓

## 📱 PWA Features (✅ Ready)

- ✅ Service worker generated
- ✅ Offline caching configured
- ✅ Install to home screen
- ✅ App manifest with icons
- ✅ Shortcuts (Scan QR, Import)
- ✅ Background sync ready

## 🎨 Design Highlights

### 2025 Trends Implemented:
- ✅ Glassmorphism with backdrop-filter
- ✅ Gradient borders and mesh gradients
- ✅ Bento grid layouts
- ✅ Micro-interactions ready (GSAP installed)
- ✅ Dark/Light mode structure
- ✅ Neumorphism styles
- ✅ Variable fonts (Inter, Plus Jakarta Sans)
- ✅ Floating animations
- ✅ Shimmer effects

## 📖 Documentation

- **[PROJECT_SETUP.md](./PROJECT_SETUP.md)** - Complete development guide
- [Laravel Docs](https://laravel.com/docs)
- [Vue 3 Docs](https://vuejs.org)
- [PrimeVue Docs](https://primevue.org)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Inertia.js](https://inertiajs.com)

## 🚀 Build Status

- ✅ Laravel installed and configured
- ✅ Database schema created
- ✅ NPM dependencies installed
- ✅ PrimeVue configured with custom theme
- ✅ PWA service worker generated
- ✅ Production build successful (957.32 KiB)
- ✅ 2025 design utilities (400+ lines CSS)
- ✅ Dark/Light mode ready
- ✅ i18n configured

## 💡 Quick Tips

1. **Access Database:** phpMyAdmin at http://localhost/phpmyadmin
2. **View Routes:** `php artisan route:list`
3. **Clear Cache:** `php artisan optimize:clear`
4. **Generate Key:** `php artisan key:generate`
5. **Vue DevTools:** Install browser extension for debugging

## 🤝 Development Team

For questions or issues, refer to PROJECT_SETUP.md for detailed implementation steps.

---

**Built with ❤️ using Laravel 12, Vue 3, TypeScript, and PrimeVue 4**

🎯 **Status:** Foundation Complete - Ready for Feature Development
📘 **Next:** See [PROJECT_SETUP.md](./PROJECT_SETUP.md) for implementation phases
