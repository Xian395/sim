<template>
  <AdminAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add New Staff</h2>
    </template>

    <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6">
              <h2 class="text-2xl font-black text-blue-700">Add New User</h2>
              <ButtonNew
                types="back"
                size="md"
                tooltips="return to user list"
                @click="$inertia.visit(route('admin.users.index'))"
              >
                Back
              </ButtonNew>
          </div>
          <div class="p-6">
           
            <form @submit.prevent="submit">
              <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                  id="name"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.name"
                  required
                  autofocus
                />
                <InputError class="mt-2" :message="form.errors.name" />
              </div>

              <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                  id="email"
                  type="email"
                  class="mt-1 block w-full"
                  v-model="form.email"
                  required
                />
                <InputError class="mt-2" :message="form.errors.email" />
              </div>

              <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                  id="password"
                  type="password"
                  class="mt-1 block w-full"
                  v-model="form.password"
                  required
                />
                <InputError class="mt-2" :message="form.errors.password" />
              </div>

              <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                  id="password_confirmation"
                  type="password"
                  class="mt-1 block w-full"
                  v-model="form.password_confirmation"
                  required
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
              </div>

              <div class="mt-4">
                <InputLabel for="role" value="Role" />
                <select
                  id="role"
                  v-model="form.role"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  required
                >
                  <option value="staff">Staff</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
              </div>

       
               <div class="flex items-center justify-end mt-4">
               
                <ButtonNew
                  types="save"
                  size="md"
                  tooltips="Save user"
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
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
          Unauthorized: You do not have access to this page.
        </div>
      </div>
    </div>
  </AdminAuthenticatedLayout>
</template>

<script setup>
import { useForm, usePage, Link } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import ButtonNew from "@/Components/ButtonNew.vue";
import { notify } from "@/globalFunctions.js";

const $page = usePage();
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'staff',
});


const submit = async () => {
  try {
    const response = await axios.post(route('admin.users.store'), form.data());
    
    notify('User created successfully.', 'success');
    form.reset();
    
  } catch (error) {
    if (error.response && error.response.data.errors) {
      const errorMessages = Object.values(error.response.data.errors).flat().join('<br>');
      notify(errorMessages, 'error');
    } else {
      notify('Something went wrong. Please try again later.', 'error');
    }
  }
};


</script>