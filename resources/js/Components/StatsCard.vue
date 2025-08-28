<template>
  <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
    <div class="flex items-center">
      <div 
        class="w-3 h-3 rounded-full mr-3"
        :class="dotColorClass"
      ></div>
      <div>
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <p 
          class="text-2xl font-bold"
          :class="valueColorClass"
        >
          {{ value }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [Number, String],
    required: true,
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'orange', 'red', 'purple', 'yellow', 'indigo'].includes(value),
  },
});

// Computed properties for dynamic color classes
const dotColorClass = computed(() => {
  const colorMap = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    orange: 'bg-orange-500',
    red: 'bg-red-500',
    purple: 'bg-purple-500',
    yellow: 'bg-yellow-500',
    indigo: 'bg-indigo-500',
  };
  return colorMap[props.color] || 'bg-blue-500';
});

const valueColorClass = computed(() => {
  const colorMap = {
    blue: 'text-gray-900',
    green: 'text-green-600',
    orange: 'text-orange-500',
    red: 'text-red-500',
    purple: 'text-purple-600',
    yellow: 'text-yellow-600',
    indigo: 'text-indigo-600',
  };
  return colorMap[props.color] || 'text-gray-900';
});
</script>