<template>
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="flex items-center justify-between border-b border-gray-200 py-4 px-6">
            <div>
              <h2 class="text-2xl font-bold text-gray-700">My Sales Transactions</h2>
              <p class="text-sm text-gray-500 mt-1">View all your sales history</p>
            </div>
          </div>
        </div>

        <!-- Period Filter -->
        <div class="flex gap-2 mb-6 flex-wrap items-center">
          <label class="text-sm font-medium text-gray-700">Period:</label>
          <button
            @click="changePeriod('all')"
            :class="[
              'px-3 py-1 rounded text-sm font-medium transition',
              selectedPeriod === 'all'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            All Time
          </button>
          <button
            @click="changePeriod('daily')"
            :class="[
              'px-3 py-1 rounded text-sm font-medium transition',
              selectedPeriod === 'daily'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            Today
          </button>
          <button
            @click="changePeriod('weekly')"
            :class="[
              'px-3 py-1 rounded text-sm font-medium transition',
              selectedPeriod === 'weekly'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            This Week
          </button>
          <button
            @click="changePeriod('monthly')"
            :class="[
              'px-3 py-1 rounded text-sm font-medium transition',
              selectedPeriod === 'monthly'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            This Month
          </button>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-blue-600">Total Sales</div>
                <div class="text-2xl font-bold text-blue-900">
                  PHP {{ formatCurrency(summary.totalSales) }}
                </div>
              </div>
              <div class="text-blue-500">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm0 6a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2v-4a2 2 0 00-2-2H4z"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-green-600">Total Transactions</div>
                <div class="text-2xl font-bold text-green-900">
                  {{ summary.totalTransactions }}
                </div>
              </div>
              <div class="text-green-500">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm0 6a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2v-4a2 2 0 00-2-2H4z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 p-4 rounded-lg border border-yellow-200">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-yellow-600">Items Sold</div>
                <div class="text-2xl font-bold text-yellow-900">
                  {{ summary.totalItemsSold }}
                </div>
              </div>
              <div class="text-yellow-500">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM5 16a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-purple-600">Avg per Transaction</div>
                <div class="text-2xl font-bold text-purple-900">
                  PHP {{ formatCurrency(summary.averagePerTransaction) }}
                </div>
              </div>
              <div class="text-purple-500">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="flex gap-4 items-end">
            <!-- Search -->
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by product name..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="transactions.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="mt-4 text-gray-600">No sales transactions found</p>
        </div>

        <!-- Transactions List -->
        <div v-else class="space-y-4">
          <div
            v-for="transaction in paginatedTransactions"
            :key="transaction.id"
            class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <p class="text-base text-gray-900">
                  <span class="font-semibold text-blue-600">Processed sale:</span>
                  <span v-if="transaction.products.length > 0">
                    <span v-for="(product, index) in transaction.products" :key="index">
                      <span class="font-semibold">{{ product.name }}</span>
                      <span class="text-gray-600">[Qty: {{ product.qty }}]</span>
                      <span v-if="index < transaction.products.length - 1">, </span>
                    </span>
                  </span>
                  <span v-else class="text-gray-600">No products</span>
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                  <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                    <p class="text-lg font-semibold text-green-600">
                      PHP {{ formatAmount(transaction.total_amount) }}
                    </p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Payment</p>
                    <p class="text-lg font-semibold text-green-600">
                      PHP {{ formatAmount(transaction.payment_amount) }}
                    </p>
                  </div>
                  <div v-if="transaction.change_amount > 0">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Change</p>
                    <p class="text-lg font-semibold text-blue-600">
                      PHP {{ formatAmount(transaction.change_amount) }}
                    </p>
                  </div>
                </div>
              </div>
              <div class="text-right ml-4 mt-2">
                <p class="text-sm font-medium text-gray-600">
                  {{ formatDate(transaction.created_at) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  ID: {{ transaction.id }}
                </p>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="paginatedTransactions.length > 0" class="bg-white rounded-lg shadow p-6 space-y-4">
            <!-- Results Info -->
            <div class="flex justify-between items-center">
              <div class="flex items-center gap-3">
                <label class="text-sm text-gray-700">Items per page:</label>
                <select
                  v-model.number="itemsPerPage"
                  @change="handleItemsPerPageChange"
                  class="px-3 py-2 pr-8 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm appearance-none bg-white cursor-pointer"
                >
                  <option :value="5">5</option>
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                </select>
              </div>

              <div class="text-sm text-gray-600">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
              </div>
            </div>

            <!-- Page Navigation -->
            <div v-if="pagination.last_page > 1" class="flex gap-2 justify-end">
              <button
                v-if="pagination.current_page > 1"
                @click="handlePageChange(pagination.current_page - 1)"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition"
              >
                Previous
              </button>

              <div class="flex gap-1 items-center">
                <template v-for="pageNum in getPaginationPages()" :key="pageNum">
                  <span v-if="pageNum === '...'" class="px-2 py-2 text-gray-500">...</span>
                  <button
                    v-else
                    @click="handlePageChange(pageNum)"
                    :class="[
                      'px-3 py-2 rounded transition',
                      pageNum === pagination.current_page
                        ? 'bg-blue-500 text-white font-semibold'
                        : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                    ]"
                  >
                    {{ pageNum }}
                  </button>
                </template>
              </div>

              <button
                v-if="pagination.current_page < pagination.last_page"
                @click="handlePageChange(pagination.current_page + 1)"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  transactions: Array,
  pagination: Object,
  search: String,
  summary: Object,
  period: String,
});

const transactions = ref(props.transactions || []);
const searchQuery = ref(props.search || '');
const selectedPeriod = ref(props.period || 'all');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const pagination = ref(props.pagination || {
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0
});
const summary = ref(props.summary || {
  totalSales: 0,
  totalTransactions: 0,
  totalItemsSold: 0,
  averagePerTransaction: 0,
});

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toFixed(2);
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0);
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const filteredTransactions = computed(() => {
  let filtered = transactions.value;

  // Apply search filter
  if (searchQuery.value) {
    filtered = filtered.filter(transaction =>
      transaction.products.some(product =>
        product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    );
  }

  return filtered;
});

const paginatedTransactions = computed(() => {
  const filtered = filteredTransactions.value;
  const total = filtered.length;
  const perPage = itemsPerPage.value;
  const page = currentPage.value;

  // Update pagination metadata
  const lastPage = Math.ceil(total / perPage);
  const from = total === 0 ? 0 : (page - 1) * perPage + 1;
  const to = Math.min(page * perPage, total);

  pagination.value = {
    total,
    per_page: perPage,
    current_page: page,
    last_page: lastPage,
    from,
    to
  };

  // Return paginated slice
  const start = (page - 1) * perPage;
  const end = start + perPage;
  return filtered.slice(start, end);
});

const handlePageChange = (page) => {
  currentPage.value = page;
};

const handleItemsPerPageChange = () => {
  currentPage.value = 1;
};

const getPaginationPages = () => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  // Always show all pages if 5 or less
  if (last <= 5) {
    for (let i = 1; i <= last; i++) {
      pages.push(i);
    }
  } else {
    // Show first page
    pages.push(1);

    // Show second page if current is 1 or 2
    if (current <= 2) {
      pages.push(2);
    } else if (current > 2) {
      pages.push('...');
    }

    // Show pages around current
    const start = Math.max(3, current - 1);
    const end = Math.min(last - 2, current + 1);

    for (let i = start; i <= end; i++) {
      if (!pages.includes(i) && pages[pages.length - 1] !== '...') {
        pages.push(i);
      }
    }

    // Show ellipsis if needed
    if (current < last - 2 && pages[pages.length - 1] !== '...') {
      pages.push('...');
    }

    // Show last 2 pages
    if (current < last - 1) {
      pages.push(last - 1);
    }
    pages.push(last);
  }

  return pages.filter(p => p !== '...' || pages.indexOf('...') === pages.lastIndexOf('...'));
};

const resetPage = () => {
  currentPage.value = 1;
};

const changePeriod = (period) => {
  selectedPeriod.value = period;
  currentPage.value = 1;

  // Reload the page with the new period using Inertia router
  router.get(route('staff.transactions'), {
    period: period,
    search: searchQuery.value
  });
};

// Watch for search changes and reset page to 1
watch(searchQuery, resetPage);
</script>
