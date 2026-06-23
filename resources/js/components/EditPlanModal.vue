<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 w-full max-w-lg mx-4">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Change Plan</h2>
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
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Select a Plan</label>
          <div class="space-y-4">
            <div v-for="plan in plans" :key="plan.id" class="border rounded-lg p-4 flex items-center justify-between" :class="selectedPlanId === plan.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
              <div>
                <p class="font-bold text-lg text-gray-900">{{ plan.name.charAt(0).toUpperCase() + plan.name.slice(1) }}</p>
                <p class="text-sm text-gray-700">{{ plan.description }}</p>
                <span class="text-xs text-gray-500">Permission: {{ plan.permission?.name || 'free' }}</span>
              </div>
              <button type="button" @click="selectedPlanId = plan.id" class="ml-4 px-3 py-1 rounded bg-blue-600 text-white text-sm font-medium" :class="selectedPlanId === plan.id ? 'opacity-100' : 'opacity-70'">
                {{ selectedPlanId === plan.id ? 'Selected' : 'Choose' }}
              </button>
            </div>
          </div>
        </div>
        <div class="flex justify-end space-x-3">
          <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="loading || !selectedPlanId" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">
            <span v-if="loading">Updating...</span>
            <span v-else>Update Plan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useMainStore } from '../stores/main.js'

const props = defineProps({
  isOpen: Boolean
})
const emit = defineEmits(['close', 'success'])
const store = useMainStore()
const loading = ref(false)
const error = ref('')
const plans = ref([])
const selectedPlanId = ref(null)

const closeModal = () => {
  emit('close')
}

const fetchPlans = async () => {
  try {
    const response = await window.axios.get('/api/plans')
    plans.value = response.data
    // Default to current plan
    selectedPlanId.value = store.user?.plan?.id || plans.value.find(p => p.name === 'free')?.id
  } catch (err) {
    error.value = 'Failed to load plans.'
  }
}

const handleSubmit = async () => {
  if (!selectedPlanId.value) return
  loading.value = true
  error.value = ''
  try {
    await window.axios.post('/api/user/plan', { plan_id: selectedPlanId.value })
    await store.fetchUser()
    emit('success')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update plan.'
  } finally {
    loading.value = false
  }
}

watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    fetchPlans()
    error.value = ''
  }
})

onMounted(() => {
  if (props.isOpen) fetchPlans()
})
</script>
