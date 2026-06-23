<template>
  <div class="min-h-screen bg-stone-100">
      <LoggedInHeaderNav 
     :userName="store.user?.name" 
      :showHeaderToggle="false"
    />

    <!-- Back button and page title below header -->
    <PageHeader 
      :pageTitle="'User Storage Report'"
      :route="'/system-info'"
    />
   
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Total Storage</p>
                <p class="text-2xl font-bold text-stone-900">{{ formatFileSize(summary.totalStorage) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Active Users</p>
                <p class="text-2xl font-bold text-stone-900">{{ summary.activeUsers }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Average per User</p>
                <p class="text-2xl font-bold text-stone-900">{{ formatFileSize(summary.averagePerUser) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Storage Table -->
      <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-stone-900">Storage Usage by User</h3>
          <p class="text-sm text-stone-500">Detailed breakdown of storage usage for each user</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Storage</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recordings</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average File Size</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in userStorageData" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 bg-stone-200 rounded-full flex items-center justify-center">
                      <span class="text-sm font-medium text-stone-600">{{ user.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ formatFileSize(Number(user.totalStorageBytes)) }}</div>
                  <div class="text-xs text-gray-500">
                    <template v-if="summary.totalStorage > 0 && user.totalStorageBytes > 0">
                      {{ ((Number(user.totalStorageBytes) / Number(summary.totalStorage)) * 100).toFixed(1) }}% of total
                    </template>
                    <template v-else>
                      0% of total
                    </template>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <template v-if="user.totalRecordings !== undefined && user.totalRecordings !== null">
                    {{ user.totalRecordings }}
                  </template>
                  <template v-else>
                    0
                  </template>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <template v-if="user.totalSessions !== undefined && user.totalSessions !== null">
                    {{ user.totalSessions }}
                  </template>
                  <template v-else>
                    0
                  </template>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatFileSize(user.averageFileSize) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(user.lastActive) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center">
          <div class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-stone-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading storage data...
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && userStorageData.length === 0" class="p-8 text-center">
          <p class="text-stone-500">No storage data available</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import LoggedInHeaderNav from './LoggedInHeaderNav.vue'
import PageHeader from './PageHeader.vue'
import { useMainStore } from '../stores/main';

const store = useMainStore();

const router = useRouter()
const loading = ref(false)
const userStorageData = ref([])
const summary = ref({
  totalStorage: 0,
  activeUsers: 0,
  averagePerUser: 0
})

// Format file size
const formatFileSize = (bytes) => {
  bytes = Number(bytes);
  if (!bytes || isNaN(bytes) || bytes <= 0) return '0 B';
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  const size = bytes / Math.pow(1024, i);
  return `${size.toFixed(i === 0 ? 0 : 1)} ${sizes[i]}`;
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Load storage data
const loadStorageData = async () => {
  loading.value = true
  try {
    const response = await window.axios.get('/api/system/users-storage')
    userStorageData.value = response.data.users || []
    summary.value = response.data.summary || {}
  } catch (error) {
    console.error('Failed to load storage data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadStorageData()
})
</script>
