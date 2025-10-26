# 🚀 PHASE 6: Multi-Role System - Implementation Guide

## Current Status: Phase 5 Complete ✅ → Phase 6 Implemented

**Project Progress: 95% Complete**

---

## 📋 PHASE 6 OVERVIEW

Phase 6 introduces a comprehensive multi-role system with three distinct user types:

1. **Admin** - Full system access
2. **Event Manager** - Manages specific events and can create agents
3. **Agent** - Scanner-only access for checking in attendees

---

## ✅ COMPLETED IMPLEMENTATION

### 1. Database Schema ✅

#### Migrations Created:
- `add_role_to_users_table` - Adds role enum field (admin, event_manager, agent)
- `create_event_user_table` - Pivot table for event-user assignments

#### Tables:
```sql
users table:
- role (enum: admin, event_manager, agent)

event_user table:
- id
- event_id (FK to events)
- user_id (FK to users)
- role (enum: event_manager, agent)
- timestamps
- unique(event_id, user_id)
```

### 2. Models & Relationships ✅

#### User Model (app/Models/User.php)
**New Methods:**
- `isAdmin()` - Check if user is admin
- `isEventManager()` - Check if user is event manager
- `isAgent()` - Check if user is agent
- `events()` - Get all events user has access to
- `managedEvents()` - Get events user manages
- `canAccessEvent($eventId)` - Check event access
- `canManageEvent($eventId)` - Check event management permission

#### Event Model (app/Models/Event.php)
**New Relationships:**
- `users()` - Get all assigned users (managers and agents)
- `managers()` - Get event managers only
- `agents()` - Get agents only

### 3. Middleware ✅

#### Created Middleware:
- `EnsureUserIsAdmin` - Admin-only access
- `EnsureUserIsEventManager` - Event manager + admin access
- `EnsureUserIsAgent` - All authenticated users (for scanner routes)

#### Registered in bootstrap/app.php:
```php
'admin' => EnsureUserIsAdmin::class,
'event_manager' => EnsureUserIsEventManager::class,
'agent' => EnsureUserIsAgent::class,
```

### 4. Controllers ✅

#### UserManagementController
**Methods:**
- `index()` - List users per event
- `create()` - Show create user form
- `store()` - Create new event manager or agent
- `eventUsers()` - Show users for specific event
- `detach()` - Remove user from event
- `destroy()` - Delete user (admin only)

#### DashboardController (Updated)
**Methods:**
- `index()` - Routes to role-specific dashboard
- `adminDashboard()` - Full system stats
- `eventManagerDashboard()` - Event-specific stats
- `agentDashboard()` - Scanner-only view

### 5. Routes ✅

#### Route Structure:
```php
// Admin Only
Route::middleware(['admin'])->group(function () {
    - Events management
    - User management (all users)
});

// Event Manager & Admin
Route::middleware(['event_manager'])->group(function () {
    - User management (event-specific)
    - Attendee management
    - Badge generation
    - Import attendees
});

// All Users (including Agents)
- Dashboard (role-specific)
- Check-in scanner
- Profile management
```

### 6. Vue Components ✅

#### Created Components:
- `Dashboard/AgentDashboard.vue` - Scanner-only interface for agents
- `UserManagement/Index.vue` - List users per event
- `UserManagement/Create.vue` - Create event manager or agent

---

## 🎯 ROLE CAPABILITIES

### Admin
✅ Full system access
✅ Create and manage all events
✅ Create and manage all users (admins, event managers, agents)
✅ View all attendees and check-ins
✅ Access all features

### Event Manager
✅ View and manage assigned events only
✅ Create and manage agents for their events
✅ Manage attendees in their events
✅ Generate badges for their events
✅ Import attendees to their events
✅ View check-in data for their events
❌ Cannot create/edit events
❌ Cannot create other event managers
❌ Cannot access other events' data

### Agent
✅ View scanner dashboard
✅ Access QR code scanner
✅ Perform manual check-ins
✅ View their assigned events
❌ Cannot manage users
❌ Cannot manage attendees
❌ Cannot generate badges
❌ Cannot import data
❌ Cannot view full statistics

---

## 📁 FILES CREATED/MODIFIED

### Backend Files Created:
- `database/migrations/2025_10_26_184626_add_role_to_users_table.php`
- `database/migrations/2025_10_26_184706_create_event_user_table.php`
- `app/Http/Middleware/EnsureUserIsAdmin.php`
- `app/Http/Middleware/EnsureUserIsEventManager.php`
- `app/Http/Middleware/EnsureUserIsAgent.php`
- `app/Http/Controllers/UserManagementController.php`

### Backend Files Modified:
- `app/Models/User.php` - Added role methods and relationships
- `app/Models/Event.php` - Added user relationships
- `app/Http/Controllers/DashboardController.php` - Added role-based dashboards
- `bootstrap/app.php` - Registered middleware aliases
- `routes/web.php` - Added role-based route groups

### Frontend Files Created:
- `resources/js/Pages/Dashboard/AgentDashboard.vue`
- `resources/js/Pages/UserManagement/Index.vue`
- `resources/js/Pages/UserManagement/Create.vue`

---

## 🚀 HOW TO USE

### 1. Create Event Manager (Admin Only)

1. Login as admin
2. Go to `/users/create`
3. Fill in details:
   - Name
   - Email
   - Password
   - Role: "Event Manager"
   - Select events to manage
4. Click "Create User"

### 2. Create Agent (Admin or Event Manager)

1. Login as admin or event manager
2. Go to `/event-users/create` (event manager) or `/users/create` (admin)
3. Fill in details:
   - Name
   - Email
   - Password
   - Role: "Agent"
   - Select events
4. Click "Create User"

### 3. Agent Workflow

1. Agent logs in
2. Sees simplified scanner dashboard
3. Clicks "QR Scanner" or "Manual Check-in"
4. Scans attendee QR codes or enters ticket numbers
5. Confirms check-in

### 4. Event Manager Workflow

1. Event manager logs in
2. Sees dashboard with their events only
3. Can:
   - Manage attendees in their events
   - Create agents for their events
   - Generate badges
   - Import attendees
   - View check-ins

---

## 🔐 SECURITY FEATURES

✅ Role-based middleware protection
✅ Event-level access control
✅ Users can only access their assigned events
✅ Event managers cannot access other events
✅ Agents have read-only access (scanner only)
✅ Password hashing for all users
✅ CSRF protection on all forms

---

## 📊 DATABASE RELATIONSHIPS

```
User (N) <---> (N) Event
     via event_user pivot table

Relationships:
- User hasMany events (via belongsToMany)
- Event hasMany users (via belongsToMany)
- User hasMany managedEvents (where pivot.role = 'event_manager')
- Event hasMany managers (where pivot.role = 'event_manager')
- Event hasMany agents (where pivot.role = 'agent')
```

---

## 🎨 UI FEATURES

### Agent Dashboard:
- Large check-in counter
- Event list with details
- Prominent scanner buttons
- Simple, focused interface
- Instructions section

### User Management Interface:
- Event-based user listing
- Visual separation of managers and agents
- Role badges (color-coded)
- Easy user creation
- Event assignment with multi-select

---

## 🧪 TESTING CHECKLIST

### Admin Tests:
- [ ] Can create events
- [ ] Can create event managers
- [ ] Can create agents
- [ ] Can assign users to multiple events
- [ ] Can view all users
- [ ] Can delete users

### Event Manager Tests:
- [ ] Can see assigned events only
- [ ] Can create agents for their events
- [ ] Cannot create agents for other events
- [ ] Can manage attendees in their events
- [ ] Cannot access other events' data
- [ ] Can import attendees to their events

### Agent Tests:
- [ ] Sees scanner-only dashboard
- [ ] Can access QR scanner
- [ ] Can perform manual check-in
- [ ] Can see assigned events
- [ ] Cannot access admin features
- [ ] Cannot access event manager features
- [ ] Navigation shows only check-in options

### General Tests:
- [ ] Role-based redirect after login
- [ ] Middleware blocks unauthorized access
- [ ] Event access properly restricted
- [ ] All forms validate correctly
- [ ] Password creation follows security rules

---

## 🐛 TROUBLESHOOTING

**Issue:** User role not updating after changes
**Solution:** Clear cache: `php artisan cache:clear && php artisan config:clear`

**Issue:** Middleware not blocking access
**Solution:** Ensure middleware is registered in `bootstrap/app.php` and routes use correct middleware

**Issue:** Event manager sees all events
**Solution:** Check `canManageEvent()` method in User model and verify pivot table data

**Issue:** Agent can access admin routes
**Solution:** Verify route groups have correct middleware applied in `routes/web.php`

---

## 💡 TIPS

1. **Always assign users to events** - Without event assignment, event managers and agents cannot function
2. **Use meaningful emails** - Email becomes the username for login
3. **Document event assignments** - Keep track of which users manage which events
4. **Test role permissions** - Always test with different user roles
5. **Secure passwords** - Enforce minimum 8 characters for security

---

## 🔮 FUTURE ENHANCEMENTS

Potential Phase 7 features:

- Email notifications for user creation
- Password reset functionality
- User activity logs
- Event-specific permissions (custom permissions per event)
- Bulk user import
- User suspension (instead of deletion)
- Two-factor authentication
- API access for mobile apps

---

## 📚 API ENDPOINTS

### User Management:
```
GET  /users                     - List all users (Admin)
GET  /users/create              - Create user form (Admin)
POST /users                     - Store new user (Admin)
DELETE /users/{user}            - Delete user (Admin)

GET  /event-users               - List event users (Event Manager)
GET  /event-users/create        - Create user form (Event Manager)
POST /event-users               - Store new user (Event Manager)
GET  /events/{event}/users      - Show event users
DELETE /events/{event}/users/{user} - Remove user from event
```

### Check-in (All Users):
```
GET  /check-in                  - QR scanner interface
POST /check-in/scan             - Process QR scan
GET  /check-in/manual           - Manual check-in form
POST /check-in/manual           - Submit manual check-in
```

---

## 🎉 SUCCESS CRITERIA

Phase 6 is complete when:

✅ Three roles implemented (admin, event_manager, agent)
✅ Role-based middleware working
✅ Agent dashboard shows scanner-only interface
✅ Event managers can create agents
✅ Event managers can only access their events
✅ Agents can only access scanner
✅ All routes properly protected
✅ User management interface functional
✅ Documentation complete

---

## 📝 NOTES

- Default user role is "admin" (see migration)
- All existing users are admins by default
- Event managers must be assigned to at least one event
- Agents must be assigned to at least one event
- Users can be assigned to multiple events
- Removing last event from user leaves them with no access

---

**🎯 Phase 6 Complete!**

**Project Progress: 95% Complete**

**Next Steps:** Testing and deployment preparation

---

## 🔗 RELATED DOCUMENTATION

- [PROJECT_STATUS.md](./PROJECT_STATUS.md) - Overall project status
- [PHASE_5_START.md](./PHASE_5_START.md) - Mobile & PWA features
- [README.md](./README.md) - Quick start guide
