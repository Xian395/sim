<template>
    <AuthenticatedLayout>
        <div
            class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 mt-12 sm:rounded-lg"
        >
            <div class="flex h-screen">
                <!-- Main Product Area -->
                <div class="flex-1 flex flex-col">
                    <!-- Search and Filter Bar -->
                    <div class="bg-white shadow-sm sm:rounded-lg px-6 py-4">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <!-- Search Input -->
                            <div class="relative flex-1 max-w-2xl">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                >
                                    <svg
                                        class="h-5 w-5 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                </div>
                                <input
                                    v-model="searchQuery"
                                    @input="debouncedSearch"
                                    type="text"
                                    placeholder="Search"
                                    class="block w-full pl-10 pr-3 py-3 text-lg border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                />
                                <div
                                    v-if="searchQuery"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <button
                                        @click="clearSearch"
                                        class="text-gray-400 hover:text-gray-600"
                                    >
                                        <svg
                                            class="h-5 w-5"
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
                            </div>

                            <!-- Filters Row -->
                            <div class="flex flex-wrap gap-3 items-center">
                                <!-- Category Filter -->
                                <select
                                    v-model="categoryFilter"
                                    @change="applyFilters"
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                >
                                    <option value="">All Categories</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>

                                <!-- Stock Filter -->
                                <select
                                    v-model="stockFilter"
                                    @change="applyFilters"
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                >
                                    <option value="all">All Stock</option>
                                    <option value="available">Available</option>
                                    <option value="in_stock">
                                        In Stock (>5)
                                    </option>
                                    <option value="low_stock">
                                        Low Stock (1-5)
                                    </option>
                                    <option value="out_of_stock">
                                        Out of Stock
                                    </option>
                                </select>

                                <!-- Sort Options -->
                                <select
                                    v-model="sortBy"
                                    @change="applyFilters"
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                >
                                    <option value="name">Sort by Name</option>
                                    <option value="price">Sort by Price</option>
                                    <option value="stock">Sort by Stock</option>
                                    <option value="category">
                                        Sort by Category
                                    </option>
                                    <option value="item_code">
                                        Sort by Item Code
                                    </option>
                                    <option value="barcode">
                                        Sort by Barcode
                                    </option>
                                </select>

                                <!-- Sort Order -->
                                <button
                                    @click="toggleSortOrder"
                                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm flex items-center space-x-1"
                                    :title="
                                        sortOrder === 'asc'
                                            ? 'Ascending'
                                            : 'Descending'
                                    "
                                >
                                    <svg
                                        v-if="sortOrder === 'asc'"
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 11l5-5m0 0l5 5m-5-5v12"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 13l-5 5m0 0l-5-5m5 5V6"
                                        />
                                    </svg>
                                </button>

                                <!-- Clear Filters -->
                                <button
                                    v-if="hasActiveFilters"
                                    @click="clearAllFilters"
                                    class="px-3 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <!-- Product Stats -->
                        <div
                            v-if="stats"
                            class="flex flex-wrap gap-4 mt-4 text-sm text-gray-600"
                        >
                            <span class="flex items-center">
                                <div
                                    class="w-2 h-2 bg-blue-500 rounded-full mr-2"
                                ></div>
                                Total: {{ stats.total_products }}
                            </span>
                            <span class="flex items-center">
                                <div
                                    class="w-2 h-2 bg-green-500 rounded-full mr-2"
                                ></div>
                                In Stock: {{ stats.in_stock }}
                            </span>
                            <span class="flex items-center">
                                <div
                                    class="w-2 h-2 bg-orange-500 rounded-full mr-2"
                                ></div>
                                Low Stock: {{ stats.low_stock }}
                            </span>
                            <span class="flex items-center">
                                <div
                                    class="w-2 h-2 bg-red-500 rounded-full mr-2"
                                ></div>
                                Out of Stock: {{ stats.out_of_stock }}
                            </span>
                            <span class="ml-auto font-medium">
                                Showing {{ products.length }} products
                            </span>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="flex-1 p-4 overflow-y-auto">
                        <div class="flex flex-wrap justify-center gap-8">
                            <div
                                v-for="product in products"
                                :key="product.id"
                                @click="addToCart(product)"
                                class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:scale-[1.02] border border-gray-100 hover:border-blue-300 flex flex-col justify-between"
                                :class="{
                                    'opacity-50': product.stock_quantity === 0,
                                }"
                                style="
                                    width: 200px;
                                    height: 215px;
                                    padding: 1.5rem;
                                "
                            >
                                <!-- Stock Status Indicator -->
                                <div
                                    class="absolute top-4 right-4 flex space-x-1"
                                >
                                    <div
                                        v-if="product.stock_quantity === 0"
                                        class="w-3 h-3 bg-red-500 rounded-full border-2 border-white shadow-sm"
                                    ></div>
                                    <div
                                        v-else-if="product.stock_quantity <= 5"
                                        class="w-3 h-3 bg-orange-500 rounded-full border-2 border-white shadow-sm"
                                    ></div>
                                    <div
                                        v-else
                                        class="w-3 h-3 bg-green-500 rounded-full border-2 border-white shadow-sm"
                                    ></div>
                                </div>

                                <!-- Product Image -->
                                <!-- Product Image -->
                                <div class="flex justify-center mb-3">
                                    <div
                                        class="w-16 h-16 rounded-lg overflow-hidden shadow-lg relative"
                                    >
                                        <img
                                            v-if="product.image_path"
                                            :src="
                                                getImageUrl(product.image_path)
                                            "
                                            :alt="product.name"
                                            class="w-full h-full object-cover"
                                            @error="handleImageError"
                                        />
                                        <div
                                            class="w-full h-full rounded-lg bg-gradient-to-br flex items-center justify-center absolute inset-0"
                                            :class="[
                                                getProductGradient(product.id),
                                                product.image_path
                                                    ? 'gradient-fallback hidden'
                                                    : '',
                                            ]"
                                        >
                                            <svg
                                                class="w-6 h-6 text-white"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <!-- Product Info -->
                                <div
                                    class="text-center flex-1 flex flex-col justify-between min-h-0"
                                >
                                    <div class="mb-2">
                                        <h3
                                            class="font-semibold text-gray-900 text-sm leading-tight mb-1 line-clamp-2 overflow-hidden"
                                            style="
                                                display: -webkit-box;
                                                -webkit-line-clamp: 2;
                                                -webkit-box-orient: vertical;
                                                max-height: 2.5rem;
                                            "
                                        >
                                            {{ product.name }}
                                        </h3>
                                        <p
                                            class="text-xs text-gray-500 mb-2 truncate"
                                        >
                                            {{
                                                product.category_names ||
                                                "Uncategorized"
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="text-lg font-bold text-gray-900 mb-1"
                                        >
                                            ₱{{
                                                parseFloat(
                                                    product.price
                                                ).toFixed(2)
                                            }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            <span
                                                v-if="
                                                    product.stock_quantity === 0
                                                "
                                                class="text-red-600 font-medium"
                                                >Out of Stock</span
                                            >
                                            <span
                                                v-else-if="
                                                    product.stock_quantity <= 5
                                                "
                                                class="text-orange-600 font-medium"
                                                >Low ({{
                                                    product.stock_quantity
                                                }})</span
                                            >
                                            <span
                                                v-else
                                                class="text-green-600 font-medium"
                                                >Stock ({{
                                                    product.stock_quantity
                                                }})</span
                                            >
                                        </p>
                                    </div>
                                </div>

                                <!-- Hover Effect -->
                                <div
                                    class="absolute inset-0 bg-blue-50 rounded-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-300"
                                ></div>

                                <!-- Add to Cart Icon (appears on hover) -->
                                <div
                                    class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                >
                                    <div
                                        class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center shadow-lg"
                                    >
                                        <svg
                                            class="w-3 h-3 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-if="products.length === 0"
                            class="text-center py-16"
                        >
                            <svg
                                class="mx-auto h-24 w-24 text-gray-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2-2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">
                                No products found
                            </h3>
                            <p class="mt-2 text-gray-500">
                                Try adjusting your search terms
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cart Sidebar -->
                <div
                    class="w-96 bg-white shadow-2xl border-l border-gray-200 flex flex-col"
                >
                    <!-- Cart Header -->
                    <div
                        class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold">Current Order</h3>
                                <p class="text-blue-100 text-sm">
                                    {{ cart.length }} item{{
                                        cart.length !== 1 ? "s" : ""
                                    }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold">
                                    ₱{{ total.toFixed(2) }}
                                </p>
                                <p class="text-blue-100 text-xs">Total</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div class="flex-1 overflow-y-auto">
                        <div
                            v-if="cart.length === 0"
                            class="flex flex-col items-center justify-center h-full text-gray-500 p-8"
                        >
                            <svg
                                class="w-20 h-20 mb-4 text-gray-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.8 7.2M7 13l-1.8 7.2m0 0h12.6M7 13v8a2 2 0 002 2h6a2 2 0 002-2v-8"
                                />
                            </svg>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">
                                Cart is empty
                            </h4>
                            <p class="text-center text-gray-500">
                                Select products to add them to your order
                            </p>
                        </div>

                        <div v-else class="p-4 space-y-3">
                            <div
                                v-for="(item, index) in cart"
                                :key="index"
                                class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:bg-gray-100 transition-colors"
                            >
                                <div
                                    class="flex justify-between items-start mb-3"
                                >
                                    <div class="flex-1 pr-3">
                                        <h4
                                            class="font-semibold text-gray-900 text-sm leading-tight mb-1"
                                        >
                                            {{ item.name }}
                                        </h4>
                                        <p class="text-xs text-gray-500">
                                            {{ item.barcode }} •
                                            {{
                                                item.category?.name ||
                                                "No Category"
                                            }}
                                        </p>
                                    </div>
                                    <button
                                        @click="removeFromCart(index)"
                                        class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded-lg transition-colors"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div
                                        class="flex items-center bg-white rounded-lg border border-gray-200"
                                    >
                                        <button
                                            @click="decrementQuantity(index)"
                                            class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-l-lg transition-colors"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 12H4"
                                                />
                                            </svg>
                                        </button>
                                        <span
                                            class="w-12 text-center text-sm font-semibold py-2 border-x border-gray-200"
                                            >{{ item.quantity }}</span
                                        >
                                        <button
                                            @click="incrementQuantity(index)"
                                            :disabled="
                                                item.quantity >=
                                                item.stock_quantity
                                            "
                                            class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-r-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">
                                            ₱{{
                                                parseFloat(item.price).toFixed(
                                                    2
                                                )
                                            }}
                                            each
                                        </p>
                                        <p class="font-bold text-gray-900">
                                            ₱{{
                                                (
                                                    item.quantity * item.price
                                                ).toFixed(2)
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary & Actions -->
                    <!-- Cart Summary & Actions -->
                    <div class="border-t border-gray-200 bg-gray-50 p-6">
                        <!-- Order Summary -->
                        <div class="space-y-3 mb-6">
                            <div
                                class="flex justify-between text-lg font-bold border-t pt-3"
                            >
                                <span>Total:</span>
                                <span class="text-blue-600"
                                    >₱{{ total.toFixed(2) }}</span
                                >
                            </div>
                        </div>

                        <!-- Complete Sale Button -->
                        <button
                            @click="showPaymentModal = true"
                            :disabled="cart.length === 0"
                            class="w-full py-4 rounded-xl font-bold text-lg transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2 mb-4"
                            :class="
                                cart.length === 0
                                    ? 'bg-gray-400 text-white cursor-not-allowed'
                                    : 'bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800'
                            "
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
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                            <span>
                                {{
                                    cart.length === 0
                                        ? "Add items to cart"
                                        : "Proceed to Payment"
                                }}
                            </span>
                        </button>

                        <!-- Receipt Section -->
                        <div
                            v-if="lastSaleReceipt"
                            class="p-4 bg-green-50 border border-green-200 rounded-xl"
                        >
                            <div class="flex items-center mb-3">
                                <svg
                                    class="w-5 h-5 text-green-600 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <h4 class="font-bold text-green-800">
                                    Sale Completed!
                                </h4>
                            </div>

                            <!-- Transaction Summary -->
                            <div class="text-sm text-green-700 mb-4 space-y-1">
                                <p>
                                    <strong>Receipt:</strong>
                                    {{ lastSaleReceipt.receipt_id }}
                                </p>
                                <p>
                                    <strong>Total:</strong> ₱{{
                                        parseFloat(
                                            lastSaleReceipt.total
                                        ).toFixed(2)
                                    }}
                                </p>
                                <p v-if="lastSaleReceipt.payment_amount">
                                    <strong>Payment:</strong> ₱{{
                                        parseFloat(
                                            lastSaleReceipt.payment_amount
                                        ).toFixed(2)
                                    }}
                                </p>
                                <p v-if="lastSaleReceipt.change_amount">
                                    <strong>Change:</strong> ₱{{
                                        parseFloat(
                                            lastSaleReceipt.change_amount
                                        ).toFixed(2)
                                    }}
                                </p>
                            </div>

                            <button
                                @click="generateReceipt"
                                class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center space-x-2 font-semibold transition-colors"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                <span>Print Receipt</span>
                            </button>
                        </div>
                    </div>

                    <!-- Payment Modal -->
                    <div
                        v-if="showPaymentModal"
                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                    >
                        <div
                            class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto"
                        >
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold text-gray-900">
                                    Payment Details
                                </h3>
                                <button
                                    @click="closePaymentModal"
                                    class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100"
                                >
                                    <svg
                                        class="w-6 h-6"
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

                            <!-- Order Summary in Modal -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                                <h4 class="font-semibold text-gray-900 mb-3">
                                    Order Summary
                                </h4>
                                <div class="space-y-2 mb-4">
                                    <div
                                        v-for="(item, index) in cart"
                                        :key="index"
                                        class="flex justify-between text-sm"
                                    >
                                        <span
                                            >{{ item.name }} x{{
                                                item.quantity
                                            }}</span
                                        >
                                        <span
                                            >₱{{
                                                (
                                                    item.quantity * item.price
                                                ).toFixed(2)
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div
                                    class="border-t pt-3 flex justify-between items-center font-bold text-lg"
                                >
                                    <span>Total Amount:</span>
                                    <span class="text-blue-600"
                                        >₱{{ total.toFixed(2) }}</span
                                    >
                                </div>
                            </div>

                            <!-- Payment Amount Input -->
                            <div class="mb-6">
                                <label
                                    for="paymentAmount"
                                    class="block text-sm font-medium text-gray-700 mb-3"
                                >
                                    Amount Received
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                                    >
                                        <span class="text-gray-500 text-xl"
                                            >₱</span
                                        >
                                    </div>
                                    <input
                                        id="paymentAmount"
                                        v-model="paymentAmount"
                                        @input="calculateChange"
                                        @focus="$event.target.select()"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="block w-full pl-10 pr-4 py-4 text-xl font-semibold border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        :class="{
                                            'border-red-300 focus:ring-red-500':
                                                paymentAmount &&
                                                parseFloat(paymentAmount) <
                                                    total,
                                            'border-green-300 focus:ring-green-500':
                                                paymentAmount &&
                                                parseFloat(paymentAmount) >=
                                                    total,
                                        }"
                                    />
                                </div>

                                <!-- Quick Amount Buttons -->
                                <div class="flex flex-wrap gap-2 mt-4">
                                    <button
                                        v-for="amount in quickAmounts"
                                        :key="amount"
                                        @click="setPaymentAmount(amount)"
                                        class="px-4 py-2 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium"
                                    >
                                        ₱{{ amount }}
                                    </button>
                                    <button
                                        @click="setExactAmount"
                                        class="px-4 py-2 text-sm bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors font-medium"
                                    >
                                        Exact Amount
                                    </button>
                                </div>
                            </div>

                            <!-- Change Display -->
                            <div
                                v-if="
                                    paymentAmount &&
                                    parseFloat(paymentAmount) > 0
                                "
                                class="p-4 rounded-xl mb-6"
                                :class="
                                    change >= 0
                                        ? 'bg-green-50 border border-green-200'
                                        : 'bg-red-50 border border-red-200'
                                "
                            >
                                <div class="flex justify-between items-center">
                                    <span
                                        class="font-semibold text-lg"
                                        :class="
                                            change >= 0
                                                ? 'text-green-700'
                                                : 'text-red-700'
                                        "
                                    >
                                        {{
                                            change >= 0
                                                ? "Change:"
                                                : "Insufficient Payment:"
                                        }}
                                    </span>
                                    <span
                                        class="text-2xl font-bold"
                                        :class="
                                            change >= 0
                                                ? 'text-green-900'
                                                : 'text-red-900'
                                        "
                                    >
                                        ₱{{ Math.abs(change).toFixed(2) }}
                                    </span>
                                </div>

                                <!-- Insufficient payment warning -->
                                <div
                                    v-if="change < 0"
                                    class="mt-3 text-sm text-red-600 flex items-center"
                                >
                                    <svg
                                        class="w-4 h-4 mr-2"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    Need ₱{{
                                        (
                                            total - parseFloat(paymentAmount)
                                        ).toFixed(2)
                                    }}
                                    more
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <button
                                    @click="closePaymentModal"
                                    class="flex-1 py-3 px-4 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="processSale"
                                    :disabled="
                                        cart.length === 0 ||
                                        (paymentAmount &&
                                            parseFloat(paymentAmount) < total)
                                    "
                                    class="flex-1 py-3 px-4 rounded-xl font-semibold transition-all duration-200 flex items-center justify-center space-x-2"
                                    :class="
                                        cart.length === 0 ||
                                        (paymentAmount &&
                                            parseFloat(paymentAmount) < total)
                                            ? 'bg-gray-400 text-white cursor-not-allowed'
                                            : 'bg-gradient-to-r from-green-600 to-green-700 text-white hover:from-green-700 hover:to-green-800'
                                    "
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
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    <span>
                                        {{
                                            cart.length === 0
                                                ? "Add Items"
                                                : paymentAmount &&
                                                  parseFloat(paymentAmount) <
                                                      total
                                                ? "Insufficient Payment"
                                                : "Complete Sale"
                                        }}
                                    </span>
                                </button>
                            </div>
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

const getImageUrl = (imagePath) => {
    if (!imagePath) {
        return null;
    }

    if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
        return imagePath;
    }

    let normalizedPath = imagePath;
    if (!imagePath.startsWith("/")) {
        normalizedPath = `/storage/${imagePath}`;
    }

    const baseUrl = window.location.origin;
    return `${baseUrl}${normalizedPath}`;
};

const handleImageError = (event) => {
    console.warn(`Image failed to load: ${event.target.src}`);
    event.target.style.display = "none";
    const fallback =
        event.target.parentElement.querySelector(".gradient-fallback");
    if (fallback) {
        fallback.classList.remove("hidden");
    }
};

const getProductGradient = (productId) => {
    return productGradients[productId % productGradients.length];
};

watch(
    () => page.props.flash?.receipt_data,
    (newReceiptData) => {
        if (newReceiptData) {
            lastSaleReceipt.value = newReceiptData;
            console.log("Receipt data received:", newReceiptData);
            console.log(
                "Products in receipt:",
                newReceiptData.items.map((item) => item.display_name)
            );
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
            onSuccess: () => {
                notify("Sale processed successfully!", "success");
                cart.value = [];
                paymentAmount.value = "";
                change.value = 0;
                showPaymentModal.value = false;
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

::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
