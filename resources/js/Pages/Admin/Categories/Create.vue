<template>
  <AdminAuthenticatedLayout>

    <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
           <div class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6">
              <h2 class="text-2xl font-black text-blue-700">Add New Category</h2>
              <ButtonNew
                types="back"
                size="md"
                tooltips="return to category list"
                @click="$inertia.visit(route('admin.categories.index'))"
              >
                Back
              </ButtonNew>
          </div>
          <div class="p-6">
           
            <form @submit.prevent="submit">
              <div class="mt-4">
                <InputLabel for="name" value="Category Name" />
                <TextInput
                  id="name"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.name"
                  required
                />
                <InputError class="mt-2" :message="form.errors.name" />
              </div>

              <div class="flex items-center justify-end mt-4">
               
                <ButtonNew
                  types="save"
                  size="md"
                  tooltips="Save category"
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
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { notify } from "@/globalFunctions.js";
import ButtonNew from "@/Components/ButtonNew.vue";



const $page = usePage();

const form = useForm({
  name: '',
});

const submit = async () => {
  try {
    const response = await axios.post(route('admin.categories.store'), form.data());
    
    notify('Product saved successfully.', 'success');
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