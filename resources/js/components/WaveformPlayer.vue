<template>
  <div ref="container" :class="containerClass" />
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import WaveSurfer from 'wavesurfer.js'

const props = defineProps({
  url: {
    type: String,
    required: true,
  },
  autoplay: {
    type: Boolean,
    default: true,
  },
  variant: {
    type: String,
    default: 'inline',
    validator: (value) => ['inline', 'background'].includes(value),
  },
})

const emit = defineEmits(['finish', 'play', 'pause', 'ready'])

const container = ref(null)
let wavesurfer = null
let resizeObserver = null
let timeUpdateHandler = null

const isBackground = computed(() => props.variant === 'background')

const containerClass = computed(() =>
  isBackground.value
    ? 'absolute inset-0 h-full w-full pointer-events-auto'
    : 'w-full min-h-[64px]'
)

const getHeight = () => {
  if (!container.value) return 64
  return isBackground.value
    ? Math.max(container.value.clientHeight, 120)
    : 64
}

const createPlayer = () => {
  if (!container.value) return

  wavesurfer = WaveSurfer.create({
    container: container.value,
    waveColor: isBackground.value ? 'rgba(168, 162, 158, 0.28)' : '#d6d3d1',
    progressColor: isBackground.value ? 'rgba(87, 83, 78, 0.45)' : '#78716c',
    cursorColor: isBackground.value ? 'rgba(87, 83, 78, 0.55)' : '#57534e',
    barWidth: 2,
    barGap: 1,
    barRadius: 2,
    height: getHeight(),
    normalize: true,
    url: props.url,
  })

  wavesurfer.on('ready', () => {
    emit('ready')
    if (props.autoplay) {
      wavesurfer.play()
    }
  })
  wavesurfer.on('play', () => emit('play'))
  wavesurfer.on('pause', () => emit('pause'))
  wavesurfer.on('finish', () => emit('finish'))
}

const resizePlayer = () => {
  if (!wavesurfer || !container.value || !isBackground.value) return
  wavesurfer.setOptions({ height: getHeight() })
}

onMounted(() => {
  createPlayer()

  if (container.value?.parentElement && isBackground.value) {
    resizeObserver = new ResizeObserver(resizePlayer)
    resizeObserver.observe(container.value.parentElement)
  }
})

watch(
  () => props.url,
  (url) => {
    if (wavesurfer && url) {
      wavesurfer.load(url)
    }
  }
)

onUnmounted(() => {
  resizeObserver?.disconnect()
  if (wavesurfer && timeUpdateHandler) {
    wavesurfer.un('timeupdate', timeUpdateHandler)
  }
  wavesurfer?.destroy()
  wavesurfer = null
})

const play = () => wavesurfer?.play()
const pause = () => wavesurfer?.pause()
const toggle = () => wavesurfer?.playPause()
const isPlaying = () => wavesurfer?.isPlaying() ?? false
const getDuration = () => wavesurfer?.getDuration() ?? 0

const onTimeUpdate = (callback) => {
  if (!wavesurfer) return
  timeUpdateHandler = callback
  wavesurfer.on('timeupdate', timeUpdateHandler)
}

defineExpose({ play, pause, toggle, isPlaying, getDuration, onTimeUpdate })
</script>
