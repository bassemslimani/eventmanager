<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Image from 'primevue/image';

interface BadgeTemplate {
    id: number;
    type: string;
    name: string;
    background_image: string | null;
    is_active: boolean;
}

interface Props {
    templates: BadgeTemplate[];
}

const props = defineProps<Props>();

const getTypeSeverity = (type: string) => {
    const severities: Record<string, string> = {
        exhibitor: 'success',
        guest: 'info',
        organizer: 'warn',
    };
    return severities[type] || 'secondary';
};

const deleteTemplate = (id: number) => {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(`/badge-templates/${id}`);
    }
};

const toggleActive = (id: number) => {
    router.post(`/badge-templates/${id}/toggle-active`);
};
</script>

<template>
    <Head title="Badge Templates" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gradient mb-2">Badge Templates</h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Manage badge designs and backgrounds
                        </p>
                    </div>
                    <Button
                        label="Create Template"
                        icon="pi pi-plus"
                        class="gradient-btn"
                        @click="router.visit('/badge-templates/create')"
                    />
                </div>

                <!-- Data Table -->
                <div class="glass-card overflow-hidden">
                    <DataTable
                        :value="templates"
                        stripedRows
                        class="custom-datatable"
                    >
                        <Column field="id" header="ID" sortable style="width: 80px" />

                        <Column field="name" header="Name" sortable />

                        <Column field="type" header="Type" sortable>
                            <template #body="slotProps">
                                <Tag
                                    :value="slotProps.data.type"
                                    :severity="getTypeSeverity(slotProps.data.type)"
                                />
                            </template>
                        </Column>

                        <Column field="background_image" header="Background">
                            <template #body="slotProps">
                                <Image
                                    v-if="slotProps.data.background_image"
                                    :src="`/storage/${slotProps.data.background_image}`"
                                    alt="Badge background"
                                    width="80"
                                    preview
                                />
                                <span v-else class="text-gray-400">No image</span>
                            </template>
                        </Column>

                        <Column field="is_active" header="Status" sortable>
                            <template #body="slotProps">
                                <Tag
                                    :value="slotProps.data.is_active ? 'Active' : 'Inactive'"
                                    :severity="slotProps.data.is_active ? 'success' : 'secondary'"
                                />
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 250px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="info"
                                        size="small"
                                        @click="router.visit(`/badge-templates/${slotProps.data.id}/edit`)"
                                    />
                                    <Button
                                        :icon="slotProps.data.is_active ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                        :severity="slotProps.data.is_active ? 'warning' : 'success'"
                                        size="small"
                                        @click="toggleActive(slotProps.data.id)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        size="small"
                                        @click="deleteTemplate(slotProps.data.id)"
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
