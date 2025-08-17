<template>
  <div class="min-h-screen bg-stone-100">
    <!-- Sticky Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center space-x-4">
            <button 
              @click="$router.push('/system-info')"
              class="text-stone-500 hover:text-stone-700 p-2 rounded-lg transition-colors duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <h1 class="text-2xl font-bold text-stone-700">Session Management by User</h1>
          </div>
          
          <!-- Navigation -->
          <div class="flex items-center space-x-4">
            <router-link 
              to="/system-info"
              class="text-stone-500 hover:text-stone-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
            >
              Back to System Info
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 00-2 2v2a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Total Sessions</p>
                <p class="text-2xl font-bold text-stone-900">{{ summary.totalSessions }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Active Sessions</p>
                <p class="text-2xl font-bold text-stone-900">{{ summary.activeSessions }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-yellow-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Avg Duration</p>
                <p class="text-2xl font-bold text-stone-900">{{ formatDuration(summary.averageDuration) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Users with Sessions</p>
                <p class="text-2xl font-bold text-stone-900">{{ summary.usersWithSessions }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Sessions Table -->
      <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-stone-900">Session Management by User</h3>
          <p class="text-sm text-stone-500">Session activity breakdown for each user</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sessions</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Sessions</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recordings per Session</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Session</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in userSessionsData" :key="user.id" class="hover:bg-gray-50">
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
                  <div class="text-sm font-medium text-gray-900">{{ user.totalSessions }}</div>
                  <div class="text-xs text-gray-500">{{ user.recentSessions }} in last 30 days</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="user.activeSessions > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ user.activeSessions }} active
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    0 active
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDuration(user.totalDuration) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ user.averageRecordingsPerSession }}</div>
                  <div class="text-xs text-gray-500">avg per session</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(user.lastSession) }}</td>
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
            Loading session data...
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && userSessionsData.length === 0" class="p-8 text-center">
          <p class="text-stone-500">No session data available</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const userSessionsData = ref([])
const summary = ref({
  totalSessions: 0,
  activeSessions: 0,
  averageDuration: 0,
  usersWithSessions: 0
})

// Format duration
const formatDuration = (seconds) => {
  if (!seconds) return '0 min'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
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

// Load session data
const loadSessionData = async () => {
  loading.value = true
  try {
    const response = await window.axios.get('/api/system/users-sessions')
    userSessionsData.value = response.data.users || []
    summary.value = response.data.summary || {}
  } catch (error) {
    console.error('Failed to load session data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSessionData()
})
</script>
