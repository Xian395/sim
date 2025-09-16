<template>
    <button
        type="button"
        :class="buttonClasses"
        @click="$emit('click', $event)"
    >
        <span :class="iconClass"></span> <slot></slot>
        <div v-if="props.tooltips" :class="[tooltipClasses]">
            {{ props.tooltips }}
        </div>
    </button>
</template>

<script setup>
import { computed } from 'vue';
defineEmits(['click']);
const props = defineProps({
    types: {
        type: String,
        default: 'edit'
    },
    color: {
        type: String,
        default: '', 
    },
    size: {
        type: String,
        default: 'sm', 
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    icons: {
        type: String,
        default: ''
    },
    tooltips: {
        type: String,
        default: ''
    },
    placement: {
        type: String,
        default: 'top',
        validator: (value) => ['top', 'left'].includes(value)
    },
});

const defaultIcons = {
    'delete': 'mdi mdi-delete-empty',
    'edit': 'mdi mdi-pencil-box-multiple',
    'add': 'mdi mdi-plus-box',
    'download': 'mdi mdi-download',
    'view': 'mdi mdi-eye-outline',
    'cancel': 'mdi mdi-close-box',
    'save': 'mdi mdi-content-save-check',
    'back': 'mdi mdi-arrow-left-circle',
    'remove' : 'mdi mdi-minus-circle',
    'pdf' : 'mdi mdi-file-export',
    'login' : 'mdi mdi-login',
    'archive' : 'mdi-archive-arrow-up'
};

const iconClass = computed(() => {
    return props.icons || defaultIcons[props.types] || '';
});

const tooltipClasses = computed(() => {
    return props.placement === 'top'
        ? 'absolute left-0 top-1/2 -translate-y-1/2 -translate-x-full mr-2 w-max max-w-xs p-2 text-xs text-white bg-gray-800 rounded-md opacity-0 invisible transition-all duration-300 group-hover:opacity-100 group-hover:visible group-hover:-translate-x-[110%]'
        : 'absolute left-1/2 bottom-full -translate-x-1/2 -mb-2 w-max max-w-xs p-2 text-xs text-white bg-gray-800 rounded-md opacity-0 invisible transition-all duration-300 group-hover:opacity-100 group-hover:visible group-hover:-translate-y-2';
});

const colorClasses = (size) => {
    const paddingX = size === 'text-lg' ? 'px-5' : 'px-2.5';
    const paddingY = size === 'text-lg' ? 'py-2.5' : 'py-1.5';
    const paddingClasses = `${paddingX} ${paddingY}`;
    return {
        blue: `relative group text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-700 font-medium rounded-lg ${paddingClasses}`,
        green: `relative group text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-200 dark:bg-green-700 dark:hover:bg-green-600 dark:focus:ring-green-700 font-medium rounded-lg ${paddingClasses}`,
        red: `relative group text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-200 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-700 font-medium rounded-lg ${paddingClasses}`,
        yellow: `relative group text-white bg-yellow-300 hover:bg-yellow-400 focus:ring-4 focus:ring-yellow-100 dark:bg-yellow-200 dark:hover:bg-yellow-300 dark:focus:ring-yellow-400 font-medium rounded-lg ${paddingClasses}`,
        purple: `relative group text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-200 dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 font-medium rounded-lg ${paddingClasses}`,
        gray: `relative group text-white bg-gray-800 hover:bg-gray-900 focus:ring-4 focus:ring-gray-400 dark:bg-gray-700 dark:hover:bg-gray-800 dark:focus:ring-gray-600 font-medium rounded-lg ${paddingClasses}`,
        orange: `relative group text-white bg-orange-500 hover:bg-orange-600 focus:ring-4 focus:ring-orange-300 dark:bg-orange-400 dark:hover:bg-orange-500 dark:focus:ring-orange-600 font-medium rounded-lg ${paddingClasses}`,
        teal: `relative group text-white bg-teal-500 hover:bg-teal-600 focus:ring-4 focus:ring-teal-300 dark:bg-teal-400 dark:hover:bg-teal-500 dark:focus:ring-teal-600 font-medium rounded-lg ${paddingClasses}`,
        pink: `relative group text-white bg-pink-500 hover:bg-pink-600 focus:ring-4 focus:ring-pink-300 dark:bg-pink-400 dark:hover:bg-pink-500 dark:focus:ring-pink-600 font-medium rounded-lg ${paddingClasses}`,
        indigo: `relative group text-white bg-indigo-500 hover:bg-indigo-600 focus:ring-4 focus:ring-indigo-300 dark:bg-indigo-400 dark:hover:bg-indigo-500 dark:focus:ring-indigo-600 font-medium rounded-lg ${paddingClasses}`,
        cyan: `relative group text-white bg-cyan-500 hover:bg-cyan-600 focus:ring-4 focus:ring-cyan-300 dark:bg-cyan-400 dark:hover:bg-cyan-500 dark:focus:ring-cyan-600 font-medium rounded-lg ${paddingClasses}`,
    };
};

const sizeClasses = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
};

const buttonClasses = computed(() => {
    const size = sizeClasses[props.size] || sizeClasses.md; 
    const colorMap = {
        add: 'green',
        delete: 'red',
        edit: 'green',
        view: 'purple',
        download: 'indigo',
        cancel: 'orange',
        save: 'blue',
        back: 'red',
        remove: 'orange',
        pdf: 'red',
        login: 'blue',
        archive: 'orange'
        
    };
    const color = props.color?.trim() || colorMap[props.types?.trim()] || 'blue';
    const classes = colorClasses(size)[color];
    return classes || colorClasses(size).blue; 
});


</script>

