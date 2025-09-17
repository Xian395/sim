<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

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
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Reset Password</h1>
                <p class="text-gray-500 text-sm">
                    Forgot your password? No problem. Just let us know your email
                    address and we will email you a password reset link that will allow
                    you to choose a new one.
                </p>
            </div>

            <div
                v-if="status"
                class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-sm font-medium text-green-600"
            >
                {{ status }}
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
                        {{ form.processing ? "Sending Reset Link..." : "Email Password Reset Link" }}
                    </div>
                </button>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <Link
                    :href="route('login')"
                    class="text-blue-600 hover:text-blue-800 font-medium transition text-sm"
                >
                    ← Back to Login
                </Link>
            </div>

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
