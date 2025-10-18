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
                                <p class="mt-1 text-sm text-gray-600">
                                    You can set this now or later when adding stock (after you know your acquisition cost). Products without a selling price cannot be sold at POS.
                                </p>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.price"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel
                                    for="category_ids"
                                    value="Categories"
                                />
                                <div
                                    class="mt-1 p-3 border border-gray-300 rounded-md bg-white max-h-48 overflow-y-auto"
                                >
                                    <div
                                        v-for="category in categories"
                                        :key="category.id"
                                        class="flex items-center mb-2 last:mb-0"
                                    >
                                        <input
                                            :id="'category_' + category.id"
                                            type="checkbox"
                                            :value="category.id"
                                            v-model="form.category_ids"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        />
                                        <label
                                            :for="'category_' + category.id"
                                            class="ml-2 text-sm text-gray-900 cursor-pointer"
                                        >
                                            {{ category.name }}
                                        </label>
                                    </div>
                                    <div
                                        v-if="categories.length === 0"
                                        class="text-sm text-gray-500 italic"
                                    >
                                        No categories available
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">
                                        Select one or more active categories for this
                                        product. Only active categories are available for selection.
                                    </p>
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.category_ids"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="brand_id" value="Brand (Optional)" />
                                <div class="flex gap-2">
                                    <select
                                        id="brand_id"
                                        v-model="form.brand_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select a brand (optional)</option>
                                        <option
                                            v-for="brand in availableBrands"
                                            :key="brand.id"
                                            :value="brand.id"
                                        >
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                    <button
                                        type="button"
                                        @click="openQuickBrandModal"
                                        class="mt-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 whitespace-nowrap"
                                        title="Add New Brand"
                                    >
                                        + Add Brand
                                    </button>
                                </div>
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

const form = useForm({
    name: "",
    item_code: "",
    price: "",
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

            // Auto-select the newly created brand
            form.brand_id = newBrand.id;

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