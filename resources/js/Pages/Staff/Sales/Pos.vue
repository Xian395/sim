<template>
    <AuthenticatedLayout>
        <div
            class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 mt-12 sm:rounded-lg relative overflow-hidden">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div
                    class="absolute -top-4 -left-4 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse">
                </div>
                <div class="absolute -bottom-8 -right-4 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse"
                    style="animation-delay: 2s"></div>
            </div>

            <!-- Main Product Area -->
            <div class="h-screen flex flex-col relative z-10">
                <!-- Compact Search and Filter Bar -->
                <div class="bg-white/90 backdrop-blur-xl shadow-lg border border-white/20 sm:rounded-2xl mx-4 mt-4 mb-2">
                    <!-- Compact Header Section -->
                    <div class="p-3 space-y-3">
                        <!-- Search Bar -->
                        <div class="flex gap-3 items-center">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input v-model="searchQuery" @input="debouncedSearch" type="text"
                                    placeholder="Search products..."
                                    class="block w-full pl-10 pr-10 py-2.5 text-sm bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300" />
                                <!-- Search Loading Indicator -->
                                <div v-if="searchQuery && searchTimeout"
                                    class="absolute inset-y-0 right-3 flex items-center">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                                </div>
                                <!-- Clear Button -->
                                <div v-if="searchQuery && !searchTimeout"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button @click="clearSearch"
                                        class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-all duration-200">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Toggle Filters Button -->
                            <button @click="showAdvancedFilters = !showAdvancedFilters"
                                class="px-3 py-2.5 bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-xl hover:bg-white hover:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/20 text-sm font-medium flex items-center space-x-2 transition-all duration-200 group whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span>Filters</span>
                            </button>

                            <!-- Cart Toggle Button -->
                            <button @click="toggleCartDrawer"
                                class="px-3 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/20 text-sm font-medium flex items-center space-x-2 transition-all duration-200 group whitespace-nowrap shadow-md hover:shadow-lg"
                                :class="{ 'ring-4 ring-blue-400': isCartOpen }">
                                <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.8 7.2M7 13l-1.8 7.2m0 0h12.6M7 13v8a2 2 0 002 2h6a2 2 0 002-2v-8" />
                                </svg>
                                <span v-if="cart.length > 0" class="font-semibold flex items-center space-x-1">
                                    <span>{{ cart.length }}</span>
                                    <div class="bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[20px] animate-bounce">
                                        {{ cart.length }}
                                    </div>
                                </span>
                                <span v-else>Cart</span>
                            </button>
                        </div>

                        <!-- Advanced Filters (Collapsible) -->
                        <transition name="slide-down">
                            <div v-if="showAdvancedFilters" class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-gray-200">
                                <!-- Category Filter -->
                                <div class="relative">
                                    <select v-model="categoryFilter" @change="applyFilters"
                                        class="appearance-none w-full pl-3 pr-8 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium cursor-pointer transition-all duration-200">
                                        <option value="">All Categories</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Stock Filter -->
                                <div class="relative">
                                    <select v-model="stockFilter" @change="applyFilters"
                                        class="appearance-none w-full pl-3 pr-8 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium cursor-pointer transition-all duration-200">
                                        <option value="all">All Stock</option>
                                        <option value="available">Available</option>
                                        <option value="in_stock">In Stock</option>
                                        <option value="low_stock">Low Stock</option>
                                        <option value="out_of_stock">Out</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Sort Options -->
                                <div class="relative">
                                    <select v-model="sortBy" @change="applyFilters"
                                        class="appearance-none w-full pl-3 pr-8 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium cursor-pointer transition-all duration-200">
                                        <option value="name">By Name</option>
                                        <option value="price">By Price</option>
                                        <option value="stock">By Stock</option>
                                        <option value="category">By Category</option>
                                        <option value="item_code">By Code</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Sort Order -->
                                <div class="flex gap-2">
                                    <button @click="toggleSortOrder"
                                        class="flex-1 px-2 py-2 bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg hover:bg-white hover:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/20 text-xs font-medium flex items-center justify-center space-x-1 transition-all duration-200"
                                        :title="sortOrder === 'asc' ? 'Ascending' : 'Descending'">
                                        <svg v-if="sortOrder === 'asc'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                        </svg>
                                        <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                        </svg>
                                    </button>
                                    <!-- Clear Filters -->
                                    <button v-if="hasActiveFilters" @click="clearAllFilters"
                                        class="flex-1 px-2 py-2 text-xs font-medium text-red-600 hover:text-white hover:bg-red-500 bg-red-50 border-2 border-red-200 hover:border-red-500 rounded-lg transition-all duration-200">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </transition>

                        <!-- Compact Stats Bar -->
                        <div v-if="stats" class="flex flex-wrap gap-3 p-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100 text-xs font-medium">
                            <div class="flex items-center space-x-1.5">
                                <div class="w-2 h-2 bg-blue-500 rounded-full shadow-lg shadow-blue-500/30"></div>
                                <span class="text-gray-700">Total: <span class="text-blue-600 font-semibold">{{ stats.total_products }}</span></span>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <div class="w-2 h-2 bg-green-500 rounded-full shadow-lg shadow-green-500/30"></div>
                                <span class="text-gray-700">In Stock: <span class="text-green-600 font-semibold">{{ stats.in_stock }}</span></span>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <div class="w-2 h-2 bg-orange-500 rounded-full shadow-lg shadow-orange-500/30"></div>
                                <span class="text-gray-700">Low: <span class="text-orange-600 font-semibold">{{ stats.low_stock }}</span></span>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <div class="w-2 h-2 bg-red-500 rounded-full shadow-lg shadow-red-500/30"></div>
                                <span class="text-gray-700">Out: <span class="text-red-600 font-semibold">{{ stats.out_of_stock }}</span></span>
                            </div>
                            <div class="ml-auto flex items-center space-x-1.5">
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span class="text-gray-600">Showing: <span class="font-semibold">{{ products.length }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Products Grid -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                        <div v-for="product in products" :key="product.id" @click="addToCart(product)"
                            class="group relative bg-white/80 backdrop-blur-sm rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 cursor-pointer transform hover:scale-105 border border-white/50 hover:border-blue-200 overflow-hidden"
                            :class="{
                                'opacity-50 cursor-not-allowed': product.stock_quantity === 0,
                            }">
                            <!-- Gradient Overlay -->
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-blue-50 opacity-0 group-hover:opacity-50 transition-opacity duration-500">
                            </div>

                            <!-- Stock Status with Animation -->
                            <div class="absolute top-4 right-4 z-20">
                                <div v-if="product.stock_quantity === 0"
                                    class="w-4 h-4 bg-red-500 rounded-full border-2 border-white shadow-lg pulse-red">
                                </div>
                                <div v-else-if="product.stock_quantity <= 5"
                                    class="w-4 h-4 bg-orange-500 rounded-full border-2 border-white shadow-lg pulse-orange">
                                </div>
                                <div v-else
                                    class="w-4 h-4 bg-green-500 rounded-full border-2 border-white shadow-lg pulse-green">
                                </div>
                            </div>

                            <div class="p-6 h-full flex flex-col">
                                <!-- Enhanced Product Image -->
                                <div class="flex justify-center mb-4">
                                    <div
                                        class="relative w-28 h-28 rounded-2xl overflow-hidden shadow-xl group-hover:shadow-2xl transition-shadow duration-500">
                                        <img v-if="product.image_url" :src="product.image_url"
                                            :alt="product.name"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            @error="handleImageError" />
                                        <div class="w-full h-full rounded-2xl bg-gradient-to-br flex items-center justify-center absolute inset-0 transition-transform duration-500 group-hover:scale-110"
                                            :class="[
                                                getProductGradient(product.id),
                                                product.image_url ? 'gradient-fallback hidden' : '',
                                            ]">
                                            <svg class="w-10 h-10 text-white opacity-90" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                        <!-- Shimmer Effect -->
                                        <div
                                            class="absolute inset-0 -translate-x-full group-hover:translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000">
                                        </div>
                                    </div>
                                </div>

                                <!-- Enhanced Product Info -->
                                <div class="text-center flex-1 flex flex-col justify-between">
                                    <div class="mb-3">
                                        <h3
                                            class="font-bold text-gray-900 text-sm leading-tight mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-300">
                                            {{ product.name }}
                                        </h3>
                                        <p
                                            class="text-xs text-gray-500 mb-2 px-2 py-1 bg-gray-100 rounded-lg inline-block">
                                            {{ product.category_names || "Uncategorized" }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <p v-if="product.price"
                                            class="text-xl font-black text-gray-900 group-hover:text-blue-600 transition-colors duration-300">
                                            ₱{{ parseFloat(product.price).toFixed(2) }}
                                        </p>
                                        <p v-else
                                            class="text-sm font-bold text-amber-600 bg-amber-100 px-3 py-1.5 rounded-lg">
                                            No price set
                                        </p>
                                        <div class="flex items-center justify-center space-x-2">
                                            <span v-if="product.stock_quantity === 0"
                                                class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">Out
                                                of Stock</span>
                                            <span v-else-if="product.stock_quantity <= 5"
                                                class="text-xs font-bold text-orange-600 bg-orange-100 px-2 py-1 rounded-full">Low
                                                ({{ product.stock_quantity }})</span>
                                            <span v-else
                                                class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded-full">Stock
                                                ({{ product.stock_quantity }})</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Add to Cart Animation -->
                            <div
                                class="absolute bottom-4 right-4 transform translate-y-8 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                <div
                                    class="w-8 h-8 bg-gradient-to-r from-blue-600 to-blue-700 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-shadow duration-200">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Ripple Effect -->
                            <div
                                class="absolute inset-0 scale-0 group-active:scale-100 bg-blue-400 rounded-2xl opacity-20 transition-transform duration-200">
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Empty State -->
                    <div v-if="products.length === 0" class="text-center py-20 space-y-6">
                        <div class="relative">
                            <svg class="mx-auto h-32 w-32 text-gray-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2-2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-blue-100 to-transparent rounded-full filter blur-3xl opacity-30">
                            </div>
                        </div>
                        <div class="space-y-3">
                            <h3 class="text-2xl font-bold text-gray-900">No products found</h3>
                            <p class="text-gray-500 max-w-md mx-auto">Try adjusting your search terms or filters to find
                                what you're looking for.</p>
                        </div>
                        <button @click="clearAllFilters"
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors duration-200 font-semibold">
                            Clear All Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rest of the cart drawer, payment modal, etc. remains the same -->
            <!-- Cart Drawer Overlay -->
            <div v-if="isCartOpen" @click="closeCartDrawer"
                class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-30 transition-opacity duration-300">
            </div>

            <!-- Enhanced Cart Drawer -->
            <div class="fixed top-0 right-0 h-full w-96 bg-white/95 backdrop-blur-xl shadow-2xl z-40 transform transition-transform duration-200 ease-out flex flex-col border-l border-gray-200"
                :class="isCartOpen ? 'translate-x-0' : 'translate-x-full'">
                <!-- Enhanced Cart Header -->
                <div
                    class="bg-gradient-to-r from-blue-600 via-blue-700 to-purple-600 text-white p-6 relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400/20 to-purple-400/20"></div>
                    <div class="absolute inset-0"
                        style="background-image: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);">
                    </div>

                    <!-- Cart Close Button (Fixed Position) -->
                    <button @click="closeCartDrawer"
                        class="fixed bottom-8 right-6 z-[60] bg-gradient-to-r from-red-500 to-red-600 text-white rounded-2xl shadow-2xl hover:shadow-red-500/25 transition-all duration-500 flex items-center group overflow-hidden p-4"
                        :class="{ 'right-[25rem]': isCartOpen }" v-if="isCartOpen">
                        <!-- Ripple Effect -->
                        <div
                            class="absolute inset-0 bg-white/20 rounded-2xl scale-0 group-hover:scale-110 transition-transform duration-500">
                        </div>

                        <div class="relative z-10 flex items-center space-x-2">
                            <svg class="w-6 h-6 transition-transform duration-300 group-hover:rotate-180" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="font-semibold">Close</span>
                        </div>
                    </button>

                    <div class="relative z-10 flex flex-col space-y-6">
                        <div class="space-y-1">
                            <h3 class="text-2xl font-bold">Current Order</h3>
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-60"></div>
                                    <p class="text-blue-100 text-sm font-medium">
                                        {{ cart.length }} {{ cart.length === 1 ? 'item' : 'items' }} selected
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- Highlighted Total Amount -->
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/30">
                            <p class="text-4xl font-black text-white drop-shadow-lg">
                                ₱{{ total.toFixed(2) }}
                            </p>
                            <p class="text-blue-100 text-xs uppercase tracking-wider font-semibold mt-1">
                                Total Amount
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cart Items (Enhanced) -->
                <div class="flex-1 overflow-y-auto">
                    <div v-if="cart.length === 0"
                        class="flex flex-col items-center justify-center h-full text-gray-500 p-8 space-y-6">
                        <div class="relative">
                            <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.8 7.2M7 13l-1.8 7.2m0 0h12.6M7 13v8a2 2 0 002 2h6a2 2 0 002-2v-8" />
                            </svg>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-blue-100 to-transparent rounded-full filter blur-2xl opacity-30">
                            </div>
                        </div>
                        <div class="text-center space-y-2">
                            <h4 class="text-xl font-bold text-gray-900">Cart is empty</h4>
                            <p class="text-gray-500">Start adding products to create an order</p>
                        </div>
                    </div>

                    <div v-else class="p-4 space-y-4">
                        <div v-for="(item, index) in cart" :key="index"
                            class="bg-gradient-to-r from-gray-50 to-blue-50/50 rounded-2xl p-4 border border-gray-100 hover:border-blue-200 hover:shadow-md transition-all duration-300 group">
                            <!-- Product Header with Image -->
                            <div class="flex gap-3 mb-4">
                                <!-- Product Image -->
                                <div class="relative w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-gray-200 border-2 border-gray-100">
                                    <img v-if="item.image_url" :src="item.image_url"
                                        :alt="item.name"
                                        class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full rounded-xl bg-gradient-to-br flex items-center justify-center"
                                        :class="getProductGradient(item.id)">
                                        <svg class="w-8 h-8 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start gap-2">
                                        <div class="flex-1">
                                            <h4 class="font-bold text-gray-900 text-sm leading-tight mb-1 group-hover:text-blue-600 transition-colors duration-300 line-clamp-2">
                                                {{ item.name }}
                                            </h4>
                                            <p class="text-xs text-gray-500 font-medium">
                                                {{ item.barcode }}
                                            </p>
                                        </div>
                                        <button @click="removeFromCart(index)"
                                            class="text-red-500 hover:text-white hover:bg-red-500 p-1.5 rounded-lg transition-all duration-200 flex-shrink-0">
                                            <svg class="w-4 h-4 hover:scale-110 transition-transform duration-200"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity and Price -->
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center bg-white rounded-lg border-2 border-gray-200 shadow-sm gap-0">
                                    <button @click="decrementQuantity(index)"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-white hover:bg-blue-500 rounded-l-lg transition-all duration-200">
                                        <svg class="w-3 h-3 hover:scale-110 transition-transform duration-200"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <input
                                        :value="item.quantity"
                                        @input="(e) => updateQuantity(index, e.target.value)"
                                        @blur="(e) => validateQuantity(index, e.target.value)"
                                        type="number"
                                        min="1"
                                        :max="item.stock_quantity"
                                        class="w-12 text-center text-xs font-bold py-1 bg-gray-50 border-0 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    <button @click="incrementQuantity(index)"
                                        :disabled="item.quantity >= item.stock_quantity"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-white hover:bg-blue-500 rounded-r-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-3 h-3 hover:scale-110 transition-transform duration-200"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="text-right">
                                    <p class="text-xs text-gray-500 font-medium">
                                        ₱{{ parseFloat(item.price).toFixed(2) }}
                                    </p>
                                    <p class="font-bold text-gray-900">
                                        ₱{{ (item.quantity * item.price).toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Cart Footer -->
                <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50/50 backdrop-blur-xl p-6">
                    <!-- Order Summary -->
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-center text-2xl font-bold border-t border-gray-300 pt-4">
                            <span class="text-gray-800">Total:</span>
                            <span class="text-blue-600 bg-blue-100 px-3 py-1 rounded-xl">₱{{ total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Enhanced Complete Sale Button -->
                    <button @click="showPaymentModal = true" :disabled="cart.length === 0"
                        class="w-full py-4 rounded-2xl font-bold text-lg transition-all duration-300 shadow-xl hover:shadow-2xl flex items-center justify-center space-x-3 mb-4 relative overflow-hidden group"
                        :class="cart.length === 0
                                ? 'bg-gray-400 text-white cursor-not-allowed'
                                : 'bg-gradient-to-r from-blue-600 via-blue-700 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700'
                            ">
                        <!-- Button Ripple Effect -->
                        <div
                            class="absolute inset-0 bg-white/20 transform scale-0 group-hover:scale-100 transition-transform duration-500 rounded-2xl">
                        </div>

                        <div class="relative z-10 flex items-center space-x-3">
                            <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>
                                {{ cart.length === 0 ? "Add items to cart" : "Proceed to Payment" }}
                            </span>
                        </div>
                    </button>

                    <!-- Enhanced Receipt Section -->
                    <div v-if="lastSaleReceipt"
                        class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl shadow-lg relative overflow-hidden">
                        <!-- Success Pattern -->
                        <div class="absolute inset-0 opacity-5">
                            <div class="absolute inset-0"
                                style="background-image: radial-gradient(circle at 50% 50%, rgba(34, 197, 94, 0.3) 0%, transparent 70%);">
                            </div>
                        </div>

                        <div class="relative z-10">
                            <div class="flex items-center mb-4 space-x-3">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-green-800 text-lg">Sale Completed!</h4>
                            </div>

                            <!-- Transaction Summary -->
                            <div class="text-sm text-green-700 mb-6 space-y-2 bg-white/50 p-4 rounded-xl">
                                <p class="flex justify-between">
                                    <span class="font-semibold">Receipt:</span>
                                    <span class="font-mono">{{ lastSaleReceipt.receipt_id }}</span>
                                </p>
                                <p class="flex justify-between">
                                    <span class="font-semibold">Total:</span>
                                    <span class="font-bold text-green-800">₱{{
                                        parseFloat(lastSaleReceipt.total).toFixed(2) }}</span>
                                </p>
                                <p v-if="lastSaleReceipt.payment_amount" class="flex justify-between">
                                    <span class="font-semibold">Payment:</span>
                                    <span>₱{{ parseFloat(lastSaleReceipt.payment_amount).toFixed(2) }}</span>
                                </p>
                                <p v-if="lastSaleReceipt.change_amount" class="flex justify-between">
                                    <span class="font-semibold">Change:</span>
                                    <span>₱{{ parseFloat(lastSaleReceipt.change_amount).toFixed(2) }}</span>
                                </p>
                            </div>

                            <button @click="generateReceipt"
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl hover:from-blue-700 hover:to-blue-800 flex items-center justify-center space-x-3 font-bold transition-all duration-300 shadow-lg hover:shadow-xl group relative overflow-hidden">
                                <!-- Button Animation -->
                                <div
                                    class="absolute inset-0 bg-white/20 transform -translate-x-full group-hover:translate-x-full transition-transform duration-700">
                                </div>

                                <div class="relative z-10 flex items-center space-x-3">
                                    <svg class="h-5 w-5 group-hover:scale-110 transition-transform duration-200"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Print Receipt</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Payment Modal -->
            <div v-if="showPaymentModal"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                <div
                    class="bg-white/95 backdrop-blur-xl rounded-3xl p-8 max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-white/20 relative">
                    <!-- Modal Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-purple-50/50 rounded-3xl"></div>

                    <div class="relative z-10">
                        <!-- Enhanced Modal Header -->
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3
                                    class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-blue-600 bg-clip-text text-transparent">
                                    Payment Details
                                </h3>
                                <p class="text-gray-500 text-sm mt-1">Complete your transaction</p>
                            </div>
                            <button @click="closePaymentModal"
                                class="text-gray-400 hover:text-gray-600 p-3 rounded-2xl hover:bg-gray-100 transition-all duration-200 group">
                                <svg class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Enhanced Order Summary -->
                        <div
                            class="mb-8 p-6 bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl border border-gray-200">
                            <h4 class="font-bold text-gray-900 mb-4 text-lg">Order Summary</h4>
                            <div class="space-y-3 mb-6">
                                <div v-for="(item, index) in cart" :key="index"
                                    class="flex justify-between items-center text-sm p-3 bg-white rounded-xl">
                                    <div class="flex-1">
                                        <span class="font-semibold text-gray-900">{{ item.name }}</span>
                                        <span class="text-gray-500 ml-2">x{{ item.quantity }}</span>
                                    </div>
                                    <span class="font-bold text-gray-900">₱{{ (item.quantity * item.price).toFixed(2)
                                        }}</span>
                                </div>
                            </div>
                            <div class="border-t-2 border-gray-300 pt-4 flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-900">Total Amount:</span>
                                <span class="text-2xl font-black text-blue-600">₱{{ total.toFixed(2) }}</span>
                            </div>
                        </div>

                        <!-- Enhanced Payment Input -->
                        <div class="mb-8">
                            <label for="paymentAmount" class="block text-sm font-bold text-gray-700 mb-3">
                                Amount Received
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-2xl font-bold">₱</span>
                                </div>
                                <input id="paymentAmount" v-model="paymentAmount" @input="calculateChange"
                                    @focus="$event.target.select()" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="block w-full pl-14 pr-6 py-5 text-2xl font-bold bg-white border-3 rounded-2xl focus:outline-none focus:ring-4 transition-all duration-300 placeholder-gray-400"
                                    :class="{
                                        'border-red-300 focus:ring-red-500/20 focus:border-red-500 bg-red-50': paymentAmount && parseFloat(paymentAmount) < total,
                                        'border-green-300 focus:ring-green-500/20 focus:border-green-500 bg-green-50': paymentAmount && parseFloat(paymentAmount) >= total,
                                        'border-gray-300 focus:ring-blue-500/20 focus:border-blue-500': !paymentAmount
                                    }" />
                            </div>

                            <!-- Enhanced Quick Amount Buttons -->
                            <div class="flex flex-wrap gap-3 mt-6">
                                <button v-for="amount in quickAmounts" :key="amount" @click="setPaymentAmount(amount)"
                                    class="px-4 py-3 text-sm font-bold bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 rounded-xl hover:from-blue-200 hover:to-blue-300 hover:scale-105 transition-all duration-200 border border-blue-300">
                                    ₱{{ amount }}
                                </button>
                                <button @click="setExactAmount"
                                    class="px-4 py-3 text-sm font-bold bg-gradient-to-r from-green-100 to-emerald-200 text-green-700 rounded-xl hover:from-green-200 hover:to-emerald-300 hover:scale-105 transition-all duration-200 border border-green-300">
                                    Exact Amount
                                </button>
                            </div>
                        </div>

                        <!-- Enhanced Change Display -->
                        <div v-if="paymentAmount && parseFloat(paymentAmount) > 0"
                            class="p-6 rounded-2xl mb-6 relative overflow-hidden" :class="change >= 0
                                    ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200'
                                    : 'bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200'
                                ">
                            <!-- Background Effect -->
                            <div class="absolute inset-0 opacity-10"
                                :class="change >= 0 ? 'bg-gradient-to-br from-green-400 to-emerald-400' : 'bg-gradient-to-br from-red-400 to-rose-400'">
                            </div>

                            <div class="relative z-10 flex justify-between items-center">
                                <span class="font-bold text-xl"
                                    :class="change >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ change >= 0 ? "Change:" : "Insufficient:" }}
                                </span>
                                <span class="text-3xl font-black"
                                    :class="change >= 0 ? 'text-green-900' : 'text-red-900'">
                                    ₱{{ Math.abs(change).toFixed(2) }}
                                </span>
                            </div>

                            <!-- Enhanced Warning -->
                            <div v-if="change < 0"
                                class="mt-4 text-sm text-red-600 flex items-center bg-white/60 p-3 rounded-xl">
                                <svg class="w-5 h-5 mr-2 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="font-semibold">Need ₱{{ (total - parseFloat(paymentAmount)).toFixed(2) }}
                                    more</span>
                            </div>
                        </div>

                        <!-- Enhanced Action Buttons -->
                        <div class="flex gap-4">
                            <button @click="closePaymentModal"
                                class="flex-1 py-4 px-6 border-2 border-gray-300 text-gray-700 rounded-2xl font-bold hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 group">
                                <span
                                    class="group-hover:scale-105 inline-block transition-transform duration-200">Cancel</span>
                            </button>
                            <button @click="processSale"
                                :disabled="cart.length === 0 || (paymentAmount && parseFloat(paymentAmount) < total)"
                                class="flex-1 py-4 px-6 rounded-2xl font-bold transition-all duration-300 flex items-center justify-center space-x-3 relative overflow-hidden group"
                                :class="cart.length === 0 || (paymentAmount && parseFloat(paymentAmount) < total)
                                        ? 'bg-gray-400 text-white cursor-not-allowed'
                                        : 'bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 shadow-lg hover:shadow-xl'
                                    ">
                                <!-- Success Button Animation -->
                                <div
                                    class="absolute inset-0 bg-white/20 transform scale-0 group-hover:scale-110 transition-transform duration-500 rounded-2xl">
                                </div>

                                <div class="relative z-10 flex items-center space-x-3">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-200"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>
                                        {{ cart.length === 0 ? "Add Items" : paymentAmount && parseFloat(paymentAmount)
                                            < total ? "Insufficient Payment" : "Complete Sale" }} </span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { jsPDF } from "jspdf";
import { autoTable } from "jspdf-autotable";
import { notify } from "@/globalFunctions.js";

const props = defineProps({
    products: Array,
    categories: Array,
    stats: Object,
    filters: Object,
});

const page = usePage();

const searchQuery = ref(props.filters?.query || "");
const categoryFilter = ref(props.filters?.category || "");
const stockFilter = ref(props.filters?.stock || "all");
const sortBy = ref(props.filters?.sort || "name");
const sortOrder = ref(props.filters?.order || "asc");

const cart = ref([]);
const products = ref(props.products || []);
const categories = ref(props.categories || []);
const stats = ref(props.stats || {});
const lastSaleReceipt = ref(null);
const searchTimeout = ref(null);
const showAdvancedFilters = ref(false);

// Cart drawer state
const isCartOpen = ref(false);

const total = computed(() => {
    return cart.value.reduce(
        (sum, item) => sum + item.quantity * item.price,
        0
    );
});

const hasActiveFilters = computed(() => {
    return (
        searchQuery.value ||
        categoryFilter.value ||
        stockFilter.value !== "all" ||
        sortBy.value !== "name" ||
        sortOrder.value !== "asc"
    );
});

const productGradients = [
    "from-red-500 to-red-600",
    "from-blue-500 to-blue-600",
    "from-green-500 to-green-600",
    "from-yellow-500 to-yellow-600",
    "from-purple-500 to-purple-600",
    "from-pink-500 to-pink-600",
    "from-indigo-500 to-indigo-600",
    "from-teal-500 to-teal-600",
    "from-orange-500 to-orange-600",
    "from-cyan-500 to-cyan-600",
    "from-emerald-500 to-emerald-600",
    "from-violet-500 to-violet-600",
    "from-rose-500 to-rose-600",
    "from-amber-500 to-amber-600",
    "from-lime-500 to-lime-600",
    "from-sky-500 to-sky-600",
];


const handleImageError = (event) => {
    console.warn(`Image failed to load: ${event.target.src}`);
    event.target.style.display = "none";
    const fallback = event.target.parentElement.querySelector(".gradient-fallback");
    if (fallback) {
        fallback.classList.remove("hidden");
    }
};

const getProductGradient = (productId) => {
    return productGradients[productId % productGradients.length];
};

// Cart drawer functions
const toggleCartDrawer = () => {
    isCartOpen.value = !isCartOpen.value;
};

const openCartDrawer = () => {
    isCartOpen.value = true;
};

const closeCartDrawer = () => {
    isCartOpen.value = false;
};

// Watch for cart changes
watch(cart, (newCart) => {
    if (newCart.length === 0 && !lastSaleReceipt.value) {
        isCartOpen.value = false;
    }
}, { deep: true });

watch(
    () => page.props.flash?.receipt_data,
    (newReceiptData) => {
        if (newReceiptData) {
            lastSaleReceipt.value = newReceiptData;
            console.log("Receipt data received:", newReceiptData);
            console.log("Products in receipt:", newReceiptData.items.map((item) => item.display_name));
        }
    },
    { immediate: true }
);

onMounted(() => {
    if (page.props.flash?.receipt_data) {
        lastSaleReceipt.value = page.props.flash.receipt_data;
        console.log("Receipt data loaded on mount:", lastSaleReceipt.value);
    }

    products.value = props.products || [];
    categories.value = props.categories || [];
    stats.value = props.stats || {};
});

const debouncedSearch = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    searchTimeout.value = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    const params = {
        query: searchQuery.value,
        category: categoryFilter.value,
        stock: stockFilter.value,
        sort: sortBy.value,
        order: sortOrder.value,
    };

    Object.keys(params).forEach((key) => {
        if (!params[key] || params[key] === "all") {
            delete params[key];
        }
    });

    router.get(route("staff.pos"), params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            products.value = page.props.products || [];
            stats.value = page.props.stats || {};
        },
    });
};

const clearSearch = () => {
    searchQuery.value = "";
    applyFilters();
};

const toggleSortOrder = () => {
    sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
    applyFilters();
};

const clearAllFilters = () => {
    searchQuery.value = "";
    categoryFilter.value = "";
    stockFilter.value = "all";
    sortBy.value = "name";
    sortOrder.value = "asc";
    applyFilters();
};

const addToCart = (product) => {
    if (product.stock_quantity === 0) {
        return;
    }

    const existing = cart.value.find((item) => item.id === product.id);
    if (existing) {
        if (existing.quantity < product.stock_quantity) {
            existing.quantity++;
        }
    } else {
        cart.value.push({ ...product, quantity: 1 });
    }

    console.log("Added to cart:", product.name);
};

const removeFromCart = (index) => {
    const removedItem = cart.value[index];
    console.log("Removed from cart:", removedItem.name);
    cart.value.splice(index, 1);
};

const incrementQuantity = (index) => {
    const item = cart.value[index];
    if (item.quantity < item.stock_quantity) {
        item.quantity++;
    }
};

const decrementQuantity = (index) => {
    const item = cart.value[index];
    if (item.quantity > 1) {
        item.quantity--;
    }
};

const updateQuantity = (index, value) => {
    const item = cart.value[index];
    const qty = parseInt(value) || 0;

    if (qty > 0) {
        item.quantity = qty;
    }
};

const validateQuantity = (index, value) => {
    const item = cart.value[index];
    const qty = parseInt(value) || 0;

    if (qty < 1) {
        item.quantity = 1;
    } else if (qty > item.stock_quantity) {
        item.quantity = item.stock_quantity;
    } else {
        item.quantity = qty;
    }
};

const paymentAmount = ref("");
const change = ref(0);

const quickAmounts = computed(() => {
    const totalAmount = total.value;
    const suggestions = [];

    const roundUpAmounts = [50, 100, 500, 1000];

    roundUpAmounts.forEach((amount) => {
        if (totalAmount > 0) {
            const rounded = Math.ceil(totalAmount / amount) * amount;
            if (rounded > totalAmount && !suggestions.includes(rounded)) {
                suggestions.push(rounded);
            }
        }
    });

    const commonAmounts = [100, 200, 500, 1000];
    commonAmounts.forEach((amount) => {
        if (amount > totalAmount && !suggestions.includes(amount)) {
            suggestions.push(amount);
        }
    });

    return suggestions.sort((a, b) => a - b).slice(0, 5);
});

const calculateChange = () => {
    const payment = parseFloat(paymentAmount.value) || 0;
    change.value = payment - total.value;
};

const setPaymentAmount = (amount) => {
    paymentAmount.value = amount.toString();
    calculateChange();
};

const setExactAmount = () => {
    paymentAmount.value = total.value.toFixed(2);
    calculateChange();
};

const processSale = () => {
    const payment = parseFloat(paymentAmount.value) || 0;
    const changeAmount = payment - total.value;

    if (payment < total.value) {
        alert("Payment amount is insufficient!");
        return;
    }

    router.post(
        route("staff.pos.store"),
        {
            items: cart.value.map((item) => ({
                id: item.id,
                quantity: item.quantity,
            })),
            payment_amount: payment,
            change_amount: changeAmount,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                notify("Sale processed successfully!", "success");

                // Capture receipt data BEFORE clearing cart
                if (page.props.flash?.receipt_data) {
                    lastSaleReceipt.value = page.props.flash.receipt_data;
                }

                // Now clear the cart after receipt is set
                cart.value = [];
                paymentAmount.value = "";
                change.value = 0;
                showPaymentModal.value = false;
                // Keep drawer open to show receipt
                isCartOpen.value = true;

                // Auto-refresh the page to show updated stock quantities
                setTimeout(() => {
                    router.get(route("staff.pos"), {
                        query: searchQuery.value,
                        category: categoryFilter.value,
                        stock: stockFilter.value,
                        sort: sortBy.value,
                        order: sortOrder.value,
                    }, {
                        preserveState: true,
                        preserveScroll: true,
                        onSuccess: (page) => {
                            products.value = page.props.products || [];
                            stats.value = page.props.stats || {};
                        },
                    });
                }, 1500);
            },
            onError: () => {
                notify("Sale processing failed. Please try again.", "error");
            },
        }
    );
};

const generateReceipt = () => {
    try {
        const receiptData = lastSaleReceipt.value;

        if (!receiptData) {
            alert("No receipt data available");
            return;
        }

        const doc = new jsPDF({
            orientation: "portrait",
            unit: "mm",
            format: [80, 200],
        });

        let currentY = 8;

        doc.setFontSize(14);
        doc.setFont(undefined, "bold");
        doc.text(
            "3R QUICKPAY MINIMART",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 6;

        doc.setFontSize(8);
        doc.setFont(undefined, "normal");
        doc.text(
            "123 Main Street",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 4;
        doc.text(
            "Surigao City, Philippines XXX",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 4;
        doc.text(
            "Tel: (XX) XXXX-XXXX",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 4;
        doc.text(
            "TIN: XXX-XXX-XXX-XXX",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 8;

        doc.setLineWidth(0.1);
        doc.line(5, currentY, 75, currentY);
        currentY += 6;

        doc.setFontSize(8);
        doc.setFont(undefined, "bold");
        doc.text(`RECEIPT #: ${receiptData.receipt_id}`, 5, currentY);
        currentY += 4;

        const currentDateTime = new Date();
        const formattedDate = currentDateTime.toLocaleDateString("en-PH", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
        });
        const formattedTime = currentDateTime.toLocaleTimeString("en-PH", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: true,
        });

        doc.setFont(undefined, "normal");
        doc.text(`Date: ${formattedDate}`, 5, currentY);
        currentY += 4;
        doc.text(`Time: ${formattedTime}`, 5, currentY);
        currentY += 4;
        doc.text(`Cashier: ${receiptData.staff_name}`, 5, currentY);
        currentY += 4;
        doc.text(`Terminal: POS-XXX`, 5, currentY);
        currentY += 8;

        doc.line(5, currentY, 75, currentY);
        currentY += 4;

        doc.setFont(undefined, "bold");
        doc.text("ITEM", 5, currentY);
        doc.text("QTY", 45, currentY, { align: "center" });
        doc.text("PRICE", 60, currentY, { align: "right" });
        doc.text("AMOUNT", 75, currentY, { align: "right" });
        currentY += 4;

        doc.line(5, currentY, 75, currentY);
        currentY += 4;

        doc.setFontSize(6);
        doc.setFont(undefined, "normal");
        receiptData.items.forEach((item) => {
            const itemName =
                item.display_name.length > 100
                    ? item.display_name.substring(0, 20) + "..."
                    : item.display_name;

            doc.text(itemName, 5, currentY);
            doc.text(item.quantity.toString(), 45, currentY, {
                align: "center",
            });
            doc.text(`PHP ${parseFloat(item.price).toFixed(2)}`, 60, currentY, {
                align: "right",
            });
            doc.text(
                `PHP ${parseFloat(item.subtotal).toFixed(2)}`,
                75,
                currentY,
                { align: "right" }
            );
            currentY += 4;
        });

        currentY += 2;

        doc.line(5, currentY, 75, currentY);
        currentY += 4;

        doc.setFont(undefined, "normal");
        doc.text("SUBTOTAL:", 5, currentY);
        doc.text(
            `PHP ${parseFloat(receiptData.total).toFixed(2)}`,
            75,
            currentY,
            { align: "right" }
        );
        currentY += 4;

        const subtotal = parseFloat(receiptData.total);
        const vatAmount = 0;
        doc.text("VAT (0%):", 5, currentY);
        doc.text(`PHP ${vatAmount.toFixed(2)}`, 75, currentY, {
            align: "right",
        });
        currentY += 4;

        doc.setFont(undefined, "bold");
        doc.setFontSize(10);
        doc.text("TOTAL:", 5, currentY);
        doc.text(
            `PHP ${parseFloat(receiptData.total).toFixed(2)}`,
            75,
            currentY,
            { align: "right" }
        );
        currentY += 6;

        if (receiptData.payment_amount) {
            doc.setFontSize(8);
            doc.setFont(undefined, "normal");
            doc.text("CASH PAYMENT:", 5, currentY);
            doc.text(
                `PHP ${parseFloat(receiptData.payment_amount).toFixed(2)}`,
                75,
                currentY,
                { align: "right" }
            );
            currentY += 4;

            if (receiptData.change_amount) {
                doc.setFont(undefined, "bold");
                doc.text("CHANGE:", 5, currentY);
                doc.text(
                    `PHP ${parseFloat(receiptData.change_amount).toFixed(2)}`,
                    75,
                    currentY,
                    { align: "right" }
                );
                currentY += 6;
            }
        }

        doc.line(5, currentY, 75, currentY);
        currentY += 6;

        doc.setFontSize(8);
        doc.setFont(undefined, "normal");
        doc.text(
            "VAT REG TIN: XXX-XXX-XXX-XXX",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 4;
        doc.text(
            "THIS SERVES AS YOUR OFFICIAL RECEIPT",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 6;

        doc.setFont(undefined, "bold");
        doc.text(
            "Thank you for buying with us!",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 4;
        doc.setFont(undefined, "normal");
        doc.text(
            "Please come again",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 6;

        doc.setFontSize(7);
        doc.text(
            "Store Hours: Mon-Sat 8AM-5PM",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );
        currentY += 3;
        doc.text(
            "Customer Hotline: (XX) XXXX-XXXX",
            doc.internal.pageSize.getWidth() / 2,
            currentY,
            { align: "center" }
        );

        const pdfBlob = doc.output("blob");
        const pdfUrl = URL.createObjectURL(pdfBlob);
        window.open(pdfUrl, "_blank");

        doc.save(`receipt-${receiptData.receipt_id}.pdf`);
    } catch (error) {
        notify(
            "Error generating receipt. Please check the console for details."
        );
    }
};

const showPaymentModal = ref(false);
const closePaymentModal = () => {
    showPaymentModal.value = false;
    // paymentAmount.value = "";
    // change.value = 0;
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Enhanced Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(241, 245, 249, 0.5);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #cbd5e1, #94a3b8);
    border-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #94a3b8, #64748b);
}

/* Custom Pulse Animations for Stock Status */
@keyframes pulse-red {

    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    }

    50% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
    }
}

@keyframes pulse-orange {

    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7);
    }

    50% {
        box-shadow: 0 0 0 8px rgba(249, 115, 22, 0);
    }
}

@keyframes pulse-green {

    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }

    50% {
        box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
    }
}

.pulse-red {
    animation: pulse-red 2s infinite;
}

.pulse-orange {
    animation: pulse-orange 2s infinite;
}

.pulse-green {
    animation: pulse-green 2s infinite;
}

/* Glass Morphism Effect */
.glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Smooth Transitions for Interactive Elements */
.smooth-transition {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Enhanced Button Hover Effects */
.btn-hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

/* Subtle Glow Effects */
.glow-blue {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
}

.glow-green {
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}

/* Card Hover Animation */
.card-hover:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Hide number input spinners/arrows */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

/* Responsive Grid Improvements */
@media (max-width: 640px) {
    .grid-responsive {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }
}

@media (min-width: 1400px) {
    .grid-responsive {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
}

/* Slide Down Transition for Filters */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
}

.slide-down-enter-from {
    opacity: 0;
    transform: translateY(-10px);
    max-height: 0;
}

.slide-down-enter-to {
    opacity: 1;
    transform: translateY(0);
    max-height: 200px;
}

.slide-down-leave-from {
    opacity: 1;
    transform: translateY(0);
    max-height: 200px;
}

.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
    max-height: 0;
}
</style>