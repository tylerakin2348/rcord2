<template>
  <div class="flex flex-col h-full min-h-0">
    <div class="flex-1 relative overflow-hidden min-h-0 bg-stone-100">
      <WaveformPlayer
        ref="player"
        variant="background"
        :url="url"
        :autoplay="autoplay"
        @play="onPlay"
        @pause="onPause"
        @finish="onFinish"
        @ready="onReady"
      />

      <div class="relative z-10 flex flex-col items-center justify-center h-full px-8 pointer-events-none">
        <div class="text-center mb-10 max-w-lg pointer-events-none">
          <h2 class="text-2xl font-semibold text-stone-800 mb-2">
            {{ title }}
          </h2>
          <p v-if="subtitle" class="text-sm text-stone-500">
            {{ subtitle }}
          </p>
        </div>

        <button
          type="button"
          class="pointer-events-auto rounded-full flex items-center justify-center w-20 h-20 bg-white/90 shadow-lg border border-stone-200 hover:bg-white transition-colors duration-200 focus:outline-none focus:ring-4 focus:ring-stone-200"
          :aria-label="isPlaying ? 'Pause' : 'Play'"
          @click="toggle"
        >
          <svg
            v-if="!isPlaying"
            class="w-9 h-9 text-stone-700 ml-1"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M8 5v14l11-7z" />
          </svg>
          <svg
            v-else
            class="w-9 h-9 text-stone-700"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
          </svg>
        </button>

        <button
          type="button"
          class="pointer-events-auto mt-8 text-sm font-medium text-stone-500 hover:text-stone-800 transition-colors duration-200"
          @click="$emit('close')"
        >
          Back to recording
        </button>
      </div>
    </div>

    <div class="relative z-10 p-4 border-t border-stone-200 bg-stone-50 shrink-0">
      <div class="text-center text-3xl font-mono font-bold text-stone-800">
        {{ displayTime }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import WaveformPlayer from './WaveformPlayer.vue'

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  url: {
    type: String,
    required: true,
  },
  autoplay: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['close', 'play', 'pause', 'finish'])

const player = ref(null)
const isPlaying = ref(false)
const currentTime = ref(0)
const duration = ref(0)

const displayTime = computed(() => formatTime(currentTime.value))

const formatTime = (seconds) => {
  const total = Math.max(0, Math.floor(seconds || 0))
  const mins = Math.floor(total / 60)
  const secs = total % 60
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const onReady = () => {
  duration.value = player.value?.getDuration() ?? 0
  player.value?.onTimeUpdate((time) => {
    currentTime.value = time
  })
}

const onPlay = () => {
  isPlaying.value = true
  emit('play')
}

const onPause = () => {
  isPlaying.value = false
  emit('pause')
}

const onFinish = () => {
  isPlaying.value = false
  currentTime.value = duration.value
  emit('finish')
}

const toggle = () => {
  player.value?.toggle()
  isPlaying.value = player.value?.isPlaying() ?? false
}

defineExpose({
  toggle,
  stop: () => {
    player.value?.pause()
    isPlaying.value = false
  },
})
</script>
