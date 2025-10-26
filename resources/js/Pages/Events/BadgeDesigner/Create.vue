<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import ColorPicker from 'primevue/colorpicker';
import Checkbox from 'primevue/checkbox';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { ref } from 'vue';

interface Event {
    id: number;
    name: string;
}

interface Props {
    event: Event;
    category: string;
}

const props = defineProps<Props>();

const form = useForm({
    category: props.category,
    front_template: null as File | null,
    back_template: null as File | null,
    terms_and_conditions: '',
    badge_size: 'standard',
    badge_width: 400,
    badge_height: 600,
    font_family: 'Inter',
    primary_color: '#000000',
    secondary_color: '#666666',
    show_qr_code: true,
    show_logo: true,
    show_category_badge: true,
    is_active: true,
});

const frontPreview = ref<string | null>(null);
const backPreview = ref<string | null>(null);

const badgeSizeOptions = [
    { label: 'Standard (400x600px)', value: 'standard' },
    { label: 'Large (500x750px)', value: 'large' },
    { label: 'Small (300x450px)', value: 'small' },
    { label: 'Custom', value: 'custom' },
];

const fontOptions = [
    { label: 'Inter', value: 'Inter' },
    { label: 'Roboto', value: 'Roboto' },
    { label: 'Montserrat', value: 'Montserrat' },
    { label: 'Open Sans', value: 'Open Sans' },
    { label: 'Lato', value: 'Lato' },
    { label: 'Arial', value: 'Arial' },
];

const onFrontTemplateSelect = (event: any) => {
    form.front_template = event.files[0];
    frontPreview.value = URL.createObjectURL(event.files[0]);
};

const onBackTemplateSelect = (event: any) => {
    form.back_template = event.files[0];
    backPreview.value = URL.createObjectURL(event.files[0]);
};

const submit = () => {
    form.post(`/events/${props.event.id}/badge-designer`, {
        forceFormData: true,
        preserveScroll: true,
    });
};

const getCategoryLabel = () => {
    const labels: Record<string, string> = {
        exhibitor: 'Exhibitor',
        guest: 'Guest',
        organizer: 'Organizer',
        vip: 'VIP',
    };
    return labels[props.category] || props.category;
};
</script>

<template>
    <Head :title="`Create ${getCategoryLabel()} Badge - ${event.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <Button
                        label="Back to Badge Designer"
                        icon="pi pi-arrow-left"
                        severity="secondary"
                        text
                        @click="router.visit(`/events/${event.id}/badge-designer`)"
                        class="mb-3"
                    />
                    <h1 class="text-3xl font-bold text-gradient mb-2">
                        Create {{ getCategoryLabel() }} Badge Template
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Design the perfect badge for {{ getCategoryLabel().toLowerCase() }} attendees
                    </p>
                </div>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column: Templates & Preview -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Template Upload Section -->
                            <Card class="glass-card">
                                <template #header>
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="text-xl font-bold flex items-center gap-2">
                                            <i class="pi pi-images"></i>
                                            Badge Templates
                                        </h3>
                                    </div>
                                </template>

                                <template #content>
                                    <TabView>
                                        <TabPanel header="Front Design">
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium mb-2">
                                                        Upload Front Template
                                                    </label>
                                                    <FileUpload
                                                        mode="basic"
                                                        accept="image/*"
                                                        :maxFileSize="5000000"
                                                        @select="onFrontTemplateSelect"
                                                        :auto="false"
                                                        chooseLabel="Choose Front Image"
                                                        class="w-full"
                                                    />
                                                    <small class="text-gray-500">Recommended: 400x600px PNG or JPG (max 5MB)</small>
                                                </div>

                                                <!-- Front Preview -->
                                                <div v-if="frontPreview" class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4">
                                                    <p class="text-sm font-medium mb-2">Preview</p>
                                                    <img :src="frontPreview" alt="Front preview" class="w-full rounded-lg shadow-lg" />
                                                </div>
                                            </div>
                                        </TabPanel>

                                        <TabPanel header="Back Design">
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium mb-2">
                                                        Upload Back Template
                                                    </label>
                                                    <FileUpload
                                                        mode="basic"
                                                        accept="image/*"
                                                        :maxFileSize="5000000"
                                                        @select="onBackTemplateSelect"
                                                        :auto="false"
                                                        chooseLabel="Choose Back Image"
                                                        class="w-full"
                                                    />
                                                    <small class="text-gray-500">Recommended: 400x600px PNG or JPG (max 5MB)</small>
                                                </div>

                                                <!-- Back Preview -->
                                                <div v-if="backPreview" class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4">
                                                    <p class="text-sm font-medium mb-2">Preview</p>
                                                    <img :src="backPreview" alt="Back preview" class="w-full rounded-lg shadow-lg" />
                                                </div>
                                            </div>
                                        </TabPanel>
                                    </TabView>
                                </template>
                            </Card>

                            <!-- Terms and Conditions -->
                            <Card class="glass-card">
                                <template #header>
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="text-xl font-bold flex items-center gap-2">
                                            <i class="pi pi-file-edit"></i>
                                            Terms & Conditions
                                        </h3>
                                    </div>
                                </template>

                                <template #content>
                                    <div>
                                        <label class="block text-sm font-medium mb-2">
                                            Terms to Print on Badge (Optional)
                                        </label>
                                        <Textarea
                                            v-model="form.terms_and_conditions"
                                            rows="5"
                                            class="w-full"
                                            placeholder="Enter terms and conditions, disclaimers, or notes to be printed on the badge back..."
                                        />
                                        <small class="text-gray-500">
                                            Keep it concise. Long text may be truncated to fit the badge.
                                        </small>
                                    </div>
                                </template>
                            </Card>
                        </div>

                        <!-- Right Column: Settings -->
                        <div class="space-y-6">
                            <!-- Badge Dimensions -->
                            <Card class="glass-card">
                                <template #header>
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="font-bold flex items-center gap-2">
                                            <i class="pi pi-arrows-alt"></i>
                                            Dimensions
                                        </h3>
                                    </div>
                                </template>

                                <template #content>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-2">Size Preset</label>
                                            <Dropdown
                                                v-model="form.badge_size"
                                                :options="badgeSizeOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                class="w-full"
                                            />
                                        </div>

                                        <div v-if="form.badge_size === 'custom'" class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-2">Width (px)</label>
                                                <InputNumber v-model="form.badge_width" :min="100" :max="1000" class="w-full" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-2">Height (px)</label>
                                                <InputNumber v-model="form.badge_height" :min="100" :max="1500" class="w-full" />
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>

                            <!-- Visual Settings -->
                            <Card class="glass-card">
                                <template #header>
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="font-bold flex items-center gap-2">
                                            <i class="pi pi-palette"></i>
                                            Visual Settings
                                        </h3>
                                    </div>
                                </template>

                                <template #content>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-2">Font Family</label>
                                            <Dropdown
                                                v-model="form.font_family"
                                                :options="fontOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                class="w-full"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-2">Primary Color</label>
                                            <div class="flex gap-2 items-center">
                                                <ColorPicker v-model="form.primary_color" format="hex" />
                                                <InputText v-model="form.primary_color" class="flex-1" />
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-2">Secondary Color</label>
                                            <div class="flex gap-2 items-center">
                                                <ColorPicker v-model="form.secondary_color" format="hex" />
                                                <InputText v-model="form.secondary_color" class="flex-1" />
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>

                            <!-- Display Options -->
                            <Card class="glass-card">
                                <template #header>
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="font-bold flex items-center gap-2">
                                            <i class="pi pi-eye"></i>
                                            Display Options
                                        </h3>
                                    </div>
                                </template>

                                <template #content>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <Checkbox v-model="form.show_qr_code" :binary="true" inputId="show_qr" />
                                            <label for="show_qr" class="text-sm font-medium">Show QR Code</label>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <Checkbox v-model="form.show_logo" :binary="true" inputId="show_logo" />
                                            <label for="show_logo" class="text-sm font-medium">Show Event Logo</label>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <Checkbox v-model="form.show_category_badge" :binary="true" inputId="show_cat" />
                                            <label for="show_cat" class="text-sm font-medium">Show Category Badge</label>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <Checkbox v-model="form.is_active" :binary="true" inputId="is_active" />
                                            <label for="is_active" class="text-sm font-medium">Set as Active</label>
                                        </div>
                                    </div>
                                </template>
                            </Card>

                            <!-- Actions -->
                            <div class="flex flex-col gap-3">
                                <Button
                                    label="Create Badge Template"
                                    icon="pi pi-check"
                                    class="gradient-btn w-full"
                                    type="submit"
                                    :loading="form.processing"
                                />
                                <Button
                                    label="Cancel"
                                    severity="secondary"
                                    @click="router.visit(`/events/${event.id}/badge-designer`)"
                                    type="button"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
