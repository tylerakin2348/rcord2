<template>
  <div ref="container" class="w-full min-h-[64px]" />
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
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
})

const emit = defineEmits(['finish', 'play', 'pause', 'ready'])

const container = ref(null)
let wavesurfer = null

const createPlayer = () => {
  if (!container.value) return

  wavesurfer = WaveSurfer.create({
    container: container.value,
    waveColor: '#d6d3d1',
    progressColor: '#78716c',
    cursorColor: '#57534e',
    barWidth: 2,
    barGap: 1,
    barRadius: 2,
    height: 64,
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

onMounted(createPlayer)

watch(
  () => props.url,
  (url) => {
    if (wavesurfer && url) {
      wavesurfer.load(url)
    }
  }
)

onUnmounted(() => {
  wavesurfer?.destroy()
  wavesurfer = null
})

const play = () => wavesurfer?.play()
const pause = () => wavesurfer?.pause()
const toggle = () => wavesurfer?.playPause()
const isPlaying = () => wavesurfer?.isPlaying() ?? false

defineExpose({ play, pause, toggle, isPlaying })
</script>
