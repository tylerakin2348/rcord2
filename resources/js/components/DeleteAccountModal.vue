<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5)">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-xl font-semibold text-red-700 mb-4">Delete Account</h2>
      <p class="mb-6 text-red-600">Are you sure you want to permanently delete your account? This action cannot be undone.</p>
      <div class="flex justify-end space-x-2">
        <button @click="$emit('close')" class="px-4 py-2 rounded bg-stone-200 text-stone-700 hover:bg-stone-300">Cancel</button>
        <button @click="deleteAccount" :disabled="loading" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 font-medium">
          <span v-if="loading">Deleting...</span>
          <span v-else>Delete</span>
        </button>
      </div>
      <p v-if="error" class="mt-4 text-red-500 text-sm">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  visible: Boolean
})
const emit = defineEmits(['close', 'success'])
const loading = ref(false)
const error = ref('')

watch(() => props.visible, (val) => {
  if (!val) error.value = ''
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
