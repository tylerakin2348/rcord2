<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="visible"
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
          <h2 class="text-xl font-semibold text-red-700 mb-4">Delete Account</h2>
          <p class="mb-6 text-red-600">
            Are you sure you want to permanently delete your account? This action cannot be undone.
          </p>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              class="px-4 py-2 rounded-lg bg-stone-200 text-stone-700 hover:bg-stone-300 transition-colors"
              @click="$emit('close')"
            >
              Cancel
            </button>
            <button
              type="button"
              class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-medium transition-colors disabled:opacity-50"
              :disabled="loading"
              @click="deleteAccount"
            >
              <span v-if="loading">Deleting...</span>
              <span v-else>Delete</span>
            </button>
          </div>
          <p v-if="error" class="mt-4 text-red-500 text-sm">{{ error }}</p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  visible: Boolean,
})

const emit = defineEmits(['close', 'success'])
const loading = ref(false)
const error = ref('')

watch(() => props.visible, (val) => {
  if (!val) error.value = ''
})

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.visible) {
    emit('close')
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
})

async function deleteAccount() {
  loading.value = true
  error.value = ''
  try {
    await axios.delete('/api/user')
    emit('success')
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to delete account.'
  } finally {
    loading.value = false
  }
}
</script>
