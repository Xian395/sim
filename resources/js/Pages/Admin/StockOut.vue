<template>
    <AdminAuthenticatedLayout>
        <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6"
                    >
                        <h2 class="text-2xl font-black text-blue-700">
                            Stock Out
                        </h2>
                    </div>
                    <div class="p-6">
                        <div
                            v-if="$page.props.flash?.success"
                            class="mb-4 text-green-600"
                        >
                            {{ $page.props.flash.success }}
                        </div>
                        <div
                            v-if="$page.props.flash?.error"
                            class="mb-4 text-red-600"
                        >
                            {{ $page.props.flash.error }}
                        </div>
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
                                        placeholder="Search and select product by name or item code..."
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @input="filterProducts"
                                        @focus="showDropdown = true"
                                        @blur="handleBlur"
                                        @keydown="handleKeydown"
                                        autocomplete="off"
                                    />

                                    <div
                                        v-if="
                                            showDropdown &&
                                            filteredProducts.length > 0
                                        "
                                        class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-xl max-h-60 overflow-auto"
                                        style="
                                            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1),
                                                0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
                                                'px-4 py-2 cursor-pointer hover:bg-gray-100',
                                                {
                                                    'bg-gray-100':
                                                        index ===
                                                        highlightedIndex,
                                                },
                                            ]"
                                        >
                                            <div class="font-bold">
                                                {{ product.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ product.barcode }} | Stock:
                                                {{ product.stock_quantity }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="
                                            showDropdown &&
                                            filteredProducts.length === 0 &&
                                            searchQuery
                                        "
                                        class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-xl p-4 text-gray-500"
                                        style="
                                            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1),
                                                0 4px 6px -2px rgba(0, 0, 0, 0.05);
                                        "
                                    >
                                        No products found
                                    </div>

                                    <div
                                        v-if="selectedProduct"
                                        class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-md"
                                    >
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <div>
                                                <div
                                                    class="font-bold text-blue-900"
                                                >
                                                    {{ selectedProduct.name }}
                                                </div>
                                                <div
                                                    class="text-sm text-blue-700"
                                                >
                                                    Barcode:
                                                    {{
                                                        selectedProduct.barcode
                                                    }}
                                                    | Current Stock:
                                                    {{
                                                        selectedProduct.stock_quantity
                                                    }}
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="clearSelection"
                                                class="text-blue-600 hover:text-blue-800"
                                            >
                                                ✕
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
                                    :max="maxQuantity"
                                    class="mt-1 block w-full"
                                    v-model.number="form.quantity"
                                    required
                                />
                                <div
                                    v-if="selectedProduct"
                                    class="mt-1 text-sm text-gray-600"
                                >
                                    Available stock:
                                    {{ selectedProduct.stock_quantity }}
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.quantity"
                                />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="reason" value="Reason" />
                                <select
                                    id="reason"
                                    v-model="form.reason"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Select Reason</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="expired">Expired</option>
                                    <option value="lost">Lost</option>
                                    <option value="stolen">Stolen</option>
                                    <option value="returned">
                                        Returned to Supplier
                                    </option>
                                    <option value="promotional">
                                        Promotional Use
                                    </option>
                                    <option value="sample">
                                        Sample/Testing
                                    </option>
                                    <option value="adjustment">
                                        Inventory Adjustment
                                    </option>
                                    <option value="other">Other</option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.reason"
                                />
                            </div>

                            <div class="mb-4">
                                <InputLabel
                                    for="notes"
                                    value="Notes (Optional)"
                                />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    rows="3"
                                    placeholder="Additional details about the stock removal..."
                                ></textarea>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.notes"
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
                                    types="remove"
                                    size="md"
                                    tooltips="Remove stock"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    @click="submit"
                                >
                                    Remove
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
import { computed, ref } from "vue";
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
    quantity: 1,
    reason: "",
    notes: "",
    transaction_date: new Date().toISOString().split("T")[0],
});

const searchQuery = ref("");
const filteredProducts = ref(products);
const showDropdown = ref(false);
const selectedProduct = ref(null);
const highlightedIndex = ref(-1);
const maxQuantity = ref(null);

const updateMaxQuantity = () => {
    if (selectedProduct.value) {
        maxQuantity.value = selectedProduct.value.stock_quantity;
        if (form.quantity > maxQuantity.value) {
            form.quantity = Math.min(form.quantity, maxQuantity.value);
        }
    } else {
        maxQuantity.value = null;
    }
};

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
    updateMaxQuantity();
};

const clearSelection = () => {
    selectedProduct.value = null;
    form.product_id = "";
    searchQuery.value = "";
    filteredProducts.value = products;
    highlightedIndex.value = -1;
    maxQuantity.value = null;
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

// const submit = () => {
//   form.post(route('admin.stock-out.store'), {
//     onSuccess: () => {
//       form.reset();
//       clearSelection();
//     },
//     onError: (errors) => console.log('Validation errors:', errors),
//   });
// };

const submit = async () => {
    try {
        const response = await axios.post(
            route("admin.stock-out.store"),
            form.data()
        );

        notify("Stock-out saved successfully.", "success");
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
