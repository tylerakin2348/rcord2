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
          @click="$emit('close')"
        />
        <div
          class="modal-panel relative w-full max-w-md rounded-xl bg-white shadow-xl border border-stone-200/80 p-6"
          @click.stop
        >
          <h2 class="text-xl font-semibold text-stone-900 mb-4">Edit Email</h2>
          <form @submit.prevent="submit">
            <div class="mb-4">
              <label class="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
              <input
                v-model="email"
                type="email"
                class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-stone-500 focus:border-transparent"
                required
              />
            </div>
            <div class="flex justify-end gap-2">
              <button
                type="button"
                class="px-4 py-2 rounded-lg bg-stone-200 text-stone-700 hover:bg-stone-300 transition-colors"
                @click="$emit('close')"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 rounded-lg bg-stone-600 text-white hover:bg-stone-700 font-medium transition-colors disabled:opacity-50"
              >
                <span v-if="loading">Saving...</span>
                <span v-else>Save</span>
              </button>
            </div>
            <p v-if="error" class="mt-4 text-red-500 text-sm">{{ error }}</p>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { updateUser } from '../api/user'
import { useMainStore } from '../stores/main'

const props = defineProps({ isOpen: Boolean })
const emit = defineEmits(['close', 'success'])
const email = ref('')
const loading = ref(false)
const error = ref('')
const store = useMainStore()

watch(() => props.isOpen, (open) => {
  if (open) {
    email.value = store.user?.email || ''
    error.value = ''
  }
})

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.isOpen) {
    emit('close')
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
})

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await updateUser({ email: email.value })
    emit('success')
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to update email.'
  } finally {
    loading.value = false
  }
}
</script>
