<template>
  <div ref="container" :class="containerClass" />
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue'
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
    : 'w-full min-h-[72px]'
)

const getHeight = () => {
  if (!container.value) return 72
  return isBackground.value
    ? Math.max(container.value.clientHeight, 120)
    : 72
}

const destroyPlayer = () => {
  resizeObserver?.disconnect()
  resizeObserver = null
  if (wavesurfer && timeUpdateHandler) {
    wavesurfer.un('timeupdate', timeUpdateHandler)
    timeUpdateHandler = null
  }
  wavesurfer?.destroy()
  wavesurfer = null
}

const createPlayer = () => {
  if (!container.value || !props.url) return

  destroyPlayer()

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
    fillParent: true,
    dragToSeek: true,
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

  const observeTarget = container.value.parentElement ?? container.value
  resizeObserver = new ResizeObserver(() => {
    if (!wavesurfer || !container.value) return
    wavesurfer.setOptions({ height: getHeight() })
  })
  resizeObserver.observe(observeTarget)
}

const initPlayer = async () => {
  await nextTick()
  requestAnimationFrame(() => createPlayer())
}

onMounted(() => {
  initPlayer()
})

watch(
  () => props.url,
  (url) => {
    if (!url) {
      destroyPlayer()
      return
    }
    if (wavesurfer) {
      wavesurfer.load(url)
    } else {
      initPlayer()
    }
  }
)

onUnmounted(() => {
  destroyPlayer()
})

const play = () => wavesurfer?.play()
const pause = () => wavesurfer?.pause()
const toggle = () => wavesurfer?.playPause()
const isPlaying = () => wavesurfer?.isPlaying() ?? false
const getDuration = () => wavesurfer?.getDuration() ?? 0
const getCurrentTime = () => wavesurfer?.getCurrentTime() ?? 0

const onTimeUpdate = (callback) => {
  if (!wavesurfer) return
  if (timeUpdateHandler) {
    wavesurfer.un('timeupdate', timeUpdateHandler)
  }
  timeUpdateHandler = callback
  wavesurfer.on('timeupdate', timeUpdateHandler)
}

defineExpose({ play, pause, toggle, isPlaying, getDuration, getCurrentTime, onTimeUpdate })
</script>
