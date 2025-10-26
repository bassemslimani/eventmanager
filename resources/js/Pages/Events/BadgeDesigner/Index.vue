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
</script>

<template>
    <Head :title="`Badge Designer - ${event.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
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
                    <h1 class="text-3xl font-bold text-gradient mb-2">Badge Designer</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Design professional badges for {{ event.name }}
                    </p>
                </div>

                <!-- Instructions Card -->
                <Card class="glass-card mb-6">
                    <template #content>
                        <div class="flex items-start gap-4">
                            <i class="pi pi-info-circle text-3xl text-blue-500"></i>
                            <div>
                                <h3 class="font-bold text-lg mb-2">How Badge Designer Works</h3>
                                <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                    <li>Create unique badge designs for each attendee category (Exhibitor, Guest, Organizer, VIP)</li>
                                    <li>Upload front and back template images for your badges</li>
                                    <li>Customize element positions, colors, fonts, and sizes</li>
                                    <li>Add terms and conditions that will appear on the badge</li>
                                    <li>Preview your designs before activation</li>
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
                        class="glass-card hover:shadow-xl transition-all"
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
                            <div class="space-y-4">
                                <!-- Front Template Preview -->
                                <div>
                                    <p class="text-sm font-medium mb-2">Front Design</p>
                                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg h-48 flex items-center justify-center">
                                        <Image
                                            v-if="template.front_template"
                                            :src="`/storage/${template.front_template}`"
                                            alt="Front template"
                                            class="w-full h-full object-contain"
                                            preview
                                        />
                                        <div v-else class="text-center text-gray-400">
                                            <i class="pi pi-image text-4xl mb-2"></i>
                                            <p class="text-sm">No front design</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Back Template Preview -->
                                <div>
                                    <p class="text-sm font-medium mb-2">Back Design</p>
                                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg h-48 flex items-center justify-center">
                                        <Image
                                            v-if="template.back_template"
                                            :src="`/storage/${template.back_template}`"
                                            alt="Back template"
                                            class="w-full h-full object-contain"
                                            preview
                                        />
                                        <div v-else class="text-center text-gray-400">
                                            <i class="pi pi-image text-4xl mb-2"></i>
                                            <p class="text-sm">No back design</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template #footer>
                            <div class="flex gap-2">
                                <Button
                                    v-if="!template.id"
                                    label="Create"
                                    icon="pi pi-plus"
                                    class="gradient-btn flex-1"
                                    @click="createTemplate(template.category)"
                                />
                                <template v-else>
                                    <Button
                                        label="Edit"
                                        icon="pi pi-pencil"
                                        severity="info"
                                        class="flex-1"
                                        @click="editTemplate(template.id)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        @click="deleteTemplate(template.id)"
                                    />
                                </template>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
