<template>
  <div
    class="flex flex-col h-full transition-all duration-300"
    :class="[
      viewMode === 'grid'
        ? 'bg-white rounded-xl border p-4 min-h-[152px]'
        : 'bg-white rounded-xl border px-4 py-3',
      isExpanded
        ? 'border-stone-400 shadow-sm'
        : 'border-stone-200 hover:border-stone-300 hover:shadow-sm',
      isPlaying && !isExpanded ? 'ring-2 ring-stone-300 ring-offset-1' : '',
    ]"
  >
    <!-- Grid layout -->
    <div
      v-if="viewMode === 'grid'"
      class="flex flex-col h-full flex-1"
    >
      <button
        type="button"
        class="flex items-start gap-3 mb-3 text-left flex-1 min-h-0"
        @click="$emit('toggle-expand')"
      >
        <div class="p-2.5 rounded-lg bg-stone-100 shrink-0">
          <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <div class="font-medium text-stone-900 truncate">{{ title }}</div>
          <div class="text-sm text-stone-500 mt-0.5">{{ meta }}</div>
          <div v-if="subtitle" class="text-xs text-stone-400 mt-1 truncate">{{ subtitle }}</div>
        </div>
      </button>

      <div
        v-if="isExpanded && playbackUrl"
        class="mb-3 rounded-lg overflow-hidden bg-stone-50 border border-stone-100 p-2"
      >
        <WaveformPlayer
          :key="`${recording.id}-${playbackUrl}`"
          ref="player"
          :url="playbackUrl"
          :autoplay="false"
          variant="inline"
          @play="onWaveformPlay"
          @pause="onWaveformPause"
          @finish="onWaveformFinish"
        />
      </div>

      <div class="flex items-center gap-1 mt-auto pt-2 border-t border-stone-100 shrink-0">
        <button
          type="button"
          class="p-2 rounded-full text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors"
          :aria-label="isPlaying ? 'Pause' : 'Play'"
          @click.stop="handlePlayClick"
        >
          <svg v-if="!isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
          </svg>
        </button>
        <button
          type="button"
          class="p-2 rounded-full transition-colors"
          :class="isExpanded ? 'text-stone-800 bg-stone-100' : 'text-stone-500 hover:text-stone-800 hover:bg-stone-100'"
          aria-label="Expand waveform"
          @click.stop="$emit('toggle-expand')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
          </svg>
        </button>
        <button
          type="button"
          class="p-2 rounded-full text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors"
          aria-label="Open full player"
          @click.stop="$emit('open-full')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
        </button>
        <DownloadFormatMenu
          button-class="text-stone-400 hover:text-stone-800 hover:bg-stone-100"
          @download="(format) => $emit('download', format)"
        />
        <button
          type="button"
          class="p-2 rounded-full text-stone-400 hover:text-red-600 hover:bg-red-50 transition-colors"
          aria-label="Delete"
          @click.stop="$emit('delete')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- List layout -->
    <div v-else class="flex flex-col gap-3">
      <div class="flex items-center gap-3">
        <button
          type="button"
          class="p-2 rounded-lg bg-stone-100 shrink-0"
          @click="$emit('toggle-expand')"
        >
          <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
          </svg>
        </button>
        <button
          type="button"
          class="min-w-0 flex-1 text-left"
          @click="$emit('toggle-expand')"
        >
          <div class="font-medium text-stone-900 truncate">{{ title }}</div>
          <div class="text-sm text-stone-500">{{ meta }}</div>
        </button>
        <div class="flex items-center gap-1 shrink-0">
          <button
            type="button"
            class="p-2 rounded-full text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors"
            @click.stop="handlePlayClick"
          >
            <svg v-if="!isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 5v14l11-7z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
            </svg>
          </button>
          <DownloadFormatMenu
            button-class="text-stone-400 hover:text-stone-800 hover:bg-stone-100"
            @download="(format) => $emit('download', format)"
          />
          <button
            type="button"
            class="p-2 rounded-full text-stone-400 hover:text-red-600 hover:bg-red-50 transition-colors"
            @click.stop="$emit('delete')"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
      <div
        v-if="isExpanded && playbackUrl"
        class="rounded-lg overflow-hidden bg-stone-50 border border-stone-100 p-2"
      >
        <WaveformPlayer
          :key="`${recording.id}-${playbackUrl}`"
          ref="player"
          :url="playbackUrl"
          :autoplay="false"
          variant="inline"
          @play="onWaveformPlay"
          @pause="onWaveformPause"
          @finish="onWaveformFinish"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import WaveformPlayer from './WaveformPlayer.vue'
import DownloadFormatMenu from './DownloadFormatMenu.vue'

const props = defineProps({
  recording: { type: Object, required: true },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  viewMode: { type: String, default: 'grid' },
  isPlaying: { type: Boolean, default: false },
  isExpanded: { type: Boolean, default: false },
  playbackUrl: { type: String, default: '' },
})

const emit = defineEmits([
  'toggle-play',
  'toggle-expand',
  'open-full',
  'download',
  'delete',
  'waveform-play',
  'waveform-pause',
  'waveform-finish',
])

const player = ref(null)

const meta = computed(() =>
  [props.recording.formatted_duration || props.recording.duration,
   props.recording.formatted_file_size || props.recording.size]
    .filter(Boolean)
    .join(' · ')
)

const handlePlayClick = () => {
  if (props.isExpanded && props.playbackUrl && player.value) {
    player.value.toggle()
    return
  }
  emit('toggle-play')
}

const onWaveformPlay = () => emit('waveform-play')
const onWaveformPause = () => emit('waveform-pause')
const onWaveformFinish = () => emit('waveform-finish')
</script>
