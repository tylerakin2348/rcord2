<template>
  <div v-if="isOpen" class="fixed inset-0 flex items-center justify-center z-50" :style="{ background: 'rgba(0,0,0,0.5)' }">
    <div class="bg-white rounded-lg p-8 w-full max-w-md mx-4">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
          {{ isEditing ? 'Edit User' : 'Add New User' }}
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

        <div class="mb-4">
          <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
          <select v-model="form.role_id" id="role" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
          </select>
        </div>

        <div class="mb-6">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Password {{ isEditing ? '(leave blank to keep current)' : '' }}
          </label>
          <input
            v-model="form.password"
            type="password"
            id="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            :required="!isEditing"
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
            <span v-if="loading">Processing...</span>
            <span v-else>{{ isEditing ? 'Update User' : 'Create User' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'

const roles = ref([])

onMounted(async () => {
  try {
    const response = await window.axios.get('/api/roles')
    roles.value = response.data
  } catch (error) {
    // handle error
  }
})
// Escape key closes modal
const handleEscape = (e) => {
  if (e.key === 'Escape' && props.isOpen) {
    closeModal();
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})
onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
})
import { useMainStore } from '../stores/main.js'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const store = useMainStore()
const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  password: '',
  role_id: ''
})

const isEditing = computed(() => !!props.user)

const resetForm = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  error.value = ''
}

const populateForm = () => {
  if (props.user) {
    form.name = props.user.name || ''
    form.email = props.user.email || ''
    form.password = ''
    form.role_id = props.user.role_id || ''
  } else {
    resetForm()
  }
}

const closeModal = () => {
  resetForm()
  emit('close')
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    if (isEditing.value) {
      const updateData = {
        name: form.name,
        email: form.email
      }
      
      // Only include password if it's provided
      if (form.password) {
        updateData.password = form.password
      }
      
      await store.updateUser(props.user.id, updateData)
    } else {
      await store.createUser(form)
    }

    emit('success')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Watch for props changes to populate form
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    populateForm()
    store.clearError()
  }
})

watch(() => props.user, () => {
  if (props.isOpen) {
    populateForm()
  }
})

// Watch for store errors
watch(() => store.error, (newError) => {
  if (newError) {
    error.value = newError
  }
})
</script>
