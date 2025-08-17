<template>
  <div class="min-h-screen bg-stone-100">
    <!-- Sticky Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center space-x-4">
            <button 
              @click="$router.push('/')"
              class="text-stone-500 hover:text-stone-700 p-2 rounded-lg transition-colors duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <h1 class="text-2xl font-bold text-stone-700">System Information</h1>
          </div>
          
          <!-- Navigation -->
          <div class="flex items-center space-x-4">
            <router-link 
              to="/profile"
              class="text-stone-500 hover:text-stone-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
            >
              User Profile
            </router-link>
            <button 
              @click="handleLogout"
              class="text-stone-500 hover:text-stone-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- System Stats -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Total Users</p>
                <p class="text-2xl font-bold text-stone-900">{{ systemStats.totalUsers }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Total Recordings</p>
                <p class="text-2xl font-bold text-stone-900">{{ systemStats.totalRecordings }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Storage Used</p>
                <p class="text-2xl font-bold text-stone-900">{{ formatFileSize(systemStats.totalStorageBytes) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-orange-100 rounded-lg">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Total Sessions</p>
                <p class="text-2xl font-bold text-stone-900">{{ systemStats.totalSessions }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Stats -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-indigo-100 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Total Duration</p>
                <p class="text-2xl font-bold text-stone-900">{{ formatDuration(systemStats.totalDuration) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-teal-100 rounded-lg">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Avg Recordings/User</p>
                <p class="text-2xl font-bold text-stone-900">{{ systemStats.averageRecordingsPerUser }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-pink-100 rounded-lg">
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Recent Users</p>
                <p class="text-2xl font-bold text-stone-900">{{ systemStats.recentUsers }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-2 bg-yellow-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-stone-500">Recent Recordings</p>
                <p class="text-2xl font-bold text-stone-900">{{ systemStats.recentRecordings }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Management Section (Admin only) -->
      <div v-if="isAdmin" class="bg-white shadow-sm rounded-lg mb-8">
        <div class="px-6 py-8">
          <div class="flex justify-between items-center mb-6">
            <div>
              <h2 class="text-xl font-semibold text-stone-900">User Management</h2>
              <p class="text-stone-500">Manage all users in the system</p>
            </div>
            <button
              @click="showCreateUser = true"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>Add New User</span>
            </button>
          </div>

          <div v-if="loadingUsers" class="text-center py-8">
            <div class="inline-flex items-center space-x-3 text-stone-500">
              <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Loading users...</span>
            </div>
          </div>

          <div v-else class="overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-stone-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-stone-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in users" :key="user.id" class="hover:bg-stone-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-stone-200 flex items-center justify-center">
                          <span class="text-sm font-medium text-stone-700">{{ user.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-stone-900">{{ user.name }}</div>
                          <div class="text-sm text-stone-500">#{{ user.id }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-900">{{ user.email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                            :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'">
                        {{ user.role || 'user' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-500">{{ formatDate(user.created_at) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                      <button
                        @click="editUser(user)"
                        class="text-stone-600 hover:text-stone-900 transition-colors duration-200"
                      >
                        Edit
                      </button>
                      <button
                        @click="confirmDeleteUser(user)"
                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                        :disabled="user.id === store.user.id"
                        :class="{ 'opacity-50 cursor-not-allowed': user.id === store.user.id }"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- System Settings -->
      <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-8">
          <h2 class="text-xl font-semibold text-stone-900 mb-6">System Settings</h2>
          
          <div class="space-y-6">
            <div class="flex items-center justify-between p-4 bg-stone-50 rounded-lg">
              <div>
                <h3 class="font-medium text-stone-900">System Maintenance</h3>
                <p class="text-sm text-stone-500">Run system maintenance tasks and cleanup</p>
              </div>
              <button
                @click="runMaintenance"
                class="bg-stone-600 hover:bg-stone-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                :disabled="maintenanceRunning"
              >
                <span v-if="maintenanceRunning">Running...</span>
                <span v-else>Run Maintenance</span>
              </button>
            </div>

            <div class="flex items-center justify-between p-4 bg-stone-50 rounded-lg">
              <div>
                <h3 class="font-medium text-stone-900">System Backup</h3>
                <p class="text-sm text-stone-500">Create a full system backup</p>
              </div>
              <button
                @click="createBackup"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                :disabled="backupRunning"
              >
                <span v-if="backupRunning">Creating...</span>
                <span v-else>Create Backup</span>
              </button>
            </div>

            <div class="flex items-center justify-between p-4 bg-stone-50 rounded-lg">
              <div>
                <h3 class="font-medium text-stone-900">Export System Data</h3>
                <p class="text-sm text-stone-500">Export all system data for analysis</p>
              </div>
              <button
                @click="exportSystemData"
                class="bg-stone-600 hover:bg-stone-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
              >
                Export Data
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- User Form Modal -->
    <UserFormModal
      v-if="showCreateUser || showEditUser"
      :user="selectedUser"
      @close="closeUserModal"
      @success="handleUserSuccess"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-if="showDeleteConfirm"
      title="Delete User"
      :message="`Are you sure you want to delete ${userToDelete?.name}? This action cannot be undone.`"
      confirmText="Delete User"
      confirmClass="bg-red-600 hover:bg-red-700"
      @close="showDeleteConfirm = false"
      @confirm="deleteUser"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMainStore } from '../stores/main'
import UserFormModal from './UserFormModal.vue'
import ConfirmModal from './ConfirmModal.vue'

const router = useRouter()
const store = useMainStore()

// States
const users = ref([])
const systemStats = ref({
  totalUsers: 0,
  totalRecordings: 0,
  totalSessions: 0,
  totalDuration: 0,
  totalStorageBytes: 0,
  recentUsers: 0,
  recentRecordings: 0,
  recentSessions: 0,
  averageRecordingsPerUser: 0,
  averageRecordingDuration: 0,
  averageFileSize: 0,
  systemStartDate: null
})

const loadingUsers = ref(false)
const maintenanceRunning = ref(false)
const backupRunning = ref(false)

// Modal states
const showCreateUser = ref(false)
const showEditUser = ref(false)
const showDeleteConfirm = ref(false)
const selectedUser = ref(null)
const userToDelete = ref(null)

// Computed
const isAdmin = computed(() => {
  return store.user?.role === 'admin'
})

// Methods
const handleLogout = async () => {
  try {
    await store.logout()
    router.push('/')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

const formatDuration = (seconds) => {
  if (!seconds) return '0 min'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
}

const loadSystemStats = async () => {
  try {
    const response = await window.axios.get('/api/system/stats')
    systemStats.value = response.data
  } catch (error) {
    console.error('Failed to load system stats:', error)
  }
}

const loadUsers = async () => {
  if (!isAdmin.value) return
  
  loadingUsers.value = true
  try {
    const response = await window.axios.get('/api/admin/users')
    users.value = response.data.users || []
  } catch (error) {
    console.error('Failed to load users:', error)
  } finally {
    loadingUsers.value = false
  }
}

const editUser = (user) => {
  selectedUser.value = user
  showEditUser.value = true
}

const confirmDeleteUser = (user) => {
  userToDelete.value = user
  showDeleteConfirm.value = true
}

const deleteUser = async () => {
  if (!userToDelete.value) return
  
  try {
    await window.axios.delete(`/api/admin/users/${userToDelete.value.id}`)
    users.value = users.value.filter(u => u.id !== userToDelete.value.id)
    showDeleteConfirm.value = false
    userToDelete.value = null
    // Reload stats
    await loadSystemStats()
  } catch (error) {
    console.error('Failed to delete user:', error)
  }
}

const closeUserModal = () => {
  showCreateUser.value = false
  showEditUser.value = false
  selectedUser.value = null
}

const handleUserSuccess = async () => {
  closeUserModal()
  await loadUsers()
  await loadSystemStats()
}

const runMaintenance = async () => {
  maintenanceRunning.value = true
  try {
    await window.axios.post('/api/admin/maintenance')
    // Show success message
  } catch (error) {
    console.error('Maintenance failed:', error)
  } finally {
    maintenanceRunning.value = false
  }
}

const createBackup = async () => {
  backupRunning.value = true
  try {
    const response = await window.axios.post('/api/admin/backup', {}, {
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'application/zip' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `system-backup-${new Date().toISOString().split('T')[0]}.zip`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Backup failed:', error)
  } finally {
    backupRunning.value = false
  }
}

const exportSystemData = async () => {
  try {
    const response = await window.axios.get('/api/admin/export', {
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `system-data-${new Date().toISOString().split('T')[0]}.json`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Export failed:', error)
  }
}

onMounted(async () => {
  await loadSystemStats()
  if (isAdmin.value) {
    await loadUsers()
  }
})
</script>
