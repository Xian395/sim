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
                    <div class="p-6">
                        <DataTable 
                            :data="categories" 
                            :columns="tableColumns"
                            :empty-message="'No categories found'"
                             ref="dataTable"
                        >
                            <template #column-actions="{ item }">
                                <div class="flex justify-end space-x-2">
                                    <ButtonNew
                                        types="edit"
                                        tooltips="Edit Category"
                                        class="text-blue-600 hover:text-blue-900"
                                        @click="openEditModal(item)"
                                    />
                                    <ButtonNew
                                        types="delete"
                                        tooltips="Delete Category"
                                        class="text-red-600 hover:text-red-900"
                                        @click="openDeleteModal(item)"
                                    />
                                </div>
                            </template>
                        </DataTable>
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
                    Are you sure you want to delete the category <strong>"{{ categoryToDelete?.name }}"</strong>?
                    <br>
                    <span class="text-red-600">You won't be able to revert this!</span>
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

defineProps({
    categories: Array,
});

const $page = usePage();
const dataTable = ref();
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedCategory = ref(null);
const categoryToDelete = ref(null);

const tableColumns = computed(() => [
    {
        key: 'name',
        label: 'CATEGORY NAME',
        type: 'text',
        align: 'left'
    },
    {
        key: 'actions',
        label: 'ACTIONS',
        type: 'custom',
        align: 'right'
    }
]);

const editForm = useForm({
    name: '',
});

const openDeleteModal = (category) => {
    categoryToDelete.value = category;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    categoryToDelete.value = null;
};

const confirmDelete = async () => {
    if (!categoryToDelete.value) return;

    try {
        await router.delete(
            route("admin.categories.destroy", categoryToDelete.value.id),
            {
                onSuccess: () => {
                    reload();
                    notify("Category deleted successfully!", "success");
                    closeDeleteModal();
                },
                onError: (errors) => {
                    
                    let errorMessage = "Failed to delete category. Please try again.";
                    
                    if (errors && errors.category) {
                        errorMessage = Array.isArray(errors.category) 
                            ? errors.category[0] 
                            : errors.category;
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
            "An unexpected error occurred while deleting the category.",
            "error"
        );
        closeDeleteModal();
    }
};



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
        preserveScroll: true,
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
</script>