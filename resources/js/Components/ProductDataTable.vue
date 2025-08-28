<template>
  <div class="data-table-container">
    <!-- Search and Filter Controls -->
    <div class="mb-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search products..."
            class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        
        <select
          v-model="itemsPerPage"
          class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="10">10 per page</option>
          <option value="25">25 per page</option>
          <option value="50">50 per page</option>
          <option value="100">100 per page</option>
        </select>
      </div>
      
      <div class="text-sm text-gray-500">
        Showing {{ startIndex + 1 }} - {{ endIndex }} of {{ filteredData.length }} results
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
              :class="{ 'hover:bg-gray-100': column.sortable }"
              @click="column.sortable && toggleSort(column.key)"
            >
              <div class="flex items-center gap-2">
                {{ column.label }}
                <div v-if="column.sortable" class="flex flex-col">
                  <svg
                    class="w-3 h-3 text-gray-400"
                    :class="{ 'text-blue-600': sortColumn === column.key && sortDirection === 'asc' }"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
                  </svg>
                  <svg
                    class="w-3 h-3 text-gray-400 -mt-1"
                    :class="{ 'text-blue-600': sortColumn === column.key && sortDirection === 'desc' }"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                  </svg>
                </div>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="(item, index) in paginatedData"
            :key="item.id"
            class="hover:bg-gray-50 transition-colors duration-150"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap"
            >
              <!-- Item Code Column -->
              <div v-if="column.key === 'item_code'" class="text-sm font-medium text-gray-900">
                {{ item.item_code }}
              </div>
              
              <!-- Name Column -->
              <div v-else-if="column.key === 'name'" class="min-w-0">
                <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                <div v-if="item.description" class="text-sm text-gray-500 truncate max-w-xs">
                  {{ item.description }}
                </div>
              </div>
              
              <!-- Barcode Column -->
              <div v-else-if="column.key === 'barcode'" class="flex flex-col">
                <div class="mb-2">
                  <svg :width="120" height="30" class="border border-gray-200">
                    <g v-for="(digit, digitIndex) in item.barcode" :key="digitIndex">
                      <rect 
                        :x="digitIndex * 9" 
                        y="0" 
                        :width="parseInt(digit) % 2 === 0 ? 2 : 4" 
                        height="25" 
                        :fill="parseInt(digit) % 2 === 0 ? '#000' : '#333'"
                      />
                    </g>
                  </svg>
                </div>
                <div class="text-xs font-mono text-gray-700 bg-gray-50 px-2 py-1 rounded">
                  {{ item.barcode }}
                </div>
                <button 
                  @click="$emit('copy-barcode', item.barcode)"
                  class="mt-1 text-xs text-blue-600 hover:text-blue-800 text-left"
                  title="Copy barcode"
                >
                  📋 Copy
                </button>
              </div>
              
              <!-- Price Column -->
              <div v-else-if="column.key === 'price'" class="text-sm font-medium text-gray-900">
                {{ column.format ? column.format(item.price) : item.price }}
              </div>
              
              <!-- Category Column -->
              <div v-else-if="column.key === 'category'">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ item.category?.name || 'N/A' }}
                </span>
              </div>
              
              <!-- Stock Column -->
              <div v-else-if="column.key === 'stock_quantity'" class="text-sm text-gray-900">
                {{ item.stock_quantity || 0 }}
                <span v-if="(item.stock_quantity || 0) < 10" class="ml-1 text-red-500 text-xs">⚠️ Low</span>
              </div>
              
              <!-- Actions Column -->
              <div v-else-if="column.key === 'actions'" class="text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button
                    @click="$emit('edit', item)"
                    class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50"
                    title="Edit product"
                  >
                    Edit
                  </button>
                  <button
                    @click="$emit('delete', item)"
                    class="text-red-600 hover:text-red-900 px-2 py-1 rounded hover:bg-red-50"
                    title="Delete product"
                  >
                    Delete
                  </button>
                  <button
                    @click="$emit('print-barcode', item)"
                    class="text-green-600 hover:text-green-900 px-2 py-1 rounded hover:bg-green-50"
                    title="Print Barcode"
                  >
                    🖨️ Print
                  </button>
                </div>
              </div>
              
              <!-- Default Column -->
              <div v-else class="text-sm text-gray-900">
                {{ column.accessor ? column.accessor(item) : item[column.key] }}
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div v-if="paginatedData.length === 0" class="text-center py-12">
        <div class="text-gray-500 text-lg mb-2">
          {{ searchQuery ? 'No products match your search' : 'No products found' }}
        </div>
        <div v-if="searchQuery" class="text-gray-400 text-sm">
          Try adjusting your search terms
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="mt-4 flex items-center justify-between">
      <div class="text-sm text-gray-500">
        Page {{ currentPage }} of {{ totalPages }}
      </div>
      
      <div class="flex items-center gap-2">
        <button
          @click="goToPage(1)"
          :disabled="currentPage === 1"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          First
        </button>
        
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        
        <div class="flex gap-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            class="px-3 py-2 text-sm border rounded-lg"
            :class="{
              'bg-blue-500 text-white border-blue-500': page === currentPage,
              'border-gray-300 hover:bg-gray-50': page !== currentPage
            }"
          >
            {{ page }}
          </button>
        </div>
        
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
        
        <button
          @click="goToPage(totalPages)"
          :disabled="currentPage === totalPages"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Last
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['edit', 'delete', 'print-barcode', 'copy-barcode']);

// Reactive data
const searchQuery = ref('');
const sortColumn = ref('');
const sortDirection = ref('asc');
const currentPage = ref(1);
const itemsPerPage = ref(25);

// Computed properties
const filteredData = computed(() => {
  if (!searchQuery.value) return props.data;
  
  const query = searchQuery.value.toLowerCase();
  return props.data.filter(item => {
    return props.columns.some(column => {
      if (!column.searchable) return false;
      
      let value;
      if (column.accessor) {
        value = column.accessor(item);
      } else if (column.key.includes('.')) {
        // Handle nested properties like 'category.name'
        value = column.key.split('.').reduce((obj, key) => obj?.[key], item);
      } else {
        value = item[column.key];
      }
      
      return String(value || '').toLowerCase().includes(query);
    });
  });
});

const sortedData = computed(() => {
  if (!sortColumn.value) return filteredData.value;
  
  const column = props.columns.find(col => col.key === sortColumn.value);
  
  return [...filteredData.value].sort((a, b) => {
    let aValue, bValue;
    
    if (column.accessor) {
      aValue = column.accessor(a);
      bValue = column.accessor(b);
    } else if (column.key.includes('.')) {
      aValue = column.key.split('.').reduce((obj, key) => obj?.[key], a);
      bValue = column.key.split('.').reduce((obj, key) => obj?.[key], b);
    } else {
      aValue = a[column.key];
      bValue = b[column.key];
    }
    
    // Handle null/undefined values
    if (aValue === null || aValue === undefined) aValue = '';
    if (bValue === null || bValue === undefined) bValue = '';
    
    // Convert to strings for comparison
    aValue = String(aValue).toLowerCase();
    bValue = String(bValue).toLowerCase();
    
    if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1;
    if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
});

const totalPages = computed(() => {
  return Math.ceil(sortedData.value.length / itemsPerPage.value);
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return sortedData.value.slice(start, end);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage.value;
});

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage.value, filteredData.value.length);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i);
      }
      pages.push('...', total);
    } else if (current >= total - 3) {
      pages.push(1, '...');
      for (let i = total - 4; i <= total; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1, '...');
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i);
      }
      pages.push('...', total);
    }
  }
  
  return pages.filter(page => page !== '...');
});

// Methods
const toggleSort = (columnKey) => {
  if (sortColumn.value === columnKey) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = columnKey;
    sortDirection.value = 'asc';
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Watchers
watch([searchQuery, itemsPerPage], () => {
  currentPage.value = 1; // Reset to first page when search or items per page changes
});
</script>

<style scoped>
/* Add any custom styles here */
.data-table-container {
  /* Custom styles for the data table container */
}
</style>