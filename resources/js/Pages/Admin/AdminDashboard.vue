<template>
  <AdminAuthenticatedLayout>
    <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h1 class="text-2xl font-bold text-gray-800">Welcome back, {{ $page.props.auth.user.name }}!</h1>
                <p class="text-gray-600 mt-1">Here's what's happening with your inventory today.</p>
              </div>
              <div class="text-right">
                <div class="text-sm text-gray-500">Last updated</div>
                <div class="text-lg font-semibold">{{ currentDateTime }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-md border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Product Overview</h3>
            <div class="text-sm text-gray-500">
              Showing {{ products.length }} products
            </div>
          </div>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div 
              @click="handleStatsCardClick('all')"
              class="cursor-pointer transform transition-all duration-200 hover:scale-105 hover:shadow-lg"
            >
              <StatsCard
                title="Total Products"
                :value="calculatedStats.total_products"
                color="blue"
                class="hover:bg-blue-50 transition-colors duration-200"
              />
            </div>

            <div 
              @click="handleStatsCardClick('in-stock')"
              class="cursor-pointer transform transition-all duration-200 hover:scale-105 hover:shadow-lg"
            >
              <StatsCard
                title="In Stock"
                :value="calculatedStats.in_stock"
                color="green"
                class="hover:bg-green-50 transition-colors duration-200"
              />
            </div>

            <div 
              @click="handleStatsCardClick('low-stock')"
              class="cursor-pointer transform transition-all duration-200 hover:scale-105 hover:shadow-lg"
            >
              <StatsCard
                title="Low Stock"
                :value="calculatedStats.low_stock"
                color="orange"
                class="hover:bg-orange-50 transition-colors duration-200"
              />
            </div>

            <div 
              @click="handleStatsCardClick('out-of-stock')"
              class="cursor-pointer transform transition-all duration-200 hover:scale-105 hover:shadow-lg"
            >
              <StatsCard
                title="Out of Stock"
                :value="calculatedStats.out_of_stock"
                color="red"
                class="hover:bg-red-50 transition-colors duration-200"
              />
            </div>
          </div>
        </div>

        <!-- Product Sales Chart -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Sales Overview</h3>
          <div class="mb-4">
            <p class="text-sm text-gray-600">Top 10 products by sales volume (highest to lowest)</p>
          </div>
          <div v-if="hasActualSalesData" class="h-96">
            <canvas ref="salesChart"></canvas>
          </div>
          <div v-else class="h-96 flex items-center justify-center bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <div class="text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <h4 class="mt-2 text-sm font-medium text-gray-900">No sales data available</h4>
              <p class="mt-1 text-sm text-gray-500">Sales data will appear here once products are sold.</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Financial Overview</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
              <div class="text-sm text-green-600 font-medium">Total Inventory Value</div>
              <div class="text-2xl font-bold text-green-800">₱{{ formatCurrency(calculatedStats.total_value) }}</div>
              <div class="text-xs text-green-600 mt-1">{{ calculatedStats.total_products }} items</div>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <div class="text-sm text-blue-600 font-medium">Average Product Value</div>
              <div class="text-2xl font-bold text-blue-800">₱{{ formatCurrency(calculatedStats.avg_value) }}</div>
              <div class="text-xs text-blue-600 mt-1">per item</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
              <div class="text-sm text-purple-600 font-medium">Total Stock Units</div>
              <div class="text-2xl font-bold text-purple-800">{{ calculatedStats.total_stock_units }}</div>
              <div class="text-xs text-purple-600 mt-1">units available</div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Stock Alerts -->
          <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Stock Alerts</h3>
            <div class="space-y-3">
              <div v-if="criticalProducts.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-3">
                <div class="flex items-center">
                  <div class="bg-red-100 rounded-full p-1 mr-3">
                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-red-800">Critical Stock Alert</div>
                    <div class="text-xs text-red-600">{{ criticalProducts.length }} products out of stock</div>
                  </div>
                </div>
              </div>
              
              <div v-if="lowStockProducts.length > 0" class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                <div class="flex items-center">
                  <div class="bg-orange-100 rounded-full p-1 mr-3">
                    <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-orange-800">Low Stock Warning</div>
                    <div class="text-xs text-orange-600">{{ lowStockProducts.length }} products running low</div>
                  </div>
                </div>
              </div>

              <div v-if="criticalProducts.length === 0 && lowStockProducts.length === 0" class="bg-green-50 border border-green-200 rounded-lg p-3">
                <div class="flex items-center">
                  <div class="bg-green-100 rounded-full p-1 mr-3">
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-green-800">All Good!</div>
                    <div class="text-xs text-green-600">No stock alerts at this time</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button 
                @click="router.visit('/admin/products/create')"
                class="w-full flex items-center justify-between p-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors duration-200"
              >
                <div class="flex items-center">
                  <div class="bg-blue-100 rounded-full p-2 mr-3">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-blue-800">Add New Product</span>
                </div>
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </button>

              <button 
                @click="router.visit('/admin/products')"
                class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition-colors duration-200"
              >
                <div class="flex items-center">
                  <div class="bg-gray-100 rounded-full p-2 mr-3">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-800">Manage Products</span>
                </div>
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </button>

              <button 
                @click="exportInventoryReport"
                class="w-full flex items-center justify-between p-3 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-colors duration-200"
              >
                <div class="flex items-center">
                  <div class="bg-green-100 rounded-full p-2 mr-3">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-green-800">Export Report</span>
                </div>
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Most Valuable Products</h3>
            <div class="space-y-3">
              <div v-for="product in topValueProducts" :key="product.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</div>
                  <div class="text-xs text-gray-500">Stock: {{ product.stock_quantity || 0 }}</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-semibold text-gray-800">₱{{ formatCurrency(product.price) }}</div>
                  <div class="text-xs" :class="getStockStatusClass(product.stock_quantity)">
                    {{ getStockStatus(product.stock_quantity) }}
                  </div>
                </div>
              </div>
              <div v-if="topValueProducts.length === 0" class="text-center text-gray-500 py-4">
                No products available
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Products Needing Attention</h3>
            <div class="space-y-3">
              <div v-for="product in productsNeedingAttention" :key="product.id" class="flex items-center justify-between p-3 rounded-lg" :class="product.stock_quantity === 0 ? 'bg-red-50 border border-red-200' : 'bg-orange-50 border border-orange-200'">
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</div>
                  <div class="text-xs text-gray-500">Price: ₱{{ formatCurrency(product.price) }}</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-semibold">{{ product.stock_quantity || 0 }} units</div>
                  <div class="text-xs" :class="getStockStatusClass(product.stock_quantity)">
                    {{ getStockStatus(product.stock_quantity) }}
                  </div>
                </div>
              </div>
              <div v-if="productsNeedingAttention.length === 0" class="text-center text-gray-500 py-4">
                All products are well-stocked!
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Products -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Recently Added Products</h3>
            <button 
              @click="router.visit('/admin/products')"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium"
            >
              View All →
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left py-2 text-sm font-medium text-gray-600">Product</th>
                  <th class="text-left py-2 text-sm font-medium text-gray-600">Price</th>
                  <th class="text-left py-2 text-sm font-medium text-gray-600">Stock</th>
                  <th class="text-left py-2 text-sm font-medium text-gray-600">Status</th>
                  <th class="text-left py-2 text-sm font-medium text-gray-600">Added</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in recentProducts" :key="product.id" class="border-b border-gray-100 hover:bg-gray-50">
                  <td class="py-3 text-sm text-gray-800">{{ product.name }}</td>
                  <td class="py-3 text-sm text-gray-600">₱{{ formatCurrency(product.price) }}</td>
                  <td class="py-3 text-sm text-gray-600">{{ product.stock_quantity || 0 }}</td>
                  <td class="py-3">
                    <span class="text-xs px-2 py-1 rounded-full" :class="getStockStatusClass(product.stock_quantity)">
                      {{ getStockStatus(product.stock_quantity) }}
                    </span>
                  </td>
                  <td class="py-3 text-sm text-gray-500">{{ formatDate(product.created_at) }}</td>
                </tr>
              </tbody>
            </table>
            <div v-if="recentProducts.length === 0" class="text-center text-gray-500 py-8">
              No recent products to display
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
import { ref, computed, onMounted, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import StatsCard from '@/Components/StatsCard.vue';
import UnAuthorized from "@/Components/UnAuthorized.vue";
import { notify2 } from "@/globalFunctions.js";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  BarController,
  Title,
  Tooltip,
  Legend
} from 'chart.js';

const $page = usePage();

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
  productSalesData: {
    type: Array,
    default: () => [],
  },
});

// Register Chart.js components
ChartJS.register(CategoryScale, LinearScale, BarElement, BarController, Title, Tooltip, Legend);

const products = ref(props.products || []);
const stats = ref(props.stats || {});
const productSalesData = ref(props.productSalesData || []);
const currentDateTime = ref('');
const salesChart = ref(null);
let chartInstance = null;

const calculatedStats = computed(() => {
  const productList = products.value || [];
  
  const totalProducts = productList.length;
  const inStock = productList.filter(product => (product.stock_quantity || 0) >= 11).length;
  const lowStock = productList.filter(product => {
    const stock = product.stock_quantity || 0;
    return stock > 0 && stock < 11;
  }).length;
  const outOfStock = productList.filter(product => (product.stock_quantity || 0) === 0).length;
  
  const totalValue = productList.reduce((sum, product) => {
    return sum + ((product.price || 0) * (product.stock_quantity || 0));
  }, 0);
  
  const avgValue = totalProducts > 0 ? totalValue / totalProducts : 0;
  const totalStockUnits = productList.reduce((sum, product) => sum + (product.stock_quantity || 0), 0);
  
  return {
    total_products: totalProducts,
    in_stock: inStock,
    low_stock: lowStock,
    out_of_stock: outOfStock,
    total_value: totalValue,
    avg_value: avgValue,
    total_stock_units: totalStockUnits
  };
});

const criticalProducts = computed(() => {
  return (products.value || []).filter(product => (product.stock_quantity || 0) === 0);
});

const lowStockProducts = computed(() => {
  return (products.value || []).filter(product => {
    const stock = product.stock_quantity || 0;
    return stock > 0 && stock < 11;
  });
});

const topValueProducts = computed(() => {
  return [...(products.value || [])]
    .sort((a, b) => (b.price || 0) - (a.price || 0))
    .slice(0, 5);
});

const productsNeedingAttention = computed(() => {
  return [...(products.value || [])]
    .filter(product => (product.stock_quantity || 0) <= 10)
    .sort((a, b) => (a.stock_quantity || 0) - (b.stock_quantity || 0))
    .slice(0, 5);
});

const recentProducts = computed(() => {
  return [...(products.value || [])]
    .sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
    .slice(0, 5);
});

const hasActualSalesData = computed(() => {
  return productSalesData.value &&
         productSalesData.value.length > 0 &&
         productSalesData.value.some(item => item.total_sales > 0);
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
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

const handleStatsCardClick = (filter) => {
  const routes = {
    'all': '/admin/products', 
    'in-stock': '/admin/products', 
    'low-stock': '/admin/products', 
    'out-of-stock': '/admin/products' 
  };

  if (routes[filter]) {
    router.visit(routes[filter]);
  }
};

const getStockStatus = (stock) => {
  if (stock === 0) return 'Out of Stock';
  if (stock < 11) return 'Low Stock';
  return 'In Stock';
};

const getStockStatusClass = (stock) => {
  if (stock === 0) return 'text-red-600 bg-red-100';
  if (stock < 11) return 'text-orange-600 bg-orange-100';
  return 'text-green-600 bg-green-100';
};

const exportInventoryReport = () => {
  const headers = ['Product Name', 'Price', 'Stock Quantity', 'Status', 'Total Value'];
  const csvContent = [
    headers.join(','),
    ...(products.value || []).map(product => [
      `"${product.name || ''}"`,
      product.price || 0,
      product.stock_quantity || 0,
      `"${getStockStatus(product.stock_quantity || 0)}"`,
      (product.price || 0) * (product.stock_quantity || 0)
    ].join(','))
  ].join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `inventory-report-${new Date().toISOString().split('T')[0]}.csv`;
  a.click();
  window.URL.revokeObjectURL(url);

  notify2('Inventory report exported successfully!', 'success');
};

const createSalesChart = () => {
  if (!salesChart.value) {
    console.log('Sales chart canvas not found');
    return;
  }

  if (!productSalesData.value || productSalesData.value.length === 0) {
    console.log('No sales data available');
    return;
  }

  // Destroy existing chart if it exists
  if (chartInstance) {
    chartInstance.destroy();
  }

  const salesData = productSalesData.value || [];
  console.log('Raw sales data:', salesData);

  // Filter out products with zero sales and sort by total_sales in descending order
  const filteredData = salesData.filter(item => item.total_sales > 0);
  const sortedData = [...filteredData].sort((a, b) => b.total_sales - a.total_sales);

  console.log('Filtered and sorted data:', sortedData);

  // If no products have sales, don't render the chart
  if (sortedData.length === 0) {
    console.log('No products with sales found');
    return;
  }

  const labels = sortedData.map(item => {
    // Ensure we have a valid name
    const name = item.name || 'Unnamed Product';
    // Truncate long product names for better display
    return name.length > 20 ? name.substring(0, 20) + '...' : name;
  });

  const data = sortedData.map(item => {
    // Ensure we have valid numerical data
    const sales = parseInt(item.total_sales) || 0;
    console.log(`Product: ${item.name}, Sales: ${sales}`);
    return sales;
  });

  // Generate colors for bars (gradient from highest to lowest)
  const colors = sortedData.map((_, index) => {
    const intensity = 1 - (index / sortedData.length) * 0.7;
    return `rgba(59, 130, 246, ${intensity})`;
  });

  const borderColors = sortedData.map((_, index) => {
    const intensity = 1 - (index / sortedData.length) * 0.7;
    return `rgba(37, 99, 235, ${intensity})`;
  });

  chartInstance = new ChartJS(salesChart.value, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total Sales',
        data: data,
        backgroundColor: colors,
        borderColor: borderColors,
        borderWidth: 1,
        borderRadius: 4,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Units Sold'
          },
          ticks: {
            stepSize: 1
          }
        },
        x: {
          title: {
            display: true,
            text: 'Products'
          },
          ticks: {
            maxRotation: 45,
            minRotation: 45
          }
        }
      },
      plugins: {
        title: {
          display: true,
          text: 'Product Sales Performance',
          font: {
            size: 16,
            weight: 'bold'
          }
        },
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            title: function(tooltipItems) {
              const index = tooltipItems[0].dataIndex;
              return sortedData[index]?.name || '';
            },
            label: function(context) {
              return `Units Sold: ${context.parsed.y}`;
            }
          }
        }
      }
    }
  });
};

onMounted(() => {
  products.value = props.products || [];
  stats.value = props.stats || {};
  productSalesData.value = props.productSalesData || [];
  updateDateTime();

  setInterval(updateDateTime, 60000);

  if ($page.props.auth.user.role === 'admin') {
    showStockAlerts();

    // Create chart after DOM is ready
    nextTick(() => {
      createSalesChart();
    });
  }
});

const showStockAlerts = () => {
  const { low_stock, out_of_stock } = calculatedStats.value;
  
  if (out_of_stock > 0) {
    notify2(
      `⚠️ Warning: ${out_of_stock} product${out_of_stock > 1 ? 's are' : ' is'} out of stock!`,
      'error',
    );
  }
  
  if (low_stock > 0) {
    setTimeout(() => {
      notify2(
        `Alert: ${low_stock} product${low_stock > 1 ? 's have' : ' has'} low stock !`,
        'warning',
      );
    }, out_of_stock > 0 ? 1500 : 0); 
  }
  
  if (out_of_stock === 0 && low_stock === 0 && calculatedStats.value.total_products > 0) {
    setTimeout(() => {
      notify2(
        '✅ All products are well-stocked!',
        'success',
      );
    }, 500);
  }
};
</script>