<template>
    <AdminAuthenticatedLayout>
        <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Sales Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 p-6 rounded-lg shadow-lg text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Today's Sales</p>
                                <p class="text-2xl font-bold">₱{{ formatCurrency(salesSummary?.today || 0) }}</p>
                                <p class="text-green-100 text-xs mt-1">
                                    vs Yesterday: {{ salesSummary?.yesterday ? formatPercentageChange(salesSummary.today, salesSummary.yesterday) : 'N/A' }}
                                </p>
                            </div>
                            <div class="text-green-200">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 p-6 rounded-lg shadow-lg text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">This Week</p>
                                <p class="text-2xl font-bold">₱{{ formatCurrency(salesSummary?.thisWeek || 0) }}</p>
                                <p class="text-blue-100 text-xs mt-1">
                                    vs Last Week: {{ salesSummary?.lastWeek ? formatPercentageChange(salesSummary.thisWeek, salesSummary.lastWeek) : 'N/A' }}
                                </p>
                            </div>
                            <div class="text-blue-200">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-[#C70039] to-purple-600 p-6 rounded-lg shadow-lg text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">This Month</p>
                                <p class="text-2xl font-bold">₱{{ formatCurrency(salesSummary?.thisMonth || 0) }}</p>
                                <p class="text-purple-100 text-xs mt-1">
                                    vs Last Month: {{ salesSummary?.lastMonth ? formatPercentageChange(salesSummary.thisMonth, salesSummary.lastMonth) : 'N/A' }}
                                </p>
                            </div>
                            <div class="text-purple-200">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Tabs Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Business Reports</h2>

                        <!-- Tab Navigation -->
                        <div class="flex flex-wrap gap-3 mb-6">
                            <button
                                @click="activeTab = 'sales'"
                                :class="[
                                    'flex items-center px-6 py-3 rounded-lg font-semibold text-base transition-all duration-200 shadow-md',
                                    activeTab === 'sales'
                                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-blue-300 transform scale-105'
                                        : 'bg-white text-gray-700 border-2 border-blue-200 hover:border-blue-400 hover:bg-blue-50 hover:shadow-lg'
                                ]"
                            >
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Sales Reports
                            </button>

                            <button
                                @click="activeTab = 'brandSales'; brandReportFilters.period = 'daily'; loadBrandSalesReport()"
                                :class="[
                                    'flex items-center px-6 py-3 rounded-lg font-semibold text-base transition-all duration-200 shadow-md',
                                    activeTab === 'brandSales'
                                        ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-purple-300 transform scale-105'
                                        : 'bg-white text-gray-700 border-2 border-purple-200 hover:border-purple-400 hover:bg-purple-50 hover:shadow-lg'
                                ]"
                            >
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Brand Sales Report
                            </button>

                            <button
                                @click="activeTab = 'income'; incomeReportFilters.period = 'daily'; loadProducts(); loadIncomeReport()"
                                :class="[
                                    'flex items-center px-6 py-3 rounded-lg font-semibold text-base transition-all duration-200 shadow-md',
                                    activeTab === 'income'
                                        ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-emerald-300 transform scale-105'
                                        : 'bg-white text-gray-700 border-2 border-emerald-200 hover:border-emerald-400 hover:bg-emerald-50 hover:shadow-lg'
                                ]"
                            >
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Income & Profit Report
                            </button>
                        </div>

                        <!-- Tab Content -->
                        <div class="mt-6">

                            <!-- Brand Sales Report Tab -->
                            <div v-if="activeTab === 'brandSales'" class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Brand Sales Analysis</h3>
                                        <p class="text-sm text-gray-600">Track sales performance by brand across different periods</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <ButtonNew
                                            types="pdf"
                                            size="md"
                                            tooltips="Export Brand Sales Report"
                                            @click="exportBrandSalesReport"
                                            v-if="brandSalesReportData"
                                        >
                                            Export PDF
                                        </ButtonNew>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Period</label>
                                        <select
                                            v-model="brandReportFilters.period"
                                            @change="loadBrandSalesReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        >
                                            <option value="" disabled>Select a period</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="range">Date Range</option>
                                        </select>
                                    </div>

                                    <div v-if="brandReportFilters.period === 'daily' || brandReportFilters.period === 'weekly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                        <input
                                            v-model="brandReportFilters.date"
                                            @change="loadBrandSalesReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        />
                                    </div>

                                    <div v-if="brandReportFilters.period === 'range'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                        <input
                                            v-model="brandReportFilters.startDate"
                                            @change="loadBrandSalesReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        />
                                    </div>

                                    <div v-if="brandReportFilters.period === 'range'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                        <input
                                            v-model="brandReportFilters.endDate"
                                            @change="loadBrandSalesReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        />
                                    </div>

                                    <div v-if="brandReportFilters.period === 'monthly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                        <select
                                            v-model="brandReportFilters.year"
                                            @change="loadBrandSalesReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        >
                                            <option v-for="year in availableYears" :key="year" :value="year">
                                                {{ year }}
                                            </option>
                                        </select>
                                    </div>

                                    <div v-if="brandReportFilters.period === 'monthly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                                        <select
                                            v-model="brandReportFilters.month"
                                            @change="loadBrandSalesReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        >
                                            <option v-for="(month, index) in months" :key="index" :value="index + 1">
                                                {{ month }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="brandSalesReportData" class="bg-white rounded-lg border border-gray-200">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h4 class="text-lg font-medium text-gray-900">Sales by Brand</h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="brandSale in brandSalesReportData.brandSales" :key="brandSale.brand" class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ brandSale.brand }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <span class="font-medium text-green-600">₱{{ formatCurrency(brandSale.totalAmount) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ brandSale.totalTransactions }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div v-if="loadingBrandSalesReport" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                                    <p class="mt-2 text-gray-600">Loading brand sales report...</p>
                                </div>
                            </div>

                            <!-- Income & Profit Report Tab -->
                            <div v-if="activeTab === 'income'" class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Income & Profit Analysis</h3>
                                        <p class="text-sm text-gray-600">Detailed profit tracking with FIFO costing and acquisition price history</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <ButtonNew
                                            types="pdf"
                                            size="md"
                                            tooltips="Export Income Report"
                                            @click="exportIncomeReport"
                                            v-if="incomeReportData"
                                        >
                                            Export PDF
                                        </ButtonNew>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Period</label>
                                        <select
                                            v-model="incomeReportFilters.period"
                                            @change="loadIncomeReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        >
                                            <option value="" disabled>Select a period</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="range">Date Range</option>
                                        </select>
                                    </div>

                                    <div v-if="incomeReportFilters.period === 'daily' || incomeReportFilters.period === 'weekly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                        <input
                                            v-model="incomeReportFilters.date"
                                            @change="loadIncomeReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>

                                    <div v-if="incomeReportFilters.period === 'range'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                        <input
                                            v-model="incomeReportFilters.startDate"
                                            @change="loadIncomeReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>

                                    <div v-if="incomeReportFilters.period === 'range'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                        <input
                                            v-model="incomeReportFilters.endDate"
                                            @change="loadIncomeReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>

                                    <div v-if="incomeReportFilters.period === 'monthly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                        <select
                                            v-model="incomeReportFilters.year"
                                            @change="loadIncomeReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        >
                                            <option v-for="year in availableYears" :key="year" :value="year">
                                                {{ year }}
                                            </option>
                                        </select>
                                    </div>

                                    <div v-if="incomeReportFilters.period === 'monthly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                                        <select
                                            v-model="incomeReportFilters.month"
                                            @change="loadIncomeReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        >
                                            <option v-for="(month, index) in months" :key="index" :value="index + 1">
                                                {{ month }}
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Product</label>
                                        <select
                                            v-model="incomeReportFilters.product"
                                            @change="loadIncomeReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        >
                                            <option value="">All Products</option>
                                            <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                                                {{ product.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Income Report Results -->
                                <div v-if="incomeReportData" class="space-y-6">
                                    <!-- Summary Stats -->
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                                            <div class="text-sm font-medium text-blue-600">Total Revenue</div>
                                            <div class="text-2xl font-bold text-blue-900">
                                                ₱{{ formatCurrency(incomeReportData.summary.total_revenue || 0) }}
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-r from-orange-50 to-orange-100 p-4 rounded-lg border border-orange-200">
                                            <div class="text-sm font-medium text-orange-600">Total Cost (FIFO)</div>
                                            <div class="text-2xl font-bold text-orange-900">
                                                ₱{{ formatCurrency(incomeReportData.summary.total_cost || 0) }}
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 p-4 rounded-lg border border-emerald-200">
                                            <div class="text-sm font-medium text-emerald-600">Net Profit</div>
                                            <div class="text-2xl font-bold text-emerald-900">
                                                ₱{{ formatCurrency(incomeReportData.summary.total_profit || 0) }}
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                                            <div class="text-sm font-medium text-purple-600">Profit Margin</div>
                                            <div class="text-2xl font-bold text-purple-900">
                                                {{ formatCurrency(incomeReportData.summary.profit_margin || 0) }}%
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Products Breakdown -->
                                    <div v-for="product in incomeReportData.products" :key="product.product_id" class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                        <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 px-6 py-4 border-b border-emerald-200">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <h4 class="text-lg font-bold text-emerald-900">{{ product.product_name }}</h4>
                                                    <p class="text-sm text-emerald-700">Item Code: {{ product.item_code }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm text-emerald-700">Profit Margin</div>
                                                    <div class="text-2xl font-bold text-emerald-900">{{ formatCurrency(product.profit_margin) }}%</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Product Summary -->
                                        <div class="grid grid-cols-4 gap-4 p-4 bg-gray-50">
                                            <div>
                                                <div class="text-xs text-gray-600">Quantity Sold</div>
                                                <div class="text-lg font-bold text-gray-900">{{ product.total_quantity_sold }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-600">Total Revenue</div>
                                                <div class="text-lg font-bold text-blue-600">₱{{ formatCurrency(product.total_revenue) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-600">Total Cost</div>
                                                <div class="text-lg font-bold text-orange-600">₱{{ formatCurrency(product.total_cost) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-600">Net Profit</div>
                                                <div class="text-lg font-bold text-emerald-600">₱{{ formatCurrency(product.total_profit) }}</div>
                                            </div>
                                        </div>

                                        <!-- Acquisition Price History -->
                                        <div v-if="product.acquisition_history && product.acquisition_history.length > 0" class="px-6 py-4 bg-yellow-50 border-t border-yellow-200">
                                            <h5 class="text-sm font-semibold text-yellow-900 mb-2">📊 Acquisition Price History (Stock Added in Period)</h5>
                                            <div class="grid grid-cols-5 gap-2 text-xs">
                                                <div v-for="(history, idx) in product.acquisition_history" :key="idx" class="bg-white p-2 rounded border border-yellow-200">
                                                    <div class="font-semibold text-yellow-900">{{ history.date }}</div>
                                                    <div class="text-gray-600">Qty: {{ history.quantity }}</div>
                                                    <div class="text-emerald-600 font-bold">₱{{ formatCurrency(history.acquisition_price) }}/unit</div>
                                                    <div class="text-gray-500 text-xs">Remaining: {{ history.remaining_quantity }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Transactions Table -->
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selling Price</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost (FIFO)</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin %</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr v-for="transaction in product.transactions" :key="transaction.id" class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ transaction.date }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ transaction.quantity }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600">₱{{ formatCurrency(transaction.selling_price) }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-orange-600">₱{{ formatCurrency(transaction.unit_cost) }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-900">₱{{ formatCurrency(transaction.revenue) }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-orange-900">₱{{ formatCurrency(transaction.cost) }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-emerald-600">₱{{ formatCurrency(transaction.profit) }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm" :class="transaction.profit_margin > 30 ? 'text-emerald-600 font-bold' : transaction.profit_margin > 15 ? 'text-yellow-600' : 'text-red-600'">
                                                            {{ formatCurrency(transaction.profit_margin) }}%
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ transaction.user }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="loadingIncomeReport" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
                                    <p class="mt-2 text-gray-600">Loading income report...</p>
                                </div>

                                <div v-if="!incomeReportData && !loadingIncomeReport && incomeReportFilters.period" class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-gray-600">No data available for the selected period</p>
                                </div>
                            </div>

                            <!-- Sales Reports Tab -->
                            <div v-if="activeTab === 'sales'" class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Sales Analytics</h3>
                                        <p class="text-sm text-gray-600">Comprehensive sales reporting with charts and breakdowns</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <ButtonNew
                                            types="pdf"
                                            size="md"
                                            tooltips="Export Sales Report"
                                            @click="exportSalesReport"
                                            v-if="salesReportData"
                                        >
                                            Export PDF
                                        </ButtonNew>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Period</label>
                                        <select
                                            v-model="reportFilters.period"
                                            @change="loadSalesReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="" disabled>Select a period</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="range">Date Range</option>
                                        </select>
                                    </div>

                                    <div v-if="reportFilters.period === 'daily' || reportFilters.period === 'weekly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                        <input
                                            v-model="reportFilters.date"
                                            @change="loadSalesReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div v-if="reportFilters.period === 'range'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                        <input
                                            v-model="reportFilters.startDate"
                                            @change="loadSalesReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div v-if="reportFilters.period === 'range'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                        <input
                                            v-model="reportFilters.endDate"
                                            @change="loadSalesReport"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div v-if="reportFilters.period === 'monthly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                        <select
                                            v-model="reportFilters.year"
                                            @change="loadSalesReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option v-for="year in availableYears" :key="year" :value="year">
                                                {{ year }}
                                            </option>
                                        </select>
                                    </div>

                                    <div v-if="reportFilters.period === 'monthly'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                                        <select
                                            v-model="reportFilters.month"
                                            @change="loadSalesReport"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option v-for="(month, index) in months" :key="index" :value="index + 1">
                                                {{ month }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Sales Report Results -->
                                <div v-if="salesReportData" class="space-y-6">
                                    <!-- Summary Stats -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                                            <div class="text-sm font-medium text-green-600">Total Sales</div>
                                            <div class="text-2xl font-bold text-green-900">
                                                ₱{{ formatCurrency(salesReportData.salesData.totalAmount || 0) }}
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                                            <div class="text-sm font-medium text-blue-600">Total Transactions</div>
                                            <div class="text-2xl font-bold text-blue-900">
                                                {{ salesReportData.salesData.totalTransactions || 0 }}
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                                            <div class="text-sm font-medium text-purple-600">Average Transaction</div>
                                            <div class="text-2xl font-bold text-purple-900">
                                                ₱{{ formatCurrency(salesReportData.salesData.averageTransaction || 0) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Chart -->
                                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                                        <h4 class="text-lg font-medium text-gray-900 mb-4">Sales Chart</h4>
                                        <div class="h-64">
                                            <canvas ref="salesChart"></canvas>
                                        </div>
                                    </div>

                                    <!-- Detailed Breakdown -->
                                    <div class="bg-white rounded-lg border border-gray-200">
                                        <div class="px-6 py-4 border-b border-gray-200">
                                            <h4 class="text-lg font-medium text-gray-900">Detailed Breakdown</h4>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ reportFilters.period === 'daily' ? 'Hour' : 'Date' }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Amount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr v-for="item in getBreakdownData()" :key="item.key" class="hover:bg-gray-50">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {{ item.label }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            ₱{{ formatCurrency(item.amount) }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {{ item.transactions }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="loadingSalesReport" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                    <p class="mt-2 text-gray-600">Loading sales report...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Staff Sales Report Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Staff Sales Report</h2>
                                <p class="text-gray-600 text-sm mt-1">Track individual staff member sales performance</p>
                            </div>
                            <div class="flex space-x-2">
                                <ButtonNew
                                    types="pdf"
                                    size="md"
                                    tooltips="Export Staff Sales Report"
                                    @click="exportStaffSalesReport"
                                    v-if="staffSalesReportData"
                                >
                                    Export PDF
                                </ButtonNew>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Report Period</label>
                                <select
                                    v-model="staffReportFilters.period"
                                    @change="loadStaffSalesReport"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="" disabled>Select a period</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="range">Date Range</option>
                                </select>
                            </div>

                            <div v-if="staffReportFilters.period === 'daily' || staffReportFilters.period === 'weekly'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input
                                    v-model="staffReportFilters.date"
                                    @change="loadStaffSalesReport"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div v-if="staffReportFilters.period === 'range'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input
                                    v-model="staffReportFilters.startDate"
                                    @change="loadStaffSalesReport"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div v-if="staffReportFilters.period === 'range'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input
                                    v-model="staffReportFilters.endDate"
                                    @change="loadStaffSalesReport"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div v-if="staffReportFilters.period === 'monthly'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Month & Year</label>
                                <input
                                    v-model="staffReportFilters.monthYear"
                                    @change="loadStaffSalesReport"
                                    type="month"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>
                        </div>

                        <!-- Staff Sales Data -->
                        <div v-if="staffSalesReportData" class="space-y-6">
                            <!-- Summary Stats -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 p-4 rounded-lg border border-indigo-200">
                                    <div class="text-sm font-medium text-indigo-600">Total Sales</div>
                                    <div class="text-2xl font-bold text-indigo-900">
                                        ₱{{ formatCurrency(staffSalesReportData.summary.totalSales || 0) }}
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                                    <div class="text-sm font-medium text-blue-600">Total Transactions</div>
                                    <div class="text-2xl font-bold text-blue-900">
                                        {{ staffSalesReportData.summary.totalTransactions || 0 }}
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                                    <div class="text-sm font-medium text-purple-600">Average per Staff</div>
                                    <div class="text-2xl font-bold text-purple-900">
                                        ₱{{ formatCurrency(staffSalesReportData.summary.averagePerStaff || 0) }}
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-cyan-50 to-cyan-100 p-4 rounded-lg border border-cyan-200">
                                    <div class="text-sm font-medium text-cyan-600">Total Staff</div>
                                    <div class="text-2xl font-bold text-cyan-900">
                                        {{ staffSalesReportData.staffSales?.length || 0 }}
                                    </div>
                                </div>
                            </div>

                            <!-- Staff Sales Table -->
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h4 class="text-lg font-medium text-gray-900">Staff Performance Breakdown</h4>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Transaction</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="staff in staffSalesReportData.staffSales" :key="staff.id" class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ staff.name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getRoleClass(staff.role)">
                                                        {{ staff.role }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                                    ₱{{ formatCurrency(staff.totalSales) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ staff.totalTransactions }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ₱{{ formatCurrency(staff.averageTransaction) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: staff.percentage + '%' }"></div>
                                                        </div>
                                                        <span class="font-medium text-gray-900">{{ staff.percentage.toFixed(1) }}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div v-if="loadingStaffSalesReport" class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                            <p class="mt-2 text-gray-600">Loading staff sales report...</p>
                        </div>

                        <div v-if="!staffSalesReportData && !loadingStaffSalesReport && staffReportFilters.period" class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-600">No data available for the selected period</p>
                        </div>
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
import { ref, computed, onMounted, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import AdminAuthenticatedLayout from "@/Layouts/AdminAuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import { jsPDF } from "jspdf";
import { autoTable } from "jspdf-autotable";
import ButtonNew from "@/Components/ButtonNew.vue";
import Chart from 'chart.js/auto';
import { notify , getLogoBase64} from "@/globalFunctions.js";

const { salesSummary } = defineProps(["salesSummary"]);

const loadingSalesReport = ref(false);
const salesReportData = ref(null);
const salesChart = ref(null);
const chartInstance = ref(null);


const showBrandSalesReport = ref(false);
const loadingBrandSalesReport = ref(false);
const brandSalesReportData = ref(null);

const activeTab = ref('sales');

const reportFilters = ref({
    period: '',
    date: new Date().toISOString().split('T')[0],
    startDate: new Date().toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0],
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
});

const brandReportFilters = ref({
    period: '',
    date: new Date().toISOString().split('T')[0],
    startDate: new Date().toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0],
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
});

const incomeReportFilters = ref({
    period: '',
    date: new Date().toISOString().split('T')[0],
    startDate: new Date().toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0],
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
    product: '',
});

const staffReportFilters = ref({
    period: 'daily',
    date: new Date().toISOString().split('T')[0],
    startDate: new Date().toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0],
    monthYear: new Date().toISOString().slice(0, 7),
});

const loadingStaffSalesReport = ref(false);
const staffSalesReportData = ref(null);

const loadingIncomeReport = ref(false);
const incomeReportData = ref(null);
const availableProducts = ref([]);

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = 0; i < 5; i++) {
        years.push(currentYear - i);
    }
    return years;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatPercentageChange = (current, previous) => {
    if (!previous || previous === 0) return '+0%';
    const change = ((current - previous) / previous) * 100;
    const sign = change >= 0 ? '+' : '';
    return `${sign}${change.toFixed(1)}%`;
};

const loadSalesReport = async () => {
    loadingSalesReport.value = true;
    try {
        const params = new URLSearchParams({
            period: reportFilters.value.period,
            date: reportFilters.value.date,
            startDate: reportFilters.value.startDate,
            endDate: reportFilters.value.endDate,
            year: reportFilters.value.year,
            month: reportFilters.value.month,
        });

        const response = await fetch(`/admin/api/sales-report?${params}`);
        const data = await response.json();
        salesReportData.value = data;

        await nextTick();
        renderChart();
    } catch (error) {
        console.error('Error loading sales report:', error);
    } finally {
        loadingSalesReport.value = false;
    }
};

const renderChart = () => {
    if (!salesChart.value || !salesReportData.value) return;

    if (chartInstance.value) {
        chartInstance.value.destroy();
    }

    const ctx = salesChart.value.getContext('2d');
    const chartData = salesReportData.value.chartData;

    let labels = [];
    let data = [];

    if (reportFilters.value.period === 'daily') {
        labels = chartData.map(item => item.hour);
        data = chartData.map(item => item.amount);
    } else if (reportFilters.value.period === 'weekly') {
        labels = chartData.map(item => item.day);
        data = chartData.map(item => item.amount);
    } else {
        labels = chartData.map(item => `Day ${item.day}`);
        data = chartData.map(item => item.amount);
    }

    chartInstance.value = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales Amount (₱)',
                data: data,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Sales: ₱${context.parsed.y.toLocaleString()}`;
                        }
                    }
                }
            }
        }
    });
};

const getBreakdownData = () => {
    if (!salesReportData.value) return [];

    const data = salesReportData.value.salesData;
    
    if (reportFilters.value.period === 'daily') {
        return salesReportData.value.chartData.map(item => ({
            key: item.hour,
            label: item.hour,
            amount: item.amount,
            transactions: item.transactions,
        }));
    } else if (reportFilters.value.period === 'weekly') {
        return data.dailyBreakdown?.map(item => ({
            key: item.date,
            label: `${item.dayName} (${item.date})`,
            amount: item.amount,
            transactions: item.transactions,
        })) || [];
    } else {
        return data.dailyBreakdown?.map(item => ({
            key: item.date,
            label: `Day ${item.day} (${item.date})`,
            amount: item.amount,
            transactions: item.transactions,
        })) || [];
    }
};






const exportSalesReport = async () => {
   if (!salesReportData.value) return;

   try {
       const doc = new jsPDF();
       const data = salesReportData.value.salesData;
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
       const title = "SALES REPORT";
       const titleWidth = doc.getTextWidth(title);
       doc.text(title, (pageWidth - titleWidth) / 2, yPosition);

       yPosition += 4;

       doc.setFont(undefined, 'normal');
       doc.setFontSize(11);
       const periodText = `Period: ${reportFilters.value.period.charAt(0).toUpperCase() + reportFilters.value.period.slice(1)}`;
       let dateText;
       if (reportFilters.value.period === 'monthly') {
           dateText = `Month: ${months[reportFilters.value.month - 1]} ${reportFilters.value.year}`;
       } else if (reportFilters.value.period === 'range') {
           dateText = `Range: ${reportFilters.value.startDate} to ${reportFilters.value.endDate}`;
       } else {
           dateText = `Date: ${reportFilters.value.date}`;
       }

       doc.text(periodText, 14, yPosition);
       const dateTextWidth = doc.getTextWidth(dateText);
       doc.text(dateText, pageWidth - dateTextWidth - 14, yPosition);

       yPosition += 10;

       doc.setFontSize(13);
       doc.text("Summary", 14, yPosition);

       yPosition += 8;

       doc.setFontSize(10);
       doc.text(`• Total Sales: PHP ${formatCurrency(data.totalAmount)}`, 18, yPosition);
       yPosition += 7;
       doc.text(`• Total Transactions: ${data.totalTransactions}`, 18, yPosition);
       yPosition += 7;
       doc.text(`• Average Transaction: PHP ${formatCurrency(data.averageTransaction || 0)}`, 18, yPosition);
       yPosition += 10;

       const breakdownData = getBreakdownData();
       const tableRows = breakdownData.map(item => [
           item.label,
           `PHP ${formatCurrency(item.amount)}`,
           item.transactions.toString(),
       ]);

       autoTable(doc, {
           head: [['Period', 'Sales Amount', 'Transactions']],
           body: tableRows,
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

       let filename = `sales-report-${reportFilters.value.period}`;
       if (reportFilters.value.period === 'range') {
           filename += `-${reportFilters.value.startDate}-to-${reportFilters.value.endDate}`;
       } else if (reportFilters.value.period === 'monthly') {
           filename += `-${reportFilters.value.year}-${reportFilters.value.month}`;
       } else {
           filename += `-${reportFilters.value.date}`;
       }
       filename += '.pdf';
       const pdfBlob = doc.output('blob');
       const pdfUrl = URL.createObjectURL(pdfBlob);
       window.open(pdfUrl, '_blank');

   } catch (error) {
       alert("Error generating PDF. Please try again.");
       console.error(error);
   }
};







const loadBrandSalesReport = async () => {
    loadingBrandSalesReport.value = true;
    try {
        const params = new URLSearchParams({
            period: brandReportFilters.value.period,
            date: brandReportFilters.value.date,
            startDate: brandReportFilters.value.startDate,
            endDate: brandReportFilters.value.endDate,
            year: brandReportFilters.value.year,
            month: brandReportFilters.value.month,
        });

        const response = await fetch(`/admin/api/brand-sales-report?${params}`);
        const data = await response.json();
        brandSalesReportData.value = data;
    } catch (error) {
        console.error('Error loading brand sales report:', error);
    } finally {
        loadingBrandSalesReport.value = false;
    }
};



const exportBrandSalesReport = async () => {
    if (!brandSalesReportData.value) return;

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
        const title = "BRAND SALES REPORT";
        const titleWidth = doc.getTextWidth(title);
        doc.text(title, (pageWidth - titleWidth) / 2, yPosition);

        yPosition += 4;

        doc.setFont(undefined, 'normal');
        doc.setFontSize(11);
        const periodText = `Period: ${brandReportFilters.value.period.charAt(0).toUpperCase() + brandReportFilters.value.period.slice(1)}`;
        let dateText;
        if (brandReportFilters.value.period === 'monthly') {
            dateText = `Month: ${months[brandReportFilters.value.month - 1]} ${brandReportFilters.value.year}`;
        } else if (brandReportFilters.value.period === 'range') {
            dateText = `Range: ${brandReportFilters.value.startDate} to ${brandReportFilters.value.endDate}`;
        } else {
            dateText = `Date: ${brandReportFilters.value.date}`;
        }

        doc.text(periodText, 14, yPosition);
        const dateTextWidth = doc.getTextWidth(dateText);
        doc.text(dateText, pageWidth - dateTextWidth - 14, yPosition);

        yPosition += 10;

        const tableRows = brandSalesReportData.value.brandSales.map(brandSale => [
            brandSale.brand,
            `PHP ${formatCurrency(brandSale.totalAmount)}`,
            brandSale.totalTransactions.toString(),
        ]);

        autoTable(doc, {
            head: [['Brand', 'Total Sales', 'Transactions']],
            body: tableRows,
            startY: yPosition,
            styles: { fontSize: 9 },
            headStyles: { fillColor: [147, 51, 234], textColor: 255 },
            margin: { left: 14, right: 14, top: 10 }
        });

        const finalY = doc.lastAutoTable.finalY || yPosition + 10;
        const generatedText = `Generated on: ${new Date().toLocaleString()}`;
        const textWidth = doc.getTextWidth(generatedText);
        doc.setFontSize(9);
        doc.text(generatedText, pageWidth - 14 - textWidth, finalY + 8);

        let filename = `brand-sales-report-${brandReportFilters.value.period}`;
        if (brandReportFilters.value.period === 'range') {
            filename += `-${brandReportFilters.value.startDate}-to-${brandReportFilters.value.endDate}`;
        } else if (brandReportFilters.value.period === 'monthly') {
            filename += `-${brandReportFilters.value.year}-${brandReportFilters.value.month}`;
        } else {
            filename += `-${brandReportFilters.value.date}`;
        }
        filename += '.pdf';
        const pdfBlob = doc.output('blob');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        window.open(pdfUrl, '_blank');

    } catch (error) {
        alert("Error generating PDF. Please try again.");
        console.error(error);
    }
};

const loadIncomeReport = async () => {
    loadingIncomeReport.value = true;
    try {
        const params = new URLSearchParams({
            period: incomeReportFilters.value.period,
            date: incomeReportFilters.value.date,
            startDate: incomeReportFilters.value.startDate,
            endDate: incomeReportFilters.value.endDate,
            year: incomeReportFilters.value.year,
            month: incomeReportFilters.value.month,
            product: incomeReportFilters.value.product,
        });

        const response = await fetch(`/admin/api/income-report?${params}`);
        const data = await response.json();
        incomeReportData.value = data.incomeData;
    } catch (error) {
        console.error('Error loading income report:', error);
        notify('Error loading income report. Please try again.', 'error');
    } finally {
        loadingIncomeReport.value = false;
    }
};

const exportIncomeReport = async () => {
    if (!incomeReportData.value) return;

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
        const title = "INCOME & PROFIT REPORT";
        const titleWidth = doc.getTextWidth(title);
        doc.text(title, (pageWidth - titleWidth) / 2, yPosition);

        yPosition += 4;

        doc.setFont(undefined, 'normal');
        doc.setFontSize(11);
        const periodText = `Period: ${incomeReportFilters.value.period.charAt(0).toUpperCase() + incomeReportFilters.value.period.slice(1)}`;
        let dateText;
        if (incomeReportFilters.value.period === 'monthly') {
            dateText = `Month: ${months[incomeReportFilters.value.month - 1]} ${incomeReportFilters.value.year}`;
        } else if (incomeReportFilters.value.period === 'range') {
            dateText = `Range: ${incomeReportFilters.value.startDate} to ${incomeReportFilters.value.endDate}`;
        } else {
            dateText = `Date: ${incomeReportFilters.value.date}`;
        }

        doc.text(periodText, 14, yPosition);
        const dateTextWidth = doc.getTextWidth(dateText);
        doc.text(dateText, pageWidth - dateTextWidth - 14, yPosition);

        yPosition += 10;

        // Summary Section
        doc.setFontSize(13);
        doc.text("Summary", 14, yPosition);

        yPosition += 8;

        doc.setFontSize(10);
        doc.text(`• Total Revenue: PHP ${formatCurrency(incomeReportData.value.summary.total_revenue)}`, 18, yPosition);
        yPosition += 7;
        doc.text(`• Total Cost (FIFO): PHP ${formatCurrency(incomeReportData.value.summary.total_cost)}`, 18, yPosition);
        yPosition += 7;
        doc.text(`• Net Profit: PHP ${formatCurrency(incomeReportData.value.summary.total_profit)}`, 18, yPosition);
        yPosition += 7;
        doc.text(`• Profit Margin: ${formatCurrency(incomeReportData.value.summary.profit_margin)}%`, 18, yPosition);
        yPosition += 10;

        // Products breakdown
        for (const product of incomeReportData.value.products) {
            // Check if we need a new page
            if (yPosition > 250) {
                doc.addPage();
                yPosition = 20;
            }

            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text(product.product_name, 14, yPosition);
            yPosition += 5;

            doc.setFont(undefined, 'normal');
            doc.setFontSize(9);
            doc.text(`Item Code: ${product.item_code}`, 14, yPosition);
            yPosition += 5;

            doc.text(`Quantity Sold: ${product.total_quantity_sold} | Revenue: PHP ${formatCurrency(product.total_revenue)} | Cost: PHP ${formatCurrency(product.total_cost)} | Profit: PHP ${formatCurrency(product.total_profit)} | Margin: ${formatCurrency(product.profit_margin)}%`, 14, yPosition);
            yPosition += 8;

            // Transactions table for this product
            const transactionRows = product.transactions.map(transaction => [
                transaction.date,
                transaction.quantity.toString(),
                `PHP ${formatCurrency(transaction.selling_price)}`,
                `PHP ${formatCurrency(transaction.unit_cost)}`,
                `PHP ${formatCurrency(transaction.revenue)}`,
                `PHP ${formatCurrency(transaction.cost)}`,
                `PHP ${formatCurrency(transaction.profit)}`,
                `${formatCurrency(transaction.profit_margin)}%`,
            ]);

            autoTable(doc, {
                head: [['Date', 'Qty', 'Sell Price', 'Unit Cost', 'Revenue', 'Cost', 'Profit', 'Margin %']],
                body: transactionRows,
                startY: yPosition,
                styles: { fontSize: 7 },
                headStyles: { fillColor: [16, 185, 129], textColor: 255 },
                margin: { left: 14, right: 14 },
                didDrawPage: (data) => {
                    yPosition = data.cursor.y + 10;
                }
            });

            yPosition = doc.lastAutoTable.finalY + 10;
        }

        const finalY = doc.lastAutoTable ? doc.lastAutoTable.finalY : yPosition;
        const generatedText = `Generated on: ${new Date().toLocaleString()}`;
        const textWidth = doc.getTextWidth(generatedText);
        doc.setFontSize(9);
        doc.text(generatedText, pageWidth - 14 - textWidth, finalY + 8);

        let filename = `income-report-${incomeReportFilters.value.period}`;
        if (incomeReportFilters.value.period === 'range') {
            filename += `-${incomeReportFilters.value.startDate}-to-${incomeReportFilters.value.endDate}`;
        } else if (incomeReportFilters.value.period === 'monthly') {
            filename += `-${incomeReportFilters.value.year}-${incomeReportFilters.value.month}`;
        } else {
            filename += `-${incomeReportFilters.value.date}`;
        }
        if (incomeReportFilters.value.product) {
            filename += `-product-${incomeReportFilters.value.product}`;
        }
        filename += '.pdf';
        const pdfBlob = doc.output('blob');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        window.open(pdfUrl, '_blank');

    } catch (error) {
        alert("Error generating PDF. Please try again.");
        console.error(error);
    }
};

const loadProducts = async () => {
    try {
        const response = await fetch('/admin/api/products-list');
        if (response.ok) {
            const products = await response.json();
            availableProducts.value = products;
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
};

const loadStaffSalesReport = async () => {
    loadingStaffSalesReport.value = true;
    try {
        const params = new URLSearchParams({
            period: staffReportFilters.value.period,
            date: staffReportFilters.value.date,
            startDate: staffReportFilters.value.startDate,
            endDate: staffReportFilters.value.endDate,
            monthYear: staffReportFilters.value.monthYear,
        });

        console.log('Loading staff sales report with params:', params.toString());
        const response = await fetch(`/admin/api/staff-sales-report?${params}`);

        if (!response.ok) {
            console.error('API error response:', response.status, response.statusText);
            notify(`Error loading report: ${response.statusText}`, 'error');
            return;
        }

        const data = await response.json();
        console.log('Staff sales report data received:', data);
        staffSalesReportData.value = data;
    } catch (error) {
        console.error('Error loading staff sales report:', error);
        notify('Error loading staff sales report. Please try again.', 'error');
    } finally {
        loadingStaffSalesReport.value = false;
    }
};

const getRoleClass = (role) => {
    const roleClasses = {
        'admin': 'bg-red-100 text-red-800',
        'manager': 'bg-orange-100 text-orange-800',
        'staff': 'bg-blue-100 text-blue-800',
    };
    return roleClasses[role] || 'bg-gray-100 text-gray-800';
};

const exportStaffSalesReport = async () => {
    if (!staffSalesReportData.value) return;

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
        const title = "STAFF SALES REPORT";
        const titleWidth = doc.getTextWidth(title);
        doc.text(title, (pageWidth - titleWidth) / 2, yPosition);

        yPosition += 4;

        doc.setFont(undefined, 'normal');
        doc.setFontSize(11);
        const periodText = `Period: ${staffReportFilters.value.period.charAt(0).toUpperCase() + staffReportFilters.value.period.slice(1)}`;
        let dateText;
        if (staffReportFilters.value.period === 'monthly') {
            dateText = `Month: ${staffReportFilters.value.monthYear}`;
        } else if (staffReportFilters.value.period === 'range') {
            dateText = `Range: ${staffReportFilters.value.startDate} to ${staffReportFilters.value.endDate}`;
        } else {
            dateText = `Date: ${staffReportFilters.value.date}`;
        }

        doc.text(periodText, 14, yPosition);
        const dateTextWidth = doc.getTextWidth(dateText);
        doc.text(dateText, pageWidth - dateTextWidth - 14, yPosition);

        yPosition += 10;

        doc.setFontSize(13);
        doc.text("Summary", 14, yPosition);

        yPosition += 8;

        doc.setFontSize(10);
        doc.text(`• Total Sales: PHP ${formatCurrency(staffSalesReportData.value.summary.totalSales)}`, 18, yPosition);
        yPosition += 7;
        doc.text(`• Total Transactions: ${staffSalesReportData.value.summary.totalTransactions}`, 18, yPosition);
        yPosition += 7;
        doc.text(`• Average per Staff: PHP ${formatCurrency(staffSalesReportData.value.summary.averagePerStaff)}`, 18, yPosition);
        yPosition += 10;

        const tableRows = staffSalesReportData.value.staffSales.map(staff => [
            staff.name,
            staff.role,
            `PHP ${formatCurrency(staff.totalSales)}`,
            staff.totalTransactions.toString(),
            `PHP ${formatCurrency(staff.averageTransaction)}`,
            `${staff.percentage.toFixed(1)}%`,
        ]);

        autoTable(doc, {
            head: [['Staff Name', 'Role', 'Total Sales', 'Transactions', 'Avg Transaction', 'Percentage']],
            body: tableRows,
            startY: yPosition,
            styles: { fontSize: 9 },
            headStyles: { fillColor: [79, 70, 229], textColor: 255 },
            margin: { left: 14, right: 14, top: 10 }
        });

        const finalY = doc.lastAutoTable.finalY || yPosition + 10;
        const generatedText = `Generated on: ${new Date().toLocaleString()}`;
        const textWidth = doc.getTextWidth(generatedText);
        doc.setFontSize(9);
        doc.text(generatedText, pageWidth - 14 - textWidth, finalY + 8);

        let filename = `staff-sales-report-${staffReportFilters.value.period}`;
        if (staffReportFilters.value.period === 'range') {
            filename += `-${staffReportFilters.value.startDate}-to-${staffReportFilters.value.endDate}`;
        } else if (staffReportFilters.value.period === 'monthly') {
            filename += `-${staffReportFilters.value.monthYear}`;
        } else {
            filename += `-${staffReportFilters.value.date}`;
        }
        filename += '.pdf';
        const pdfBlob = doc.output('blob');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        window.open(pdfUrl, '_blank');

    } catch (error) {
        alert("Error generating PDF. Please try again.");
        console.error(error);
    }
};

onMounted(() => {
    // Load staff sales report for today by default
    loadStaffSalesReport();
});
</script>