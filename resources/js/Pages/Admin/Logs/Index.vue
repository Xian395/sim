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

onMounted(() => {
    // Sales report will load when user selects a period
});
</script>