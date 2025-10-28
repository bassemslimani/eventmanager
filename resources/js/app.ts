import '../css/app.css';
import '../css/button-fix.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { MotionPlugin } from '@vueuse/motion';
import { createI18n } from 'vue-i18n';

// PrimeVue imports
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Ripple from 'primevue/ripple';
import Tooltip from 'primevue/tooltip';

// Import PrimeVue CSS
import 'primeicons/primeicons.css';

const appName = import.meta.env.VITE_APP_NAME || 'QRMH';

// Custom PrimeVue Aura preset with 2025 design trends
const QRMHPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{emerald.50}',
            100: '{emerald.100}',
            200: '{emerald.200}',
            300: '{emerald.300}',
            400: '{emerald.400}',
            500: '{emerald.500}',
            600: '{emerald.600}',
            700: '{emerald.700}',
            800: '{emerald.800}',
            900: '{emerald.900}',
            950: '{emerald.950}'
        },
        colorScheme: {
            light: {
                primary: {
                    color: '{emerald.500}',
                    inverseColor: '#ffffff',
                    hoverColor: '{emerald.600}',
                    activeColor: '{emerald.700}'
                },
                highlight: {
                    background: '{emerald.950}',
                    focusBackground: '{emerald.700}',
                    color: '#ffffff',
                    focusColor: '#ffffff'
                },
                surface: {
                    0: '#ffffff',
                    50: '{gray.50}',
                    100: '{gray.100}',
                    200: '{gray.200}',
                    300: '{gray.300}',
                    400: '{gray.400}',
                    500: '{gray.500}',
                    600: '{gray.600}',
                    700: '{gray.700}',
                    800: '{gray.800}',
                    900: '{gray.900}',
                    950: '{gray.950}'
                }
            },
            dark: {
                primary: {
                    color: '{emerald.400}',
                    inverseColor: '{emerald.950}',
                    hoverColor: '{emerald.300}',
                    activeColor: '{emerald.200}'
                },
                highlight: {
                    background: 'rgba(16, 185, 129, .16)',
                    focusBackground: 'rgba(16, 185, 129, .24)',
                    color: 'rgba(255,255,255,.87)',
                    focusColor: 'rgba(255,255,255,.87)'
                },
                surface: {
                    0: '#0A0A0B',
                    50: '{zinc.50}',
                    100: '{zinc.100}',
                    200: '{zinc.200}',
                    300: '{zinc.300}',
                    400: '{zinc.400}',
                    500: '{zinc.500}',
                    600: '{zinc.600}',
                    700: '{zinc.700}',
                    800: '{zinc.800}',
                    900: '{zinc.900}',
                    950: '{zinc.950}'
                }
            }
        }
    }
});

// i18n configuration for multilingual support (English/Arabic)
const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
        en: {
            // English translations will be added here
        },
        ar: {
            // Arabic translations will be added here
        }
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: QRMHPreset,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '.dark-mode',
                        cssLayer: false
                    }
                },
                ripple: true,
                inputVariant: 'filled',
                pt: {
                    button: {
                        root: {
                            class: 'force-white-text',
                            style: 'color: rgb(255, 255, 255) !important;'
                        },
                        label: {
                            class: 'force-white-text',
                            style: 'color: rgb(255, 255, 255) !important;'
                        },
                        icon: {
                            class: 'force-white-text',
                            style: 'color: rgb(255, 255, 255) !important;'
                        }
                    }
                },
                ptOptions: {
                    mergeProps: true,
                    mergeSections: true
                },
                locale: {
                    startsWith: 'Starts with',
                    contains: 'Contains',
                    notContains: 'Not contains',
                    endsWith: 'Ends with',
                    equals: 'Equals',
                    notEquals: 'Not equals',
                    noFilter: 'No Filter',
                    lt: 'Less than',
                    lte: 'Less than or equal to',
                    gt: 'Greater than',
                    gte: 'Greater than or equal to',
                    dateIs: 'Date is',
                    dateIsNot: 'Date is not',
                    dateBefore: 'Date is before',
                    dateAfter: 'Date is after',
                    clear: 'Clear',
                    apply: 'Apply',
                    matchAll: 'Match All',
                    matchAny: 'Match Any',
                    addRule: 'Add Rule',
                    removeRule: 'Remove Rule',
                    accept: 'Yes',
                    reject: 'No',
                    choose: 'Choose',
                    upload: 'Upload',
                    cancel: 'Cancel',
                    completed: 'Completed',
                    pending: 'Pending',
                    dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    chooseYear: 'Choose Year',
                    chooseMonth: 'Choose Month',
                    chooseDate: 'Choose Date',
                    prevDecade: 'Previous Decade',
                    nextDecade: 'Next Decade',
                    prevYear: 'Previous Year',
                    nextYear: 'Next Year',
                    prevMonth: 'Previous Month',
                    nextMonth: 'Next Month',
                    prevHour: 'Previous Hour',
                    nextHour: 'Next Hour',
                    prevMinute: 'Previous Minute',
                    nextMinute: 'Next Minute',
                    prevSecond: 'Previous Second',
                    nextSecond: 'Next Second',
                    am: 'am',
                    pm: 'pm',
                    today: 'Today',
                    weekHeader: 'Wk',
                    firstDayOfWeek: 0,
                    dateFormat: 'mm/dd/yy',
                    weak: 'Weak',
                    medium: 'Medium',
                    strong: 'Strong',
                    passwordPrompt: 'Enter a password',
                    emptyFilterMessage: 'No results found',
                    emptyMessage: 'No available options'
                }
            })
            .use(ToastService)
            .use(ConfirmationService)
            .use(MotionPlugin)
            .use(i18n);

        // Register global directives
        app.directive('ripple', Ripple);
        app.directive('tooltip', Tooltip);

        app.mount(el);

        // Force white text on all buttons - JavaScript override
        setTimeout(() => {
            const style = document.createElement('style');
            style.id = 'button-text-force-fix';
            style.innerHTML = `
                button.p-button:not(.p-button-text):not(.p-button-outlined),
                button.p-button:not(.p-button-text):not(.p-button-outlined):hover,
                button.p-button:not(.p-button-text):not(.p-button-outlined):active,
                button.p-button:not(.p-button-text):not(.p-button-outlined):focus,
                button.p-button:not(.p-button-text):not(.p-button-outlined) *,
                button.p-button:not(.p-button-text):not(.p-button-outlined):hover *,
                button.p-button:not(.p-button-text):not(.p-button-outlined):active *,
                button.p-button:not(.p-button-text):not(.p-button-outlined):focus * {
                    color: rgb(255, 255, 255) !important;
                }
            `;
            document.head.appendChild(style);
        }, 100);

    },
    progress: {
        color: '#10b981',
        showSpinner: true,
    },
});
