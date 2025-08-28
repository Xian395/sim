<template>
  <div class="responsive-table-container">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto shadow-sm rounded-lg">
      <table class="min-w-full divide-y divide-gray-200 bg-white">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider',
                column.align === 'center' ? 'text-center' : 
                column.align === 'right' ? 'text-right' : 'text-left',
                column.sortable ? 'cursor-pointer hover:bg-gray-100' : ''
              ]"
              @click="column.sortable ? handleSort(column.key) : null"
            >
              <div class="flex items-center gap-2">
                {{ column.label }}
                <div v-if="column.sortable" class="flex flex-col">
                  <svg 
                    :class="[
                      'w-3 h-3 transition-colors',
                      sortField === column.key && sortDirection === 'asc' 
                        ? 'text-blue-600' : 'text-gray-400'
                    ]"
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                  </svg>
                  <svg 
                    :class="[
                      'w-3 h-3 transition-colors -mt-1',
                      sortField === column.key && sortDirection === 'desc' 
                        ? 'text-blue-600' : 'text-gray-400'
                    ]"
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="(item, index) in sortedData" 
            :key="getRowKey(item, index)"
            :class="[
              'transition-colors duration-150',
              hoverable ? 'hover:bg-gray-50' : '',
              striped && index % 2 === 1 ? 'bg-gray-25' : '',
              getRowClass ? getRowClass(item, index) : ''
            ]"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-4 whitespace-nowrap',
                column.align === 'center' ? 'text-center' : 
                column.align === 'right' ? 'text-right' : 'text-left',
                column.cellClass || ''
              ]"
            >
              <slot 
                :name="`cell-${column.key}`" 
                :item="item" 
                :value="getNestedValue(item, column.key)"
                :index="index"
              >
                <div v-if="column.type === 'currency'" class="text-sm font-medium text-gray-900">
                  {{ formatCurrency(getNestedValue(item, column.key)) }}
                </div>
                <div v-else-if="column.type === 'badge'" class="inline-flex items-center">
                  <span :class="getBadgeClass(getNestedValue(item, column.key), column.badgeColors)">
                    {{ getNestedValue(item, column.key) }}
                  </span>
                </div>
                <div v-else-if="column.type === 'boolean'" class="text-sm">
                  <span :class="getNestedValue(item, column.key) ? 'text-green-600' : 'text-red-600'">
                    {{ getNestedValue(item, column.key) ? '✓' : '✗' }}
                  </span>
                </div>
                <div v-else-if="column.type === 'date'" class="text-sm text-gray-900">
                  {{ formatDate(getNestedValue(item, column.key)) }}
                </div>
                <div v-else class="text-sm text-gray-900">
                  {{ column.formatter ? column.formatter(getNestedValue(item, column.key), item) : getNestedValue(item, column.key) }}
                </div>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-4">
      <div
        v-for="(item, index) in sortedData"
        :key="getRowKey(item, index)"
        class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
      >
        <slot name="mobile-card" :item="item" :index="index">
          <div class="space-y-3">
            <div
              v-for="column in visibleMobileColumns"
              :key="column.key"
              class="flex justify-between items-start"
            >
              <div class="text-sm font-medium text-gray-500 flex-shrink-0 w-1/3">
                {{ column.label }}:
              </div>
              <div class="text-sm text-gray-900 flex-1 text-right">
                <slot 
                  :name="`mobile-${column.key}`" 
                  :item="item" 
                  :value="getNestedValue(item, column.key)"
                  :index="index"
                >
                  <div v-if="column.type === 'currency'">
                    {{ formatCurrency(getNestedValue(item, column.key)) }}
                  </div>
                  <div v-else-if="column.type === 'badge'">
                    <span :class="getBadgeClass(getNestedValue(item, column.key), column.badgeColors)">
                      {{ getNestedValue(item, column.key) }}
                    </span>
                  </div>
                  <div v-else-if="column.type === 'boolean'">
                    <span :class="getNestedValue(item, column.key) ? 'text-green-600' : 'text-red-600'">
                      {{ getNestedValue(item, column.key) ? '✓' : '✗' }}
                    </span>
                  </div>
                  <div v-else-if="column.type === 'date'">
                    {{ formatDate(getNestedValue(item, column.key)) }}
                  </div>
                  <div v-else>
                    {{ column.formatter ? column.formatter(getNestedValue(item, column.key), item) : getNestedValue(item, column.key) }}
                  </div>
                </slot>
              </div>
            </div>
            
            <!-- Mobile Actions -->
            <div v-if="$slots['mobile-actions']" class="pt-3 border-t border-gray-100">
              <slot name="mobile-actions" :item="item" :index="index"></slot>
            </div>
          </div>
        </slot>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="sortedData.length === 0" class="text-center py-12 bg-white rounded-lg shadow-sm">
      <slot name="empty-state">
        <div class="text-gray-500 text-lg mb-2">{{ emptyMessage }}</div>
        <div class="text-gray-400 text-sm">{{ emptySubMessage }}</div>
      </slot>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
      <div class="flex items-center space-x-2">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-gray-600">Loading...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  // Data
  data: {
    type: Array,
    required: true,
    default: () => []
  },
  
  // Column definitions
  columns: {
    type: Array,
    required: true,
    default: () => []
  },
  
  // Table behavior
  sortable: {
    type: Boolean,
    default: true
  },
  
  hoverable: {
    type: Boolean,
    default: true
  },
  
  striped: {
    type: Boolean,
    default: false
  },
  
  loading: {
    type: Boolean,
    default: false
  },
  
  // Mobile behavior
  mobileBreakpoint: {
    type: String,
    default: 'md'
  },
  
  // Empty state
  emptyMessage: {
    type: String,
    default: 'No data available'
  },
  
  emptySubMessage: {
    type: String,
    default: 'There are no items to display at this time.'
  },
  
  // Row key for v-for
  rowKey: {
    type: [String, Function],
    default: 'id'
  },
  
  // Custom row class function
  getRowClass: {
    type: Function,
    default: null
  },
  
  // Currency formatting
  currencySymbol: {
    type: String,
    default: '₱'
  },
  
  // Initial sort
  defaultSort: {
    type: Object,
    default: () => ({ field: '', direction: 'asc' })
  }
});

const emit = defineEmits(['sort', 'row-click']);

// Sorting
const sortField = ref(props.defaultSort.field);
const sortDirection = ref(props.defaultSort.direction);

const sortedData = computed(() => {
  if (!sortField.value || !props.sortable) {
    return props.data;
  }
  
  return [...props.data].sort((a, b) => {
    const aVal = getNestedValue(a, sortField.value);
    const bVal = getNestedValue(b, sortField.value);
    
    if (aVal === null || aVal === undefined) return 1;
    if (bVal === null || bVal === undefined) return -1;
    
    let comparison = 0;
    
    if (typeof aVal === 'string' && typeof bVal === 'string') {
      comparison = aVal.localeCompare(bVal);
    } else if (typeof aVal === 'number' && typeof bVal === 'number') {
      comparison = aVal - bVal;
    } else if (aVal instanceof Date && bVal instanceof Date) {
      comparison = aVal.getTime() - bVal.getTime();
    } else {
      comparison = String(aVal).localeCompare(String(bVal));
    }
    
    return sortDirection.value === 'asc' ? comparison : -comparison;
  });
});

// Mobile columns (exclude action columns typically)
const visibleMobileColumns = computed(() => {
  return props.columns.filter(col => !col.hideOnMobile);
});

// Utility functions
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj);
};

const getRowKey = (item, index) => {
  if (typeof props.rowKey === 'function') {
    return props.rowKey(item, index);
  }
  return getNestedValue(item, props.rowKey) || index;
};

const handleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  
  emit('sort', { field: sortField.value, direction: sortDirection.value });
};

const formatCurrency = (value) => {
  if (value === null || value === undefined || isNaN(value)) return '';
  return `${props.currencySymbol}${parseFloat(value).toFixed(2)}`;
};

const formatDate = (value) => {
  if (!value) return '';
  const date = new Date(value);
  return date.toLocaleDateString();
};

const getBadgeClass = (value, colors = {}) => {
  const defaultColors = {
    default: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800',
    success: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
    warning: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    error: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800',
    info: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'
  };
  
  const allColors = { ...defaultColors, ...colors };
  return allColors[value] || allColors.default;
};
</script>

<style scoped>
.responsive-table-container {
  position: relative;
}

.bg-gray-25 {
  background-color: #fafafa;
}

/* Custom scrollbar for better UX */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>