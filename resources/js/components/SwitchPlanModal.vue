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
          class="modal-panel relative w-full max-w-md rounded-xl bg-white shadow-xl border border-stone-200/80 p-6"
          @click.stop
        >
          <div class="flex justify-between items-center mb-6">
            <div>
              <p class="text-sm text-stone-500">Switch To The</p>
              <h2 class="text-2xl font-bold text-stone-900">
                {{ plan?.name ? plan.name.charAt(0).toUpperCase() + plan.name.slice(1) : '' }} Plan
              </h2>
            </div>
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
          <div v-if="plan" class="mb-6">
            <p class="text-sm text-stone-700 mb-2">{{ plan.description }}</p>
            <ul class="list-disc ml-4 text-sm text-stone-600 mb-2">
              <li v-if="plan.name === 'full'">All features unlocked</li>
              <li v-else-if="plan.name === 'base'">Standard features</li>
              <li v-else>Limited features</li>
            </ul>
          </div>
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
            {{ error }}
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
              type="button"
              class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-50"
              :disabled="loading"
              @click="switchPlan"
            >
              <span v-if="loading">Switching...</span>
              <span v-else>Confirm Switch</span>
            </button>
          </div>
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
  plan: Object,
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

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.isOpen) {
    closeModal()
  }
}

watch(() => props.isOpen, (open) => {
  if (!open) error.value = ''
})

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
})
</script>
