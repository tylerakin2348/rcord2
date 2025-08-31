<template>
  <div class="min-h-screen bg-stone-100">
    <LoggedInHeaderNav :userName="store.user?.name" :showHeaderToggle="false" />
    <PageHeader :pageTitle="'User Management'" :route="'/system-info'" />
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-stone-900">All Users</h3>
          <p class="text-sm text-stone-500">Manage users, roles, and activity</p>
        </div>
        <div v-if="loading" class="text-center py-8">
          <span class="text-stone-500">Loading users...</span>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">#{{ user.id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'">
                    {{ user.role || 'user' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button @click="editUser(user)" class="text-stone-600 hover:text-stone-900 transition-colors duration-200">Edit</button>
                  <button @click="confirmDeleteUser(user)" class="text-red-600 hover:text-red-900 transition-colors duration-200">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <UserFormModal v-if="showEditUser" :user="selectedUser" @close="closeUserModal" @success="handleUserSuccess" />
      <ConfirmModal v-if="showDeleteConfirm" title="Delete User" :message="`Are you sure you want to delete ${userToDelete?.name}? This action cannot be undone.`" confirmText="Delete User" confirmClass="bg-red-600 hover:bg-red-700" @close="showDeleteConfirm = false" @confirm="deleteUser" />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useMainStore } from '../stores/main'
import UserFormModal from './UserFormModal.vue'
import ConfirmModal from './ConfirmModal.vue'
import LoggedInHeaderNav from './LoggedInHeaderNav.vue'
import PageHeader from './PageHeader.vue'

const store = useMainStore()
const users = ref([])
const loading = ref(false)
const showEditUser = ref(false)
const showDeleteConfirm = ref(false)
const selectedUser = ref(null)
const userToDelete = ref(null)

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const loadUsers = async () => {
  loading.value = true
  try {
    const response = await window.axios.get('/api/admin/users')
    users.value = response.data.users || []
  } catch (error) {
    console.error('Failed to load users:', error)
  } finally {
    loading.value = false
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
  } catch (error) {
    console.error('Failed to delete user:', error)
  }
}

const closeUserModal = () => {
  showEditUser.value = false
  selectedUser.value = null
}

const handleUserSuccess = async () => {
  closeUserModal()
  await loadUsers()
}

onMounted(() => {
  loadUsers()
})
</script>
