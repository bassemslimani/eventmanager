<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">System Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success/Error Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Tabs -->
                <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex" aria-label="Tabs">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                type="button"
                                @click="activeTab = tab.id"
                                :class="activeTab === tab.id ? 'tab-active' : 'tab-inactive'"
                            >
                                {{ tab.name }}
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Mail Settings -->
                <div v-show="activeTab === 'mail'" class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Email Configuration</h3>
                    <form @submit.prevent="updateMailSettings">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mail Driver</label>
                                <select v-model="mailForm.mail_mailer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="smtp">SMTP</option>
                                    <option value="sendmail">Sendmail</option>
                                    <option value="mailgun">Mailgun</option>
                                    <option value="ses">Amazon SES</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mail Host</label>
                                <input v-model="mailForm.mail_host" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="smtp.mailtrap.io" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mail Port</label>
                                <input v-model="mailForm.mail_port" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="2525" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Encryption</label>
                                <select v-model="mailForm.mail_encryption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="">None</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mail Username</label>
                                <input v-model="mailForm.mail_username" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mail Password</label>
                                <input v-model="mailForm.mail_password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">From Address</label>
                                <input v-model="mailForm.mail_from_address" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="noreply@example.com" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">From Name</label>
                                <input v-model="mailForm.mail_from_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Creative Hub" />
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                                Save Mail Settings
                            </button>
                            <button type="button" @click="testEmail" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                                Send Test Email
                            </button>
                        </div>
                    </form>

                    <!-- Test Email Modal -->
                    <div v-if="showTestEmailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-96">
                            <h3 class="text-lg font-medium mb-4">Send Test Email</h3>
                            <input v-model="testEmailAddress" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mb-4" placeholder="Enter email address" />
                            <div class="flex justify-end gap-2">
                                <button @click="showTestEmailModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">Cancel</button>
                                <button @click="sendTestEmail" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Send</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Branding Settings -->
                <div v-show="activeTab === 'branding'" class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Branding & Logo</h3>
                    <form @submit.prevent="updateBrandingSettings">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Application Name</label>
                                <input v-model="brandingForm.app_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Brand Color</label>
                                <div class="flex gap-2">
                                    <input v-model="brandingForm.brand_color" type="color" class="h-10 w-20 rounded border-gray-300" />
                                    <input v-model="brandingForm.brand_color" type="text" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Application Logo</label>
                                <input type="file" @change="handleLogoUpload" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p class="mt-1 text-sm text-gray-500">Recommended size: 150x150px. Max 2MB.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Favicon</label>
                                <input type="file" @change="handleFaviconUpload" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p class="mt-1 text-sm text-gray-500">Recommended size: 32x32px. Max 1MB.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">PWA Icon (Mobile App Icon)</label>
                                <input type="file" @change="handlePwaIconUpload" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p class="mt-1 text-sm text-gray-500">Recommended size: 192x192px. Max 2MB. Used for mobile home screen icon.</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                                Save Branding Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- SEO Settings -->
                <div v-show="activeTab === 'seo'" class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">SEO Configuration</h3>
                    <form @submit.prevent="updateSeoSettings">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">SEO Title</label>
                                <input v-model="seoForm.seo_title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <p class="mt-1 text-sm text-gray-500">Recommended: 50-60 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">SEO Description</label>
                                <textarea v-model="seoForm.seo_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                <p class="mt-1 text-sm text-gray-500">Recommended: 150-160 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">SEO Keywords</label>
                                <textarea v-model="seoForm.seo_keywords" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                <p class="mt-1 text-sm text-gray-500">Comma-separated keywords</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">SEO Image</label>
                                <input type="file" @change="handleSeoImageUpload" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p class="mt-1 text-sm text-gray-500">Recommended size: 1200x630px. Max 2MB.</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                                Save SEO Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- General Settings -->
                <div v-show="activeTab === 'general'" class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">General Configuration</h3>
                    <form @submit.prevent="updateGeneralSettings">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-700">Send Welcome Email on Attendee Registration</label>
                                    <p class="text-sm text-gray-500 mt-1">Automatically send a welcome email when a new attendee is created</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="generalForm.send_welcome_email" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Timezone</label>
                                <select v-model="generalForm.timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <optgroup label="UTC">
                                        <option value="UTC">UTC (Coordinated Universal Time)</option>
                                    </optgroup>
                                    <optgroup label="Middle East & Gulf">
                                        <option value="Asia/Qatar">Asia/Qatar (GMT+3) - Doha</option>
                                        <option value="Asia/Dubai">Asia/Dubai (GMT+4) - UAE</option>
                                        <option value="Asia/Kuwait">Asia/Kuwait (GMT+3) - Kuwait</option>
                                        <option value="Asia/Bahrain">Asia/Bahrain (GMT+3) - Bahrain</option>
                                        <option value="Asia/Riyadh">Asia/Riyadh (GMT+3) - Saudi Arabia</option>
                                        <option value="Asia/Muscat">Asia/Muscat (GMT+4) - Oman</option>
                                        <option value="Asia/Jerusalem">Asia/Jerusalem (GMT+2) - Israel</option>
                                        <option value="Asia/Beirut">Asia/Beirut (GMT+2) - Lebanon</option>
                                    </optgroup>
                                    <optgroup label="North America">
                                        <option value="America/New_York">America/New York (EST/EDT)</option>
                                        <option value="America/Chicago">America/Chicago (CST/CDT)</option>
                                        <option value="America/Denver">America/Denver (MST/MDT)</option>
                                        <option value="America/Los_Angeles">America/Los Angeles (PST/PDT)</option>
                                        <option value="America/Toronto">America/Toronto (EST/EDT) - Canada</option>
                                        <option value="America/Vancouver">America/Vancouver (PST/PDT) - Canada</option>
                                        <option value="America/Mexico_City">America/Mexico City (CST/CDT)</option>
                                    </optgroup>
                                    <optgroup label="Europe">
                                        <option value="Europe/London">Europe/London (GMT/BST) - UK</option>
                                        <option value="Europe/Paris">Europe/Paris (CET/CEST) - France</option>
                                        <option value="Europe/Berlin">Europe/Berlin (CET/CEST) - Germany</option>
                                        <option value="Europe/Rome">Europe/Rome (CET/CEST) - Italy</option>
                                        <option value="Europe/Madrid">Europe/Madrid (CET/CEST) - Spain</option>
                                        <option value="Europe/Amsterdam">Europe/Amsterdam (CET/CEST) - Netherlands</option>
                                        <option value="Europe/Brussels">Europe/Brussels (CET/CEST) - Belgium</option>
                                        <option value="Europe/Zurich">Europe/Zurich (CET/CEST) - Switzerland</option>
                                        <option value="Europe/Stockholm">Europe/Stockholm (CET/CEST) - Sweden</option>
                                        <option value="Europe/Athens">Europe/Athens (EET/EEST) - Greece</option>
                                        <option value="Europe/Istanbul">Europe/Istanbul (TRT) - Turkey</option>
                                        <option value="Europe/Moscow">Europe/Moscow (MSK) - Russia</option>
                                    </optgroup>
                                    <optgroup label="Asia">
                                        <option value="Asia/Tokyo">Asia/Tokyo (JST) - Japan</option>
                                        <option value="Asia/Seoul">Asia/Seoul (KST) - South Korea</option>
                                        <option value="Asia/Shanghai">Asia/Shanghai (CST) - China</option>
                                        <option value="Asia/Hong_Kong">Asia/Hong Kong (HKT)</option>
                                        <option value="Asia/Singapore">Asia/Singapore (SGT)</option>
                                        <option value="Asia/Bangkok">Asia/Bangkok (ICT) - Thailand</option>
                                        <option value="Asia/Jakarta">Asia/Jakarta (WIB) - Indonesia</option>
                                        <option value="Asia/Manila">Asia/Manila (PHT) - Philippines</option>
                                        <option value="Asia/Karachi">Asia/Karachi (PKT) - Pakistan</option>
                                        <option value="Asia/Kolkata">Asia/Kolkata (IST) - India</option>
                                        <option value="Asia/Dhaka">Asia/Dhaka (BST) - Bangladesh</option>
                                    </optgroup>
                                    <optgroup label="Australia & Pacific">
                                        <option value="Australia/Sydney">Australia/Sydney (AEDT/AEST)</option>
                                        <option value="Australia/Melbourne">Australia/Melbourne (AEDT/AEST)</option>
                                        <option value="Australia/Brisbane">Australia/Brisbane (AEST)</option>
                                        <option value="Australia/Perth">Australia/Perth (AWST)</option>
                                        <option value="Pacific/Auckland">Pacific/Auckland (NZDT/NZST) - New Zealand</option>
                                        <option value="Pacific/Fiji">Pacific/Fiji (FJT)</option>
                                    </optgroup>
                                    <optgroup label="Africa">
                                        <option value="Africa/Cairo">Africa/Cairo (EET) - Egypt</option>
                                        <option value="Africa/Johannesburg">Africa/Johannesburg (SAST) - South Africa</option>
                                        <option value="Africa/Lagos">Africa/Lagos (WAT) - Nigeria</option>
                                        <option value="Africa/Nairobi">Africa/Nairobi (EAT) - Kenya</option>
                                        <option value="Africa/Casablanca">Africa/Casablanca (WET) - Morocco</option>
                                    </optgroup>
                                    <optgroup label="South America">
                                        <option value="America/Sao_Paulo">America/Sao Paulo (BRT) - Brazil</option>
                                        <option value="America/Buenos_Aires">America/Buenos Aires (ART) - Argentina</option>
                                        <option value="America/Santiago">America/Santiago (CLT) - Chile</option>
                                        <option value="America/Bogota">America/Bogota (COT) - Colombia</option>
                                        <option value="America/Lima">America/Lima (PET) - Peru</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date Format</label>
                                <select v-model="generalForm.date_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="Y-m-d">YYYY-MM-DD (2025-10-27)</option>
                                    <option value="m/d/Y">MM/DD/YYYY (10/27/2025)</option>
                                    <option value="d/m/Y">DD/MM/YYYY (27/10/2025)</option>
                                    <option value="F j, Y">Month DD, YYYY (October 27, 2025)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                                Save General Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    settings: Object
});

const activeTab = ref('mail');

const tabs = [
    { id: 'mail', name: 'Email Settings' },
    { id: 'branding', name: 'Branding' },
    { id: 'seo', name: 'SEO' },
    { id: 'general', name: 'General' }
];

// Forms
const mailForm = reactive({
    mail_mailer: props.settings?.mail?.mail_mailer || 'smtp',
    mail_host: props.settings?.mail?.mail_host || '',
    mail_port: props.settings?.mail?.mail_port || '',
    mail_username: props.settings?.mail?.mail_username || '',
    mail_password: props.settings?.mail?.mail_password || '',
    mail_encryption: props.settings?.mail?.mail_encryption || 'tls',
    mail_from_address: props.settings?.mail?.mail_from_address || '',
    mail_from_name: props.settings?.mail?.mail_from_name || ''
});

const brandingForm = reactive({
    app_name: props.settings?.branding?.app_name || 'Creative Hub',
    brand_color: props.settings?.branding?.brand_color || '#4F46E5',
    app_logo: null,
    app_favicon: null,
    pwa_icon: null
});

const seoForm = reactive({
    seo_title: props.settings?.seo?.seo_title || '',
    seo_description: props.settings?.seo?.seo_description || '',
    seo_keywords: props.settings?.seo?.seo_keywords || '',
    seo_image: null
});

const generalForm = reactive({
    timezone: props.settings?.general?.timezone || 'UTC',
    date_format: props.settings?.general?.date_format || 'Y-m-d',
    send_welcome_email: props.settings?.general?.send_welcome_email ?? true
});

// Test email modal
const showTestEmailModal = ref(false);
const testEmailAddress = ref('');

// Update functions
const updateMailSettings = () => {
    router.post(route('settings.mail.update'), mailForm);
};

const updateBrandingSettings = () => {
    const formData = new FormData();
    formData.append('app_name', brandingForm.app_name);
    formData.append('brand_color', brandingForm.brand_color);
    if (brandingForm.app_logo) formData.append('app_logo', brandingForm.app_logo);
    if (brandingForm.app_favicon) formData.append('app_favicon', brandingForm.app_favicon);
    if (brandingForm.pwa_icon) formData.append('pwa_icon', brandingForm.pwa_icon);

    router.post(route('settings.branding.update'), formData);
};

const updateSeoSettings = () => {
    const formData = new FormData();
    formData.append('seo_title', seoForm.seo_title);
    formData.append('seo_description', seoForm.seo_description);
    formData.append('seo_keywords', seoForm.seo_keywords);
    if (seoForm.seo_image) formData.append('seo_image', seoForm.seo_image);

    router.post(route('settings.seo.update'), formData);
};

const updateGeneralSettings = () => {
    router.post(route('settings.general.update'), generalForm);
};

// File upload handlers
const handleLogoUpload = (event) => {
    brandingForm.app_logo = event.target.files[0];
};

const handleFaviconUpload = (event) => {
    brandingForm.app_favicon = event.target.files[0];
};

const handlePwaIconUpload = (event) => {
    brandingForm.pwa_icon = event.target.files[0];
};

const handleSeoImageUpload = (event) => {
    seoForm.seo_image = event.target.files[0];
};

// Test email
const testEmail = () => {
    showTestEmailModal.value = true;
};

const sendTestEmail = () => {
    router.post(route('settings.test-email'), { email: testEmailAddress.value });
    showTestEmailModal.value = false;
    testEmailAddress.value = '';
};
</script>

<style scoped>
.tab-active {
    width: 25%;
    padding: 1rem 0.25rem;
    text-align: center;
    border-bottom: 2px solid #4F46E5;
    font-weight: 500;
    font-size: 0.875rem;
    color: #4F46E5 !important;
    background-color: #EEF2FF;
    transition: all 0.2s;
}

.tab-inactive {
    width: 25%;
    padding: 1rem 0.25rem;
    text-align: center;
    border-bottom: 2px solid transparent;
    font-weight: 500;
    font-size: 0.875rem;
    color: #111827 !important;
    background-color: white;
    transition: all 0.2s;
}

.tab-inactive:hover {
    color: #4F46E5 !important;
    border-bottom-color: #D1D5DB;
    background-color: #F9FAFB;
}
</style>
