<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Image from 'primevue/image';

interface Event {
    id: number;
    name: string;
}

interface BadgeTemplate {
    id: number | null;
    category: string;
    front_template: string | null;
    back_template: string | null;
    is_active: boolean;
}

interface Props {
    event: Event;
    templates: BadgeTemplate[];
}

const props = defineProps<Props>();

const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
        exhibitor: 'success',
        guest: 'info',
        organizer: 'warn',
        vip: 'danger',
    };
    return colors[category] || 'secondary';
};

const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        exhibitor: 'pi-building',
        guest: 'pi-user',
        organizer: 'pi-users',
        vip: 'pi-star-fill',
    };
    return icons[category] || 'pi-tag';
};

const createTemplate = (category: string) => {
    router.visit(`/events/${props.event.id}/badge-designer/create/${category}`);
};

const editTemplate = (templateId: number) => {
    router.visit(`/events/${props.event.id}/badge-designer/${templateId}/edit`);
};

const deleteTemplate = (templateId: number) => {
    if (confirm('Are you sure you want to delete this badge template?')) {
        router.delete(`/events/${props.event.id}/badge-designer/${templateId}`);
    }
};

const openVisualDesigner = (category: string, templateId?: number) => {
    if (templateId) {
        router.visit(`/events/${props.event.id}/badge-designer/${templateId}/visual-edit`);
    } else {
        router.visit(`/events/${props.event.id}/badge-designer/visual/${category}`);
    }
};
</script>

<template>
    <Head :title="`Badge Designer - ${event.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <Button
                        label="Back to Events"
                        icon="pi pi-arrow-left"
                        severity="secondary"
                        text
                        @click="router.visit('/events')"
                        class="mb-3"
                    />
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Badge Designer</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Design professional badges for {{ event.name }}
                    </p>
                </div>

                <!-- Instructions Card -->
                <Card class="bg-white dark:bg-gray-800 rounded-xl shadow-md mb-6">
                    <template #content>
                        <div class="flex items-start gap-4">
                            <i class="pi pi-palette text-3xl text-gray-600 dark:text-gray-400"></i>
                            <div>
                                <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">Professional Visual Badge Designer</h3>
                                <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                    <li><strong>Upload A4 template:</strong> Add your ready-made badge design (8.5cm × 12.5cm)</li>
                                    <li><strong>Drag & drop elements:</strong> Position attendee names, companies, QR codes, and logos precisely</li>
                                    <li><strong>Real-time preview:</strong> See exactly how your badge will look when printed</li>
                                    <li><strong>Professional controls:</strong> Adjust fonts, colors, alignment, and sizing with precision</li>
                                    <li><strong>Category-specific designs:</strong> Create unique badges for Exhibitor, Guest, Organizer, and VIP</li>
                                </ul>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Badge Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card
                        v-for="template in templates"
                        :key="template.category"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl transition-all"
                    >
                        <template #header>
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-3">
                                    <Tag
                                        :value="template.category.toUpperCase()"
                                        :severity="getCategoryColor(template.category)"
                                        :icon="`pi ${getCategoryIcon(template.category)}`"
                                    />
                                    <Tag
                                        v-if="template.id && template.is_active"
                                        value="Active"
                                        severity="success"
                                        icon="pi pi-check"
                                    />
                                </div>
                            </div>
                        </template>

                        <template #content>
                            <div>
                                <!-- Badge Preview -->
                                <div>
                                    <p class="text-sm font-medium mb-2 text-gray-900 dark:text-white">Badge Preview</p>
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg h-64 flex items-center justify-center">
                                        <Image
                                            v-if="template.front_template"
                                            :src="`/storage/${template.front_template}`"
                                            alt="Badge template"
                                            class="w-full h-full object-contain"
                                            preview
                                        />
                                        <div v-else class="text-center text-gray-400">
                                            <i class="pi pi-image text-4xl mb-2"></i>
                                            <p class="text-sm">No badge design yet</p>
                                            <p class="text-xs mt-1">Click below to create one</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template #footer>
                            <div class="space-y-2">
                                <!-- Primary: Visual Designer Button -->
                                <Button
                                    :label="template.id ? 'Open Visual Designer' : 'Create with Visual Designer'"
                                    icon="pi pi-palette"
                                    class="gradient-btn w-full"
                                    @click="openVisualDesigner(template.category, template.id || undefined)"
                                />

                                <!-- Secondary: Delete Button for existing templates -->
                                <Button
                                    v-if="template.id"
                                    label="Delete Template"
                                    icon="pi pi-trash"
                                    severity="danger"
                                    outlined
                                    class="w-full"
                                    @click="deleteTemplate(template.id)"
                                />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
