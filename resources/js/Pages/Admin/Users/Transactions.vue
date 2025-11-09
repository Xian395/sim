<template>
  <AdminAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Staff Transactions</h2>
    </template>

    <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6">
            <div>
              <h2 class="text-2xl font-black text-gray-700">
                Transactions - {{ staff?.name }}
              </h2>
              <p class="text-sm text-gray-500 mt-1">Role: {{ staff?.role }}</p>
            </div>

            <Link
              :href="route('admin.users.index')"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"
            >
              Back to Users
            </Link>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
          <p class="text-gray-600">Loading transactions...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="transactions.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
          <p class="text-gray-600">No transactions found for this staff member.</p>
        </div>

        <!-- Transactions List -->
        <div v-else class="space-y-4">
          <!-- Search and Filters -->
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
import { ref, onMounted, computed, watch } from 'vue';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import { notify } from "@/globalFunctions.js";

const props = defineProps({
  userId: [String, Number],
  staff: {
    type: Object,
    default: () => ({ name: '', role: '' })
  }
});

const staff = ref(props.staff || {
  name: '',
  role: ''
});
const transactions = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const pagination = ref({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0
});

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toFixed(2);
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

const fetchTransactions = async () => {
  loading.value = true;
  try {
    const response = await fetch(`/api/staff-transactions/${props.userId}`);
    if (!response.ok) throw new Error('Failed to fetch');
    transactions.value = await response.json();
  } catch (error) {
    console.error('Error fetching transactions:', error);
    notify('Failed to load transactions', 'error');
  } finally {
    loading.value = false;
  }
};

const resetPage = () => {
  currentPage.value = 1;
};

onMounted(() => {
  fetchTransactions();
});

// Watch for search changes and reset page to 1
watch(searchQuery, resetPage);
</script>
