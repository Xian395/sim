<template>
    <AdminAuthenticatedLayout>
        <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
          
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6"
                    >
                         <h2 class="text-2xl font-black text-blue-700">
                            Category Management
                        </h2>

                        <ButtonNew
                            types="add"
                            size="md"
                            tooltips="Add New Category"
                            @click="
                                $inertia.visit(route('admin.categories.create'))
                            "
                        >
                            Add
                        </ButtonNew>
                    </div>

                    <!-- Filter Tabs and Search -->
                    <div class="px-6 pt-4 pb-2 space-y-4">
                        <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
                            <button
                                @click="filterCategories('active')"
                                :class="{
                                    'bg-white text-blue-600 shadow-sm': props.filter === 'active',
                                    'text-gray-500 hover:text-gray-700': props.filter !== 'active'
                                }"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                            >
                                Active Categories
                                <span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    {{ activeCategoryCount }}
                                </span>
                            </button>
                            <button
                                @click="filterCategories('archived')"
                                :class="{
                                    'bg-white text-orange-600 shadow-sm': props.filter === 'archived',
                                    'text-gray-500 hover:text-gray-700': props.filter !== 'archived'
                                }"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                            >
                                Archived Categories
                                <span class="ml-2 bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                                    {{ archivedCategoryCount }}
                                </span>
                            </button>
                            <button
                                @click="filterCategories('all')"
                                :class="{
                                    'bg-white text-gray-900 shadow-sm': props.filter === 'all',
                                    'text-gray-500 hover:text-gray-700': props.filter !== 'all'
                                }"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                            >
                                All Categories
                                <span class="ml-2 bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                    {{ totalCategoryCount }}
                                </span>
                            </button>
                        </div>

                        <!-- Search Bar -->
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                @input="handleSearch"
                                type="text"
                                placeholder="Search categories by name..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                    <div class="p-6">
                        <DataTable
                            :data="categories"
                            :columns="tableColumns"
                            :empty-message="'No categories found'"
                             ref="dataTable"
                        >
                            <template #column-status="{ item }">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium': item.status === 'active',
                                        'bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium': item.status === 'inactive'
                                    }"
                                >
                                    {{ item.status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </template>
                            <template #column-actions="{ item }">
                                <div class="flex justify-end space-x-2">
                                    <ButtonNew
                                        types="edit"
                                        tooltips="Edit Category"
                                        class="text-blue-600 hover:text-blue-900"
                                        @click="openEditModal(item)"
                                    />
                                    <ButtonNew
                                        :types="item.status === 'active' ? 'archive' : 'save'"
                                        :tooltips="item.status === 'active' ? 'Archive Category' : 'Activate Category'"
                                        :class="item.status === 'active' ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900'"
                                        @click="toggleStatus(item)"
                                    />
                                </div>
                            </template>
                        </DataTable>

                        <!-- Pagination -->
                        <div v-if="categories.length > 0 && props.pagination.last_page > 1" class="mt-6 border-t border-gray-200 pt-6 space-y-4">
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
                        Edit Category
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
                        <InputLabel for="edit_name" value="Category Name" />
                        <TextInput
                            id="edit_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.name"
                            required
                        />
                        <InputError class="mt-2" :message="editForm.errors.name" />
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                    

                        <div class="flex justify-end space-x-2 pt-4">
                        <ButtonNew
                            types="cancel"
                            size="md"
                            tooltips="Cancel and return to category list"
                            @click="closeEditModal"
                        >
                            Cancel
                        </ButtonNew>

                        <ButtonNew
                            types="save"
                            size="md"
                            tooltips="Update category"
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
    categories: Array,
    filter: {
        type: String,
        default: 'active'
    },
    counts: {
        type: Object,
        default: () => ({})
    },
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
const selectedCategory = ref(null);
const itemsPerPage = ref(props.pagination.per_page);
const currentPage = ref(props.pagination.current_page);
const searchQuery = ref('');

const activeCategoryCount = computed(() => props.counts.active || 0);
const archivedCategoryCount = computed(() => props.counts.archived || 0);
const totalCategoryCount = computed(() => props.counts.total || 0);

const tableColumns = computed(() => [
    {
        key: 'name',
        label: 'CATEGORY NAME',
        type: 'text',
        align: 'left'
    },
    {
        key: 'status',
        label: 'STATUS',
        type: 'custom',
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
    router.get(route('admin.categories.index', {
        filter: props.filter,
        page: page,
        per_page: itemsPerPage.value
    }), {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleItemsPerPageChange = () => {
    router.get(route('admin.categories.index', {
        filter: props.filter,
        page: 1,
        per_page: itemsPerPage.value
    }), {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const editForm = useForm({
    name: '',
});




const openEditModal = (category) => {
    selectedCategory.value = category;
    editForm.name = category.name;
    editForm.clearErrors();
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedCategory.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const reload = () => {
    router.reload({
        only: ["products", "categories"],
        preserveState: true,
        preserveScroll: true
    });
};

const submitEdit = async () => {
    try {
        const response = await axios.put(`/admin/categories/${selectedCategory.value.id}`, {
            name: editForm.name,
            _method: 'PUT'
        });

        closeEditModal();
        reload();
        notify('Category updated successfully', 'success');
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
        } else {
            notify('Failed to update category', 'error');
        }
    }
};

const toggleStatus = async (category) => {
    try {
        await router.patch(
            route("admin.categories.toggle-status", category.id),
            {},
            {
                onSuccess: () => {
                    reload();
                    const message = category.status === 'active'
                        ? 'Category deactivated successfully!'
                        : 'Category activated successfully!';
                    notify(message, "success");
                },
                onError: (errors) => {
                    notify("Failed to update category status. Please try again.", "error");
                }
            }
        );
    } catch (error) {
        notify("An unexpected error occurred while updating the category status.", "error");
    }
};

const filterCategories = (filter) => {
    router.get(route('admin.categories.index', {
        filter: filter,
        page: 1,
        per_page: itemsPerPage.value,
        search: searchQuery.value
    }), {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSearch = () => {
    router.get(route('admin.categories.index', {
        filter: props.filter,
        page: 1,
        per_page: itemsPerPage.value,
        search: searchQuery.value
    }), {}, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>