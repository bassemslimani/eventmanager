# 🚀 PHASE 3: Vue Components & UI - Quick Start Guide

## Current Status: Phase 2 Complete ✅ → Starting Phase 3

---

## 📋 PHASE 3 CHECKLIST

Copy this checklist to track your progress:

```
PHASE 3: VUE COMPONENTS & UI

[ ] Step 1: Create Dashboard/Index.vue with Bento grid layout
[ ] Step 2: Create Attendees/Index.vue with PrimeVue DataTable
[ ] Step 3: Create Attendees/Create.vue form
[ ] Step 4: Create Attendees/Edit.vue form
[ ] Step 5: Create Events/Index.vue page
[ ] Step 6: Create reusable Modern components (GlassCard, etc.)
[ ] Step 7: Test all pages and navigation
[ ] Step 8: Verify responsive design
```

---

## 🎯 STEP-BY-STEP INSTRUCTIONS

### **STEP 1: Create Dashboard/Index.vue**

This is the main dashboard with Bento grid layout showing stats and recent activity.

**Create file: resources/js/Pages/Dashboard/Index.vue**

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import { ref, onMounted } from 'vue';

interface Stats {
    total_attendees: number;
    total_exhibitors: number;
    total_guests: number;
    total_organizers: number;
    checked_in_today: number;
    badges_generated: number;
    active_events: number;
}

interface Props {
    stats: Stats;
    recentCheckIns: any[];
    upcomingEvents: any[];
}

const props = defineProps<Props>();

const chartData = ref();
const chartOptions = ref();

onMounted(() => {
    chartData.value = {
        labels: ['Exhibitors', 'Guests', 'Organizers'],
        datasets: [
            {
                data: [
                    props.stats.total_exhibitors,
                    props.stats.total_guests,
                    props.stats.total_organizers
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(234, 179, 8, 0.8)'
                ],
                borderColor: [
                    '#10B981',
                    '#3B82F6',
                    '#EAB308'
                ],
                borderWidth: 2
            }
        ]
    };

    chartOptions.value = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gradient mb-2">
                        Welcome to QRMH
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Modern Event Badge Management System
                    </p>
                </div>

                <!-- Bento Grid Stats -->
                <div class="bento-grid mb-8">
                    <!-- Total Attendees -->
                    <Card class="bento-item glass-card hover:shadow-glow transition-smooth">
                        <template #content>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        Total Attendees
                                    </p>
                                    <h3 class="text-3xl font-bold text-gradient">
                                        {{ stats.total_attendees }}
                                    </h3>
                                </div>
                                <i class="pi pi-users text-4xl text-emerald-500"></i>
                            </div>
                        </template>
                    </Card>

                    <!-- Checked In Today -->
                    <Card class="bento-item glass-card hover:shadow-glow transition-smooth">
                        <template #content>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        Checked In Today
                                    </p>
                                    <h3 class="text-3xl font-bold text-emerald-600">
                                        {{ stats.checked_in_today }}
                                    </h3>
                                </div>
                                <i class="pi pi-check-circle text-4xl text-emerald-500"></i>
                            </div>
                        </template>
                    </Card>

                    <!-- Badges Generated -->
                    <Card class="bento-item glass-card hover:shadow-glow transition-smooth">
                        <template #content>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        Badges Generated
                                    </p>
                                    <h3 class="text-3xl font-bold text-blue-600">
                                        {{ stats.badges_generated }}
                                    </h3>
                                </div>
                                <i class="pi pi-id-card text-4xl text-blue-500"></i>
                            </div>
                        </template>
                    </Card>

                    <!-- Active Events -->
                    <Card class="bento-item glass-card hover:shadow-glow transition-smooth">
                        <template #content>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        Active Events
                                    </p>
                                    <h3 class="text-3xl font-bold text-purple-600">
                                        {{ stats.active_events }}
                                    </h3>
                                </div>
                                <i class="pi pi-calendar text-4xl text-purple-500"></i>
                            </div>
                        </template>
                    </Card>

                    <!-- Chart - Wide -->
                    <Card class="bento-item-wide glass-card">
                        <template #header>
                            <h3 class="text-xl font-bold p-4">Attendee Distribution</h3>
                        </template>
                        <template #content>
                            <div class="h-64">
                                <Chart type="doughnut" :data="chartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>

                    <!-- Recent Check-ins - Tall -->
                    <Card class="bento-item-tall glass-card">
                        <template #header>
                            <h3 class="text-xl font-bold p-4">Recent Activity</h3>
                        </template>
                        <template #content>
                            <div v-if="recentCheckIns.length > 0" class="space-y-3">
                                <div
                                    v-for="checkIn in recentCheckIns"
                                    :key="checkIn.id"
                                    class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800"
                                >
                                    <i class="pi pi-check-circle text-emerald-500 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="font-semibold text-sm">{{ checkIn.attendee?.name || 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ new Date(checkIn.scanned_at).toLocaleString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center text-gray-500 py-8">
                                <i class="pi pi-inbox text-4xl mb-2"></i>
                                <p>No recent check-ins</p>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button
                        class="gradient-btn magnetic-btn flex items-center justify-center gap-3 py-4"
                        @click="$inertia.visit('/check-in')"
                    >
                        <i class="pi pi-qrcode text-2xl"></i>
                        <span class="font-semibold">Scan QR Code</span>
                    </button>

                    <button
                        class="gradient-btn magnetic-btn flex items-center justify-center gap-3 py-4"
                        @click="$inertia.visit('/import')"
                    >
                        <i class="pi pi-upload text-2xl"></i>
                        <span class="font-semibold">Import Attendees</span>
                    </button>

                    <button
                        class="gradient-btn magnetic-btn flex items-center justify-center gap-3 py-4"
                        @click="$inertia.visit('/badges')"
                    >
                        <i class="pi pi-id-card text-2xl"></i>
                        <span class="font-semibold">Generate Badges</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

### **STEP 2: Create Attendees/Index.vue**

This page displays all attendees in a PrimeVue DataTable with search and filtering.

**Create file: resources/js/Pages/Attendees/Index.vue**

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';

interface Attendee {
    id: number;
    type: string;
    name: string;
    email: string;
    company: string;
    qr_code: string;
    checked_in_at: string | null;
}

interface Props {
    attendees: {
        data: Attendee[];
        links: any[];
        meta: any;
    };
    filters: {
        type?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    type: props.filters.type || null,
    search: props.filters.search || '',
});

const typeOptions = [
    { label: 'All Types', value: null },
    { label: 'Exhibitors', value: 'exhibitor' },
    { label: 'Guests', value: 'guest' },
    { label: 'Organizers', value: 'organizer' },
];

const searchAttendees = () => {
    router.get('/attendees', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getTypeSeverity = (type: string) => {
    const severities: Record<string, string> = {
        exhibitor: 'success',
        guest: 'info',
        organizer: 'warn',
    };
    return severities[type] || 'secondary';
};

const deleteAttendee = (id: number) => {
    if (confirm('Are you sure you want to delete this attendee?')) {
        router.delete(`/attendees/${id}`);
    }
};
</script>

<template>
    <Head title="Attendees" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gradient">Attendees</h1>
                    <Button
                        label="Add New Attendee"
                        icon="pi pi-plus"
                        class="gradient-btn"
                        @click="router.visit('/attendees/create')"
                    />
                </div>

                <!-- Filters -->
                <div class="glass-card p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Search</label>
                            <InputText
                                v-model="filters.search"
                                placeholder="Search by name, email..."
                                class="w-full"
                                @keyup.enter="searchAttendees"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Type</label>
                            <Dropdown
                                v-model="filters.type"
                                :options="typeOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Filter by type"
                                class="w-full"
                                @change="searchAttendees"
                            />
                        </div>

                        <div class="flex items-end">
                            <Button
                                label="Clear Filters"
                                icon="pi pi-filter-slash"
                                severity="secondary"
                                @click="filters = { type: null, search: '' }; searchAttendees()"
                            />
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="modern-card">
                    <DataTable
                        :value="attendees.data"
                        stripedRows
                        paginator
                        :rows="20"
                        :rowsPerPageOptions="[10, 20, 50, 100]"
                        class="custom-datatable"
                    >
                        <Column field="id" header="ID" sortable style="width: 80px" />

                        <Column field="type" header="Type" sortable>
                            <template #body="slotProps">
                                <Tag
                                    :value="slotProps.data.type"
                                    :severity="getTypeSeverity(slotProps.data.type)"
                                />
                            </template>
                        </Column>

                        <Column field="name" header="Name" sortable />
                        <Column field="email" header="Email" sortable />
                        <Column field="company" header="Company" sortable />
                        <Column field="qr_code" header="QR Code" />

                        <Column field="checked_in_at" header="Check-in Status">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.checked_in_at"
                                    value="Checked In"
                                    severity="success"
                                    icon="pi pi-check"
                                />
                                <Tag
                                    v-else
                                    value="Not Checked In"
                                    severity="secondary"
                                />
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 150px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="info"
                                        size="small"
                                        @click="router.visit(`/attendees/${slotProps.data.id}/edit`)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        size="small"
                                        @click="deleteAttendee(slotProps.data.id)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

### **STEP 3: Create Attendees/Create.vue**

Form for creating new attendees.

**Create file: resources/js/Pages/Attendees/Create.vue**

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';

interface Event {
    id: number;
    name: string;
}

interface Props {
    events: Event[];
}

const props = defineProps<Props>();

const form = useForm({
    event_id: null,
    type: 'exhibitor',
    name: '',
    name_ar: '',
    email: '',
    mobile: '',
    company: '',
    company_ar: '',
    category: null,
    role: '',
    department: '',
});

const typeOptions = [
    { label: 'Exhibitor', value: 'exhibitor' },
    { label: 'Guest', value: 'guest' },
    { label: 'Organizer', value: 'organizer' },
];

const categoryOptions = [
    { label: 'Freelancer', value: 'freelancer' },
    { label: 'Company', value: 'company' },
];

const submit = () => {
    form.post('/attendees');
};
</script>

<template>
    <Head title="Create Attendee" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gradient mb-2">Create New Attendee</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Add a new attendee to the system
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="glass-card p-6 space-y-6">
                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Type *</label>
                        <Dropdown
                            v-model="form.type"
                            :options="typeOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select type"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.type }"
                        />
                        <small v-if="form.errors.type" class="text-red-500">{{ form.errors.type }}</small>
                    </div>

                    <!-- Event -->
                    <div v-if="events.length > 0">
                        <label class="block text-sm font-medium mb-2">Event (Optional)</label>
                        <Dropdown
                            v-model="form.event_id"
                            :options="events"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select event"
                            class="w-full"
                        />
                    </div>

                    <!-- Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Name (English) *</label>
                            <InputText
                                v-model="form.name"
                                placeholder="John Doe"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.name }"
                            />
                            <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Name (Arabic)</label>
                            <InputText
                                v-model="form.name_ar"
                                placeholder="جون دو"
                                class="w-full"
                                dir="rtl"
                            />
                        </div>
                    </div>

                    <!-- Email & Mobile -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Email *</label>
                            <InputText
                                v-model="form.email"
                                type="email"
                                placeholder="john@example.com"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.email }"
                            />
                            <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Mobile</label>
                            <InputText
                                v-model="form.mobile"
                                placeholder="+966 50 123 4567"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <!-- Company (for Exhibitors and Guests) -->
                    <div v-if="form.type !== 'organizer'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Company (English)</label>
                            <InputText
                                v-model="form.company"
                                placeholder="Company Name"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Company (Arabic)</label>
                            <InputText
                                v-model="form.company_ar"
                                placeholder="اسم الشركة"
                                class="w-full"
                                dir="rtl"
                            />
                        </div>
                    </div>

                    <!-- Category (for Exhibitors) -->
                    <div v-if="form.type === 'exhibitor'">
                        <label class="block text-sm font-medium mb-2">Category</label>
                        <Dropdown
                            v-model="form.category"
                            :options="categoryOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select category"
                            class="w-full"
                        />
                    </div>

                    <!-- Role & Department (for Organizers) -->
                    <div v-if="form.type === 'organizer'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Role</label>
                            <InputText
                                v-model="form.role"
                                placeholder="Event Manager"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Department</label>
                            <InputText
                                v-model="form.department"
                                placeholder="Operations"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-end">
                        <Button
                            label="Cancel"
                            severity="secondary"
                            @click="$inertia.visit('/attendees')"
                            type="button"
                        />
                        <Button
                            label="Create Attendee"
                            icon="pi pi-check"
                            class="gradient-btn"
                            type="submit"
                            :loading="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

### **STEP 4: Create Attendees/Edit.vue**

Form for editing existing attendees (similar to Create but with existing data).

**Create file: resources/js/Pages/Attendees/Edit.vue**

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';

interface Event {
    id: number;
    name: string;
}

interface Attendee {
    id: number;
    event_id: number | null;
    type: string;
    name: string;
    name_ar: string | null;
    email: string;
    mobile: string | null;
    company: string | null;
    company_ar: string | null;
    category: string | null;
    role: string | null;
    department: string | null;
}

interface Props {
    attendee: Attendee;
    events: Event[];
}

const props = defineProps<Props>();

const form = useForm({
    event_id: props.attendee.event_id,
    type: props.attendee.type,
    name: props.attendee.name,
    name_ar: props.attendee.name_ar || '',
    email: props.attendee.email,
    mobile: props.attendee.mobile || '',
    company: props.attendee.company || '',
    company_ar: props.attendee.company_ar || '',
    category: props.attendee.category,
    role: props.attendee.role || '',
    department: props.attendee.department || '',
});

const typeOptions = [
    { label: 'Exhibitor', value: 'exhibitor' },
    { label: 'Guest', value: 'guest' },
    { label: 'Organizer', value: 'organizer' },
];

const categoryOptions = [
    { label: 'Freelancer', value: 'freelancer' },
    { label: 'Company', value: 'company' },
];

const submit = () => {
    form.put(`/attendees/${props.attendee.id}`);
};
</script>

<template>
    <Head title="Edit Attendee" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gradient mb-2">Edit Attendee</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Update attendee information
                    </p>
                </div>

                <!-- Form (same structure as Create.vue) -->
                <form @submit.prevent="submit" class="glass-card p-6 space-y-6">
                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Type *</label>
                        <Dropdown
                            v-model="form.type"
                            :options="typeOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select type"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.type }"
                        />
                        <small v-if="form.errors.type" class="text-red-500">{{ form.errors.type }}</small>
                    </div>

                    <!-- Event -->
                    <div v-if="events.length > 0">
                        <label class="block text-sm font-medium mb-2">Event (Optional)</label>
                        <Dropdown
                            v-model="form.event_id"
                            :options="events"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select event"
                            class="w-full"
                        />
                    </div>

                    <!-- Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Name (English) *</label>
                            <InputText
                                v-model="form.name"
                                placeholder="John Doe"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.name }"
                            />
                            <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Name (Arabic)</label>
                            <InputText
                                v-model="form.name_ar"
                                placeholder="جون دو"
                                class="w-full"
                                dir="rtl"
                            />
                        </div>
                    </div>

                    <!-- Email & Mobile -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Email *</label>
                            <InputText
                                v-model="form.email"
                                type="email"
                                placeholder="john@example.com"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.email }"
                            />
                            <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Mobile</label>
                            <InputText
                                v-model="form.mobile"
                                placeholder="+966 50 123 4567"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <!-- Company (for Exhibitors and Guests) -->
                    <div v-if="form.type !== 'organizer'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Company (English)</label>
                            <InputText
                                v-model="form.company"
                                placeholder="Company Name"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Company (Arabic)</label>
                            <InputText
                                v-model="form.company_ar"
                                placeholder="اسم الشركة"
                                class="w-full"
                                dir="rtl"
                            />
                        </div>
                    </div>

                    <!-- Category (for Exhibitors) -->
                    <div v-if="form.type === 'exhibitor'">
                        <label class="block text-sm font-medium mb-2">Category</label>
                        <Dropdown
                            v-model="form.category"
                            :options="categoryOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select category"
                            class="w-full"
                        />
                    </div>

                    <!-- Role & Department (for Organizers) -->
                    <div v-if="form.type === 'organizer'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Role</label>
                            <InputText
                                v-model="form.role"
                                placeholder="Event Manager"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Department</label>
                            <InputText
                                v-model="form.department"
                                placeholder="Operations"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-end">
                        <Button
                            label="Cancel"
                            severity="secondary"
                            @click="$inertia.visit('/attendees')"
                            type="button"
                        />
                        <Button
                            label="Update Attendee"
                            icon="pi pi-check"
                            class="gradient-btn"
                            type="submit"
                            :loading="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

### **STEP 5: Test Everything**

1. **Start the development servers:**
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

2. **Visit the application:**
- http://localhost:8000
- Login with your account
- You should see the new dashboard

3. **Test navigation:**
- Click "Attendees" in the menu
- Try creating a new attendee
- Test filtering and searching

---

## ✅ PHASE 3 COMPLETE WHEN:

- [ ] Dashboard displays with Bento grid and stats
- [ ] Attendees list shows in DataTable
- [ ] Can create new attendees via form
- [ ] Can edit existing attendees
- [ ] Filtering and search work correctly
- [ ] All PrimeVue components render properly
- [ ] No console errors
- [ ] Responsive design works on mobile

---

## 🐛 Troubleshooting

**If you get TypeScript errors:**
```bash
npm run build
```

**If components don't show:**
- Check browser console for errors
- Verify PrimeVue is imported in app.ts
- Clear Laravel cache: `php artisan optimize:clear`

**If routing doesn't work:**
- Verify routes are registered: `php artisan route:list`
- Check that controllers return Inertia responses

---

## 💡 TIPS

1. **Use browser DevTools** to debug Vue components
2. **Check Network tab** for API responses
3. **Install Vue DevTools** browser extension
4. **Use TypeScript** for better code completion
5. **Test responsiveness** at different screen sizes

---

## 📚 Reference Files

- **Full implementation code:** PROJECT_STATUS.md (Section 3)
- **Design system:** resources/css/app.css
- **PrimeVue docs:** https://primevue.org/

---

**🎉 Ready to build beautiful Vue components! Let's create Phase 3!**

**Project Progress: 50% → 75% (after Phase 3)**
