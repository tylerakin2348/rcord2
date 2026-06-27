<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[100000] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
      >
        <div
          class="absolute inset-0 bg-stone-900/50 backdrop-blur-[1px]"
          @click="$emit('close')"
        />
        <div
          class="modal-panel relative w-full max-w-md rounded-xl bg-white shadow-xl border border-stone-200/80 p-6"
          @click.stop
        >
          <div class="flex items-start gap-3 mb-4">
            <div class="p-2 bg-red-50 rounded-full shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h3 :id="titleId" class="text-lg font-semibold text-stone-900 pt-0.5">{{ title }}</h3>
          </div>

          <p class="text-sm text-stone-600 mb-6">{{ message }}</p>

          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors"
              @click="$emit('close')"
            >
              {{ cancelText }}
            </button>
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
              :class="confirmClass"
              @click="$emit('confirm')"
            >
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Confirm Action',
  },
  message: {
    type: String,
    default: 'Are you sure you want to perform this action?',
  },
  confirmText: {
    type: String,
    default: 'Confirm',
  },
  cancelText: {
    type: String,
    default: 'Cancel',
  },
  confirmClass: {
    type: String,
    default: 'bg-red-600 hover:bg-red-700',
  },
  titleId: {
    type: String,
    default: 'confirm-modal-title',
  },
})

const emit = defineEmits(['close', 'confirm'])

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
</script>
