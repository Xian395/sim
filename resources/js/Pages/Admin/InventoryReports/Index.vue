<template>
  <AdminAuthenticatedLayout>
    <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h1 class="text-2xl font-bold text-gray-800">Inventory Reports</h1>
                <p class="text-gray-600 mt-1">Monitor stock levels and inventory values across all products.</p>
              </div>
              <div class="text-right">
                <div class="text-sm text-gray-500">Last updated</div>
                <div class="text-lg font-semibold">{{ currentDateTime }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Inventory Report Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="space-y-6">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Inventory Overview</h3>
                  <p class="text-sm text-gray-600">Monitor stock levels and inventory values</p>
                </div>
                <div class="flex space-x-2">
                  <ButtonNew
                    types="secondary"
                    size="md"
                    @click="toggleFilters"
                  >
                    {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                  </ButtonNew>
                  <ButtonNew
                    types="pdf"
                    size="md"
                    tooltips="Export Inventory Report"
                    @click="exportInventoryReport"
                    v-if="inventoryReportData"
                  >
                    Export PDF
                  </ButtonNew>
                </div>
              </div>

              <!-- Filters Section -->
              <div v-if="inventoryReportData && showFilters" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <!-- Search Filter -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Product</label>
                    <input
                      v-model="filters.search"
                      type="text"
                      placeholder="Search by product name..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <!-- Brand Filter -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <select
                      v-model="filters.brand"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="">All Brands</option>
                      <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                    </select>
                  </div>

                  <!-- Status Filter -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select
                      v-model="filters.status"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="">All Status</option>
                      <option value="In Stock">In Stock</option>
                      <option value="Low Stock">Low Stock</option>
                      <option value="Out of Stock">Out of Stock</option>
                    </select>
                  </div>

                  <!-- Stock Quantity Range -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                    <div class="flex space-x-2">
                      <input
                        v-model.number="filters.minStock"
                        type="number"
                        placeholder="Min"
                        min="0"
                        class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                      <input
                        v-model.number="filters.maxStock"
                        type="number"
                        placeholder="Max"
                        min="0"
                        class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                    </div>
                  </div>

                  <!-- Price Range -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                    <div class="flex space-x-2">
                      <input
                        v-model.number="filters.minPrice"
                        type="number"
                        placeholder="Min"
                        min="0"
                        step="0.01"
                        class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                      <input
                        v-model.number="filters.maxPrice"
                        type="number"
                        placeholder="Max"
                        min="0"
                        step="0.01"
                        class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                    </div>
                  </div>

                  <!-- Clear Filters Button -->
                  <div class="flex items-end">
                    <ButtonNew
                      types="secondary"
                      size="md"
                      @click="clearFilters"
                      class="w-full"
                    >
                      Clear Filters
                    </ButtonNew>
                  </div>
                </div>

                <!-- Active Filters Display -->
                <div v-if="hasActiveFilters" class="mt-3 flex flex-wrap gap-2">
                  <span class="text-sm font-medium text-gray-700">Active filters:</span>
                  <span v-if="filters.search" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Search: "{{ filters.search }}"
                    <button @click="filters.search = ''" class="ml-1 text-blue-600 hover:text-blue-800">×</button>
                  </span>
                  <span v-if="filters.brand" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Brand: {{ filters.brand }}
                    <button @click="filters.brand = ''" class="ml-1 text-green-600 hover:text-green-800">×</button>
                  </span>
                  <span v-if="filters.status" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Status: {{ filters.status }}
                    <button @click="filters.status = ''" class="ml-1 text-yellow-600 hover:text-yellow-800">×</button>
                  </span>
                  <span v-if="filters.minStock !== null || filters.maxStock !== null" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    Stock: {{ filters.minStock || 0 }}-{{ filters.maxStock || '∞' }}
                    <button @click="filters.minStock = null; filters.maxStock = null" class="ml-1 text-purple-600 hover:text-purple-800">×</button>
                  </span>
                  <span v-if="filters.minPrice !== null || filters.maxPrice !== null" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Price: ₱{{ filters.minPrice || 0 }}-₱{{ filters.maxPrice || '∞' }}
                    <button @click="filters.minPrice = null; filters.maxPrice = null" class="ml-1 text-red-600 hover:text-red-800">×</button>
                  </span>
                </div>

                <!-- Results Count -->
                <div v-if="filteredProducts" class="mt-3 text-sm text-gray-600">
                  Showing {{ filteredProducts.length }} of {{ inventoryReportData.products.length }} products
                </div>
              </div>

              <div v-if="inventoryReportData" class="space-y-6">
                  <!-- Summary Stats -->
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="text-sm font-medium text-blue-600">Total Products</div>
                          <div class="text-2xl font-bold text-blue-900">
                            {{ hasActiveFilters ? filteredSummary.totalProducts : inventoryReportData.summary.totalProducts }}
                          </div>
                        </div>
                        <div class="text-blue-500">
                          <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="text-sm font-medium text-green-600">Total Value</div>
                          <div class="text-2xl font-bold text-green-900">
                            ₱{{ formatCurrency(hasActiveFilters ? filteredSummary.totalValue : inventoryReportData.summary.totalValue || 0) }}
                          </div>
                        </div>
                        <div class="text-green-500">
                          <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 p-4 rounded-lg border border-yellow-200">
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="text-sm font-medium text-yellow-600">Low Stock</div>
                          <div class="text-2xl font-bold text-yellow-900">
                            {{ hasActiveFilters ? filteredSummary.lowStockCount : inventoryReportData.summary.lowStockCount }}
                          </div>
                        </div>
                        <div class="text-yellow-500">
                          <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <div class="bg-gradient-to-r from-red-50 to-red-100 p-4 rounded-lg border border-red-200">
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="text-sm font-medium text-red-600">Out of Stock</div>
                          <div class="text-2xl font-bold text-red-900">
                            {{ hasActiveFilters ? filteredSummary.outOfStockCount : inventoryReportData.summary.outOfStockCount }}
                          </div>
                        </div>
                        <div class="text-red-500">
                          <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Inventory Details Table -->
                  <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                      <h4 class="text-lg font-medium text-gray-900">Product Inventory Details</h4>
                    </div>
                    <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                          <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                          <tr v-if="filteredProducts.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center">
                              <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters to see more results.</p>
                                <div class="mt-6">
                                  <ButtonNew
                                    types="secondary"
                                    size="sm"
                                    @click="clearFilters"
                                  >
                                    Clear all filters
                                  </ButtonNew>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr v-else v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                              {{ product.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                              {{ product.brand }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {{ product.stock_quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              ₱{{ formatCurrency(product.price) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              ₱{{ formatCurrency(product.value) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                              <span :class="getStatusBadgeClass(product.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ product.status }}
                              </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                              <div class="flex space-x-2">
                                <button
                                  @click="viewStockHistory(product.id)"
                                  class="text-blue-600 hover:text-blue-900 font-medium flex items-center"
                                >
                                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                  </svg>
                                  Stock History
                                </button>
                                <button
                                  @click="viewSaleHistory(product.id)"
                                  class="text-green-600 hover:text-green-900 font-medium flex items-center"
                                >
                                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                  </svg>
                                  Sale History
                                </button>
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

              <div v-if="loadingInventoryReport" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-600">Loading inventory report...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else>
      <div>
        <UnAuthorized />
      </div>
    </div>

    <Modal :show="showStockHistoryModal" @close="closeStockHistoryModal" max-width="4xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Stock Movement History - {{ selectedProductName }}</h3>
          <button @click="closeStockHistoryModal" class="text-gray-400 hover:text-gray-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div v-if="loadingStockHistory" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600">Loading stock history...</p>
        </div>

        <div v-else-if="stockHistory && stockHistory.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="entry in stockHistory" :key="entry.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(entry.created_at) }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                  <span v-if="entry.action === 'stock_in'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Stock In
                  </span>
                  <span v-else-if="entry.action === 'stock_out'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    Stock Out
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ entry.action }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                  <span :class="entry.action === 'stock_in' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                    {{ entry.action === 'stock_in' ? '+' : '-' }}{{ entry.quantity }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                  {{ entry.user }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                  {{ entry.details }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="mt-2">No stock movement history found for this product.</p>
        </div>

        <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
          <ButtonNew
            types="cancel"
            size="md"
            @click="closeStockHistoryModal"
          >
            Close
          </ButtonNew>
        </div>
      </div>
    </Modal>

    <Modal :show="showSaleHistoryModal" @close="closeSaleHistoryModal" max-width="4xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Product Sale History - {{ selectedProductName }}</h3>
            <p class="text-sm text-gray-500 mt-1">Showing sales from the last 7 days</p>
          </div>
          <button @click="closeSaleHistoryModal" class="text-gray-400 hover:text-gray-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div v-if="loadingSaleHistory" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
          <p class="mt-2 text-gray-600">Loading sale history...</p>
        </div>

        <div v-else-if="saleHistory && saleHistory.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="entry in saleHistory" :key="entry.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(entry.date) }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    {{ entry.brand }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                  <span class="text-green-600 font-semibold">{{ entry.quantity }}</span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                  {{ entry.details }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          <p class="mt-2">No sales found for this product in the last 7 days.</p>
        </div>

        <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
          <ButtonNew
            types="cancel"
            size="md"
            @click="closeSaleHistoryModal"
          >
            Close
          </ButtonNew>
        </div>
      </div>
    </Modal>
  </AdminAuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import ButtonNew from '@/Components/ButtonNew.vue';
import UnAuthorized from "@/Components/UnAuthorized.vue";
import Modal from '@/Components/Modal.vue';
import { notify2, getLogoBase64 } from "@/globalFunctions.js";
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

const $page = usePage();

const inventoryReportData = ref(null);
const loadingInventoryReport = ref(false);
const currentDateTime = ref('');
const showFilters = ref(false);

const filters = ref({
  search: '',
  brand: '',
  status: '',
  minStock: null,
  maxStock: null,
  minPrice: null,
  maxPrice: null
});

const showStockHistoryModal = ref(false);
const loadingStockHistory = ref(false);
const stockHistory = ref([]);
const selectedProductName = ref('');

const showSaleHistoryModal = ref(false);
const loadingSaleHistory = ref(false);
const saleHistory = ref([]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0);
};

const availableBrands = computed(() => {
  if (!inventoryReportData.value?.products) return [];
  const brands = [...new Set(inventoryReportData.value.products.map(product => product.brand))];
  return brands.filter(brand => brand && brand !== 'No Brand').sort();
});

const filteredProducts = computed(() => {
  if (!inventoryReportData.value?.products) return [];

  let filtered = inventoryReportData.value.products;

  if (filters.value.search) {
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(filters.value.search.toLowerCase())
    );
  }

  if (filters.value.brand) {
    filtered = filtered.filter(product => product.brand === filters.value.brand);
  }

  if (filters.value.status) {
    filtered = filtered.filter(product => product.status === filters.value.status);
  }

  if (filters.value.minStock !== null) {
    filtered = filtered.filter(product => product.stock_quantity >= filters.value.minStock);
  }

  if (filters.value.maxStock !== null) {
    filtered = filtered.filter(product => product.stock_quantity <= filters.value.maxStock);
  }

  if (filters.value.minPrice !== null) {
    filtered = filtered.filter(product => product.price >= filters.value.minPrice);
  }

  if (filters.value.maxPrice !== null) {
    filtered = filtered.filter(product => product.price <= filters.value.maxPrice);
  }

  return filtered;
});

const hasActiveFilters = computed(() => {
  return filters.value.search ||
         filters.value.brand ||
         filters.value.status ||
         filters.value.minStock !== null ||
         filters.value.maxStock !== null ||
         filters.value.minPrice !== null ||
         filters.value.maxPrice !== null;
});

const toggleFilters = () => {
  showFilters.value = !showFilters.value;
};

const clearFilters = () => {
  filters.value = {
    search: '',
    brand: '',
    status: '',
    minStock: null,
    maxStock: null,
    minPrice: null,
    maxPrice: null
  };
};

const filteredSummary = computed(() => {
  if (!filteredProducts.value) return null;

  const totalProducts = filteredProducts.value.length;
  const totalValue = filteredProducts.value.reduce((sum, product) => sum + product.value, 0);
  const lowStockCount = filteredProducts.value.filter(product => product.status === 'Low Stock').length;
  const outOfStockCount = filteredProducts.value.filter(product => product.status === 'Out of Stock').length;

  return {
    totalProducts,
    totalValue,
    lowStockCount,
    outOfStockCount
  };
});

const updateDateTime = () => {
  const now = new Date();
  currentDateTime.value = now.toLocaleString('en-PH', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const loadInventoryReport = async () => {
  loadingInventoryReport.value = true;
  try {
    const response = await fetch('/admin/inventory-reports/report');
    const data = await response.json();
    inventoryReportData.value = data;
  } catch (error) {
    console.error('Error loading inventory report:', error);
    notify2('Failed to load inventory report', 'error');
  } finally {
    loadingInventoryReport.value = false;
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Out of Stock':
      return 'bg-red-100 text-red-800';
    case 'Low Stock':
      return 'bg-yellow-100 text-yellow-800';
    case 'In Stock':
      return 'bg-green-100 text-green-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const viewStockHistory = async (productId) => {
  const product = inventoryReportData.value.products.find(p => p.id === productId);
  if (!product) return;

  selectedProductName.value = product.name;
  showStockHistoryModal.value = true;
  loadingStockHistory.value = true;

  try {
    const response = await fetch(`/admin/inventory-reports/stock-history/${productId}`);
    const data = await response.json();
    stockHistory.value = data;
  } catch (error) {
    console.error('Error loading stock history:', error);
    notify2('Failed to load stock history', 'error');
  } finally {
    loadingStockHistory.value = false;
  }
};

const closeStockHistoryModal = () => {
  showStockHistoryModal.value = false;
  stockHistory.value = [];
  selectedProductName.value = '';
};

const viewSaleHistory = async (productId) => {
  const product = inventoryReportData.value.products.find(p => p.id === productId);
  if (!product) return;

  selectedProductName.value = product.name;
  showSaleHistoryModal.value = true;
  loadingSaleHistory.value = true;

  try {
    const response = await fetch(`/admin/inventory-reports/sale-history/${productId}`);
    const data = await response.json();
    saleHistory.value = data;
  } catch (error) {
    console.error('Error loading sale history:', error);
    notify2('Failed to load sale history', 'error');
  } finally {
    loadingSaleHistory.value = false;
  }
};

const closeSaleHistoryModal = () => {
  showSaleHistoryModal.value = false;
  saleHistory.value = [];
  selectedProductName.value = '';
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const exportInventoryReport = async () => {
  if (!inventoryReportData.value) return;

  try {
    const doc = new jsPDF();
    const pageWidth = doc.internal.pageSize.width;

    const logoBase64 = await getLogoBase64();

    let yPosition = 8;

    if (logoBase64) {
      const logoWidth = 20;
      const logoHeight = 20;
      const logoX = (pageWidth - logoWidth) / 2;

      doc.addImage(logoBase64, 'PNG', logoX, yPosition, logoWidth, logoHeight);
      yPosition += logoHeight + 2;
    }

    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    const title = "INVENTORY REPORT";
    const titleWidth = doc.getTextWidth(title);
    doc.text(title, (pageWidth - titleWidth) / 2, yPosition);

    yPosition += 4;

    doc.setFont(undefined, 'normal');
    doc.setFontSize(11);
    const reportDate = `Report Date: ${new Date().toLocaleDateString()}`;
    const reportDateWidth = doc.getTextWidth(reportDate);
    doc.text(reportDate, (pageWidth - reportDateWidth) / 2, yPosition);

    yPosition += 10;

    doc.setFontSize(13);
    doc.text("Summary", 14, yPosition);

    yPosition += 8;

    doc.setFontSize(10);
    doc.text(`• Total Products: ${inventoryReportData.value.summary.totalProducts}`, 18, yPosition);
    yPosition += 7;
    doc.text(`• Total Inventory Value: PHP ${formatCurrency(inventoryReportData.value.summary.totalValue)}`, 18, yPosition);
    yPosition += 7;
    doc.text(`• Low Stock Items: ${inventoryReportData.value.summary.lowStockCount}`, 18, yPosition);
    yPosition += 7;
    doc.text(`• Out of Stock Items: ${inventoryReportData.value.summary.outOfStockCount}`, 18, yPosition);
    yPosition += 10;

    const tableData = inventoryReportData.value.products.map(product => [
      product.name,
      product.brand,
      product.stock_quantity.toString(),
      `PHP ${formatCurrency(product.price)}`,
      `PHP ${formatCurrency(product.value)}`,
      product.status
    ]);

    autoTable(doc, {
      head: [['Product Name', 'Brand', 'Stock Qty', 'Unit Price', 'Total Value', 'Status']],
      body: tableData,
      startY: yPosition,
      styles: { fontSize: 9 },
      headStyles: { fillColor: [59, 130, 246], textColor: 255 },
      margin: { left: 14, right: 14, top: 10 }
    });

    const finalY = doc.lastAutoTable.finalY || yPosition + 10;
    const generatedText = `Generated on: ${new Date().toLocaleString()}`;
    const textWidth = doc.getTextWidth(generatedText);
    doc.setFontSize(9);
    doc.text(generatedText, pageWidth - 14 - textWidth, finalY + 8);

    const filename = `inventory-report-${new Date().toISOString().split('T')[0]}.pdf`;
    const pdfBlob = doc.output('blob');
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, '_blank');

    notify2('Inventory report exported successfully!', 'success');
  } catch (error) {
    alert("Error generating PDF. Please try again.");
    console.error(error);
  }
};

onMounted(() => {
  updateDateTime();
  setInterval(updateDateTime, 60000);
  loadInventoryReport();
});
</script>