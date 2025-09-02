<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import ButtonNew from "@/Components/ButtonNew.vue";
import { ref } from "vue";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route("login"), {
        onFinish: () => {
            form.reset("password");
        },
        onError: (errors) => {
            console.log("Login errors:", errors);
        },
        onSuccess: () => {
            console.log("Login successful");
        },
    });
};
</script> 

<template>
    <Head title="Login" />

    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-100 via-blue-50 to-sky-100 p-6 relative overflow-hidden"
    >
        <div class="absolute inset-0">
            <div
                class="absolute -top-32 -right-32 w-96 h-96 bg-indigo-200 rounded-full blur-3xl opacity-40"
            ></div>
            <div
                class="absolute -bottom-32 -left-32 w-96 h-96 bg-sky-200 rounded-full blur-3xl opacity-40"
            ></div>
        </div>

        <div
            class="w-full max-w-md relative z-10 bg-white/80 backdrop-blur-2xl rounded-3xl shadow-2xl border border-white/40 p-8"
        >
            <div class="text-center mb-6 relative">
                <div class="relative h-32 flex items-center justify-center">
                    <img
                        src="/logo1.png"
                        alt="Company Logo"
                        class="h-40 w-auto relative z-10"
                        style="margin-top: -1rem; margin-bottom: -1rem;"
                    />
                </div>
                <p class="text-gray-500 mt-2 text-sm">
                    Sign in to continue managing your operations
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Email -->
                <div class="relative">
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        class="peer w-full px-4 pt-6 pb-2 text-gray-900 placeholder-transparent border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-all"
                        placeholder="Email Address"
                    />
                    <label
                        for="email"
                        class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-blue-600"
                    >
                        Email Address
                    </label>
                    <InputError
                        class="mt-1 text-red-500 text-sm"
                        :message="form.errors.email"
                    />
                </div>

                <!-- Password -->
                <div class="relative">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        class="peer w-full px-4 pt-6 pb-2 pr-12 text-gray-900 placeholder-transparent border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-all"
                        placeholder="Password"
                    />
                    <label
                        for="password"
                        class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-blue-600"
                    >
                        Password
                    </label>
                    <!-- Toggle -->
                    <button
                        type="button"
                        @click="togglePasswordVisibility"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-blue-600 transition-colors"
                    >
                        <svg
                            v-if="!showPassword"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
                                 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 
                                 0-8.268-2.943-9.543-7a9.97 9.97 0 
                                 011.563-3.029m5.858.908a3 3 0 
                                 114.243 4.243M9.878 9.878l4.242 
                                 4.242M9.878 9.878L3 3m6.878 
                                 6.878L21 21"
                            />
                        </svg>
                    </button>
                    <InputError
                        class="mt-1 text-red-500 text-sm"
                        :message="form.errors.password"
                    />
                </div>

                <!-- Options -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center">
                        <Checkbox
                            name="remember"
                            v-model:checked="form.remember"
                            class="rounded text-blue-600 border-gray-300 focus:ring-blue-500"
                        />
                        <span class="ml-2 text-gray-600">Remember me</span>
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-blue-600 hover:text-blue-800 font-medium transition"
                    >
                        Forgot password?
                    </Link>
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg"
                    :class="[
                        form.processing
                            ? '!bg-gray-400 !from-gray-400 !to-gray-500 cursor-not-allowed opacity-75 !transform-none'
                            : '',
                    ]"
                    :disabled="form.processing"
                >
                    <div class="flex items-center justify-center">
                        <svg
                            v-if="form.processing"
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 
                                   0 0 5.373 0 12h4zm2 
                                   5.291A7.962 7.962 0 014 
                                   12H0c0 3.042 1.135 5.824 
                                   3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        {{ form.processing ? "Signing in..." : "Sign In" }}
                    </div>
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <div class="flex justify-center items-center space-x-2">
                    <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                    <span class="text-xs text-gray-400">Secure Connection</span>
                </div>
            </div>
        </div>
    </div>
</template>