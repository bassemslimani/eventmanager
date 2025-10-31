<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import CustomButton from '@/Components/CustomButton.vue';
import FileUpload from 'primevue/fileupload';
import ColorPicker from 'primevue/colorpicker';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import { ref } from 'vue';

const form = useForm({
    type: 'exhibitor',
    name: '',
    background_image: null as File | null,
    overlay_color: '#000000',
    overlay_opacity: 50,
    glass_effect: false,
    gradient_direction: 'to-br',
    font_family: 'Inter',
    is_active: true,
});

const typeOptions = [
    { label: 'Exhibitor', value: 'exhibitor' },
    { label: 'Guest', value: 'guest' },
    { label: 'Organizer', value: 'organizer' },
];

const gradientOptions = [
    { label: 'Top to Bottom', value: 'to-b' },
    { label: 'Bottom to Top', value: 'to-t' },
    { label: 'Left to Right', value: 'to-r' },
    { label: 'Right to Left', value: 'to-l' },
    { label: 'Top Right', value: 'to-tr' },
    { label: 'Top Left', value: 'to-tl' },
    { label: 'Bottom Right', value: 'to-br' },
    { label: 'Bottom Left', value: 'to-bl' },
];

const fontOptions = [
    { label: 'Inter', value: 'Inter' },
    { label: 'Roboto', value: 'Roboto' },
    { label: 'Arial', value: 'Arial' },
    { label: 'Helvetica', value: 'Helvetica' },
];

const onFileSelect = (event: any) => {
    form.background_image = event.files[0];
};

const submit = () => {
    form.post('/badge-templates');
};
</script>

<template>
    <Head title="Create Badge Template" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gradient mb-2">Create Badge Template</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Design a new badge template with custom background
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="glass-card p-6 space-y-6">
                    <!-- Name & Type -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Template Name *</label>
                            <InputText
                                v-model="form.name"
                                placeholder="e.g., Visitor Exhibitor Badge"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.name }"
                            />
                            <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Attendee Type *</label>
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
                    </div>

                    <!-- Background Image -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Background Image</label>
                        <FileUpload
                            mode="basic"
                            accept="image/*"
                            :maxFileSize="2000000"
                            @select="onFileSelect"
                            :auto="false"
                            chooseLabel="Choose Background Image"
                            class="w-full"
                        />
                        <small class="text-gray-500">Upload a custom background image for the badge (max 2MB)</small>
                    </div>

                    <!-- Overlay Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Overlay Color</label>
                            <div class="flex gap-2 items-center">
                                <ColorPicker v-model="form.overlay_color" format="hex" />
                                <InputText
                                    v-model="form.overlay_color"
                                    placeholder="#000000"
                                    class="flex-1"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Overlay Opacity (%)</label>
                            <InputNumber
                                v-model="form.overlay_opacity"
                                :min="0"
                                :max="100"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <!-- Design Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Gradient Direction</label>
                            <Dropdown
                                v-model="form.gradient_direction"
                                :options="gradientOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Select direction"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Font Family</label>
                            <Dropdown
                                v-model="form.font_family"
                                :options="fontOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Select font"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="flex gap-6">
                        <div class="flex items-center gap-2">
                            <Checkbox v-model="form.glass_effect" :binary="true" inputId="glass_effect" />
                            <label for="glass_effect" class="text-sm font-medium">Enable Glass Effect</label>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox v-model="form.is_active" :binary="true" inputId="is_active" />
                            <label for="is_active" class="text-sm font-medium">Set as Active</label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-end">
                        <CustomButton
                            label="Cancel"
                            severity="secondary"
                            @click="$inertia.visit('/badge-templates')"
                            type="button"
                        />
                        <CustomButton
                            label="Create Template"
                            icon="pi-check"
                            severity="primary"
                            type="submit"
                            :disabled="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
