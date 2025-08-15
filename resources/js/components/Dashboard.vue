<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">Welcome, {{ store.user.name }}!</span>
            <button
              @click="handleLogout"
              class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- User Profile Card -->
      <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Your Profile</h3>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <p class="mt-1 text-sm text-gray-900">{{ store.user.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <p class="mt-1 text-sm text-gray-900">{{ store.user.email }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Member Since</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(store.user.created_at) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">User ID</label>
              <p class="mt-1 text-sm text-gray-900">#{{ store.user.id }}</p>
            </div>
          </div>
          <div class="mt-4">
            <button
              @click="showEditProfile = true"
              class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              Edit Profile
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="text-2xl">👥</div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ store.users.length }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="text-2xl">📊</div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Active Projects</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ store.stats.projects }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="text-2xl">⚡</div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">System Uptime</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ store.stats.uptime }}%</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Management -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
          <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">User Management</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage all users in the system</p>
          </div>
          <button
            @click="showCreateUser = true"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
          >
            Add New User
          </button>
        </div>

        <div v-if="store.isLoading" class="px-4 py-5 text-center">
          <div class="text-gray-500">Loading users...</div>
        </div>

        <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="user in store.users" :key="user.id" class="px-6 py-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">{{ user.name.charAt(0).toUpperCase() }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    @click="editUser(user)"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDeleteUser(user)"
                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                    :disabled="user.id === store.user.id"
                    :class="{ 'opacity-50 cursor-not-allowed': user.id === store.user.id }"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </main>

    <!-- User Form Modal -->
    <UserFormModal
      :isOpen="showCreateUser || showEditUser"
      :user="selectedUser"
      @close="closeUserModal"
      @success="handleUserSuccess"
    />

    <!-- Edit Profile Modal -->
    <EditProfileModal
      :isOpen="showEditProfile"
      @close="showEditProfile = false"
      @success="handleProfileUpdate"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :isOpen="showDeleteConfirm"
      :title="`Delete User`"
      :message="`Are you sure you want to delete ${userToDelete?.name}? This action cannot be undone.`"
      @close="showDeleteConfirm = false"
      @confirm="deleteUser"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useMainStore } from '../stores/main.js'
import UserFormModal from './UserFormModal.vue'
import EditProfileModal from './EditProfileModal.vue'
import ConfirmModal from './ConfirmModal.vue'

const store = useMainStore()

const showCreateUser = ref(false)
const showEditUser = ref(false)
const showEditProfile = ref(false)
const showDeleteConfirm = ref(false)
const selectedUser = ref(null)
const userToDelete = ref(null)

const handleLogout = async () => {
  await store.logout()
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
  if (userToDelete.value) {
    await store.deleteUser(userToDelete.value.id)
    showDeleteConfirm.value = false
    userToDelete.value = null
  }
}

const closeUserModal = () => {
  showCreateUser.value = false
  showEditUser.value = false
  selectedUser.value = null
}

const handleUserSuccess = () => {
  closeUserModal()
}

const handleProfileUpdate = () => {
  showEditProfile.value = false
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

onMounted(async () => {
  if (store.users.length === 0) {
    await store.fetchUsers()
  }
})
</script>
