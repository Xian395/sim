<template>
  <AdminAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>
    </template>

    <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div
            class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6"
          >
            <h2 class="text-2xl font-black text-blue-700">
              User Management
            </h2>

            <ButtonNew
              types="add"
              size="md"
              tooltips="Add new user"
              @click="$inertia.visit(route('admin.users.create'))"
            >
              Add
            </ButtonNew>
          </div>
          <div class="p-6">
            <DataTable
              :data="users"
              :columns="tableColumns"
              item-key="id"
              empty-message="No users found"
            >
              <template #column-role="{ value }">
                <span 
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    value === 'admin' ? 'bg-red-600 text-white' : 'bg-blue-600 text-white'
                  ]"
                >
                  {{ value }}
                </span>
              </template>

              <template #column-actions="{ item }">
                <div class="flex justify-end space-x-2">
                  <template v-if="item.role === 'staff'">
                    <Link
                      :href="route('admin.users.transactions', item.id)"
                      class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm font-medium inline-flex items-center justify-center"
                      title="View Transactions"
                    >
                      Transactions
                    </Link>
                  </template>
                  <ButtonNew
                    types="edit"
                    tooltips="Edit User"
                    class="text-blue-600 hover:text-blue-900"
                    @click="openEditModal(item)"
                  />
                  <ButtonNew
                    types="delete"
                    tooltips="Delete User"
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
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
          Unauthorized: You do not have access to User Management.
        </div>
      </div>
    </div>

    <Modal :show="showEditModal" @close="closeEditModal" max-width="lg">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit User</h3>
        
        <form @submit.prevent="submitEdit">
          <div>
            <InputLabel for="name" value="Name" />
            <TextInput
              id="name"
              type="text"
              class="mt-1 block w-full"
              v-model="editForm.name"
              required
              autofocus
            />
            <InputError class="mt-2" :message="editForm.errors.name" />
          </div>

          <div class="mt-4">
            <InputLabel for="email" value="Email" />
            <TextInput
              id="email"
              type="email"
              class="mt-1 block w-full"
              v-model="editForm.email"
              required
            />
            <InputError class="mt-2" :message="editForm.errors.email" />
          </div>

          <div class="mt-4">
            <InputLabel for="role" value="Role" />
            <select
              id="role"
              v-model="editForm.role"
              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
              required
            >
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
            </select>
            <InputError class="mt-2" :message="editForm.errors.role" />
          </div>

          <div class="mt-4">
            <InputLabel for="password" value="Password (Leave blank to keep unchanged)" />
            <TextInput
              id="password"
              type="password"
              class="mt-1 block w-full"
              v-model="editForm.password"
            />
            <InputError class="mt-2" :message="editForm.errors.password" />
          </div>

          <div class="mt-4">
            <InputLabel for="password_confirmation" value="Confirm Password" />
            <TextInput
              id="password_confirmation"
              type="password"
              class="mt-1 block w-full"
              v-model="editForm.password_confirmation"
            />
            <InputError class="mt-2" :message="editForm.errors.password_confirmation" />
          </div>

          <div class="flex items-center justify-end space-x-2">
            <div class="flex justify-end space-x-2 pt-4">
              <ButtonNew
                types="cancel"
                size="md"
                tooltips="Cancel and return to users list"
                @click="closeEditModal"
              >
                Cancel
              </ButtonNew>

              <ButtonNew
                types="save"
                size="md"
                tooltips="Update user"
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
          Are you sure you want to delete the user <strong>"{{ userToDelete?.name }}"</strong>?
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
import { ref, computed } from 'vue';
import { usePage, router, useForm, Link } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import ButtonNew from "@/Components/ButtonNew.vue";
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import DataTable from '@/Components/DataTable.vue';
import { notify } from "@/globalFunctions.js";

const $page = usePage();
const props = defineProps({
  users: Array,
});

const showEditModal = ref(false);
const showDeleteModal = ref(false);
const currentUser = ref(null);
const userToDelete = ref(null);

const tableColumns = computed(() => [
  {
    key: 'name',
    label: 'Name',
    type: 'text',
    align: 'left'
  },
  {
    key: 'email',
    label: 'Email',
    type: 'text',
    align: 'left'
  },
  {
    key: 'role',
    label: 'Role',
    type: 'badge',
    align: 'left',
    badgeColors: {
      'admin': 'bg-purple-100 text-purple-800',
      'staff': 'bg-blue-100 text-blue-800'
    }
  },
  {
    key: 'actions',
    label: 'Actions',
    type: 'custom',
    align: 'right'
  }
]);

const editForm = useForm({
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: '',
});

const openEditModal = (user) => {
  currentUser.value = user;
  editForm.name = user.name;
  editForm.email = user.email;
  editForm.role = user.role;
  editForm.password = '';
  editForm.password_confirmation = '';
  editForm.clearErrors();
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  currentUser.value = null;
  editForm.reset();
  editForm.clearErrors();
};

const submitEdit = () => {
  editForm.put(route('admin.users.update', currentUser.value.id), {
    onSuccess: () => {
      notify('User updated successfully', 'success');
      closeEditModal();
    },
    onError: () => {
      notify('Failed to update user. Please check the form and try again.', 'error');
    }
  });
};



const openDeleteModal = (user) => {
  userToDelete.value = user;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  userToDelete.value = null;
};

const confirmDelete = async () => {
  if (!userToDelete.value) return;

  try {
    await router.delete(
      route("admin.users.destroy", userToDelete.value.id),
      {
        onSuccess: () => {
          notify("User deleted successfully", "success");
          closeDeleteModal();
        },
        onError: (errors) => {
          notify("Failed to delete user. Please try again.", "error");
          closeDeleteModal();
        }
      }
    );
  } catch (error) {
    notify("An unexpected error occurred.", "error");
    closeDeleteModal();
  }
};

</script>