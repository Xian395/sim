<template>
    <AdminAuthenticatedLayout>
        <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div
                        class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6"
                    >
                         <h2 class="text-2xl font-black text-blue-700">Stock In</h2>
                    </div>
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <InputLabel
                                    for="product_id"
                                    value="Select Product"
                                />
                                <div class="relative">
                                    <input
                                        type="text"
                                        v-model="searchQuery"
                                        placeholder="Select or Search product by name or item code..."
                                        class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-200 ease-in-out bg-white hover:border-gray-300 text-gray-700 placeholder-gray-400"
                                        @input="filterProducts"
                                        @focus="showDropdown = true"
                                        @blur="handleBlur"
                                        @keydown="handleKeydown"
                                        autocomplete="off"
                                    />

                                    <!-- Dropdown -->
                                    <div
                                        v-if="
                                            showDropdown &&
                                            filteredProducts.length > 0
                                        "
                                        class="absolute z-50 mt-2 w-full bg-white border-2 border-gray-200 rounded-lg shadow-2xl max-h-60 overflow-auto"
                                        style="
                                            box-shadow: 0 20px 30px -5px rgba(0, 0, 0, 0.1),
                                                0 10px 20px -5px rgba(0, 0, 0, 0.04);
                                        "
                                    >
                                        <div
                                            v-for="(
                                                product, index
                                            ) in filteredProducts"
                                            :key="product.id"
                                            @mousedown.prevent="
                                                selectProduct(product)
                                            "
                                            :class="[
                                                'px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0 transition-colors duration-150',
                                                {
                                                    'bg-blue-50 border-blue-200':
                                                        index ===
                                                        highlightedIndex,
                                                },
                                            ]"
                                        >
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <img
                                                        v-if="product.image_url"
                                                        :src="product.image_url"
                                                        :alt="product.name"
                                                        class="w-12 h-12 object-cover rounded-lg border border-gray-200"
                                                        @error="handleImageError"
                                                    />
                                                    <div
                                                        v-else
                                                        class="w-12 h-12 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center"
                                                    >
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between">
                                                        <div class="font-semibold text-gray-800 truncate">
                                                            {{ product.name }}
                                                        </div>
                                                        <div class="flex-shrink-0 ml-2">
                                                            <span
                                                                :class="[
                                                                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold',
                                                                    product.stock_quantity <= 10
                                                                        ? 'bg-red-100 text-red-800 border border-red-200'
                                                                        : product.stock_quantity <= 50
                                                                        ? 'bg-yellow-100 text-yellow-800 border border-yellow-200'
                                                                        : 'bg-green-100 text-green-800 border border-green-200'
                                                                ]"
                                                            >
                                                                <span class="text-xs opacity-75 mr-1">QTY:</span>{{ product.stock_quantity }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="text-sm text-gray-600 mt-1">
                                                        <span class="font-medium">Code:</span>
                                                        <span class="font-mono text-gray-700">{{ product.barcode }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- No results message -->
                                    <div
                                        v-if="
                                            showDropdown &&
                                            filteredProducts.length === 0 &&
                                            searchQuery
                                        "
                                        class="absolute z-50 mt-2 w-full bg-white border-2 border-gray-200 rounded-lg shadow-2xl p-4 text-center text-gray-500"
                                        style="
                                            box-shadow: 0 20px 30px -5px rgba(0, 0, 0, 0.1),
                                                0 10px 20px -5px rgba(0, 0, 0, 0.04);
                                        "
                                    >
                                        <div class="text-gray-400 text-lg mb-1">🔍</div>
                                        <div class="font-medium">No products found</div>
                                        <div class="text-sm text-gray-400 mt-1">Try adjusting your search terms</div>
                                    </div>

                                    <!-- Selected product display -->
                                    <div
                                        v-if="selectedProduct"
                                        class="mt-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-sm"
                                    >
                                        <div class="flex items-start space-x-4">
                                            <div class="flex-shrink-0">
                                                <img
                                                    v-if="selectedProduct.image_url"
                                                    :src="selectedProduct.image_url"
                                                    :alt="selectedProduct.name"
                                                    class="w-16 h-16 object-cover rounded-lg border-2 border-white shadow-md"
                                                    @error="handleImageError"
                                                />
                                                <div
                                                    v-else
                                                    class="w-16 h-16 bg-white rounded-lg border-2 border-white shadow-md flex items-center justify-center"
                                                >
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between">
                                                    <div>
                                                        <div
                                                            class="font-bold text-blue-900 text-lg"
                                                        >
                                                            {{ selectedProduct.name }}
                                                        </div>
                                                        <div
                                                            class="text-sm text-blue-700 mt-1"
                                                        >
                                                            <span class="font-medium">Barcode:</span>
                                                            <span class="font-mono bg-white px-2 py-1 rounded border">{{
                                                                selectedProduct.barcode
                                                            }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4 text-right">
                                                        <div class="text-xs font-medium text-blue-700 mb-1">CURRENT STOCK</div>
                                                        <div
                                                            :class="[
                                                                'inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold border-2',
                                                                selectedProduct.stock_quantity <= 10
                                                                    ? 'bg-red-50 text-red-700 border-red-200'
                                                                    : selectedProduct.stock_quantity <= 50
                                                                    ? 'bg-yellow-50 text-yellow-700 border-yellow-200'
                                                                    : 'bg-green-50 text-green-700 border-green-200'
                                                            ]"
                                                        >
                                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM9 12a1 1 0 112 0v3a1 1 0 11-2 0v-3z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ selectedProduct.stock_quantity }}
                                                        </div>
                                                        <div
                                                            v-if="selectedProduct.stock_quantity <= 10"
                                                            class="text-xs text-red-600 font-medium mt-1"
                                                        >
                                                            ⚠️ Low Stock!
                                                        </div>
                                                        <div
                                                            v-else-if="selectedProduct.stock_quantity <= 50"
                                                            class="text-xs text-yellow-600 font-medium mt-1"
                                                        >
                                                            ⚡ Medium Stock
                                                        </div>
                                                        <div
                                                            v-else
                                                            class="text-xs text-green-600 font-medium mt-1"
                                                        >
                                                            ✅ Good Stock
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="clearSelection"
                                                class="text-blue-600 hover:text-red-600 hover:bg-red-50 p-2 rounded-full transition-colors duration-200"
                                                title="Clear selection"
                                            >
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.product_id"
                                />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="quantity" value="Quantity" />
                                <TextInput
                                    id="quantity"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    v-model="form.quantity"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.quantity"
                                />
                            </div>

                            <div class="mb-4">
                                <InputLabel
                                    for="transaction_date"
                                    value="Transaction Date"
                                />
                                <TextInput
                                    id="transaction_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.transaction_date"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.transaction_date"
                                />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <ButtonNew
                                    types="add"
                                    size="md"
                                    tooltips="Add new stock"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    @click="submit"
                                >
                                    Add
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
    </AdminAuthenticatedLayout>
</template>

<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, nextTick } from "vue";
import AdminAuthenticatedLayout from "@/Layouts/AdminAuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { notify } from "@/globalFunctions.js";
import ButtonNew from "@/Components/ButtonNew.vue";

const { products } = defineProps(["products"]);

const $page = usePage();

const form = useForm({
    product_id: "",
    quantity: "1",
    transaction_date: new Date().toISOString().split("T")[0],
});

const searchQuery = ref("");
const filteredProducts = ref(products);
const showDropdown = ref(false);
const selectedProduct = ref(null);
const highlightedIndex = ref(-1);

const isFormValid = computed(() => {
    return (
        !!form.product_id &&
        parseInt(form.quantity) >= 1 &&
        form.transaction_date
    );
});

const filterProducts = () => {
    const query = searchQuery.value.toLowerCase();
    filteredProducts.value = products.filter(
        (product) =>
            product.name.toLowerCase().includes(query) ||
            product.barcode.toLowerCase().includes(query)
    );
    highlightedIndex.value = -1;
    showDropdown.value = true;
};

const selectProduct = (product) => {
    selectedProduct.value = product;
    form.product_id = product.id;
    searchQuery.value = product.name;
    showDropdown.value = false;
    highlightedIndex.value = -1;
};

const clearSelection = () => {
    selectedProduct.value = null;
    form.product_id = "";
    searchQuery.value = "";
    filteredProducts.value = products;
    highlightedIndex.value = -1;
};

const handleBlur = () => {
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
};

const handleKeydown = (event) => {
    if (!showDropdown.value) return;

    switch (event.key) {
        case "ArrowDown":
            event.preventDefault();
            highlightedIndex.value = Math.min(
                highlightedIndex.value + 1,
                filteredProducts.value.length - 1
            );
            break;
        case "ArrowUp":
            event.preventDefault();
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1);
            break;
        case "Enter":
            event.preventDefault();
            if (highlightedIndex.value >= 0) {
                selectProduct(filteredProducts.value[highlightedIndex.value]);
            }
            break;
        case "Escape":
            showDropdown.value = false;
            highlightedIndex.value = -1;
            break;
    }
};

const handleImageError = (event) => {
    // Hide the broken image and show placeholder
    event.target.style.display = 'none';
};

const resetForm = () => {
    form.reset();
    form.product_id = "";
    form.quantity = "1";
    form.transaction_date = new Date().toISOString().split("T")[0];
    form.clearErrors();
    searchQuery.value = "";
    selectedProduct.value = null;
    filteredProducts.value = products;
    highlightedIndex.value = -1;
};

const submit = async () => {
    try {
        const response = await axios.post(
            route("admin.stock-in.store"),
            form.data()
        );

        notify("Stock-in saved successfully.", "success");
        form.reset();
    } catch (error) {
        if (error.response && error.response.data.errors) {
            const errorMessages = Object.values(error.response.data.errors)
                .flat()
                .join("<br>");
            notify(errorMessages, "error");
        } else {
            notify("Something went wrong. Please try again later.", "error");
        }
    }
};
</script>
