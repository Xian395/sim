<template>
  <label v-if="value" :for="forId" class="block font-medium text-sm text-gray-700">
    {{ value }}
  </label>
</template>

<script setup>
defineProps({
  forId: String,
  value: String,
});
</script>