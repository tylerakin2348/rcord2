<template>
  <Teleport to="body">
    <div class="fixed bottom-4 right-4 z-[100001] flex flex-col gap-2 w-full max-w-sm px-4 sm:px-0 pointer-events-none">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toastStore.items"
          :key="toast.id"
          class="pointer-events-auto rounded-xl border shadow-lg px-4 py-3 flex items-start gap-3"
          :class="toastClasses(toast.type)"
        >
          <div class="pt-0.5 shrink-0">
            <svg
              v-if="toast.type === 'pending'"
              class="w-4 h-4 animate-spin"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            <svg
              v-else-if="toast.type === 'ready'"
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg
              v-else
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z" />
            </svg>
          </div>

          <div class="min-w-0 flex-1">
            <p class="text-sm leading-snug">{{ toast.message }}</p>
            <button
              v-if="toast.actionLabel && toast.onAction"
              type="button"
              class="mt-2 text-sm font-medium underline underline-offset-2 hover:opacity-80 transition-opacity"
              @click="handleAction(toast)"
            >
              {{ toast.actionLabel }}
            </button>
          </div>

          <button
            type="button"
            class="shrink-0 p-1 rounded-md hover:bg-black/5 transition-colors"
            aria-label="Dismiss notification"
            @click="toastStore.dismiss(toast.id)"
          >
            <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToastStore } from '../stores/toasts';

const toastStore = useToastStore();

const toastClasses = (type) => {
    switch (type) {
    case 'ready':
        return 'bg-white border-emerald-200 text-stone-800';
    case 'error':
        return 'bg-white border-red-200 text-stone-800';
    default:
        return 'bg-white border-stone-200 text-stone-700';
    }
};

const handleAction = async (toast) => {
    if (!toast.onAction) return;

    try {
        await toast.onAction();
    } catch (error) {
        console.error('Toast action failed:', error);
        toastStore.error('Download failed. Please try again.');
    }
};
</script>

<style scoped>
.toast-move,
.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(12px) scale(0.98);
}

.toast-leave-active {
    position: absolute;
    right: 0;
    left: 0;
}
</style>
