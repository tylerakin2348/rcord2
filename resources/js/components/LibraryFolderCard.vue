<template>
  <div
    class="group flex flex-col h-full cursor-pointer transition-all duration-200"
    :class="viewMode === 'grid'
      ? 'bg-white rounded-xl border border-stone-200 hover:border-amber-200 hover:shadow-sm p-4 min-h-[152px]'
      : 'bg-white rounded-xl border border-stone-200 hover:border-amber-200 px-4 py-3'"
    @click="$emit('open')"
  >
    <div
      v-if="viewMode === 'grid'"
      class="flex flex-col h-full flex-1"
    >
      <div class="flex items-start gap-3 mb-3 flex-1">
        <div class="p-2.5 rounded-lg bg-amber-50 shrink-0">
          <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <div class="font-medium text-stone-900 truncate">{{ displayTitle }}</div>
          <div class="text-sm text-stone-500 mt-0.5">{{ meta }}</div>
          <div v-if="session.description" class="text-xs text-stone-400 mt-1 truncate">
            {{ session.description }}
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between mt-auto pt-2 border-t border-stone-100 shrink-0">
        <span class="text-xs text-amber-700 font-medium">Open folder →</span>
        <button
          type="button"
          class="p-2 rounded-full text-stone-400 hover:text-red-600 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100"
          aria-label="Delete session"
          @click.stop="$emit('delete')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>

    <div v-else class="flex items-center gap-2">
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <div class="p-2.5 rounded-lg bg-amber-50 shrink-0">
          <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <div class="font-medium text-stone-900 truncate">{{ displayTitle }}</div>
          <div class="text-sm text-stone-500">{{ meta }}</div>
        </div>
        <svg class="w-5 h-5 text-stone-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </div>
      <button
        type="button"
        class="p-2 rounded-full text-stone-400 hover:text-red-600 hover:bg-red-50 transition-colors shrink-0"
        aria-label="Delete session"
        @click.stop="$emit('delete')"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { displaySessionTitle } from '../utils/libraryDates.js'

const props = defineProps({
  session: { type: Object, required: true },
  viewMode: { type: String, default: 'grid' },
  recordedAt: { type: String, default: '' },
})

defineEmits(['open', 'delete'])

const displayTitle = computed(() => displaySessionTitle(props.session))

const meta = computed(() =>
  [props.recordedAt,
   props.session.formatted_session_duration,
   `${props.session.total_loops || props.session.recordings_count} loops`,
   `${props.session.recordings_count} recordings`]
    .filter(Boolean)
    .join(' · ')
)
</script>
