<template>
  <div v-if="isOpen" class="fixed inset-0 flex items-center justify-center z-50" style="background:rgba(0,0,0,0.5)">
    <div class="bg-white rounded-lg p-8 w-full max-w-md mx-4">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Edit Account</h2>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ error }}
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
          <input
            v-model="form.name"
            type="text"
            id="name"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            required
          />
        </div>

        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
          <input
            v-model="form.email"
            type="email"
            id="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            required
          />
        </div>

        <div class="mb-6">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            New Password (leave blank to keep current)
          </label>
          <input
            v-model="form.password"
            type="password"
            id="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading">Updating...</span>
            <span v-else>Update Account</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useMainStore } from '../stores/main.js'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'success'])

const store = useMainStore()
const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  password: ''
})

const resetForm = () => {
  form.name = store.user?.name || ''
  form.email = store.user?.email || ''
  form.password = ''
  error.value = ''
}

const closeModal = () => {
  emit('close')
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    const updateData = {
      name: form.name,
      email: form.email
    }
    
    // Only include password if it's provided
    if (form.password) {
      updateData.password = form.password
    }
    
    await store.updateUser(store.user.id, updateData)
    
    // Update the user in the store after successful update
    await store.fetchUser()
    
    emit('success')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Watch for modal opening to populate form
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    resetForm()
    store.clearError()
  }
})

// Watch for store errors
watch(() => store.error, (newError) => {
  if (newError) {
    error.value = newError
  }
})
</script>
