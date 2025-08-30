<template>
  <div class="min-h-screen bg-stone-100">
    <!-- Header Navigation -->
    <LoggedInHeaderNav 
      :userName="store.user?.name" 
      :handleLogout="handleLogout" 
      :showHeaderToggle="false"
      @dropdown-toggled="onDropdownToggled" 
    />

    <PageHeader 
      :pageTitle="'User Account'"
    />

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Personal Stats -->
      <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
        <div class="px-6 py-8">
          <h2 class="text-xl font-semibold text-stone-900 mb-6">Your Activity</h2>
          
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-lg border border-blue-200">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-2 bg-blue-200 rounded-lg">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-blue-800">Total Recordings</p>
                  <p class="text-2xl font-bold text-blue-900">{{ userStats.totalRecordings }}</p>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 rounded-lg border border-green-200">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-2 bg-green-200 rounded-lg">
                  <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-green-800">Recording Sessions</p>
                  <p class="text-2xl font-bold text-green-900">{{ userStats.totalSessions }}</p>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-6 rounded-lg border border-purple-200">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-2 bg-purple-200 rounded-lg">
                  <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-purple-800">Total Duration</p>
                  <p class="text-2xl font-bold text-purple-900">{{ formatDuration(userStats.totalDuration) }}</p>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 p-6 rounded-lg border border-orange-200">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-2 bg-orange-200 rounded-lg">
                  <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-orange-800">Storage Used</p>
                  <p class="text-2xl font-bold text-orange-900">{{ formatFileSize(userStats.totalStorageBytes) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Account Card -->
      <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
        <div class="px-6 py-8">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-stone-900">Account Information</h2>
            <button
              @click="showEditAccount = true"
              class="bg-stone-600 hover:bg-stone-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
            >
              Edit Account
            </button>
          </div>
          
          <!-- Profile Details Grid -->
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-stone-50 p-4 rounded-lg">
              <label class="block text-sm font-medium text-stone-700 mb-1">Full Name</label>
              <p class="text-stone-900 font-medium">{{ store.user?.name || 'N/A' }}</p>
            </div>
            
            <div class="bg-stone-50 p-4 rounded-lg">
              <label class="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
              <p class="text-stone-900 font-medium">{{ store.user?.email || 'N/A' }}</p>
            </div>
            
            <div class="bg-stone-50 p-4 rounded-lg">
              <label class="block text-sm font-medium text-stone-700 mb-1">Member Since</label>
              <p class="text-stone-900 font-medium">{{ formatDate(store.user?.created_at) }}</p>
            </div>
            
            <div class="bg-stone-50 p-4 rounded-lg">
              <label class="block text-sm font-medium text-stone-700 mb-1">User ID</label>
              <p class="text-stone-900 font-medium">#{{ store.user?.id || 'N/A' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Account Settings -->
      <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-8">
          <h2 class="text-xl font-semibold text-stone-900 mb-6">Account Settings</h2>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-stone-50 rounded-lg">
              <div>
                <h3 class="font-medium text-stone-900">Change Password</h3>
                <p class="text-sm text-stone-500">Update your account password</p>
              </div>
              <button
                @click="showChangePassword = true"
                class="bg-stone-600 hover:bg-stone-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
              >
                Change Password
              </button>
            </div>

            <div class="flex items-center justify-between p-4 bg-stone-50 rounded-lg">
              <div>
                <h3 class="font-medium text-stone-900">Export Data</h3>
                <p class="text-sm text-stone-500">Download all your recordings and data</p>
              </div>
              <button
                @click="exportUserData"
                class="bg-stone-600 hover:bg-stone-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
              >
                Export Data
              </button>
            </div>

            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200">
              <div>
                <h3 class="font-medium text-red-900">Delete Account</h3>
                <p class="text-sm text-red-600">Permanently delete your account and all data</p>
              </div>
              <button
                @click="showDeleteAccount = true"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
              >
                Delete Account
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Edit Account Modal -->
    <EditAccountModal
      v-if="showEditAccount"
      @close="showEditAccount = false"
      @success="handleAccountUpdate"
    />

    <!-- Change Password Modal -->
    <ChangePasswordModal
      v-if="showChangePassword"
      @close="showChangePassword = false"
      @success="handlePasswordChange"
    />

    <!-- Delete Account Confirmation -->
    <ConfirmModal
      v-if="showDeleteAccount"
      title="Delete Account"
      message="Are you sure you want to permanently delete your account? This action cannot be undone and will remove all your recordings and data."
      confirmText="Delete Account"
      confirmClass="bg-red-600 hover:bg-red-700"
      @close="showDeleteAccount = false"
      @confirm="deleteAccount"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useMainStore } from '../stores/main'
import ChangePasswordModal from './ChangePasswordModal.vue'
import ConfirmModal from './ConfirmModal.vue'
import LoggedInHeaderNav from './LoggedInHeaderNav.vue'
import PageHeader from './PageHeader.vue'

const store = useMainStore()
const isDrawerExpanded = ref(false)

// Modal states
const showEditAccount = ref(false)
const showChangePassword = ref(false)
const showDeleteAccount = ref(false)

// User stats (mock data - replace with real API calls)
const userStats = ref({
  totalRecordings: 0,
  totalSessions: 0,
  totalDuration: 0 // in seconds
})

// Computed properties
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
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

const formatFileSize = (bytes) => {
  if (!bytes || bytes === 0) return '0 B'
  
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  const size = bytes / Math.pow(1024, i)
  
  return `${size.toFixed(i === 0 ? 0 : 1)} ${sizes[i]}`
}

// Methods
const handleLogout = async () => {
  try {
    await store.logout()
    router.push('/')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

const handleAccountUpdate = () => {
  showEditAccount.value = false
  // Optionally refresh user data
}

const handlePasswordChange = () => {
  showChangePassword.value = false
}

const exportUserData = async () => {
  try {
    // Call API to export user data
    const response = await window.axios.get('/api/user/export', {
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `user-data-${store.user.id}-${new Date().toISOString().split('T')[0]}.json`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Export failed:', error)
  }
}

const deleteAccount = async () => {
  try {
    await window.axios.delete('/api/user/account')
    await store.logout()
    router.push('/')
  } catch (error) {
    console.error('Account deletion failed:', error)
  }
  showDeleteAccount.value = false
}

const loadUserStats = async () => {
  try {
    const response = await window.axios.get('/api/user/stats')
    userStats.value = response.data
  } catch (error) {
    console.error('Failed to load user stats:', error)
  }
}

function onDropdownToggled(isOpen) {
  if (isOpen && isDrawerExpanded.value) {
    isDrawerExpanded.value = false;
  }
}

// If you have a drawer toggle, add logic to close dropdown when drawer opens
onMounted(() => {
  loadUserStats()
})
</script>
