<template>
  <div class="overflow-x-auto" ref="scrollContainer"
    @mousedown="startDrag"
    @mousemove="dragScroll"
    @mouseup="endDrag"
    @mouseleave="endDrag"
    :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'">
    <table ref="table" class="min-w-full divide-y divide-gray-200 select-none">
      <colgroup>
        <col v-for="(column, index) in columns" :key="column.key"
          :style="getColStyle(column, index)">
      </colgroup>
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            :class="[
              'px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider',
              column.align === 'right' ? 'text-right' :
              column.align === 'center' ? 'text-center' : 'text-left',
              column.sticky ? 'sticky right-0 bg-gray-50 z-10 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]' : ''
            ]"
          >
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr
          v-for="item in data"
          :key="item[itemKey]"
          @click="selectRow(item, itemKey)"
          :class="[
            'cursor-pointer transition-colors duration-150',
            selectedRowKey === item[itemKey]
              ? 'bg-blue-100 hover:bg-blue-200'
              : 'hover:bg-gray-50'
          ]"
        >
          <td
            v-for="column in columns"
            :key="column.key"
            :class="[
              'px-6 py-4 whitespace-nowrap',
              column.align === 'right' ? 'text-right' :
              column.align === 'center' ? 'text-center' : 'text-left',
              column.sticky ? 'sticky right-0 bg-white z-10 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]' : ''
            ]"
          >
            <slot 
              v-if="$slots[`column-${column.key}`]" 
              :name="`column-${column.key}`" 
              :item="item" 
              :value="getNestedValue(item, column.key)"
              :column="column"
            ></slot>
            
            <div v-else>
              <div v-if="!column.type || column.type === 'text'" class="text-sm font-medium text-gray-900">
                {{ getNestedValue(item, column.key) }}
              </div>
              
              <div v-else-if="column.type === 'currency'" class="text-sm font-medium text-gray-900">
                ₱{{ parseFloat(getNestedValue(item, column.key) || 0).toFixed(2) }}
              </div>
              
              <span v-else-if="column.type === 'badge'" 
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  getBadgeClasses(getNestedValue(item, column.key), column.badgeColors)
                ]"
              >
                {{ getNestedValue(item, column.key) }}
              </span>
              
              <div v-else-if="column.type === 'number'" class="text-sm text-gray-900">
                {{ getNestedValue(item, column.key) || 0 }}
              </div>
              
              <div v-else-if="column.type === 'date'" class="text-sm text-gray-900">
                {{ formatDate(getNestedValue(item, column.key)) }}
              </div>
              
              <div v-else-if="column.type === 'boolean'">
                <span :class="getNestedValue(item, column.key) ? 'text-green-600' : 'text-red-600'">
                  {{ getNestedValue(item, column.key) ? '✓' : '✗' }}
                </span>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    
    <div v-if="data.length === 0" class="text-center py-12">
      <div class="text-gray-500 text-lg mb-4">
        {{ emptyMessage || 'No data found' }}
      </div>
      <slot name="empty-state"></slot>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  },
  columns: {
    type: Array,
    required: true,
    default: () => []
    // Expected format:
    // [
    //   { 
    //     key: 'name', 
    //     label: 'Name', 
    //     type: 'text', // text, currency, badge, number, date, boolean
    //     align: 'left', // left, center, right
    //     badgeColors: { // for badge type
    //       'Active': 'bg-green-100 text-green-800',
    //       'Inactive': 'bg-red-100 text-red-800'
    //     }
    //   }
    // ]
  },
  itemKey: {
    type: String,
    default: 'id'
  },
  emptyMessage: {
    type: String,
    default: 'No data found'
  }
});

const table = ref();
const scrollContainer = ref();
const isDragging = ref(false);
const dragStartX = ref(0);
const dragScrollLeft = ref(0);
const selectedRowKey = ref(null);

const startDrag = (e) => {
  isDragging.value = true;
  dragStartX.value = e.pageX - scrollContainer.value.offsetLeft;
  dragScrollLeft.value = scrollContainer.value.scrollLeft;
};

const dragScroll = (e) => {
  if (!isDragging.value) return;

  e.preventDefault();
  const x = e.pageX - scrollContainer.value.offsetLeft;
  const walk = (x - dragStartX.value) * 1; // 1 = scroll speed
  scrollContainer.value.scrollLeft = dragScrollLeft.value - walk;
};

const endDrag = () => {
  isDragging.value = false;
};

const selectRow = (item, itemKey) => {
  const key = item[itemKey];
  selectedRowKey.value = selectedRowKey.value === key ? null : key;
};

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => {
    return current && current[key] !== undefined ? current[key] : null;
  }, obj);
};

const getBadgeClasses = (value, colorMap) => {
  if (colorMap && colorMap[value]) {
    return colorMap[value];
  }
  return 'bg-blue-100 text-blue-800';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString();
};

const getColStyle = (column, index) => {
  if (column.sticky) {
    return {
      position: 'sticky',
      right: 0
    };
  }
  return {};
};

defineExpose({
  table
});
</script>