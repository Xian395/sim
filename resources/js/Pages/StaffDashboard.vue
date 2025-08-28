<template>
  <AuthenticatedLayout>
    <div v-if="$page.props.auth.user.role === 'staff'" class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">
              Welcome!
            </h1>
            <p class="text-gray-600 mt-1">Point of Sale System</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <!-- POS Access Card -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Point of Sale</h3>
                  <p class="text-sm text-gray-500">Process customer transactions</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Today's Summary -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Today's Activity</h3>
                  <p class="text-sm text-gray-500">Your transaction summary</p>
                </div>
              </div>
              <div class="mt-4">
                <div class="text-2xl font-bold text-gray-900">{{ todaysTransactions || 0 }}</div>
                <div class="text-sm text-gray-500">Transactions processed</div>
              </div>
            </div>
          </div>

          <!-- Date & Time Info -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 012 0v4h3V3a1 1 0 012 0v4h1a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V9a2 2 0 012-2h1z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Date & Time</h3>
                  <p class="text-sm text-gray-500">Current information</p>
                </div>
              </div>
              <div class="mt-4">
                <div class="text-sm text-gray-600 space-y-1">
                  <div class="font-medium text-gray-900">{{ currentDate }}</div>
                  <div class="text-lg font-bold text-purple-600">{{ currentTime }}</div>
                  <div class="text-xs text-gray-500">{{ currentDay }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar -->
        <!-- <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Calendar</h2>
            <div class="calendar">
              <div class="flex items-center justify-between mb-4">
                <button 
                  @click="previousMonth" 
                  class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                  </svg>
                </button>
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ currentMonthYear }}
                </h3>
                <button 
                  @click="nextMonth" 
                  class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </div>

              <div class="grid grid-cols-7 gap-1 mb-2">
                <div 
                  v-for="day in weekDays" 
                  :key="day"
                  class="text-center text-xs font-medium text-gray-500 py-2"
                >
                  {{ day }}
                </div>
              </div>

              <div class="grid grid-cols-7 gap-1">
                <div 
                  v-for="date in calendarDates" 
                  :key="date.key"
                  class="relative h-10 flex items-center justify-center text-sm cursor-pointer hover:bg-gray-50 rounded transition-colors"
                  :class="{
                    'text-gray-400': !date.isCurrentMonth,
                    'text-gray-900': date.isCurrentMonth,
                    'bg-blue-500 text-white hover:bg-blue-600': date.isToday,
                    'font-semibold': date.isToday
                  }"
                  @click="selectDate(date)"
                >
                  {{ date.day }}
                  <div 
                    v-if="date.hasEvents" 
                    class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-green-500 rounded-full"
                  ></div>
                </div>
              </div>

              <div v-if="selectedDate" class="mt-4 p-3 bg-gray-50 rounded-lg">
                <div class="text-sm font-medium text-gray-900">
                  {{ formatSelectedDate(selectedDate) }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  Click on dates to view more details
                </div>
              </div>
            </div>
          </div>
        </div> -->

        <!-- Quick Tips -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Quick Tips</h3>
              <div class="mt-2 text-sm text-blue-700">
                <ul class="list-disc pl-5 space-y-1">
                  <li>Double-check transaction totals before processing payment</li>
                  <li>Always provide receipts to customers</li>
                  <li>If you encounter any issues, contact your supervisor</li>
                </ul>
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
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UnAuthorized from "@/Components/UnAuthorized.vue";
import { Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

// These would typically come from your backend/props
const todaysTransactions = ref(0); // You can pass this as a prop

// Date and Time reactive data
const currentTime = ref('');
const currentDate = ref('');
const currentDay = ref('');

// Calendar data
const currentCalendarDate = ref(new Date());
const selectedDate = ref(null);
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Update time every second
const updateDateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  });
  currentDate.value = now.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  currentDay.value = now.toLocaleDateString('en-US', {
    weekday: 'long'
  });
};

let timeInterval;

onMounted(() => {
  updateDateTime();
  timeInterval = setInterval(updateDateTime, 1000);
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});

// Calendar computed properties
const currentMonthYear = computed(() => {
  return currentCalendarDate.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  });
});

const calendarDates = computed(() => {
  const year = currentCalendarDate.value.getFullYear();
  const month = currentCalendarDate.value.getMonth();
  
  // First day of the month
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  
  // Start from the beginning of the week
  const startDate = new Date(firstDay);
  startDate.setDate(startDate.getDate() - startDate.getDay());
  
  // End at the end of the week
  const endDate = new Date(lastDay);
  endDate.setDate(endDate.getDate() + (6 - endDate.getDay()));
  
  const dates = [];
  const currentDate = new Date(startDate);
  const today = new Date();
  
  while (currentDate <= endDate) {
    const dateObj = {
      day: currentDate.getDate(),
      date: new Date(currentDate),
      isCurrentMonth: currentDate.getMonth() === month,
      isToday: currentDate.toDateString() === today.toDateString(),
      hasEvents: Math.random() > 0.8, 
      key: currentDate.toISOString()
    };
    dates.push(dateObj);
    currentDate.setDate(currentDate.getDate() + 1);
  }
  
  return dates;
});

const previousMonth = () => {
  const newDate = new Date(currentCalendarDate.value);
  newDate.setMonth(newDate.getMonth() - 1);
  currentCalendarDate.value = newDate;
};

const nextMonth = () => {
  const newDate = new Date(currentCalendarDate.value);
  newDate.setMonth(newDate.getMonth() + 1);
  currentCalendarDate.value = newDate;
};

const selectDate = (dateObj) => {
  selectedDate.value = dateObj.date;
};

const formatSelectedDate = (date) => {
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
};
</script>