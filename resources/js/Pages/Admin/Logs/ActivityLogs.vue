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

                    <!-- Filters Section -->
                    <div class="p-6 border-b bg-gray-50">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4 items-end">
                            <!-- Search -->
                            <div class="sm:col-span-2 lg:col-span-2 xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                                <input
                                    v-model="filters.search"
                                    @input="applyFilters"
                                    type="text"
                                    placeholder="Search logs..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                                <input
                                    v-model="filters.dateFrom"
                                    @change="applyFilters"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                                <input
                                    v-model="filters.dateTo"
                                    @change="applyFilters"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                                <select
                                    v-model="filters.action"
                                    @change="applyFilters"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">All Actions</option>
                                    <option value="auth">Login</option>
                                    <option value="stock_in">Stock In</option>
                                    <option value="stock_out">Stock Out</option>
                                    <option value="sale">Sale</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                                <select
                                    v-model="filters.sortBy"
                                    @change="applyFilters"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="created_at">Date</option>
                                    <option value="action">Action</option>
                                    <option value="user.name">User</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                                <select
                                    v-model="filters.sortOrder"
                                    @change="applyFilters"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="desc">Newest First</option>
                                    <option value="asc">Oldest First</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Show</label>
                                <select
                                    v-model="itemsPerPage"
                                    @change="handleItemsPerPageChange"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option :value="10">10</option>
                                    <option :value="50">50</option>
                                    <option :value="100">100</option>
                                    <option :value="500">500</option>
                                    <option :value="1000">1000</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2 lg:col-span-4 xl:col-span-1 flex justify-end lg:justify-start">
                                <button
                                    @click="clearFilters"
                                    class="w-full lg:w-auto px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded transition-colors whitespace-nowrap"
                                >
                                    Clear Filters
                                </button>
                            </div>

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
                                    v-for="link in logs.links"
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
</script>
