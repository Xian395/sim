<template>
    <Head title="Add New Product" />

    <AdminAuthenticatedLayout>
        <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6"
                    >
                        <h2 class="text-2xl font-black text-blue-700">
                            Add New Product
                        </h2>
                        <ButtonNew
                            types="back"
                            size="md"
                            tooltips="return to products list"
                            @click="
                                $inertia.visit(route('admin.products.index'))
                            "
                        >
                            Back
                        </ButtonNew>
                    </div>
                    <div class="p-6">
                        <!-- 
            <div v-if="$page.props.flash?.success" class="mb-4 text-green-600">
              {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 text-red-600">
              {{ $page.props.flash.error }}
            </div> -->

                        <!-- <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm text-blue-700">
                    A barcode will be automatically generated based on the item code you provide. 
                    The item code should be unique for each product.
                  </p>
                </div>
              </div>
            </div> -->

                        <form @submit.prevent="submit">
                            <div class="mt-4">
                                <InputLabel for="name" value="Product Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.name"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="item_code" value="Item Code" />
                                <TextInput
                                    id="item_code"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.item_code"
                                    required
                                />
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">
                                        Enter a unique code for this product.
                                        <!-- This will be used to generate the barcode. -->
                                    </p>
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.item_code"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="price" value="Selling Price (₱) - Optional" />
                                <TextInput
                                    id="price"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="mt-1 block w-full"
                                    v-model="form.price"
                                    placeholder="0.00"
                                />
                                <!-- <p class="mt-1 text-sm text-gray-600">
                                    You can set this now or later when adding stock (after you know your acquisition cost). Products without a selling price cannot be sold at POS.
                                </p> -->
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.price"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel
                                    for="category_search"
                                    value="Categories"
                                />

                                <!-- Selected Categories Tags -->
                                <div v-if="selectedCategories.length > 0" class="mt-2 flex flex-wrap gap-2">
                                    <span
                                        v-for="category in selectedCategories"
                                        :key="category.id"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-300"
                                    >
                                        {{ category.name }}
                                        <button
                                            type="button"
                                            @click="removeCategory(category.id)"
                                            class="ml-2 text-blue-600 hover:text-blue-800 focus:outline-none"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </span>
                                </div>

                                <!-- Category Search Input -->
                                <div class="relative mt-2">
                                    <input
                                        ref="categoryInput"
                                        id="category_search"
                                        type="text"
                                        v-model="categorySearch"
                                        @input="filterCategories"
                                        @focus="showCategoryDropdown = true; filterCategories(); updateCategoryDropdownPosition();"
                                        @blur="handleCategoryBlur"
                                        placeholder="Type to search or click to browse..."
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    />
                                </div>

                                <!-- Category Dropdown (Teleported) -->
                                <Teleport to="body">
                                    <div
                                        v-if="showCategoryDropdown && filteredCategories.length > 0"
                                        :style="categoryDropdownStyle"
                                        class="fixed z-50 bg-white border border-gray-300 rounded-md shadow-lg max-h-80 overflow-hidden flex flex-col"
                                        style="box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);"
                                    >
                                        <div class="overflow-y-auto flex-1">
                                            <div
                                                v-for="(category, index) in displayedCategories"
                                                :key="category.id"
                                                @mousedown.prevent="addCategory(category)"
                                                class="px-4 py-2 cursor-pointer hover:bg-indigo-50 border-b border-gray-100 last:border-b-0"
                                            >
                                                {{ category.name }}
                                            </div>
                                        </div>

                                        <!-- Show More Button -->
                                        <div
                                            v-if="filteredCategories.length > categoryDisplayLimit"
                                            class="border-t border-gray-200 bg-gray-50"
                                        >
                                            <button
                                                type="button"
                                                @mousedown.prevent="openCategoryModal"
                                                class="w-full px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 transition-colors"
                                            >
                                                View all {{ filteredCategories.length }} categories
                                            </button>
                                        </div>
                                    </div>

                                    <!-- No results -->
                                    <div
                                        v-if="showCategoryDropdown && categorySearch && filteredCategories.length === 0"
                                        :style="categoryDropdownStyle"
                                        class="fixed z-50 bg-white border border-gray-300 rounded-md shadow-lg p-4 text-center text-gray-500"
                                        style="box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);"
                                    >
                                        No categories found
                                    </div>
                                </Teleport>

                                <!-- <div class="mt-1">
                                    <p class="text-sm text-gray-600">
                                        Type to search and select one or more active categories for this product.
                                    </p>
                                </div> -->
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.category_ids"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="brand_search" value="Brand (Optional)" />

                                <!-- Selected Brand Tag -->
                                <div v-if="selectedBrand" class="mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-300">
                                        {{ selectedBrand.name }}
                                        <button
                                            type="button"
                                            @click="removeBrand"
                                            class="ml-2 text-green-600 hover:text-green-800 focus:outline-none"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </span>
                                </div>

                                <!-- Brand Search Input -->
                                <div class="relative mt-2 flex gap-2">
                                    <div class="relative flex-1">
                                        <input
                                            ref="brandInput"
                                            id="brand_search"
                                            type="text"
                                            v-model="brandSearch"
                                            @input="filterBrands"
                                            @focus="showBrandDropdown = true; filterBrands(); updateBrandDropdownPosition();"
                                            @blur="handleBrandBlur"
                                            placeholder="Type to search or click to browse..."
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            :disabled="!!selectedBrand"
                                        />
                                    </div>

                                    <button
                                        type="button"
                                        @click="openQuickBrandModal"
                                        class="mt-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 whitespace-nowrap"
                                        title="Add New Brand"
                                    >
                                        + Add
                                    </button>
                                </div>

                                <!-- Brand Dropdown (Teleported) -->
                                <Teleport to="body">
                                    <div
                                        v-if="showBrandDropdown && filteredBrands.length > 0 && !selectedBrand"
                                        :style="brandDropdownStyle"
                                        class="fixed z-50 bg-white border border-gray-300 rounded-md shadow-lg max-h-80 overflow-hidden flex flex-col"
                                        style="box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);"
                                    >
                                            <div class="overflow-y-auto flex-1">
                                                <div
                                                    v-for="brand in displayedBrands"
                                                    :key="brand.id"
                                                    @mousedown.prevent="selectBrand(brand)"
                                                    class="px-4 py-2 cursor-pointer hover:bg-green-50 border-b border-gray-100 last:border-b-0"
                                                >
                                                    {{ brand.name }}
                                                </div>
                                            </div>

                                            <!-- Show More Button -->
                                            <div
                                                v-if="filteredBrands.length > brandDisplayLimit"
                                                class="border-t border-gray-200 bg-gray-50"
                                            >
                                                <button
                                                    type="button"
                                                    @mousedown.prevent="openBrandModal"
                                                    class="w-full px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 transition-colors"
                                                >
                                                    View all {{ filteredBrands.length }} brands
                                                </button>
                                            </div>
                                        </div>

                                        <!-- No results -->
                                        <div
                                            v-if="showBrandDropdown && brandSearch && filteredBrands.length === 0 && !selectedBrand"
                                            :style="brandDropdownStyle"
                                            class="fixed z-50 bg-white border border-gray-300 rounded-md shadow-lg p-4 text-center text-gray-500"
                                            style="box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);"
                                        >
                                            No brands found
                                        </div>
                                </Teleport>
                                <InputError class="mt-2" :message="form.errors.brand_id" />
                            </div>

                            <div class="mt-4">
    <InputLabel for="productimage" value="Product Image" />
    <input
    id="productimage"
    type="file"
    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
    @change="handleFileUpload"
    accept="image/*"
    required
/>
    <InputError
        class="mt-2"
        :message="form.errors.productimage"
    />
</div>

                            <div class="mt-4">
                                <InputLabel
                                    for="description"
                                    value="Description (Optional)"
                                />
                                <TextInput
                                    id="description"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.description"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.description"
                                />
                            </div>

                            <!-- Preview section -->
                            <div
                                v-if="form.item_code"
                                class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-md"
                            >
                                <h4
                                    class="text-sm font-medium text-gray-700 mb-2"
                                >
                                    Preview:
                                </h4>
                                <div class="text-sm text-gray-600">
                                    <p>
                                        <strong>Item Code:</strong>
                                        {{ form.item_code }}
                                    </p>
                                    <!-- <p><strong>Generated Barcode:</strong> (Will be created automatically when saved)</p> -->
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end mt-6 gap-3"
                            >
                                <ButtonNew
                                    types="save"
                                    size="md"
                                    tooltips="Save new product"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    @click="submit"
                                >
                                    Save
                                </ButtonNew>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                >
                    Unauthorized: You do not have access to this page.
                </div>
            </div>
        </div>

        <!-- Quick Brand Creation Modal -->
        <Modal :show="showQuickBrandModal" @close="closeQuickBrandModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Add New Brand
                    </h3>
                    <button
                        @click="closeQuickBrandModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="createQuickBrand">
                    <div class="mb-4">
                        <InputLabel for="quick_brand_name" value="Brand Name" />
                        <TextInput
                            id="quick_brand_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="quickBrandForm.name"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="quickBrandForm.errors.name" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="quick_brand_description" value="Description (Optional)" />
                        <textarea
                            id="quick_brand_description"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            rows="3"
                            v-model="quickBrandForm.description"
                        ></textarea>
                        <InputError class="mt-2" :message="quickBrandForm.errors.description" />
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <button
                            type="button"
                            @click="closeQuickBrandModal"
                            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="quickBrandForm.processing"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            {{ quickBrandForm.processing ? 'Creating...' : 'Create Brand' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Category Selection Modal -->
        <Modal :show="showCategoryModal" @close="closeCategoryModal" max-width="4xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Select Categories
                    </h3>
                    <button
                        @click="closeCategoryModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Search in Modal -->
                <div class="mb-4">
                    <input
                        type="text"
                        v-model="categoryModalSearch"
                        @input="filterModalCategories"
                        placeholder="Search categories..."
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    />
                </div>

                <!-- 2-Column Grid -->
                <div class="grid grid-cols-2 gap-3 max-h-96 overflow-y-auto p-2">
                    <div
                        v-for="category in modalFilteredCategories"
                        :key="category.id"
                        @click="toggleCategoryFromModal(category)"
                        :class="[
                            'px-4 py-3 border-2 rounded-lg cursor-pointer transition-all duration-150',
                            selectedCategories.some(c => c.id === category.id)
                                ? 'border-blue-500 bg-blue-50 text-blue-900 font-semibold'
                                : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50'
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <span>{{ category.name }}</span>
                            <svg
                                v-if="selectedCategories.some(c => c.id === category.id)"
                                class="w-5 h-5 text-blue-600"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        @click="closeCategoryModal"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md"
                    >
                        Done ({{ selectedCategories.length }} selected)
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Brand Selection Modal -->
        <Modal :show="showBrandModal" @close="closeBrandModal" max-width="4xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Select Brand
                    </h3>
                    <button
                        @click="closeBrandModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Search in Modal -->
                <div class="mb-4">
                    <input
                        type="text"
                        v-model="brandModalSearch"
                        @input="filterModalBrands"
                        placeholder="Search brands..."
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    />
                </div>

                <!-- 2-Column Grid -->
                <div class="grid grid-cols-2 gap-3 max-h-96 overflow-y-auto p-2">
                    <div
                        v-for="brand in modalFilteredBrands"
                        :key="brand.id"
                        @click="selectBrandFromModal(brand)"
                        :class="[
                            'px-4 py-3 border-2 rounded-lg cursor-pointer transition-all duration-150',
                            selectedBrand && selectedBrand.id === brand.id
                                ? 'border-green-500 bg-green-50 text-green-900 font-semibold'
                                : 'border-gray-200 hover:border-green-300 hover:bg-green-50'
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <span>{{ brand.name }}</span>
                            <svg
                                v-if="selectedBrand && selectedBrand.id === brand.id"
                                class="w-5 h-5 text-green-600"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        @click="closeBrandModal"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md"
                    >
                        Done
                    </button>
                </div>
            </div>
        </Modal>
    </AdminAuthenticatedLayout>
</template>

<script setup>
import { useForm, usePage, Head, router } from "@inertiajs/vue3";
import AdminAuthenticatedLayout from "@/Layouts/AdminAuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import ButtonNew from "@/Components/ButtonNew.vue";
import Modal from "@/Components/Modal.vue";
import { notify } from "@/globalFunctions.js";
import { ref, computed } from "vue";

const props = defineProps({
    categories: Array,
    brands: Array,
});

const $page = usePage();

// Quick Brand Modal
const showQuickBrandModal = ref(false);
const availableBrands = ref([...props.brands]);

// Category autocomplete
const categoryInput = ref(null);
const categorySearch = ref("");
const filteredCategories = ref([...props.categories]);
const showCategoryDropdown = ref(false);
const selectedCategories = ref([]);
const categoryDisplayLimit = 5;
const categoryDropdownStyle = ref({});

// Category modal
const showCategoryModal = ref(false);
const categoryModalSearch = ref("");
const modalFilteredCategories = ref([...props.categories]);

// Brand autocomplete
const brandInput = ref(null);
const brandSearch = ref("");
const filteredBrands = ref([...props.brands]);
const showBrandDropdown = ref(false);
const selectedBrand = ref(null);
const brandDisplayLimit = 5;
const brandDropdownStyle = ref({});

// Brand modal
const showBrandModal = ref(false);
const brandModalSearch = ref("");
const modalFilteredBrands = ref([...props.brands]);

// Computed properties for displaying limited items (always show first 5 in dropdown)
const displayedCategories = computed(() => {
    return filteredCategories.value.slice(0, categoryDisplayLimit);
});

const displayedBrands = computed(() => {
    return filteredBrands.value.slice(0, brandDisplayLimit);
});

const form = useForm({
    name: "",
    item_code: "",
    price: "",
    cost_price: "",
    category_ids: [],
    brand_id: "",
    description: "",
    productimage: null
});

const quickBrandForm = useForm({
    name: "",
    description: "",
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.productimage = file;
    }
};

// Position dropdown functions
const updateCategoryDropdownPosition = () => {
    if (categoryInput.value) {
        const rect = categoryInput.value.getBoundingClientRect();
        categoryDropdownStyle.value = {
            position: 'fixed',
            top: `${rect.bottom + 4}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
        };
    }
};

const updateBrandDropdownPosition = () => {
    if (brandInput.value) {
        const rect = brandInput.value.getBoundingClientRect();
        brandDropdownStyle.value = {
            position: 'fixed',
            top: `${rect.bottom + 4}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
        };
    }
};

// Category autocomplete functions
const filterCategories = () => {
    const query = categorySearch.value.toLowerCase();

    // If no query, show all available categories
    if (!query) {
        filteredCategories.value = props.categories.filter(cat =>
            !selectedCategories.value.some(selected => selected.id === cat.id)
        );
        return;
    }

    // Filter by query
    filteredCategories.value = props.categories.filter(category =>
        category.name.toLowerCase().includes(query) &&
        !selectedCategories.value.some(selected => selected.id === category.id)
    );
};

const addCategory = (category) => {
    if (!selectedCategories.value.some(c => c.id === category.id)) {
        selectedCategories.value.push(category);
        form.category_ids.push(category.id);
    }
    categorySearch.value = "";
    filteredCategories.value = props.categories.filter(cat =>
        !selectedCategories.value.some(selected => selected.id === cat.id)
    );
    showCategoryDropdown.value = false;
};

const removeCategory = (categoryId) => {
    selectedCategories.value = selectedCategories.value.filter(c => c.id !== categoryId);
    form.category_ids = form.category_ids.filter(id => id !== categoryId);
    filteredCategories.value = props.categories.filter(cat =>
        !selectedCategories.value.some(selected => selected.id === cat.id)
    );
};

const handleCategoryBlur = () => {
    setTimeout(() => {
        showCategoryDropdown.value = false;
    }, 200);
};

// Brand autocomplete functions
const filterBrands = () => {
    const query = brandSearch.value.toLowerCase();

    // If no query, show all brands
    if (!query) {
        filteredBrands.value = [...availableBrands.value];
        return;
    }

    // Filter by query
    filteredBrands.value = availableBrands.value.filter(brand =>
        brand.name.toLowerCase().includes(query)
    );
};

const selectBrand = (brand) => {
    selectedBrand.value = brand;
    form.brand_id = brand.id;
    brandSearch.value = brand.name;
    showBrandDropdown.value = false;
};

const removeBrand = () => {
    selectedBrand.value = null;
    form.brand_id = "";
    brandSearch.value = "";
    filteredBrands.value = [...availableBrands.value];
};

const handleBrandBlur = () => {
    setTimeout(() => {
        showBrandDropdown.value = false;
    }, 200);
};

// Category Modal Functions
const openCategoryModal = () => {
    showCategoryModal.value = true;
    showCategoryDropdown.value = false;
    categoryModalSearch.value = "";
    filterModalCategories();
};

const closeCategoryModal = () => {
    showCategoryModal.value = false;
};

const filterModalCategories = () => {
    const query = categoryModalSearch.value.toLowerCase();
    if (!query) {
        modalFilteredCategories.value = props.categories.filter(cat =>
            !selectedCategories.value.some(selected => selected.id === cat.id)
        );
    } else {
        modalFilteredCategories.value = props.categories.filter(category =>
            category.name.toLowerCase().includes(query) &&
            !selectedCategories.value.some(selected => selected.id === category.id)
        );
    }
};

const toggleCategoryFromModal = (category) => {
    const index = selectedCategories.value.findIndex(c => c.id === category.id);
    if (index > -1) {
        // Remove if already selected
        selectedCategories.value.splice(index, 1);
        form.category_ids = form.category_ids.filter(id => id !== category.id);
    } else {
        // Add if not selected
        selectedCategories.value.push(category);
        form.category_ids.push(category.id);
    }
    // Refresh filtered list
    filterModalCategories();
};

// Brand Modal Functions
const openBrandModal = () => {
    showBrandModal.value = true;
    showBrandDropdown.value = false;
    brandModalSearch.value = "";
    filterModalBrands();
};

const closeBrandModal = () => {
    showBrandModal.value = false;
};

const filterModalBrands = () => {
    const query = brandModalSearch.value.toLowerCase();
    if (!query) {
        modalFilteredBrands.value = [...availableBrands.value];
    } else {
        modalFilteredBrands.value = availableBrands.value.filter(brand =>
            brand.name.toLowerCase().includes(query)
        );
    }
};

const selectBrandFromModal = (brand) => {
    selectedBrand.value = brand;
    form.brand_id = brand.id;
    brandSearch.value = brand.name;
    closeBrandModal();
};

const submit = async () => {
    try {
        console.log('Form data before submit:', {
            name: form.name,
            item_code: form.item_code,
            price: form.price,
            description: form.description,
            productimage: form.productimage,
            category_ids: form.category_ids
        });

        if (!form.productimage) {
            notify("Please select a product image.", "error");
            return;
        }

        const formData = new FormData();
        
        formData.append('name', form.name);
        formData.append('item_code', form.item_code);
        formData.append('price', form.price);
        formData.append('description', form.description);
        formData.append('productimage', form.productimage);
        if (form.brand_id) {
            formData.append('brand_id', form.brand_id);
        }
        
        form.category_ids.forEach((id, index) => {
            formData.append(`category_ids[${index}]`, id);
        });

        console.log('FormData contents:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ':', pair[1]);
        }

        router.post(route('admin.products.store'), formData, {
            forceFormData: true,
            onSuccess: () => {
                notify("Product saved successfully.", "success");
                form.reset();
            },
            onError: (errors) => {
                console.log('Validation errors:', errors); 
                if (errors) {
                    const errorMessages = Object.values(errors)
                        .flat()
                        .join("<br>");
                    notify(errorMessages, "error");
                } else {
                    notify("Something went wrong. Please try again later.", "error");
                }
            }
        });
    } catch (error) {
        console.error('Submit error:', error);
        notify("Something went wrong. Please try again later.", "error");
    }
};

// Quick Brand Modal Functions
const openQuickBrandModal = () => {
    showQuickBrandModal.value = true;
    quickBrandForm.reset();
    quickBrandForm.clearErrors();
};

const closeQuickBrandModal = () => {
    showQuickBrandModal.value = false;
    quickBrandForm.reset();
    quickBrandForm.clearErrors();
};

const createQuickBrand = async () => {
    try {
        const response = await axios.post(route('admin.brands.store'), quickBrandForm.data());

        if (response.data.success) {
            // Add the new brand to the available brands list
            const newBrand = response.data.brand;
            availableBrands.value.push(newBrand);
            filteredBrands.value.push(newBrand);

            // Auto-select the newly created brand using autocomplete
            selectBrand(newBrand);

            // Close modal and notify
            closeQuickBrandModal();
            notify('Brand created successfully and selected!', 'success');
        } else {
            notify('Failed to create brand', 'error');
        }
    } catch (error) {
        console.error('Quick brand creation error:', error);
        if (error.response && error.response.data.errors) {
            quickBrandForm.setError(error.response.data.errors);
            const errorMessages = Object.values(error.response.data.errors).flat().join('<br>');
            notify(errorMessages, 'error');
        } else {
            notify('Something went wrong while creating the brand.', 'error');
        }
    }
};
</script>