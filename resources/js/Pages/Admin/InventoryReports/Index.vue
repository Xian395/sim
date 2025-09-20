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

              <div v-if="inventoryReportData" class="space-y-6">
                  <!-- Summary Stats -->
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="text-sm font-medium text-blue-600">Total Products</div>
                          <div class="text-2xl font-bold text-blue-900">
                            {{ inventoryReportData.summary.totalProducts }}
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
                            ₱{{ formatCurrency(inventoryReportData.summary.totalValue || 0) }}
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
                            {{ inventoryReportData.summary.lowStockCount }}
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
                            {{ inventoryReportData.summary.outOfStockCount }}
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
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                          <tr v-for="product in inventoryReportData.products" :key="product.id" class="hover:bg-gray-50">
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
  </AdminAuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import ButtonNew from '@/Components/ButtonNew.vue';
import UnAuthorized from "@/Components/UnAuthorized.vue";
import { notify2, getLogoBase64 } from "@/globalFunctions.js";
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

const $page = usePage();

const inventoryReportData = ref(null);
const loadingInventoryReport = ref(false);
const currentDateTime = ref('');

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0);
};

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