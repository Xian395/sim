<template>
    <AdminAuthenticatedLayout>
        <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6"
                    >
                        <h2 class="text-2xl font-black text-blue-700">
                            Brand Management
                        </h2>

                        <ButtonNew
                            types="add"
                            size="md"
                            tooltips="Add New Brand"
                            @click="
                                $inertia.visit(route('admin.brands.create'))
                            "
                        >
                            Add
                        </ButtonNew>
                    </div>
                    <div class="p-6">
                        <DataTable
                            :data="brands"
                            :columns="tableColumns"
                            :empty-message="'No brands found'"
                             ref="dataTable"
                        >
                            <template #column-actions="{ item }">
                                <div class="flex justify-end space-x-2">
                                    <button
                                        @click="openProductsModal(item)"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 hover:bg-green-200 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                        title="View Products"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        {{ item.products_count }} Products
                                    </button>
                                    <ButtonNew
                                        types="edit"
                                        tooltips="Edit Brand"
                                        class="text-blue-600 hover:text-blue-900"
                                        @click="openEditModal(item)"
                                    />
                                    <ButtonNew
                                        types="delete"
                                        tooltips="Delete Brand"
                                        class="text-red-600 hover:text-red-900"
                                        @click="openDeleteModal(item)"
                                    />
                                </div>
                            </template>
                        </DataTable>

                        <!-- Pagination -->
                        <div v-if="brands.length > 0 && props.pagination.last_page > 1" class="mt-6 border-t border-gray-200 pt-6 space-y-4">
                            <!-- Results Info -->
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <label class="text-sm text-gray-700">Items per page:</label>
                                    <select
                                        v-model.number="itemsPerPage"
                                        @change="handleItemsPerPageChange"
                                        class="px-3 py-2 pr-8 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm appearance-none bg-white cursor-pointer"
                                    >
                                        <option :value="10">10</option>
                                        <option :value="20">20</option>
                                        <option :value="30">30</option>
                                        <option :value="50">50</option>
                                    </select>
                                </div>

                                <div class="text-sm text-gray-600">
                                    Showing {{ props.pagination.from }} to {{ props.pagination.to }} of {{ props.pagination.total }} results
                                </div>
                            </div>

                            <!-- Page Navigation -->
                            <div class="flex gap-2 justify-end">
                                <button
                                    v-if="props.pagination.current_page > 1"
                                    @click="handlePageChange(props.pagination.current_page - 1)"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition"
                                >
                                    Previous
                                </button>

                                <div class="flex gap-1 items-center">
                                    <template v-for="pageNum in getPaginationPages()" :key="pageNum">
                                        <span v-if="pageNum === '...'" class="px-2 py-2 text-gray-500">...</span>
                                        <button
                                            v-else
                                            @click="handlePageChange(pageNum)"
                                            :class="[
                                                'px-3 py-2 rounded transition',
                                                pageNum === props.pagination.current_page
                                                    ? 'bg-blue-500 text-white font-semibold'
                                                    : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                                            ]"
                                        >
                                            {{ pageNum }}
                                        </button>
                                    </template>
                                </div>

                                <button
                                    v-if="props.pagination.current_page < props.pagination.last_page"
                                    @click="handlePageChange(props.pagination.current_page + 1)"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
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

        <Modal :show="showEditModal" @close="closeEditModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Edit Brand
                    </h3>
                    <button
                        @click="closeEditModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit">
                    <div class="mb-4">
                        <InputLabel for="edit_name" value="Brand Name" />
                        <TextInput
                            id="edit_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.name"
                            required
                        />
                        <InputError class="mt-2" :message="editForm.errors.name" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="edit_description" value="Description (Optional)" />
                        <textarea
                            id="edit_description"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            rows="3"
                            v-model="editForm.description"
                        ></textarea>
                        <InputError class="mt-2" :message="editForm.errors.description" />
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <div class="flex justify-end space-x-2 pt-4">
                            <ButtonNew
                                types="cancel"
                                size="md"
                                tooltips="Cancel and return to brand list"
                                @click="closeEditModal"
                            >
                                Cancel
                            </ButtonNew>

                            <ButtonNew
                                types="save"
                                size="md"
                                tooltips="Update brand"
                                :class="{ 'opacity-50': editForm.processing }"
                                :disabled="editForm.processing"
                                @click="submitEdit"
                            >
                                {{ editForm.processing ? "Updating..." : "Save" }}
                            </ButtonNew>
                        </div>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-4 md:p-5 text-center">
                <svg
                    class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 20 20"
                >
                    <path
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                </svg>
                <h3 class="mb-5 text-lg font-normal text-black dark:text-gray-400">
                    Are you sure you want to delete the brand <strong>"{{ brandToDelete?.name }}"</strong>?
                    <br>
                    <span class="text-red-600">You won't be able to revert this!</span>
                    <br>
                    <span v-if="brandToDelete?.products_count > 0" class="text-red-600">
                        This brand has {{ brandToDelete.products_count }} product(s) associated with it.
                    </span>
                </h3>
                <button
                    @click="confirmDelete"
                    type="button"
                    class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center"
                >
                    Yes, I'm sure
                </button>
                <button
                    @click="closeDeleteModal"
                    type="button"
                    class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                >
                    No, cancel
                </button>
            </div>
        </Modal>

        <!-- Products Modal -->
        <Modal :show="showProductsModal" @close="closeProductsModal" max-width="4xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Products for Brand: <span class="text-blue-600">{{ selectedBrand?.name }}</span>
                    </h3>
                    <button
                        @click="closeProductsModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div v-if="loadingProducts" class="flex justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <div v-else-if="brandProducts.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
                    <p class="mt-1 text-sm text-gray-500">This brand has no products assigned to it yet.</p>
                </div>

                <div v-else class="overflow-hidden space-y-4">
                    <!-- Pagination Controls (Top) -->
                    <div v-if="brandProducts.length > 0 && productsPagination.last_page > 1" class="flex justify-between items-center border-b border-gray-200 pb-4">
                        <div class="flex items-center gap-3">
                            <label class="text-sm text-gray-700">Items per page:</label>
                            <select
                                v-model.number="productsPerPage"
                                @change="handleProductsItemsPerPageChange"
                                class="px-3 py-2 pr-8 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm appearance-none bg-white cursor-pointer"
                            >
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="20">20</option>
                                <option :value="50">50</option>
                            </select>
                        </div>

                        <div class="text-sm text-gray-600">
                            Showing {{ productsPagination.from }} to {{ productsPagination.to }} of {{ productsPagination.total }} products
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categories</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in paginatedBrandProducts" :key="product.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-center">
                                            <div class="w-10 h-10 rounded-lg overflow-hidden shadow-sm">
                                                <img
                                                    v-if="product.image_url"
                                                    :src="product.image_url"
                                                    :alt="product.name"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div v-else class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                        <div v-if="product.description" class="text-sm text-gray-500 truncate max-w-xs">{{ product.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.item_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ parseFloat(product.price).toFixed(2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'text-red-600': product.stock_quantity === 0,
                                            'text-yellow-600': product.stock_quantity > 0 && product.stock_quantity < 11,
                                            'text-green-600': product.stock_quantity >= 11
                                        }" class="text-sm font-medium">
                                            {{ product.stock_quantity || 0 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="category in product.categories"
                                                :key="category.id"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                                            >
                                                {{ category.name }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls (Bottom) -->
                    <div v-if="brandProducts.length > 0 && productsPagination.last_page > 1" class="border-t border-gray-200 pt-4 space-y-4">
                        <div class="flex gap-2 justify-end">
                            <button
                                v-if="productsPagination.current_page > 1"
                                @click="handleProductsPageChange(productsPagination.current_page - 1)"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition"
                            >
                                Previous
                            </button>

                            <div class="flex gap-1 items-center">
                                <template v-for="pageNum in getProductsPaginationPages()" :key="pageNum">
                                    <span v-if="pageNum === '...'" class="px-2 py-2 text-gray-500">...</span>
                                    <button
                                        v-else
                                        @click="handleProductsPageChange(pageNum)"
                                        :class="[
                                            'px-3 py-2 rounded transition',
                                            pageNum === productsPagination.current_page
                                                ? 'bg-blue-500 text-white font-semibold'
                                                : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                                        ]"
                                    >
                                        {{ pageNum }}
                                    </button>
                                </template>
                            </div>

                            <button
                                v-if="productsPagination.current_page < productsPagination.last_page"
                                @click="handleProductsPageChange(productsPagination.current_page + 1)"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </AdminAuthenticatedLayout>
</template>

<script setup>
import {  router, usePage, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AdminAuthenticatedLayout from "@/Layouts/AdminAuthenticatedLayout.vue";
import { notify } from "@/globalFunctions.js";
import ButtonNew from "@/Components/ButtonNew.vue";
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import DataTable from "@/Components/DataTable.vue";

const props = defineProps({
    brands: Array,
    pagination: {
        type: Object,
        default: () => ({
            total: 0,
            per_page: 10,
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0
        })
    }
});

const $page = usePage();
const dataTable = ref();
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedBrand = ref(null);
const brandToDelete = ref(null);
const itemsPerPage = ref(props.pagination.per_page);

// Products Modal
const showProductsModal = ref(false);
const brandProducts = ref([]);
const loadingProducts = ref(false);
const productsPerPage = ref(10);
const productsCurrentPage = ref(1);
const productsPagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0
});

const tableColumns = computed(() => [
    {
        key: 'name',
        label: 'BRAND NAME',
        type: 'text',
        align: 'left'
    },
    {
        key: 'description',
        label: 'DESCRIPTION',
        type: 'text',
        align: 'left'
    },
    {
        key: 'products_count',
        label: 'PRODUCTS',
        type: 'text',
        align: 'center'
    },
    {
        key: 'actions',
        label: 'ACTIONS',
        type: 'custom',
        align: 'right'
    }
]);

const getPaginationPages = () => {
    const pages = [];
    const current = props.pagination.current_page;
    const last = props.pagination.last_page;

    if (last <= 5) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1);

        if (current <= 2) {
            pages.push(2);
        } else if (current > 2) {
            pages.push('...');
        }

        const start = Math.max(3, current - 1);
        const end = Math.min(last - 2, current + 1);

        for (let i = start; i <= end; i++) {
            if (!pages.includes(i) && pages[pages.length - 1] !== '...') {
                pages.push(i);
            }
        }

        if (current < last - 2 && pages[pages.length - 1] !== '...') {
            pages.push('...');
        }

        if (current < last - 1) {
            pages.push(last - 1);
        }
        pages.push(last);
    }

    return pages.filter(p => p !== '...' || pages.indexOf('...') === pages.lastIndexOf('...'));
};

const handlePageChange = (page) => {
    router.get(route('admin.brands.index', {
        page: page,
        per_page: itemsPerPage.value
    }), {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleItemsPerPageChange = () => {
    router.get(route('admin.brands.index', {
        page: 1,
        per_page: itemsPerPage.value
    }), {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const paginatedBrandProducts = computed(() => {
    const total = brandProducts.value.length;
    const perPage = productsPerPage.value;
    const currentPage = productsCurrentPage.value;

    // Update pagination metadata
    const lastPage = Math.ceil(total / perPage);
    const from = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const to = Math.min(currentPage * perPage, total);

    productsPagination.value = {
        total,
        per_page: perPage,
        current_page: currentPage,
        last_page: lastPage,
        from,
        to
    };

    // Return paginated slice
    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    return brandProducts.value.slice(start, end);
});

const getProductsPaginationPages = () => {
    const pages = [];
    const current = productsPagination.value.current_page;
    const last = productsPagination.value.last_page;

    if (last <= 5) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1);

        if (current <= 2) {
            pages.push(2);
        } else if (current > 2) {
            pages.push('...');
        }

        const start = Math.max(3, current - 1);
        const end = Math.min(last - 2, current + 1);

        for (let i = start; i <= end; i++) {
            if (!pages.includes(i) && pages[pages.length - 1] !== '...') {
                pages.push(i);
            }
        }

        if (current < last - 2 && pages[pages.length - 1] !== '...') {
            pages.push('...');
        }

        if (current < last - 1) {
            pages.push(last - 1);
        }
        pages.push(last);
    }

    return pages.filter(p => p !== '...' || pages.indexOf('...') === pages.lastIndexOf('...'));
};

const handleProductsPageChange = (page) => {
    productsCurrentPage.value = page;
};

const handleProductsItemsPerPageChange = () => {
    productsCurrentPage.value = 1;
};

const editForm = useForm({
    name: '',
    description: '',
});

const openDeleteModal = (brand) => {
    brandToDelete.value = brand;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    brandToDelete.value = null;
};

const confirmDelete = async () => {
    if (!brandToDelete.value) return;

    try {
        await router.delete(
            route("admin.brands.destroy", brandToDelete.value.id),
            {
                onSuccess: () => {
                    reload();
                    notify("Brand deleted successfully!", "success");
                    closeDeleteModal();
                },
                onError: (errors) => {
                    let errorMessage = "Failed to delete brand. Please try again.";

                    if (errors && errors.brand) {
                        errorMessage = Array.isArray(errors.brand)
                            ? errors.brand[0]
                            : errors.brand;
                    }
                    else if ($page.props.flash?.error) {
                        errorMessage = $page.props.flash.error;
                    }
                    else if (errors && typeof errors === 'object') {
                        if (errors.message) {
                            errorMessage = errors.message;
                        } else if (errors.error) {
                            errorMessage = errors.error;
                        }
                    }

                    notify(errorMessage, "error");
                    closeDeleteModal();
                }
            }
        );
    } catch (error) {
        notify(
            "An unexpected error occurred while deleting the brand.",
            "error"
        );
        closeDeleteModal();
    }
};

const openEditModal = (brand) => {
    selectedBrand.value = brand;
    editForm.name = brand.name;
    editForm.description = brand.description || '';
    editForm.clearErrors();
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedBrand.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const reload = () => {
    router.reload({
        only: ["brands"],
        preserveState: true,
        preserveScroll: true,
    });
};

const submitEdit = async () => {
    try {
        const response = await axios.put(`/admin/brands/${selectedBrand.value.id}`, {
            name: editForm.name,
            description: editForm.description,
            _method: 'PUT'
        });

        closeEditModal();
        reload();
        notify('Brand updated successfully', 'success');
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
        } else {
            notify('Failed to update brand', 'error');
        }
    }
};

// Products Modal Functions
const openProductsModal = async (brand) => {
    selectedBrand.value = brand;
    showProductsModal.value = true;
    loadingProducts.value = true;
    brandProducts.value = [];

    try {
        const response = await axios.get(`/admin/brands/${brand.id}/products`);
        brandProducts.value = response.data.products;
    } catch (error) {
        console.error('Error loading brand products:', error);
        notify('Failed to load brand products', 'error');
    } finally {
        loadingProducts.value = false;
    }
};

const closeProductsModal = () => {
    showProductsModal.value = false;
    selectedBrand.value = null;
    brandProducts.value = [];
    loadingProducts.value = false;
    productsCurrentPage.value = 1;
    productsPerPage.value = 10;
};

</script>