<template>
  <div v-if="isOpen" class="fixed inset-0 flex items-center justify-center z-50" style="background:rgba(0,0,0,0.5)">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-xl font-semibold mb-4">Edit Email</h2>
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
          <input v-model="email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-stone-500 focus:border-transparent" required />
        </div>
        <div class="flex justify-end space-x-2">
          <button type="button" @click="$emit('close')" class="px-4 py-2 rounded bg-stone-200 text-stone-700 hover:bg-stone-300">Cancel</button>
          <button type="submit" :disabled="loading" class="px-4 py-2 rounded bg-stone-600 text-white hover:bg-stone-700 font-medium">
            <span v-if="loading">Saving...</span>
            <span v-else>Save</span>
          </button>
        </div>
        <p v-if="error" class="mt-4 text-red-500 text-sm">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { updateUser } from '../api/user'
const props = defineProps({ isOpen: Boolean })
const emit = defineEmits(['close', 'success'])
const email = ref('')
const loading = ref(false)
const error = ref('')
import { useMainStore } from '../stores/main'
const store = useMainStore()
watch(() => props.isOpen, (open) => { if (open) { email.value = store.user?.email || ''; error.value = '' } })
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
