<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const currentSlide = ref(0);
const isVisible = ref(false);
let slideInterval = null;

const systemModules = [
    {
        icon: '📦',
        title: 'Inventory Management',
        description: 'Real-time stock monitoring, automated reorder alerts, and product categorization'
    },
    {
        icon: '💰',
        title: 'POS System',
        description: 'Fast checkout processing, receipt generation, and payment tracking'
    },
    {
        icon: '📊',
        title: 'Sales Reports',
        description: 'Daily, weekly, and monthly sales analytics with profit margin tracking'
    },
    {
        icon: '👥',
        title: 'Staff Management',
        description: 'Employee access control, shift scheduling, and performance monitoring'
    },
    {
        icon: '📈',
        title: 'Business Analytics',
        description: 'Revenue insights, top-selling products, and customer behavior analysis'
    },
    {
        icon: '🔒',
        title: 'Security & Backup',
        description: 'Secure data encryption, automated backups, and audit trails'
    }
];

const quickStats = [
    { number: 'Live', label: 'System Status', icon: '🟢' },
    { number: '24/7', label: 'Monitoring', icon: '🔍' },
    { number: 'Secure', label: 'Data Protection', icon: '🛡️' },
    { number: 'Cloud', label: 'Infrastructure', icon: '☁️' }
];

const slides = [
    {
        title: '3R QUICKPAY Internal System',
        subtitle: 'Centralized management platform for all store operations',
        image: '🏢'
    },
    {
        title: 'Real-Time Operations',
        subtitle: 'Monitor inventory, sales, and staff performance in real-time',
        image: '📊'
    },
    {
        title: 'Secure Access',
        subtitle: 'Role-based access control for different staff levels',
        image: '🔐'
    }
];

const systemFeatures = [
    'Centralized inventory control across all locations',
    'Real-time sales monitoring and reporting',
    'Staff performance tracking and scheduling',
    'Automated financial reconciliation',
    'Customer loyalty program management',
    'Supplier and vendor management system'
];

onMounted(() => {
    isVisible.value = true;
    slideInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % slides.length;
    }, 5000);
});

onUnmounted(() => {
    if (slideInterval) {
        clearInterval(slideInterval);
    }
});

const setSlide = (index) => {
    currentSlide.value = index;
};
</script>

<template>
    <Head title="3R QUICKPAY MINIMART - Internal Management System" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 bg-slate-900/90 backdrop-blur-lg border-b border-slate-700/50 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">3R QUICKPAY</h1>
                            <p class="text-xs text-slate-300">Internal Management System</p>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="hidden md:flex items-center space-x-4 text-sm">
                        <div class="flex items-center space-x-2 text-emerald-400">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                            <span>System Online</span>
                        </div>
                        <div class="text-slate-400">|</div>
                        <div class="text-slate-300">Private Access Only</div>
                    </div>

                    <!-- Auth Buttons -->
                    <nav v-if="canLogin" class="flex items-center space-x-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="bg-gradient-to-r from-emerald-600 to-blue-600 text-white px-6 py-2 rounded-lg hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex items-center space-x-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span>Dashboard</span>
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                               
                                 class="bg-gradient-to-r from-emerald-600 to-blue-600 text-white px-6 py-2 rounded-lg hover:shadow-lg transform hover:scale-105 transition-all duration-200"
                            >
                                Login
                            </Link>

                            <!-- <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="bg-gradient-to-r from-emerald-600 to-blue-600 text-white px-6 py-2 rounded-lg hover:shadow-lg transform hover:scale-105 transition-all duration-200"
                            >
                                Register Staff
                            </Link> -->
                        </template>
                    </nav>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-24 pb-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Hero Content -->
                    <div :class="['transform transition-all duration-1000', isVisible ? 'translate-x-0 opacity-100' : '-translate-x-10 opacity-0']">
                        <div class="inline-flex items-center px-4 py-2 bg-emerald-500/20 text-emerald-400 rounded-full text-sm font-medium mb-6 border border-emerald-500/30">
                            <span class="mr-2">🏢</span>
                            Internal Business Management Platform
                        </div>
                        
                        <h1 class="text-4xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                            <span class="text-emerald-400">3R QUICKPAY</span><br>
                            Management System
                        </h1>
                        
                        <p class="text-xl text-slate-300 mb-8 leading-relaxed">
                            Comprehensive internal platform for managing all aspects of 3R QuickPay Minimart operations. 
                            Streamline inventory, sales, staff, and financial processes in one secure system.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 mb-8">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('login')"
                                class="bg-gradient-to-r from-emerald-600 to-blue-600 text-white px-8 py-4 rounded-xl font-semibold hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center"
                            >
                                Access System
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 0a4 4 0 01-4 4H3V7a4 4 0 014-4h7a4 4 0 014 4v1" />
                                </svg>
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="bg-gradient-to-r from-emerald-600 to-blue-600 text-white px-8 py-4 rounded-xl font-semibold hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center"
                            >
                                Go to Dashboard
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </Link>
                            <div class="border-2 border-slate-600 text-slate-300 px-8 py-4 rounded-xl font-semibold flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Authorized Access Only
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                            <div v-for="(stat, index) in quickStats" :key="index" class="text-center bg-slate-800/50 rounded-lg p-4 border border-slate-700">
                                <div class="flex items-center justify-center mb-2 text-2xl">
                                    {{ stat.icon }}
                                </div>
                                <div class="text-xl font-bold text-white">{{ stat.number }}</div>
                                <div class="text-sm text-slate-400">{{ stat.label }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Visual -->
                    <div :class="['transform transition-all duration-1000 delay-300', isVisible ? 'translate-x-0 opacity-100' : 'translate-x-10 opacity-0']">
                        <div class="relative">
                            <!-- Animated Slider -->
                            <div class="bg-slate-800/50 backdrop-blur-lg rounded-2xl shadow-2xl p-8 mb-6 border border-slate-700">
                                <div class="text-center">
                                    <div class="text-6xl mb-4">{{ slides[currentSlide].image }}</div>
                                    <h3 class="text-2xl font-bold text-white mb-2">
                                        {{ slides[currentSlide].title }}
                                    </h3>
                                    <p class="text-slate-300">
                                        {{ slides[currentSlide].subtitle }}
                                    </p>
                                </div>
                            </div>

                            <!-- Slide Indicators -->
                            <div class="flex justify-center space-x-2">
                                <button
                                    v-for="(slide, index) in slides"
                                    :key="index"
                                    @click="setSlide(index)"
                                    :class="['w-3 h-3 rounded-full transition-all duration-200', index === currentSlide ? 'bg-emerald-500' : 'bg-slate-600']"
                                />
                            </div>

                            <!-- Floating Status Cards -->
                            <div class="absolute -top-4 -left-4 bg-emerald-500 text-white p-3 rounded-xl shadow-lg">
                                <span class="text-xl">📊</span>
                            </div>
                            <div class="absolute -bottom-4 -right-4 bg-blue-500 text-white p-3 rounded-xl shadow-lg">
                                <span class="text-xl">🔒</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- System Modules Section -->
        <section class="py-20 bg-slate-800/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                        System Modules & Features
                    </h2>
                    <p class="text-xl text-slate-300 max-w-3xl mx-auto">
                        Comprehensive tools designed specifically for 3R QuickPay Minimart's operational needs
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="(module, index) in systemModules"
                        :key="index"
                        class="group p-6 rounded-2xl bg-slate-800/50 border border-slate-700 hover:border-emerald-500/50 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 backdrop-blur-lg"
                    >
                        <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-blue-600 rounded-xl flex items-center justify-center text-white mb-4 group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">{{ module.icon }}</span>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">
                            {{ module.title }}
                        </h3>
                        <p class="text-slate-300">
                            {{ module.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Internal Features Section -->
        <section class="py-20 bg-gradient-to-r from-emerald-600/20 to-blue-600/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                            Built for 3R QuickPay Operations
                        </h2>
                        <div class="space-y-4">
                            <div v-for="(feature, index) in systemFeatures" :key="index" class="flex items-center space-x-3">
                                <svg class="w-6 h-6 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-lg text-slate-300">{{ feature }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-800/50 backdrop-blur-lg rounded-2xl p-8 border border-slate-700">
                        <div class="text-center">
                            <div class="text-5xl mb-4">🔐</div>
                            <h3 class="text-2xl font-bold text-white mb-4">Secure Access Required</h3>
                            <p class="text-slate-300 mb-6">
                                This system is exclusively for 3R QuickPay Minimart authorized personnel. 
                                Contact your administrator for access credentials.
                            </p>
                            <div class="bg-slate-700/50 rounded-lg p-4">
                                <p class="text-sm text-slate-400">
                                    <strong class="text-emerald-400">Note:</strong> All system activities are logged and monitored for security purposes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 border-t border-slate-800 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-4 gap-8">
                    <div class="col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">3R QUICKPAY MINIMART</h3>
                                <p class="text-slate-400 text-sm">Internal Management System</p>
                            </div>
                        </div>
                        <p class="text-slate-400 mb-4">
                            Private business management platform exclusively for 3R QuickPay Minimart operations and authorized personnel.
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-4 text-white">System Access</h4>
                        <ul class="space-y-2 text-slate-400">
                            <li>Staff Login Portal</li>
                            <li>Manager Dashboard</li>
                            <li>Inventory Control</li>
                            <li>Sales Reports</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-4 text-white">Support</h4>
                        <ul class="space-y-2 text-slate-400">
                            <li>Internal IT Support</li>
                            <li>System Administrator</li>
                            <li>Staff Training</li>
                            <li>24/7 Monitoring</li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-slate-800 mt-8 pt-8 text-center text-slate-400">
                    <p>&copy; 2025 3R QuickPay Minimart - Internal Management System. Authorized Access Only. | Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Additional custom styles if needed */
.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}
</style>