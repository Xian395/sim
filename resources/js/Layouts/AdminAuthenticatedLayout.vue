<!-- AdminAuthenticatedLayout -->

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside
            :class="{
                'translate-x-0': sidebarOpen,
                '-translate-x-full': !sidebarOpen,
            }"
            class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform bg-white border-r border-gray-200 lg:translate-x-0"
            aria-label="Sidebar"
        >
            <!-- Sidebar Header -->
            <div
                class="flex items-center justify-between p-3 border-b border-gray-200"
            >
                <Link
                    :href="route('admin.dashboard')"
                    class="flex items-center"
                >
                   <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-lg">
    <svg
        class="w-5 h-5 text-blue-600"
        fill="currentColor"
        viewBox="0 0 20 20"
    >
        <path
            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"
        />
    </svg>
</div>
                    <span class="ml-3 text-xl font-semibold text-gray-900"
                        >Admin Panel</span
                    >
                </Link>
                <button
                    @click="sidebarOpen = false"
                    class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 lg:hidden"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Sidebar Content -->
            <div v-if="$page.props.auth.user.role === 'admin'" class="h-full px-3 pb-4 overflow-y-auto bg-white">
                <ul class="space-y-2 font-medium mt-4">
                    <!-- Dashboard -->
                    <li>
                        <NavLink
                            :href="route('admin.dashboard')"
                            :active="route().current('admin.dashboard')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('admin.dashboard'),
                            }"
                        >
                            <svg
                                class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('admin.dashboard'),
                                }"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"
                                />
                                <path
                                    d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"
                                />
                            </svg>
                            <span class="ml-3">Dashboard</span>
                        </NavLink>
                    </li>

                    <!-- Product Management -->
                    <li>
                        <NavLink
                            :href="route('admin.products.index')"
                            :active="route().current('admin.products.*')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('admin.products.*'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('admin.products.*'),
                                }"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM8 15a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap"
                                >Products</span
                            >
                        </NavLink>
                    </li>

                    <!-- Categories -->
                    <li>
                        <NavLink
                            :href="route('admin.categories.index')"
                            :active="route().current('admin.categories.*')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('admin.categories.*'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('admin.categories.*'),
                                }"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"
                                />
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap"
                                >Categories</span
                            >
                        </NavLink>
                    </li>

                    <!-- Brands -->
                    <li>
                        <NavLink
                            :href="route('admin.brands.index')"
                            :active="route().current('admin.brands.*')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('admin.brands.*'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('admin.brands.*'),
                                }"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4zM4 9a2 2 0 100 4h12a2 2 0 100-4H4zM4 15a2 2 0 100 4h12a2 2 0 100-4H4z"
                                />
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap"
                                >Brands</span
                            >
                        </NavLink>
                    </li>

                    <!-- Stock Management Section -->
                    <li>
                        <div
                            class="flex items-center p-2 text-xs font-semibold text-gray-500 uppercase mt-6"
                        >
                            <span>Stock Management</span>
                        </div>
                    </li>

                    <!-- Stock In -->
                    <li>
                        <NavLink
                            :href="route('admin.stock-in.index')"
                            :active="route().current('admin.stock-in.*')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('admin.stock-in.*'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('admin.stock-in.*'),
                                }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                                />
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap"
                                >Stock In</span
                            >
                        </NavLink>
                    </li>

                    <!-- Stock Out -->
                    <li>
                        <NavLink
                            :href="route('admin.stock-out.index')"
                            :active="route().current('admin.stock-out.*')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('admin.stock-out.*'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('admin.stock-out.*'),
                                }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16v-4a1 1 0 00-1-1H8a1 1 0 00-1 1v4m10 0l4 4m-4-4l-4 4m-6-8V4m0 0L3 8m4-4l4 4"
                                />
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap"
                                >Stock Out</span
                            >
                        </NavLink>
                    </li>

                    <!-- Reports Section -->
                    <li>
                        <div
                            class="flex items-center p-2 text-xs font-semibold text-gray-500 uppercase mt-6"
                        >
                            <span>Reports & Users</span>
                        </div>
                    </li>

                    <!-- Reports -->
                    <li>
                        <NavLink
                            :href="route('admin.logs.index')"
                            :active="route().current('admin.logs.*')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('admin.logs.*'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('admin.logs.*'),
                                }"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path
                                    fill-rule="evenodd"
                                    d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap"
                                >Reports & Logs</span
                            >
                        </NavLink>
                    </li>

                    <!-- User Management -->
                    <li>
                        <NavLink
                            :href="route('admin.users.index')"
                            :active="route().current('admin.users.*')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('admin.users.*'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('admin.users.*'),
                                }"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM9 13a6 6 0 000 12h2a6 6 0 000-12H9z"
                                />
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap"
                                >Users</span
                            >
                        </NavLink>
                    </li>
                </ul>

                <!-- Sidebar Footer -->
                <div
                    class="absolute bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200"
                >
                    <div class="flex items-center p-2 text-gray-700 rounded-lg">
                        <div
                            class="flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full"
                        >
                            <svg
                                class="w-4 h-4 text-gray-600"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <span class="ml-3 text-sm font-medium">{{
                            $page.props.auth.user.name
                        }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Menu Button -->
        <div class="fixed top-4 left-4 z-50 lg:hidden">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 bg-white shadow-lg"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>

        <!-- Overlay for Mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Main Content Container -->
        <div class="lg:ml-64 min-h-screen bg-gray-200 relative">
            <!-- Decorative Header Background - Now covers navbar too -->
            <div
                class="absolute top-0 left-0 right-0 h-[200px] bg-[url('/8.png')] bg-top bg-no-repeat bg-[length:100%_auto] animate-bg-pulse rounded-b-2xl overflow-hidden z-0"
            ></div>

            <!-- Top Navigation -->
            <nav>
                <div class="px-3 py-3 lg:px-5 lg:pl-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center justify-start">
                            <!-- Breadcrumb or Page Title can go here -->
                        </div>

                        <!-- User Dropdown -->
                        <div class="flex items-center">
                            <div class="flex items-center ml-3 relative">
                                <Dropdown align="right" width="50">
                                    <template #trigger>
                                        <button
                                            class="flex items-center text-sm bg-white rounded-full focus:ring-4 focus:ring-blue-300 shadow-md"
                                        >

                                         <span class="ml-6 text-sm font-medium flex items-center space-x-2 h-8">
                                            <span>{{ $page.props.auth.user.name }}</span>
                                            <svg
                                                class="w-4 h-4 text-gray-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </span>

                                        </button>
                                    </template>

                                    <template #content>
                                        <div
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            <div class="text-gray-500 truncate">
                                                {{
                                                    $page.props.auth.user.email
                                                }}
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-100">
                                            <!-- <DropdownLink
                                                :href="route('profile.edit')"
                                            >
                                                Profile Settings
                                            </DropdownLink> -->
                                            <DropdownLink
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                            >
                                                Sign out
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="relative z-10 p-4 pt-2">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";

const $page = usePage();
const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);
</script>
