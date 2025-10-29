<template>
    <Modal :show="show" @close="closeModal" max-width="4xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Select Brand
                </h3>
                <button
                    @click="closeModal"
                    type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center"
                >
                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <input
                    v-model="searchQuery"
                    @input="filterBrands"
                    type="text"
                    placeholder="Search brands..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div class="grid grid-cols-2 gap-3 max-h-96 overflow-y-auto p-2">
                <div
                    v-for="brand in filteredBrands"
                    :key="brand.id"
                    @click="selectBrand(brand)"
                    :class="[
                        'p-3 rounded-lg border-2 cursor-pointer transition-all duration-200 flex items-center justify-between hover:shadow-md',
                        selectedBrand?.id === brand.id
                            ? 'border-blue-500 bg-blue-50 text-blue-900 font-semibold'
                            : 'border-gray-200 hover:border-blue-300 bg-white'
                    ]"
                >
                    <span>{{ brand.name }}</span>
                    <svg
                        v-if="selectedBrand?.id === brand.id"
                        class="w-5 h-5 text-blue-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t flex items-center justify-between">
                <span class="text-sm text-gray-600">
                    {{ selectedBrand ? '1 brand selected' : 'No brand selected' }}
                </span>
                <button
                    @click="closeModal"
                    type="button"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Done
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    brands: {
        type: Array,
        required: true
    },
    selectedBrand: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'select']);

const searchQuery = ref('');
const filteredBrands = ref([...props.brands]);

const filterBrands = () => {
    const query = searchQuery.value.toLowerCase().trim();
    if (query === '') {
        filteredBrands.value = [...props.brands];
    } else {
        filteredBrands.value = props.brands.filter(brand =>
            brand.name.toLowerCase().includes(query)
        );
    }
};

const selectBrand = (brand) => {
    emit('select', brand);
    closeModal();
};

const closeModal = () => {
    searchQuery.value = '';
    filteredBrands.value = [...props.brands];
    emit('close');
};

// Watch for show prop changes to reset search
watch(() => props.show, (newValue) => {
    if (newValue) {
        searchQuery.value = '';
        filteredBrands.value = [...props.brands];
    }
});
</script>
