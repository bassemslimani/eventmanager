# Email Phase 2 - Completion Guide

## What's Been Built (Backend 100% Complete)

### ✅ Database & Models
- Email campaigns table with full tracking
- Campaign recipients table for individual send status
- Models with relationships and helper methods
- Migrations run successfully

### ✅ Email System
- `CampaignEmail` mailable with attachment support
- Professional HTML email template
- Queue jobs for bulk sending
- Error handling and retry logic

### ✅ Backend Controller
- Full CRUD operations for campaigns
- Recipient filtering by:
  - Event
  - Category (exhibitor, visitor, guest, speaker, organizer)
  - Registration date range
- Attachment upload (multiple files, 10MB each)
- Preview recipient count before sending
- Campaign sending with queue
- Status tracking

### ✅ Routes
All routes configured in `routes/web.php` under event_manager middleware:
- `/campaigns` - List all campaigns
- `/campaigns/create` - Create new campaign
- `/campaigns/{id}` - View campaign details
- `/campaigns/{id}/edit` - Edit draft campaign
- `POST /campaigns/{id}/send` - Send campaign
- `POST /campaigns/preview-recipients` - Preview filtered recipients

### ✅ Frontend (Partial)
- **Index.vue** - Campaign list page with:
  - Status badges
  - Send statistics
  - Action buttons (View, Edit, Send, Delete)
  - Pagination

---

## What Still Needs to Be Built (Frontend Only)

### 1. Campaign Create Page (`resources/js/Pages/Campaigns/Create.vue`)

**What it needs:**
```vue
- Campaign name input
- Email subject input
- Email body textarea (rich text editor would be nice)
- Filters section:
  - Event dropdown
  - Category checkboxes (Exhibitor, Visitor, Guest, Speaker, Organizer)
  - Date range pickers (optional)
- Attachment upload (multiple files)
- Preview recipients button (shows count)
- Create & Save as Draft button
```

**Key Features:**
- Real-time recipient count preview (call `/campaigns/preview-recipients` API)
- File upload interface for attachments
- Form validation
- Redirect to campaign show page after creation

### 2. Campaign Show Page (`resources/js/Pages/Campaigns/Show.vue`)

**What it needs:**
```vue
- Campaign details card:
  - Name, subject, body
  - Status badge
  - Filters applied
  - Attachments list
  - Created by, created at

- Statistics cards:
  - Total recipients
  - Sent count
  - Failed count
  - Pending count
  - Progress bar

- Recipients table:
  - Attendee name
  - Email
  - Status (pending/sent/failed)
  - Sent timestamp
  - Error message (if failed)
  - Pagination

- Action buttons:
  - Send Campaign (if canSend())
  - Edit (if canEdit())
  - Delete
  - Back to list
```

### 3. Campaign Edit Page (`resources/js/Pages/Campaigns/Edit.vue`)

**What it needs:**
```vue
- Same form as Create page
- Pre-populated with campaign data
- Can only edit if status is 'draft' or 'failed'
- Update button instead of Create
```

---

## Quick Implementation Guide

### Step 1: Create Campaign Create Page

```bash
# Create the file
touch resources/js/Pages/Campaigns/Create.vue
```

**Minimal working version:**
```vue
<template>
    <AuthenticatedLayout>
        <template #header>
            <h2>Create Email Campaign</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow space-y-6">
                    <!-- Campaign Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Campaign Name</label>
                        <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300" />
                    </div>

                    <!-- Email Subject -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Subject</label>
                        <input v-model="form.subject" type="text" required class="mt-1 block w-full rounded-md border-gray-300" />
                    </div>

                    <!-- Email Body -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Body</label>
                        <textarea v-model="form.body" rows="10" required class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                    </div>

                    <!-- Event Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Filter by Event (Optional)</label>
                        <select v-model="form.filters.event_id" class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="">All Events</option>
                            <option v-for="event in events" :key="event.id" :value="event.id">{{ event.name }}</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Category (Optional)</label>
                        <div class="space-y-2">
                            <label v-for="category in categories" :key="category" class="flex items-center">
                                <input type="checkbox" :value="category" v-model="form.filters.categories" class="rounded" />
                                <span class="ml-2 text-sm capitalize">{{ category }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Attachments (Optional)</label>
                        <input type="file" multiple @change="handleFiles" class="mt-1 block w-full" />
                    </div>

                    <!-- Preview Recipients -->
                    <div>
                        <button type="button" @click="previewRecipients" class="bg-gray-600 text-white px-4 py-2 rounded-md">
                            Preview Recipients
                        </button>
                        <span v-if="recipientCount !== null" class="ml-3 text-sm text-gray-600">
                            {{ recipientCount }} recipients will receive this email
                        </span>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end space-x-3">
                        <Link :href="route('campaigns.index')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md">Cancel</Link>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                            Create Campaign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    events: Array,
    categories: Array
});

const form = reactive({
    name: '',
    subject: '',
    body: '',
    filters: {
        event_id: '',
        categories: []
    },
    attachments: []
});

const recipientCount = ref(null);

const handleFiles = (event) => {
    form.attachments = Array.from(event.target.files);
};

const previewRecipients = async () => {
    try {
        const response = await axios.post(route('campaigns.preview-recipients'), {
            filters: form.filters
        });
        recipientCount.value = response.data.count;
    } catch (error) {
        console.error('Failed to preview recipients', error);
    }
};

const submit = () => {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('subject', form.subject);
    formData.append('body', form.body);
    formData.append('filters', JSON.stringify(form.filters));

    form.attachments.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    router.post(route('campaigns.store'), formData);
};
</script>
```

### Step 2: Create Campaign Show Page

Similar structure showing campaign details, stats, and recipient list.

### Step 3: Create Campaign Edit Page

Copy Create page, pre-populate with campaign data, change submit endpoint to `campaigns.update`.

---

## Testing the Campaign System

### 1. Create a Campaign
1. Go to `/campaigns/create`
2. Fill in:
   - Name: "Event Reminder"
   - Subject: "Don't miss our upcoming event!"
   - Body: "Hi there, we're excited to see you at..."
3. Select filters (event, categories)
4. Click "Preview Recipients" to see count
5. Click "Create Campaign"

### 2. View Campaign
- Navigate to `/campaigns`
- Click "View" on your campaign
- See stats and recipient list

### 3. Send Campaign
- On campaign show page, click "Send"
- Campaign status changes to "sending" → "sent"
- Queue worker processes emails

### 4. Monitor Progress
- Refresh campaign show page
- Watch sent_count increase
- Check failed_count for any errors

---

## Queue Worker Required

**IMPORTANT:** The queue worker MUST be running for campaigns to send:

```bash
# Start queue worker
php artisan queue:work

# Or run in background (Windows)
start /B php artisan queue:work

# Or use Task Scheduler for production
```

---

## How It Works

1. **Create Campaign**:
   - User fills form with filters
   - Backend creates campaign and recipients records
   - Status: draft

2. **Send Campaign**:
   - User clicks "Send"
   - `ProcessCampaign` job dispatched
   - Campaign status → "sending"

3. **Process Campaign**:
   - `ProcessCampaign` job runs
   - Queues individual `SendCampaignEmail` jobs for each recipient
   - Campaign status → "sent"

4. **Send Individual Emails**:
   - Each `SendCampaignEmail` job runs
   - Sends email to one attendee
   - Updates recipient status (sent/failed)
   - Updates campaign counts

5. **Track Results**:
   - View campaign show page
   - See real-time statistics
   - View individual recipient status

---

## Current Status Summary

**✅ COMPLETE (Ready to Use):**
- Database structure
- Models and relationships
- Email templates
- Queue jobs
- Controller logic
- Routes
- Campaign list page

**⏳ PENDING (Needs Frontend Pages):**
- Campaign create page (Vue.js) - ~150 lines
- Campaign show page (Vue.js) - ~200 lines
- Campaign edit page (Vue.js) - ~150 lines

**Estimated Time to Complete:** 2-3 hours for all 3 Vue pages

---

## Next Session To-Do

1. Create `/resources/js/Pages/Campaigns/Create.vue`
2. Create `/resources/js/Pages/Campaigns/Show.vue`
3. Create `/resources/js/Pages/Campaigns/Edit.vue`
4. Test end-to-end:
   - Create campaign with filters
   - Preview recipients
   - Send campaign
   - Monitor progress
5. Add rich text editor for email body (optional enhancement)

---

## Key Files Reference

**Backend:**
- `app/Models/EmailCampaign.php`
- `app/Models/CampaignRecipient.php`
- `app/Mail/CampaignEmail.php`
- `app/Jobs/ProcessCampaign.php`
- `app/Jobs/SendCampaignEmail.php`
- `app/Http/Controllers/CampaignController.php`
- `resources/views/emails/campaign.blade.php`

**Frontend (Existing):**
- `resources/js/Pages/Campaigns/Index.vue`

**Frontend (Needed):**
- `resources/js/Pages/Campaigns/Create.vue`
- `resources/js/Pages/Campaigns/Show.vue`
- `resources/js/Pages/Campaigns/Edit.vue`

**Routes:**
- All campaign routes in `routes/web.php` (lines 89-98)
