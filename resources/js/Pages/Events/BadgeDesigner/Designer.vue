<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import CustomButton from '@/Components/CustomButton.vue';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import ColorPicker from 'primevue/colorpicker';
import Dropdown from 'primevue/dropdown';
import Divider from 'primevue/divider';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import FileUpload from 'primevue/fileupload';
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
// @ts-ignore - qrcode types not available
import QRCode from 'qrcode';

interface BaseElement {
    id: string;
    label: string;
    x: number;  // in cm
    y: number;  // in cm
    visible: boolean;
}

interface TextZone extends BaseElement {
    type: 'text';
    field: string;
    fontSize: number;
    fontWeight: string;
    align: 'left' | 'center' | 'right';
    color: string;
    maxWidth?: number; // in cm
}

interface QRCodeZone extends BaseElement {
    type: 'qrcode';
    width: number;  // in cm
    height: number;  // in cm
}

interface LogoZone extends BaseElement {
    type: 'logo';
    width: number;  // in cm
    height: number;  // in cm
}

type DesignElement = TextZone | QRCodeZone | LogoZone;

interface BadgeLayout {
    elements: DesignElement[];
}

interface Props {
    event: any;
    template?: any;
    category: string;
}

const props = defineProps<Props>();

// Badge dimensions (industry standard)
const BADGE_WIDTH_CM = 8.5;
const BADGE_HEIGHT_CM = 12.5;
const PIXELS_PER_CM = 37.8; // Display scale for canvas

// Canvas state
const zoom = ref(1.5);
const showGrid = ref(true);

// Canvas dimensions (responsive to zoom)
const canvasWidth = computed(() => BADGE_WIDTH_CM * PIXELS_PER_CM * zoom.value);
const canvasHeight = computed(() => BADGE_HEIGHT_CM * PIXELS_PER_CM * zoom.value);

// Conversion helpers
const cmToPx = (cm: number) => cm * PIXELS_PER_CM * zoom.value;
const pxToCm = (px: number) => px / (PIXELS_PER_CM * zoom.value);

// Template upload and preview
const template = ref<File | null>(null);
const templatePreview = ref<string | null>(props.template?.front_template ? `/storage/${props.template.front_template}` : null);

// Load existing layout or create default
const defaultElements = (): DesignElement[] => [
    {
        id: 'event_name',
        type: 'text',
        label: 'Event Name',
        field: 'event.name',
        x: 4.25,
        y: 1.5,
        fontSize: 16,
        fontWeight: 'bold',
        align: 'center',
        color: '#1F2937',
        visible: true,
        maxWidth: 7
    } as TextZone,
    {
        id: 'logo',
        type: 'logo',
        label: 'Event Logo',
        x: 4.25,
        y: 2.5,
        width: 3,
        height: 1.5,
        visible: true
    } as LogoZone,
    {
        id: 'attendee_name',
        type: 'text',
        label: 'Attendee Name',
        field: 'attendee.name',
        x: 4.25,
        y: 5.5,
        fontSize: 28,
        fontWeight: 'bold',
        align: 'center',
        color: '#000000',
        visible: true,
        maxWidth: 7
    } as TextZone,
    {
        id: 'company',
        type: 'text',
        label: 'Company',
        field: 'attendee.company',
        x: 4.25,
        y: 7,
        fontSize: 18,
        fontWeight: 'normal',
        align: 'center',
        color: '#4B5563',
        visible: true,
        maxWidth: 7
    } as TextZone,
    {
        id: 'category',
        type: 'text',
        label: 'Category',
        field: 'attendee.category',
        x: 4.25,
        y: 8.2,
        fontSize: 14,
        fontWeight: 'bold',
        align: 'center',
        color: '#059669',
        visible: true,
        maxWidth: 4
    } as TextZone,
    {
        id: 'qr_code',
        type: 'qrcode',
        label: 'QR Code',
        x: 4.25,
        y: 9.5,
        width: 2.5,
        height: 2.5,
        visible: true
    } as QRCodeZone,
];

// Load elements from template or use defaults
const loadedElements = props.template?.elements || props.template?.front_layout?.elements || defaultElements();
console.log('Loading template:', props.template);
console.log('Loaded elements:', loadedElements);

const elements = ref<DesignElement[]>(loadedElements);

// Selection and dragging
const selectedElement = ref<DesignElement | null>(null);
const dragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });

// Form
const form = useForm({
    category: props.category,
    front_template: null as File | null,
    elements: elements.value,
    terms_and_conditions: props.template?.terms_and_conditions || '',
    badge_width_cm: BADGE_WIDTH_CM,
    badge_height_cm: BADGE_HEIGHT_CM,
    measurement_unit: 'cm',
    font_family: props.template?.font_family || 'Inter',
    primary_color: props.template?.primary_color || '#000000',
    secondary_color: props.template?.secondary_color || '#6B7280',
    show_qr_code: props.template?.show_qr_code ?? true,
    show_logo: props.template?.show_logo ?? true,
    show_category_badge: props.template?.show_category_badge ?? true,
    is_active: props.template?.is_active ?? true,
});

// Font options
const fontOptions = [
    { label: 'Inter', value: 'Inter' },
    { label: 'Roboto', value: 'Roboto' },
    { label: 'Montserrat', value: 'Montserrat' },
    { label: 'Open Sans', value: 'Open Sans' },
    { label: 'Lato', value: 'Lato' },
    { label: 'Poppins', value: 'Poppins' },
    { label: 'Arial', value: 'Arial' },
];

// Field mapping options
const fieldOptions = [
    { label: 'Event Name', value: 'event.name' },
    { label: 'Event Date', value: 'event.date' },
    { label: 'Event Location', value: 'event.location' },
    { label: 'Attendee Name', value: 'attendee.name' },
    { label: 'Company', value: 'attendee.company' },
    { label: 'Role', value: 'attendee.role' },
    { label: 'Category', value: 'attendee.category' },
    { label: 'Email', value: 'attendee.email' },
    { label: 'Phone', value: 'attendee.phone' },
    { label: 'QR UUID', value: 'attendee.qr_uuid' },
    { label: 'Custom Text', value: 'static:Your text here' },
];

// Template upload handler
const onTemplateSelect = (event: any) => {
    template.value = event.files[0];
    templatePreview.value = URL.createObjectURL(event.files[0]);
};

// Drag and drop handlers
const startDrag = (event: MouseEvent, element: DesignElement) => {
    selectedElement.value = element;
    dragging.value = true;

    const canvas = (event.currentTarget as HTMLElement).closest('.badge-canvas');
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();

    // For centered/right aligned text, adjust the drag offset
    let xOffset = cmToPx(element.x);
    if (element.type === 'text') {
        const textEl = element as TextZone;
        if (textEl.align === 'center') {
            xOffset = cmToPx(element.x) - (event.currentTarget as HTMLElement).offsetWidth / 2;
        } else if (textEl.align === 'right') {
            xOffset = cmToPx(element.x) - (event.currentTarget as HTMLElement).offsetWidth;
        }
    } else {
        // For QR and Logo, they are centered
        xOffset = cmToPx(element.x) - (event.currentTarget as HTMLElement).offsetWidth / 2;
    }

    dragStart.value = {
        x: event.clientX - rect.left - xOffset,
        y: event.clientY - rect.top - cmToPx(element.y),
    };
};

const onMouseMove = (event: MouseEvent) => {
    if (!dragging.value || !selectedElement.value) return;

    const canvas = event.currentTarget as HTMLElement;
    const rect = canvas.getBoundingClientRect();

    const newX = pxToCm(event.clientX - rect.left - dragStart.value.x);
    const newY = pxToCm(event.clientY - rect.top - dragStart.value.y);

    selectedElement.value.x = Math.max(0, Math.min(BADGE_WIDTH_CM, newX));
    selectedElement.value.y = Math.max(0, Math.min(BADGE_HEIGHT_CM, newY));
};

const stopDrag = () => {
    dragging.value = false;
};

// Add new element
const addElement = async (type: 'text' | 'qrcode' | 'logo') => {
    const baseElement = {
        id: `${type}_${Date.now()}`,
        label: `New ${type === 'qrcode' ? 'QR Code' : type === 'logo' ? 'Logo' : 'Text'}`,
        x: BADGE_WIDTH_CM / 2,
        y: BADGE_HEIGHT_CM / 2,
        visible: true,
    };

    let newElement: DesignElement;

    if (type === 'text') {
        newElement = {
            ...baseElement,
            type: 'text',
            field: 'static:Your text here',
            fontSize: 16,
            fontWeight: 'normal',
            align: 'center',
            color: '#000000',
            maxWidth: 6,
        } as TextZone;
    } else if (type === 'qrcode') {
        newElement = {
            ...baseElement,
            type: 'qrcode',
            width: 2.5,
            height: 2.5,
        } as QRCodeZone;

        // Generate QR code immediately for new element
        await generateQRCode(newElement.id, `QRMH-${props.event.name}-${getSampleText('attendee.qr_uuid')}`);
    } else {
        newElement = {
            ...baseElement,
            type: 'logo',
            width: 3,
            height: 1.5,
        } as LogoZone;
    }

    elements.value.push(newElement);
    selectedElement.value = newElement;
};

// Delete selected element
const deleteElement = () => {
    if (!selectedElement.value) return;

    const idx = elements.value.findIndex(e => e.id === selectedElement.value!.id);

    if (idx > -1) {
        elements.value.splice(idx, 1);
        selectedElement.value = null;
    }
};

// Get sample text for preview
const getSampleText = (field: string): string => {
    const samples: Record<string, string> = {
        'event.name': props.event.name,
        'event.date': '2025-01-15',
        'event.location': 'Dubai World Trade Centre',
        'attendee.name': 'John Doe',
        'attendee.company': 'Acme Corporation',
        'attendee.role': 'Speaker',
        'attendee.category': props.category.toUpperCase(),
        'attendee.email': 'john.doe@example.com',
        'attendee.phone': '+971 50 123 4567',
        'attendee.qr_uuid': 'ABC-123-XYZ',
    };

    if (field.startsWith('static:')) {
        return field.substring(7);
    }

    return samples[field] || 'Sample Text';
};

// QR Code data URLs
const qrCodeDataUrls = ref<Record<string, string>>({});

// Generate QR code for preview
const generateQRCode = async (elementId: string, data: string) => {
    try {
        console.log('Generating QR code for element:', elementId, 'with data:', data);
        const url = await QRCode.toDataURL(data, {
            width: 500,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });
        console.log('QR code generated successfully:', url.substring(0, 50) + '...');
        qrCodeDataUrls.value[elementId] = url;
        console.log('QR codes state:', Object.keys(qrCodeDataUrls.value));
    } catch (error) {
        console.error('Error generating QR code:', error);
    }
};

// Generate QR codes for all QR elements
const generateAllQRCodes = async () => {
    console.log('Generating all QR codes for', elements.value.length, 'elements');
    for (const element of elements.value) {
        if (element.type === 'qrcode') {
            console.log('Found QR element:', element.id);
            // Generate QR code with sample UUID
            await generateQRCode(element.id, `QRMH-${props.event.name}-${getSampleText('attendee.qr_uuid')}`);
        }
    }
    console.log('Finished generating QR codes');
};

// Generate QR codes on mount
onMounted(async () => {
    console.log('Component mounted, generating QR codes...');
    await generateAllQRCodes();
});

// Get category label
const getCategoryLabel = () => {
    const labels: Record<string, string> = {
        exhibitor: 'Exhibitor',
        guest: 'Guest',
        organizer: 'Organizer',
        visitor: 'Visitor',
    };
    return labels[props.category] || props.category;
};

// Save design
const save = () => {
    // Only update template file if a new one was selected
    if (template.value) {
        form.front_template = template.value;
    }

    // Convert elements to JSON string for form data submission
    // When using forceFormData, complex objects need to be stringified
    const elementsJson = JSON.stringify(elements.value);

    console.log('Saving elements:', elements.value);
    console.log('Elements JSON:', elementsJson);
    console.log('Template file:', template.value ? 'New file selected' : 'Using existing template');

    const route = props.template
        ? `/events/${props.event.id}/badge-designer/${props.template.id}`
        : `/events/${props.event.id}/badge-designer`;

    if (props.template) {
        form.transform(data => ({
            ...data,
            elements: elementsJson, // Send as JSON string
            // Only include front_template if a new file was uploaded
            front_template: template.value || undefined,
            _method: 'PUT'
        })).post(route, {
            forceFormData: true,
            onSuccess: () => {
                console.log('Template saved successfully');
                // Clear the template.value so next save doesn't re-upload
                template.value = null;
            },
            onError: (errors) => {
                console.error('Save errors:', errors);
            }
        });
    } else {
        form.transform(data => ({
            ...data,
            elements: elementsJson // Send as JSON string
        })).post(route, {
            forceFormData: true,
            onSuccess: () => {
                console.log('Template created successfully');
            },
            onError: (errors) => {
                console.error('Save errors:', errors);
            }
        });
    }
};
</script>

<template>
    <Head :title="`Visual Badge Designer - ${getCategoryLabel()}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-4">
                        <CustomButton
                            icon="pi-arrow-left"
                            severity="secondary"
                            @click="router.visit(`/events/${event.id}/badge-designer`)"
                        />
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Visual Badge Designer</h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ getCategoryLabel() }} Badge - {{ BADGE_WIDTH_CM }}cm × {{ BADGE_HEIGHT_CM }}cm
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <CustomButton
                            label="Cancel"
                            severity="secondary"
                            @click="router.visit(`/events/${event.id}/badge-designer`)"
                        />
                        <CustomButton
                            label="Save Design"
                            icon="pi-save"
                            severity="primary"
                            @click="save"
                            :disabled="form.processing"
                        />
                    </div>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-12 gap-6">
                    <!-- Left Panel: Templates & Elements -->
                    <div class="col-span-3 space-y-6">
                        <!-- Template Upload -->
                        <Card class="bg-white dark:bg-gray-800 rounded-xl shadow-md">
                            <template #title>
                                <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                                    <i class="pi pi-images"></i>
                                    <span>A4 Template</span>
                                </div>
                            </template>
                            <template #content>
                                <div class="space-y-3">
                                    <CustomButton
                                        label="Upload A4 Template"
                                        icon="pi-upload"
                                        severity="primary"
                                        class="w-full"
                                        @click="() => (($refs.templateUpload as any)?.$el?.querySelector('input[type=file]') as HTMLInputElement)?.click()"
                                    />
                                    <FileUpload
                                        ref="templateUpload"
                                        mode="basic"
                                        accept="image/*,application/pdf"
                                        :maxFileSize="5000000"
                                        @select="onTemplateSelect"
                                        :auto="false"
                                        class="hidden"
                                    />
                                    <div v-if="templatePreview" class="relative">
                                        <img :src="templatePreview" alt="Template" class="w-full rounded border shadow" />
                                        <CustomButton
                                            icon="pi-times"
                                            severity="danger"
                                            size="small"
                                            class="absolute top-2 right-2"
                                            @click="templatePreview = null; template = null"
                                        />
                                    </div>
                                    <small class="text-gray-500 block">
                                        Upload your ready-made A4 badge design (8.5cm × 12.5cm)
                                    </small>
                                </div>
                            </template>
                        </Card>

                        <!-- Add Elements -->
                        <Card class="bg-white dark:bg-gray-800 rounded-xl shadow-md">
                            <template #title>
                                <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                                    <i class="pi pi-plus-circle"></i>
                                    <span>Add Elements</span>
                                </div>
                            </template>
                            <template #content>
                                <div class="space-y-2">
                                    <CustomButton
                                        label="Add Text"
                                        icon="pi-font"
                                        class="w-full"
                                        severity="info"
                                        @click="addElement('text')"
                                    />
                                    <CustomButton
                                        label="Add QR Code"
                                        icon="pi-qrcode"
                                        class="w-full"
                                        severity="success"
                                        @click="addElement('qrcode')"
                                    />
                                    <CustomButton
                                        label="Add Logo"
                                        icon="pi-image"
                                        class="w-full"
                                        severity="warning"
                                        @click="addElement('logo')"
                                    />
                                </div>
                            </template>
                        </Card>

                        <!-- Elements List -->
                        <Card class="bg-white dark:bg-gray-800 rounded-xl shadow-md">
                            <template #title>
                                <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                                    <i class="pi pi-list"></i>
                                    <span>Elements ({{ elements.length }})</span>
                                </div>
                            </template>
                            <template #content>
                                <div class="space-y-2 max-h-[300px] overflow-y-auto">
                                    <div
                                        v-for="element in elements"
                                        :key="element.id"
                                        class="p-3 rounded cursor-pointer transition-all border"
                                        :class="{
                                            'bg-gray-100 border-gray-400 dark:bg-gray-600 dark:border-gray-500': selectedElement?.id === element.id,
                                            'bg-white border-gray-200 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 hover:dark:bg-gray-600': selectedElement?.id !== element.id
                                        }"
                                        @click="selectedElement = element"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <i
                                                        :class="{
                                                            'pi pi-font': element.type === 'text',
                                                            'pi pi-qrcode': element.type === 'qrcode',
                                                            'pi pi-image': element.type === 'logo'
                                                        }"
                                                        class="text-sm"
                                                    ></i>
                                                    <p class="font-medium text-sm">{{ element.label }}</p>
                                                </div>
                                                <p v-if="element.type === 'text'" class="text-xs text-gray-500 mt-1">
                                                    {{ getSampleText((element as TextZone).field) }}
                                                </p>
                                            </div>
                                            <i
                                                class="cursor-pointer ml-2"
                                                :class="{
                                                    'pi pi-eye text-green-600': element.visible,
                                                    'pi pi-eye-slash text-gray-400': !element.visible
                                                }"
                                                @click.stop="element.visible = !element.visible"
                                            ></i>
                                        </div>
                                    </div>
                                    <div v-if="elements.length === 0" class="text-center py-8 text-gray-400">
                                        <i class="pi pi-inbox text-3xl mb-2 block"></i>
                                        <p class="text-sm">No elements yet. Add some!</p>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <!-- Center Panel: Canvas -->
                    <div class="col-span-6">
                        <Card class="bg-white dark:bg-gray-800 rounded-xl shadow-md">
                            <template #title>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-gray-900 dark:text-white">Badge Canvas</span>
                                        <span class="text-gray-400">|</span>
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-search-minus cursor-pointer text-lg" @click="zoom = Math.max(0.5, zoom - 0.25)"></i>
                                            <span class="text-sm min-w-[60px] text-center font-medium">{{ Math.round(zoom * 100) }}%</span>
                                            <i class="pi pi-search-plus cursor-pointer text-lg" @click="zoom = Math.min(3, zoom + 0.25)"></i>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        <CustomButton
                                            icon="pi-refresh"
                                            label="Reload QR"
                                            size="small"
                                            severity="secondary"
                                            @click="generateAllQRCodes"
                                            title="Regenerate QR codes"
                                        />
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <Checkbox v-model="showGrid" :binary="true" inputId="grid" />
                                            <span>Grid</span>
                                        </label>
                                    </div>
                                </div>
                            </template>
                            <template #content>
                                <div class="flex justify-center items-center bg-gray-50 dark:bg-gray-900 rounded-lg p-8 min-h-[900px] overflow-auto">
                                    <div
                                        class="badge-canvas relative bg-white shadow-2xl rounded"
                                        :style="{
                                            width: canvasWidth + 'px',
                                            height: canvasHeight + 'px',
                                            backgroundImage: showGrid ? 'linear-gradient(rgba(0,0,0,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.08) 1px, transparent 1px)' : 'none',
                                            backgroundSize: showGrid ? `${cmToPx(0.5)}px ${cmToPx(0.5)}px` : 'auto'
                                        }"
                                        @mousemove="onMouseMove"
                                        @mouseup="stopDrag"
                                        @mouseleave="stopDrag"
                                    >
                                        <!-- Background Template -->
                                        <img
                                            v-if="templatePreview"
                                            :src="templatePreview"
                                            alt="Template"
                                            class="absolute inset-0 w-full h-full object-cover pointer-events-none rounded"
                                        />

                                        <!-- Elements -->
                                        <div
                                            v-for="element in elements.filter(e => e.visible)"
                                            :key="element.id"
                                            class="absolute cursor-move transition-all"
                                            :class="{
                                                'ring-2 ring-gray-500 ring-offset-2 dark:ring-gray-400': selectedElement?.id === element.id,
                                                'hover:ring-1 hover:ring-gray-300 dark:hover:ring-gray-500': selectedElement?.id !== element.id
                                            }"
                                            :style="{
                                                left: element.type === 'text' && (element as TextZone).align === 'center' ? cmToPx(element.x) + 'px' :
                                                      element.type === 'text' && (element as TextZone).align === 'right' ? cmToPx(element.x) + 'px' :
                                                      element.type !== 'text' ? (cmToPx(element.x) - cmToPx((element as QRCodeZone | LogoZone).width) / 2) + 'px' :
                                                      cmToPx(element.x) + 'px',
                                                top: element.type !== 'text' ? (cmToPx(element.y) - cmToPx((element as QRCodeZone | LogoZone).height) / 2) + 'px' : cmToPx(element.y) + 'px',
                                                transform: element.type === 'text' && (element as TextZone).align === 'center' ? 'translateX(-50%)' :
                                                          element.type === 'text' && (element as TextZone).align === 'right' ? 'translateX(-100%)' : 'none',
                                                fontSize: element.type === 'text' ? ((element as TextZone).fontSize * zoom) + 'px' : undefined,
                                                fontWeight: element.type === 'text' ? (element as TextZone).fontWeight : undefined,
                                                color: element.type === 'text' ? (element as TextZone).color : undefined,
                                                textAlign: element.type === 'text' ? (element as TextZone).align : undefined,
                                                fontFamily: element.type === 'text' ? form.font_family : undefined,
                                                maxWidth: element.type === 'text' && (element as TextZone).maxWidth ? cmToPx((element as TextZone).maxWidth!) + 'px' : 'none',
                                                padding: '4px 8px',
                                                borderRadius: '4px',
                                                width: element.type !== 'text' ? cmToPx((element as QRCodeZone | LogoZone).width) + 'px' : 'auto',
                                                height: element.type !== 'text' ? cmToPx((element as QRCodeZone | LogoZone).height) + 'px' : 'auto'
                                            }"
                                            @mousedown="startDrag($event, element)"
                                            @click="selectedElement = element"
                                        >
                                            <!-- Text Element -->
                                            <template v-if="element.type === 'text'">
                                                {{ getSampleText((element as TextZone).field) }}
                                            </template>

                                            <!-- QR Code Element -->
                                            <div v-else-if="element.type === 'qrcode'" class="w-full h-full bg-white flex items-center justify-center rounded overflow-hidden">
                                                <img
                                                    v-if="qrCodeDataUrls[element.id]"
                                                    :src="qrCodeDataUrls[element.id]"
                                                    alt="QR Code"
                                                    class="w-full h-full object-contain"
                                                />
                                                <div v-else class="text-center">
                                                    <i class="pi pi-spin pi-spinner text-gray-400 text-2xl"></i>
                                                    <p class="text-xs text-gray-500 mt-1">Loading QR...</p>
                                                </div>
                                            </div>

                                            <!-- Logo Element -->
                                            <div v-else-if="element.type === 'logo'" class="w-full h-full bg-gray-50 border-2 border-dashed border-gray-400 flex items-center justify-center rounded">
                                                <div class="text-center">
                                                    <i class="pi pi-image text-gray-400" :style="{ fontSize: Math.min(cmToPx(0.8), 40) + 'px' }"></i>
                                                    <p class="text-xs text-gray-500 mt-1">Logo</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Center guides -->
                                        <div v-if="showGrid" class="absolute inset-0 pointer-events-none">
                                            <div class="absolute left-1/2 top-0 bottom-0 w-px bg-gray-400 dark:bg-gray-500 opacity-40"></div>
                                            <div class="absolute top-1/2 left-0 right-0 h-px bg-gray-400 dark:bg-gray-500 opacity-40"></div>
                                        </div>

                                        <!-- No template placeholder -->
                                        <div
                                            v-if="!templatePreview"
                                            class="absolute inset-0 flex items-center justify-center text-gray-400 pointer-events-none"
                                        >
                                            <div class="text-center">
                                                <i class="pi pi-image text-6xl mb-4 block"></i>
                                                <p class="text-lg font-medium">Upload your A4 template to get started</p>
                                                <p class="text-sm mt-2">Use the A4 Template panel on the left</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <!-- Right Panel: Properties -->
                    <div class="col-span-3">
                        <Card class="sticky top-6 bg-white dark:bg-gray-800 shadow-md">
                            <template #title>
                                <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                                    <i class="pi pi-sliders-h"></i>
                                    <span>Properties</span>
                                </div>
                            </template>
                            <template #content>
                                <div v-if="selectedElement" class="space-y-4">
                                    <!-- Common Properties -->
                                    <div>
                                        <label class="text-sm font-medium">Label</label>
                                        <InputText v-model="selectedElement.label" class="w-full mt-1" />
                                    </div>

                                    <!-- Text Specific Properties -->
                                    <template v-if="selectedElement.type === 'text'">
                                        <div>
                                            <label class="text-sm font-medium">Field Mapping</label>
                                            <Dropdown
                                                v-model="(selectedElement as TextZone).field"
                                                :options="fieldOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                class="w-full mt-1"
                                            />
                                            <small class="text-gray-500">What data to display</small>
                                        </div>

                                        <div v-if="(selectedElement as TextZone).field.startsWith('static:')">
                                            <label class="text-sm font-medium">Custom Text</label>
                                            <Textarea
                                                :modelValue="(selectedElement as TextZone).field.substring(7)"
                                                @update:modelValue="(selectedElement as TextZone).field = 'static:' + $event"
                                                rows="3"
                                                class="w-full mt-1"
                                            />
                                        </div>

                                        <Divider />

                                        <div>
                                            <label class="text-sm font-medium">Font Size</label>
                                            <div class="flex gap-2 mt-1">
                                                <InputNumber
                                                    v-model="(selectedElement as TextZone).fontSize"
                                                    :min="8"
                                                    :max="72"
                                                    class="flex-1"
                                                    showButtons
                                                />
                                                <span class="text-gray-500 text-sm self-center">px</span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium">Font Weight</label>
                                            <Dropdown
                                                v-model="(selectedElement as TextZone).fontWeight"
                                                :options="[
                                                    { label: 'Light', value: '300' },
                                                    { label: 'Normal', value: 'normal' },
                                                    { label: 'Semi-Bold', value: '600' },
                                                    { label: 'Bold', value: 'bold' }
                                                ]"
                                                optionLabel="label"
                                                optionValue="value"
                                                class="w-full mt-1"
                                            />
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium">Alignment</label>
                                            <div class="flex gap-2 mt-1">
                                                <CustomButton
                                                    icon="pi-align-left"
                                                    :severity="(selectedElement as TextZone).align === 'left' ? 'primary' : 'secondary'"
                                                    @click="(selectedElement as TextZone).align = 'left'"
                                                    class="flex-1"
                                                />
                                                <CustomButton
                                                    icon="pi-align-center"
                                                    :severity="(selectedElement as TextZone).align === 'center' ? 'primary' : 'secondary'"
                                                    @click="(selectedElement as TextZone).align = 'center'"
                                                    class="flex-1"
                                                />
                                                <CustomButton
                                                    icon="pi-align-right"
                                                    :severity="(selectedElement as TextZone).align === 'right' ? 'primary' : 'secondary'"
                                                    @click="(selectedElement as TextZone).align = 'right'"
                                                    class="flex-1"
                                                />
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium">Color</label>
                                            <div class="flex gap-2 mt-1">
                                                <ColorPicker v-model="(selectedElement as TextZone).color" class="flex-shrink-0" />
                                                <InputText v-model="(selectedElement as TextZone).color" class="flex-1" />
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium">Max Width (cm)</label>
                                            <InputNumber
                                                v-model="(selectedElement as TextZone).maxWidth"
                                                :step="0.5"
                                                :minFractionDigits="1"
                                                :min="1"
                                                :max="BADGE_WIDTH_CM"
                                                class="w-full mt-1"
                                                showButtons
                                            />
                                        </div>
                                    </template>

                                    <!-- QR Code / Logo Specific Properties -->
                                    <template v-if="selectedElement.type === 'qrcode' || selectedElement.type === 'logo'">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-sm font-medium">Width (cm)</label>
                                                <InputNumber
                                                    v-model="(selectedElement as QRCodeZone | LogoZone).width"
                                                    :step="0.1"
                                                    :minFractionDigits="1"
                                                    :min="0.5"
                                                    :max="BADGE_WIDTH_CM"
                                                    class="w-full mt-1"
                                                    showButtons
                                                />
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium">Height (cm)</label>
                                                <InputNumber
                                                    v-model="(selectedElement as QRCodeZone | LogoZone).height"
                                                    :step="0.1"
                                                    :minFractionDigits="1"
                                                    :min="0.5"
                                                    :max="BADGE_HEIGHT_CM"
                                                    class="w-full mt-1"
                                                    showButtons
                                                />
                                            </div>
                                        </div>
                                    </template>

                                    <Divider />

                                    <!-- Position -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-sm font-medium">X (cm)</label>
                                            <InputNumber
                                                v-model="selectedElement.x"
                                                :step="0.1"
                                                :minFractionDigits="1"
                                                :maxFractionDigits="2"
                                                :min="0"
                                                :max="BADGE_WIDTH_CM"
                                                class="w-full mt-1"
                                                showButtons
                                            />
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium">Y (cm)</label>
                                            <InputNumber
                                                v-model="selectedElement.y"
                                                :step="0.1"
                                                :minFractionDigits="1"
                                                :maxFractionDigits="2"
                                                :min="0"
                                                :max="BADGE_HEIGHT_CM"
                                                class="w-full mt-1"
                                                showButtons
                                            />
                                        </div>
                                    </div>

                                    <Divider />

                                    <CustomButton
                                        label="Delete Element"
                                        icon="pi-trash"
                                        severity="danger"
                                        class="w-full"
                                        @click="deleteElement"
                                    />
                                </div>
                                <div v-else class="text-center py-12 text-gray-400">
                                    <i class="pi pi-hand-pointer text-4xl mb-3 block"></i>
                                    <p class="text-sm">Select an element to edit its properties</p>
                                    <p class="text-xs mt-2">Click on any element in the canvas</p>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.badge-canvas {
    user-select: none;
}
</style>
