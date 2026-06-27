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
          class="modal-panel relative w-full max-w-md rounded-xl bg-white shadow-xl border border-stone-200/80 overflow-hidden"
          @click.stop
        >
          <div class="px-6 py-4 border-b border-stone-200">
            <h3 class="text-lg font-semibold text-stone-900">Change Password</h3>
          </div>

          <form @submit.prevent="handleSubmit" class="px-6 py-4">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Current Password</label>
                <input
                  v-model="form.currentPassword"
                  type="password"
                  class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-stone-500 focus:border-transparent"
                  required
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">New Password</label>
                <input
                  v-model="form.newPassword"
                  type="password"
                  class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-stone-500 focus:border-transparent"
                  required
                  minlength="8"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Confirm New Password</label>
                <input
                  v-model="form.confirmPassword"
                  type="password"
                  class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-stone-500 focus:border-transparent"
                  required
                >
                <p
                  v-if="form.newPassword && form.confirmPassword && form.newPassword !== form.confirmPassword"
                  class="text-red-500 text-sm mt-1"
                >
                  Passwords do not match
                </p>
              </div>
            </div>

            <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-red-700 text-sm">{{ error }}</p>
            </div>

            <div class="flex justify-end gap-3 mt-6">
              <button
                type="button"
                class="px-4 py-2 text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors"
                @click="$emit('close')"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="!canSubmit || loading"
                class="px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white rounded-lg transition-colors disabled:opacity-50"
              >
                {{ loading ? 'Changing...' : 'Change Password' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  isOpen: Boolean,
})

const emit = defineEmits(['close', 'success'])

const form = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const loading = ref(false)
const error = ref('')

const canSubmit = computed(() => {
  return form.value.currentPassword
    && form.value.newPassword
    && form.value.confirmPassword
    && form.value.newPassword === form.value.confirmPassword
    && form.value.newPassword.length >= 8
})

watch(() => props.isOpen, (open) => {
  if (open) {
    form.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
    }
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

const handleSubmit = async () => {
  if (!canSubmit.value) return

  loading.value = true
  error.value = ''

  try {
    await window.axios.post('/api/user/change-password', {
      current_password: form.value.currentPassword,
      new_password: form.value.newPassword,
      new_password_confirmation: form.value.confirmPassword,
    })

    emit('success')
  } catch (err) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Failed to change password. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
