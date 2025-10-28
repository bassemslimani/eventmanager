<template>
    <button
        :type="type"
        :disabled="disabled"
        @click="handleClick"
        :class="buttonClasses"
        class="custom-btn"
    >
        <i v-if="icon" :class="['pi', icon, iconPosition === 'left' ? 'mr-2' : 'ml-2']" :style="iconStyle"></i>
        <span v-if="label" :style="labelStyle">{{ label }}</span>
        <slot></slot>
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: String,
    icon: String,
    iconPosition: {
        type: String,
        default: 'left'
    },
    type: {
        type: String,
        default: 'button'
    },
    severity: {
        type: String,
        default: 'primary' // primary, success, info, warning, danger, secondary
    },
    disabled: Boolean,
    size: {
        type: String,
        default: 'normal' // small, normal, large
    }
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
    if (!props.disabled) {
        emit('click', event);
    }
};

const buttonClasses = computed(() => {
    const classes = [];

    // Severity classes
    classes.push(`btn-${props.severity}`);

    // Size classes
    if (props.size === 'small') {
        classes.push('btn-small');
    } else if (props.size === 'large') {
        classes.push('btn-large');
    }

    // Disabled class
    if (props.disabled) {
        classes.push('btn-disabled');
    }

    return classes.join(' ');
});

const labelStyle = computed(() => ({
    color: '#ffffff'
}));

const iconStyle = computed(() => ({
    color: '#ffffff'
}));
</script>

<style scoped>
.custom-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    color: #ffffff !important;
    outline: none;
    white-space: nowrap;
}

.custom-btn * {
    color: #ffffff !important;
}

.custom-btn:hover:not(.btn-disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.custom-btn:active:not(.btn-disabled) {
    transform: translateY(0);
}

/* Primary */
.btn-primary {
    background: linear-gradient(to right, #2563eb, #4f46e5);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-primary:hover:not(.btn-disabled) {
    background: linear-gradient(to right, #1d4ed8, #4338ca);
    box-shadow: 0 10px 25px rgba(79, 70, 229, 0.5);
}

/* Success */
.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-success:hover:not(.btn-disabled) {
    background: linear-gradient(135deg, #059669, #047857);
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.5);
}

/* Info */
.btn-info {
    background: linear-gradient(to right, #0ea5e9, #0284c7);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.btn-info:hover:not(.btn-disabled) {
    background: linear-gradient(to right, #0284c7, #0369a1);
    box-shadow: 0 10px 25px rgba(14, 165, 233, 0.5);
}

/* Warning */
.btn-warning {
    background: #f59e0b;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.btn-warning:hover:not(.btn-disabled) {
    background: #d97706;
    box-shadow: 0 10px 25px rgba(245, 158, 11, 0.5);
}

/* Danger */
.btn-danger {
    background: #ef4444;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-danger:hover:not(.btn-disabled) {
    background: #dc2626;
    box-shadow: 0 10px 25px rgba(239, 68, 68, 0.5);
}

/* Secondary */
.btn-secondary {
    background: #6b7280;
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
}

.btn-secondary:hover:not(.btn-disabled) {
    background: #4b5563;
    box-shadow: 0 10px 25px rgba(107, 114, 128, 0.5);
}

/* Sizes */
.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    border-radius: 0.5rem;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1rem;
    border-radius: 1rem;
}

/* Disabled */
.btn-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.btn-disabled:hover {
    transform: none !important;
    box-shadow: none !important;
}
</style>
