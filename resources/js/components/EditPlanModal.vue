<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[100000] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="absolute inset-0 bg-stone-900/50 backdrop-blur-[1px]"
          @click="closeModal"
        />
        <div
          class="modal-panel relative w-full max-w-lg rounded-xl bg-white shadow-xl border border-stone-200/80 p-6 max-h-[90vh] overflow-y-auto"
          @click.stop
        >
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-stone-900">Change Plan</h2>
            <button
              type="button"
              class="text-stone-400 hover:text-stone-600 transition-colors"
              @click="closeModal"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
            {{ error }}
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="mb-6">
              <label class="block text-sm font-medium text-stone-700 mb-2">Select a Plan</label>
              <div class="space-y-4">
                <div
                  v-for="plan in plans"
                  :key="plan.id"
                  class="border rounded-lg p-4 flex items-center justify-between transition-colors"
                  :class="selectedPlanId === plan.id ? 'border-blue-500 bg-blue-50' : 'border-stone-200'"
                >
                  <div>
                    <p class="font-bold text-lg text-stone-900">{{ plan.name.charAt(0).toUpperCase() + plan.name.slice(1) }}</p>
                    <p class="text-sm text-stone-700">{{ plan.description }}</p>
                    <span class="text-xs text-stone-500">Permission: {{ plan.permission?.name || 'free' }}</span>
                  </div>
                  <button
                    type="button"
                    class="ml-4 px-3 py-1 rounded-lg bg-blue-600 text-white text-sm font-medium transition-opacity"
                    :class="selectedPlanId === plan.id ? 'opacity-100' : 'opacity-70'"
                    @click="selectedPlanId = plan.id"
                  >
                    {{ selectedPlanId === plan.id ? 'Selected' : 'Choose' }}
                  </button>
                </div>
              </div>
            </div>
            <div class="flex justify-end gap-3">
              <button
                type="button"
                class="px-4 py-2 border border-stone-300 rounded-lg text-sm font-medium text-stone-700 hover:bg-stone-50 transition-colors"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading || !selectedPlanId"
                class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-50"
              >
                <span v-if="loading">Updating...</span>
                <span v-else>Update Plan</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useMainStore } from '../stores/main.js'

const props = defineProps({
  isOpen: Boolean,
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
    selectedPlanId.value = store.user?.plan?.id || plans.value.find((p) => p.name === 'free')?.id
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

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.isOpen) {
    closeModal()
  }
}

watch(() => props.isOpen, (open) => {
  if (open) {
    fetchPlans()
    error.value = ''
  }
})

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
  if (props.isOpen) fetchPlans()
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
})
</script>
