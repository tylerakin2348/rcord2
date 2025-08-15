<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 w-full max-w-md mx-4">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
          {{ isRegistering ? 'Create Account' : 'Sign In' }}
        </h2>
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
        <div v-if="isRegistering" class="mb-4">
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

        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <input
            v-model="form.password"
            type="password"
            id="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            required
          />
        </div>

        <div v-if="isRegistering" class="mb-6">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            id="password_confirmation"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            required
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading">Processing...</span>
          <span v-else>{{ isRegistering ? 'Create Account' : 'Sign In' }}</span>
        </button>
      </form>

      <div class="mt-6 text-center">
        <button
          @click="toggleMode"
          class="text-blue-600 hover:text-blue-800 font-medium"
        >
          {{ isRegistering ? 'Already have an account? Sign in' : "Don't have an account? Sign up" }}
        </button>
      </div>
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
const isRegistering = ref(false)
const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const resetForm = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
  error.value = ''
}

const closeModal = () => {
  resetForm()
  emit('close')
}

const toggleMode = () => {
  isRegistering.value = !isRegistering.value
  resetForm()
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    if (isRegistering.value) {
      if (form.password !== form.password_confirmation) {
        throw new Error('Passwords do not match')
      }
      await store.register(form)
    } else {
      await store.login({
        email: form.email,
        password: form.password
      })
    }

    emit('success')
    closeModal()
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Watch for store errors
watch(() => store.error, (newError) => {
  if (newError) {
    error.value = newError
  }
})

// Clear store error when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    store.clearError()
  }
})
</script>
