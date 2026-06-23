<template>
  <div v-if="isOpen" class="fixed inset-0 flex items-center justify-center z-50" style="background: rgba(0,0,0,0.5);">
    <div class="bg-white rounded-lg p-8 w-full max-w-md mx-4">
      <div class="flex justify-between items-center mb-6">
        <div class="d-flex">
          <h1 class="text-sm">Switch To The</h1>
          <h2 class="text-2xl font-bold text-gray-900">{{ plan.name.charAt(0).toUpperCase() + plan.name.slice(1) }} Plan</h2>
        </div>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <div v-if="plan" class="mb-6">
        <p class="text-sm text-gray-700 mb-2">{{ plan.description }}</p>
        <ul class="list-disc ml-4 text-sm mb-2">
          <li v-if="plan.name === 'full'">All features unlocked</li>
          <li v-else-if="plan.name === 'base'">Standard features</li>
          <li v-else>Limited features</li>
        </ul>
      </div>
      <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ error }}</div>
      <div class="flex justify-end space-x-3">
        <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="button" @click="switchPlan" :disabled="loading" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">
          <span v-if="loading">Switching...</span>
          <span v-else>Confirm Switch</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useMainStore } from '../stores/main.js'

const props = defineProps({
  isOpen: Boolean,
  plan: Object
})
const emit = defineEmits(['close', 'success'])
const store = useMainStore()
const loading = ref(false)
const error = ref('')

const closeModal = () => {
  emit('close')
  error.value = ''
}

const switchPlan = async () => {
  if (!props.plan?.id) return
  loading.value = true
  error.value = ''
  try {
    await window.axios.post('/api/user/plan', { plan_id: props.plan.id })
    await store.fetchUser()
    emit('success')
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to switch plan.'
  } finally {
    loading.value = false
  }
}

watch(() => props.isOpen, (isOpen) => {
  if (!isOpen) error.value = ''
})
</script>
