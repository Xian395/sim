<template>
    <AdminAuthenticatedLayout>
        <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Activity Logs Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6">
                        <h2 class="text-2xl font-black text-gray-700">Activity Logs</h2>
                        <ButtonNew
                            types="pdf"
                            size="md"
                            tooltips="Export Activity Logs"
                            @click="exportToPDF"
                        >
                            PDF
                        </ButtonNew>
                    </div>

                    <!-- Compact Search and Filter Bar -->
                    <div class="bg-white/90 backdrop-blur-xl shadow-lg border border-gray-200 mx-6 mt-4 rounded-2xl">
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
                                    <input v-model="filters.search" @input="applyFilters" type="text"
                                        placeholder="Search logs..."
                                        class="block w-full pl-10 pr-10 py-2.5 text-sm bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300" />
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
                            </div>

                            <!-- Advanced Filters (Collapsible) -->
                            <transition name="slide-down">
                                <div v-if="showAdvancedFilters" class="space-y-3 pt-2 border-t border-gray-200">
                                    <!-- First Row of Filters -->
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                        <!-- From Date Filter -->
                                        <div class="relative">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                                            <input v-model="filters.dateFrom" @change="applyFilters"
                                                type="date"
                                                class="w-full pl-3 pr-3 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium transition-all duration-200" />
                                        </div>

                                        <!-- To Date Filter -->
                                        <div class="relative">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                                            <input v-model="filters.dateTo" @change="applyFilters"
                                                type="date"
                                                class="w-full pl-3 pr-3 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium transition-all duration-200" />
                                        </div>

                                        <!-- Action Filter -->
                                        <div class="relative">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Action</label>
                                            <select v-model="filters.action" @change="applyFilters"
                                                class="appearance-none w-full pl-3 pr-8 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium cursor-pointer transition-all duration-200">
                                                <option value="">All Actions</option>
                                                <option value="auth">Login</option>
                                                <option value="stock_in">Stock In</option>
                                                <option value="stock_out">Stock Out</option>
                                                <option value="sale">Sale</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Sort By Filter -->
                                        <div class="relative">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Sort By</label>
                                            <select v-model="filters.sortBy" @change="applyFilters"
                                                class="appearance-none w-full pl-3 pr-8 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium cursor-pointer transition-all duration-200">
                                                <option value="created_at">Date</option>
                                                <option value="action">Action</option>
                                                <option value="user.name">User</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Second Row of Filters -->
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                        <!-- Order Filter -->
                                        <div class="relative">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Order</label>
                                            <select v-model="filters.sortOrder" @change="applyFilters"
                                                class="appearance-none w-full pl-3 pr-8 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium cursor-pointer transition-all duration-200">
                                                <option value="desc">Newest First</option>
                                                <option value="asc">Oldest First</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Show Filter -->
                                        <div class="relative">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Show</label>
                                            <select v-model="itemsPerPage" @change="handleItemsPerPageChange"
                                                class="appearance-none w-full pl-3 pr-8 py-2 text-xs bg-white/70 backdrop-blur-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 font-medium cursor-pointer transition-all duration-200">
                                                <option :value="10">10</option>
                                                <option :value="50">50</option>
                                                <option :value="100">100</option>
                                                <option :value="500">500</option>
                                                <option :value="1000">1000</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Clear Filters Button -->
                                        <div class="flex items-end">
                                            <button
                                                @click="clearFilters"
                                                class="w-full px-3 py-2 bg-gray-400 hover:bg-gray-500 text-white text-xs font-medium rounded-lg transition-all duration-200 whitespace-nowrap"
                                            >
                                                Clear Filters
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Active Filters Display -->
                                    <div v-if="hasActiveFilters" class="mt-3 flex flex-wrap gap-2">
                                        <span class="text-xs font-medium text-gray-700">Active filters:</span>
                                        <span v-if="filters.search" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Search: "{{ filters.search }}"
                                            <button @click="filters.search = ''" class="ml-1 text-blue-600 hover:text-blue-800">×</button>
                                        </span>
                                        <span v-if="filters.dateFrom" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            From: {{ filters.dateFrom }}
                                            <button @click="filters.dateFrom = ''" class="ml-1 text-green-600 hover:text-green-800">×</button>
                                        </span>
                                        <span v-if="filters.dateTo" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            To: {{ filters.dateTo }}
                                            <button @click="filters.dateTo = ''" class="ml-1 text-yellow-600 hover:text-yellow-800">×</button>
                                        </span>
                                        <span v-if="filters.action" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Action: {{ formatAction(filters.action) }}
                                            <button @click="filters.action = ''" class="ml-1 text-purple-600 hover:text-purple-800">×</button>
                                        </span>
                                        <span v-if="filters.sortBy !== 'created_at'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                            Sort: {{ filters.sortBy === 'action' ? 'Action' : 'User' }}
                                            <button @click="filters.sortBy = 'created_at'" class="ml-1 text-pink-600 hover:text-pink-800">×</button>
                                        </span>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="mb-4 text-sm text-gray-600">
                            {{ logs.total || logs.data?.length || 0 }} records found
                            <span v-if="isFiltered">(filtered)</span>

                        </div>

                        <DataTable
                            :data="logs.data || []"
                            :columns="columns"
                            item-key="id"
                            empty-message="No activity logs found"
                        >
                            <template #column-created_at="{ value }">
                                <div class="text-sm text-gray-900">
                                    {{ new Date(value).toLocaleString() }}
                                </div>
                            </template>

                            <template #column-action="{ value }">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ formatAction(value) }}
                                </div>
                            </template>

                            <template #column-user="{ item }">
                                <div
                                    class="text-sm font-medium"
                                    :class="item.user ? 'text-gray-900' : 'text-red-600'"
                                >
                                    {{ item.user?.name || "remove user" }}
                                </div>
                            </template>
                        </DataTable>

                        <div class="mt-4 flex justify-between items-center" v-if="logs.links">
                            <div class="text-sm text-gray-700">
                                Showing {{ logs.from || 1 }} to {{ logs.to || logs.data?.length || 0 }}
                                of {{ logs.total || logs.data?.length || 0 }} results
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    v-for="link in formatPaginationLinks"
                                    :key="link.label"
                                    @click="loadPage(link.url)"
                                    :disabled="!link.url"
                                    :class="[
                                        'px-3 py-2 rounded transition-colors',
                                        link.active
                                            ? 'bg-blue-500 text-white'
                                            : link.url
                                                ? 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                >
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-between items-center" v-else-if="logs.data && logs.data.length > itemsPerPage">
                            <div class="text-sm text-gray-700">
                                Showing {{ startIndex + 1 }} to
                                {{ Math.min(startIndex + itemsPerPage, logs.data.length) }}
                                of {{ logs.data.length }} results
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    v-if="currentPage > 1"
                                    @click="currentPage--"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors"
                                >
                                    Previous
                                </button>
                                <span class="px-4 py-2 text-gray-700">
                                    Page {{ currentPage }} of {{ totalPages }}
                                </span>
                                <button
                                    v-if="currentPage < totalPages"
                                    @click="currentPage++"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors"
                                >
                                    Next
                                </button>
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
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AdminAuthenticatedLayout from "@/Layouts/AdminAuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import { jsPDF } from "jspdf";
import { autoTable } from "jspdf-autotable";
import ButtonNew from "@/Components/ButtonNew.vue";
import { getLogoBase64 } from "@/globalFunctions.js";


const { logs } = defineProps(["logs"]);

const filters = ref({
    search: "",
    dateFrom: "",
    dateTo: "",
    action: "",
    sortBy: "created_at",
    sortOrder: "desc",
});

const showAdvancedFilters = ref(false);
const currentPage = ref(1);
const itemsPerPage = ref(10);

const columns = [
    { key: "user", label: "User", type: "text", align: "left" },
    { key: "action", label: "Action", type: "text", align: "left" },
    { key: "details", label: "Details", type: "text", align: "left" },
    { key: "created_at", label: "Timestamp", type: "text", align: "left" },
];

const totalPages = computed(() =>
    Math.ceil((logs.data?.length || 0) / itemsPerPage.value)
);
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);

const isFiltered = computed(() => {
    return (
        filters.value.search ||
        filters.value.dateFrom ||
        filters.value.dateTo ||
        filters.value.action
    );
});

const hasActiveFilters = computed(() => {
    return (
        filters.value.search ||
        filters.value.dateFrom ||
        filters.value.dateTo ||
        filters.value.action ||
        filters.value.sortBy !== "created_at" ||
        filters.value.sortOrder !== "desc"
    );
});

const formatAction = (action) => {
    return action
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};

const applyFilters = () => {
    const params = new URLSearchParams();

    if (filters.value.search) params.append('search', filters.value.search);
    if (filters.value.dateFrom) params.append('date_from', filters.value.dateFrom);
    if (filters.value.dateTo) params.append('date_to', filters.value.dateTo);
    if (filters.value.action) params.append('action', filters.value.action);
    if (filters.value.sortBy) params.append('sort_by', filters.value.sortBy);
    if (filters.value.sortOrder) params.append('sort_order', filters.value.sortOrder);

    params.append('per_page', itemsPerPage.value);

    router.get(route('admin.activity-logs.index'), Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        search: "",
        dateFrom: "",
        dateTo: "",
        action: "",
        sortBy: "created_at",
        sortOrder: "desc",
    };

    router.get(route('admin.activity-logs.index'), { per_page: itemsPerPage.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const loadPage = (url) => {
    if (url) {
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const exportToPDF = async () => {
    try {
        const exportData = logs.data || [];
        const doc = new jsPDF();

        const logoBase64 = await getLogoBase64();

        const pageWidth = doc.internal.pageSize.width;
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
        const title = 'LOG RECORDS';
        const titleWidth = doc.getTextWidth(title);
        const titleX = (pageWidth - titleWidth) / 2;
        doc.text(title, titleX, yPosition);

        doc.setFont(undefined, 'normal');

        yPosition += 5;

        let filtersText = '';
        if (isFiltered.value) {
            const activeFilters = [];
            if (filters.value.search) {
                activeFilters.push(`Search: ${filters.value.search}`);
            }
            if (filters.value.dateFrom) {
                activeFilters.push(`From Date: ${filters.value.dateFrom}`);
            }
            if (filters.value.dateTo) {
                activeFilters.push(`To Date: ${filters.value.dateTo}`);
            }
            if (filters.value.action) {
                activeFilters.push(`Action: ${formatAction(filters.value.action)}`);
            }
            filtersText = `[Filters]: ${activeFilters.join(', ')}`;
        }

        doc.setFontSize(10);

        const totalRecordsText = `Total Records: ${exportData.length}`;
        const totalRecordsWidth = doc.getTextWidth(totalRecordsText);

        doc.text(filtersText, 14, yPosition);
        doc.text(totalRecordsText, pageWidth - totalRecordsWidth - 20, yPosition);
        yPosition += 4;

        const tableColumns = [
            { header: "User", dataKey: "user" },
            { header: "Action", dataKey: "action" },
            { header: "Details", dataKey: "details" },
            { header: "Timestamp", dataKey: "timestamp" },
        ];

        const tableRows = exportData.map((log) => ({
            user: log.user?.name || "Removed User",
            action: formatAction(log.action),
            details: log.details || "N/A",
            timestamp: new Date(log.created_at).toLocaleString(),
        }));

        autoTable(doc, {
            columns: tableColumns,
            body: tableRows,
            startY: yPosition,
            styles: { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: [59, 130, 246], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [245, 245, 245] },
            columnStyles: {
                0: { cellWidth: 40 },
                1: { cellWidth: 30 },
                2: { cellWidth: 60 },
                3: { cellWidth: 45 },
            },
            margin: { top: 10 },
        });

        const finalY = doc.lastAutoTable.finalY || yPosition;
        doc.setFontSize(10);
        const generatedText = `Generated on: ${new Date().toLocaleString()}`;
        doc.text(generatedText, pageWidth - doc.getTextWidth(generatedText) - 20, finalY + 15);

        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);

            doc.setFontSize(8);
            doc.text(
                `Page ${i} of ${pageCount}`,
                doc.internal.pageSize.width - 30,
                doc.internal.pageSize.height - 10
            );
        }

        let filename = "activity-logs-report";
        if (filters.value.dateFrom || filters.value.dateTo) {
            filename += `_${filters.value.dateFrom || "start"}-to-${filters.value.dateTo || "end"}`;
        }
        if (filters.value.action) {
            filename += `_${filters.value.action}`;
        }
        filename += `_${new Date().toISOString().split("T")[0]}.pdf`;

        const pdfBlob = doc.output('blob');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        window.open(pdfUrl, '_blank');
    } catch (error) {
        console.error("Error generating PDF:", error);
        alert("Error generating PDF. Please try again.");
    }
};

const handleItemsPerPageChange = () => {
    currentPage.value = 1;

    const params = {
        per_page: itemsPerPage.value
    };

    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.dateFrom) params.date_from = filters.value.dateFrom;
    if (filters.value.dateTo) params.date_to = filters.value.dateTo;
    if (filters.value.action) params.action = filters.value.action;
    if (filters.value.sortBy) params.sort_by = filters.value.sortBy;
    if (filters.value.sortOrder) params.sort_order = filters.value.sortOrder;

    router.get(route('admin.activity-logs.index'), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatPaginationLinks = computed(() => {
    if (!logs.links) return [];

    const formatted = [];
    let previousWasEllipsis = false;

    logs.links.forEach((link, index) => {
        // Always include Previous and Next buttons
        if (link.label.includes('Previous') || link.label.includes('Next')) {
            formatted.push(link);
            previousWasEllipsis = false;
            return;
        }

        // Get the page number from label
        const pageNum = parseInt(link.label);
        const isNumber = !isNaN(pageNum);

        if (!isNumber) {
            formatted.push(link);
            previousWasEllipsis = false;
            return;
        }

        // Show first 2 pages, last 2 pages, and current page +/- 1
        const totalPages = Math.max(...logs.links.map(l => {
            const num = parseInt(l.label);
            return isNaN(num) ? 0 : num;
        }));

        const currentPageNum = logs.links.findIndex(l => l.active) > 0
            ? parseInt(logs.links.find(l => l.active)?.label || 1)
            : 1;

        const shouldShow =
            pageNum <= 2 ||  // First 2 pages
            pageNum >= totalPages - 1 ||  // Last 2 pages
            Math.abs(pageNum - currentPageNum) <= 1;  // Current page +/- 1

        if (shouldShow) {
            formatted.push(link);
            previousWasEllipsis = false;
        } else if (!previousWasEllipsis) {
            // Add ellipsis only once between groups
            formatted.push({
                label: '...',
                url: null,
                active: false
            });
            previousWasEllipsis = true;
        }
    });

    return formatted;
});
</script>

<style scoped>
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

/* Hide default select dropdown arrow */
select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: none;
    padding-right: 2.5rem;
}

select::-ms-expand {
    display: none;
}
</style>
