<!-- AuthenticatedLayout -->
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
            <div class="flex items-center justify-between p-3 border-b border-gray-200">
                <Link :href="route('staff.dashboard')" class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
</svg>
                    </div>
                    <span class="ml-3 text-xl font-semibold text-gray-900">Staff Panel</span>
                </Link>
                <button 
                    @click="sidebarOpen = false" 
                    class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 lg:hidden"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Sidebar Content -->
            <div v-if="$page.props.auth.user.role === 'staff'" class="h-full px-3 pb-4 overflow-y-auto bg-white">
                <ul class="space-y-2 font-medium mt-4">
                    <!-- Dashboard -->
                    <li>
                        <NavLink
                            :href="route('staff.dashboard')"
                            :active="route().current('staff.dashboard')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('staff.dashboard'),
                            }"
                        >
                            <svg 
                                class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700" 
                                :class="{
                                    'text-blue-700':
                                        route().current('staff.dashboard'),
                                }"
                                fill="currentColor" 
                                viewBox="0 0 20 20"
                            >
                                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                            </svg>
                            <span class="ml-3">Dashboard</span>
                        </NavLink>
                    </li>

                    <!-- Point of Sale -->
                    <li>
                        <NavLink
                            :href="route('staff.pos')"
                            :active="route().current('staff.pos')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('staff.pos'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('staff.pos'),
                                }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap">Point of Sale</span>
                            <span class="inline-flex items-center justify-center px-2 ml-3 text-xs font-medium text-blue-800 bg-blue-200 rounded-full">POS</span>
                        </NavLink>
                    </li>

                    <!-- Transactions -->
                    <li>
                        <NavLink
                            :href="route('staff.transactions')"
                            :active="route().current('staff.transactions')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                            :class="{
                                'bg-blue-100 text-blue-700':
                                    route().current('staff.transactions'),
                            }"
                        >
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-blue-700"
                                :class="{
                                    'text-blue-700':
                                        route().current('staff.transactions'),
                                }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap">Transactions</span>
                        </NavLink>
                    </li>

                    <!-- Divider -->
                    <li>
                        <div class="border-t border-gray-200 my-4"></div>
                    </li>

                    <!-- Quick Actions Section -->
                    <li>
                        <div class="flex items-center p-2 text-xs font-semibold text-gray-500 uppercase">
                            <span>Quick Actions</span>
                        </div>
                    </li>

                    <!-- New Sale (Quick Access to POS) -->
                    <li>
                        <NavLink
                            :href="route('staff.pos')"
                            class="flex items-center w-full p-2 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 group"
                        >
                            <svg class="flex-shrink-0 w-5 h-5 text-blue-500 transition duration-75 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="flex-1 ml-3 whitespace-nowrap text-blue-700">New Sale</span>
                        </NavLink>
                    </li>
                </ul>

                <!-- Sidebar Footer with User Info and Logout -->
                <div class="absolute bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200">
                    <!-- User Info -->
                    <div class="flex items-center p-2 mb-2 text-gray-700 rounded-lg">
                        <div class="flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full">
                            <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ $page.props.auth.user.name }}</p>
                            <p class="text-xs text-gray-500">Staff Member</p>
                        </div>
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
                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        <!-- Overlay for Mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Main Content -->
        <div class="lg:ml-64 min-h-screen bg-gray-200 relative">
            <!-- Decorative Header Background - Now covers navbar too -->
            <div class="absolute top-0 left-0 right-0 h-[200px] bg-[url('/8.png')] bg-top bg-no-repeat bg-[length:100%_auto] animate-bg-pulse rounded-b-2xl overflow-hidden z-0"></div>
            
            <!-- Top Navigation -->
            <nav>
                <div class="px-3 py-3 lg:px-5 lg:pl-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center justify-start">
                            <!-- Breadcrumb or Page Title can go here -->
                            <div class="hidden lg:flex items-center">
                                <h1 class="text-xl font-semibold text-gray-900">Staff Dashboard</h1>
                            </div>
                        </div>
                        
                        <!-- Quick Actions & User Dropdown -->
                        <div class="flex items-center space-x-3">
                            <!-- Quick POS Access Button -->
                            <Link 
                                :href="route('staff.pos')"
                                class="hidden sm:inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                New Sale
                            </Link>
                            
                            <!-- User Dropdown -->
                            <div class="flex items-center ml-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center text-sm bg-white rounded-full focus:ring-4 focus:ring-blue-300 shadow-md">
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
                                        <div class="px-4 py-3 text-sm text-gray-900">
                                            <div class="font-medium">{{ $page.props.auth.user.name }}</div>
                                            <div class="text-gray-500 truncate">{{ $page.props.auth.user.email }}</div>
                                            <!-- <div class="text-xs text-gray-400 mt-1">Staff Member</div> -->
                                        </div>
                                        <div class="border-t border-gray-100">
                                            <!-- <DropdownLink :href="route('profile.edit')">
                                                Profile Settings
                                            </DropdownLink> -->
                                            <DropdownLink :href="route('logout')" method="post" as="button">
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

            <main class="relative z-10 p-4 pt-2">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const $page = usePage();
const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);
</script>