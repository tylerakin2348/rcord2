<template>
  <div :class="containerClass">
    <canvas ref="canvas" :class="canvasClass" />
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from 'vue'

const props = defineProps({
  analyser: {
    type: Object,
    default: null,
  },
  active: {
    type: Boolean,
    default: false,
  },
  variant: {
    type: String,
    default: 'inline',
    validator: (value) => ['inline', 'background'].includes(value),
  },
})

const canvas = ref(null)
const waveformData = []

let animationId = null
let resizeObserver = null

const isBackground = computed(() => props.variant === 'background')
const maxPoints = computed(() => (isBackground.value ? 500 : 300))

const containerClass = computed(() =>
  isBackground.value
    ? 'absolute inset-0 h-full w-full pointer-events-none overflow-hidden'
    : 'w-full'
)

const canvasClass = computed(() =>
  isBackground.value
    ? 'block h-full w-full'
    : 'w-full h-20 rounded-lg bg-stone-200/60 block border border-stone-300'
)

const resizeCanvas = () => {
  if (!canvas.value?.parentElement) return

  const rect = canvas.value.parentElement.getBoundingClientRect()
  if (rect.width <= 0 || rect.height <= 0) return

  const dpr = window.devicePixelRatio || 1
  const height = isBackground.value ? rect.height : 80

  canvas.value.width = Math.max(1, Math.floor(rect.width * dpr))
  canvas.value.height = Math.max(1, Math.floor(height * dpr))
  canvas.value.style.width = `${rect.width}px`
  canvas.value.style.height = `${height}px`

  const ctx = canvas.value.getContext('2d')
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0)
}

const draw = () => {
  if (!props.active || !props.analyser || !canvas.value) return

  const width = canvas.value.clientWidth
  const height = canvas.value.clientHeight
  if (width <= 0 || height <= 0) {
    animationId = requestAnimationFrame(draw)
    return
  }

  const ctx = canvas.value.getContext('2d')
  const bufferLength = props.analyser.fftSize
  const dataArray = new Uint8Array(bufferLength)

  props.analyser.getByteTimeDomainData(dataArray)

  let sumSquares = 0
  for (let i = 0; i < bufferLength; i++) {
    const normalized = (dataArray[i] - 128) / 128
    sumSquares += normalized * normalized
  }
  const rms = Math.sqrt(sumSquares / bufferLength)
  const peak = Math.min(1, rms * (isBackground.value ? 5 : 6))

  waveformData.push(peak)
  if (waveformData.length > maxPoints.value) {
    waveformData.shift()
  }

  ctx.clearRect(0, 0, width, height)

  if (isBackground.value) {
    ctx.fillStyle = 'rgba(245, 245, 244, 0.45)'
    ctx.fillRect(0, 0, width, height)
  } else {
    ctx.fillStyle = '#e7e5e4'
    ctx.fillRect(0, 0, width, height)
  }

  const barWidth = width / maxPoints.value
  ctx.fillStyle = isBackground.value
    ? 'rgba(168, 162, 158, 0.28)'
    : '#57534e'

  const heightScale = isBackground.value ? 0.75 : 0.9
  const minBarHeight = isBackground.value ? 2 : 3

  waveformData.forEach((value, index) => {
    const barHeight = Math.max(minBarHeight, value * height * heightScale)
    const x = index * barWidth
    const y = (height - barHeight) / 2
    ctx.fillRect(x, y, Math.max(barWidth - 0.5, 1), barHeight)
  })

  animationId = requestAnimationFrame(draw)
}

const start = async () => {
  stop()
  waveformData.length = 0
  await nextTick()
  resizeCanvas()
  if (!canvas.value || canvas.value.clientWidth <= 0) {
    requestAnimationFrame(() => {
      resizeCanvas()
      draw()
    })
    return
  }
  draw()
}

const stop = () => {
  if (animationId) {
    cancelAnimationFrame(animationId)
    animationId = null
  }
}

watch(
  () => [props.active, props.analyser, props.variant],
  ([active, analyser]) => {
    if (active && analyser) {
      start()
    } else {
      stop()
    }
  }
)

onMounted(() => {
  if (canvas.value?.parentElement) {
    resizeObserver = new ResizeObserver(() => {
      resizeCanvas()
      if (props.active && props.analyser && !animationId) {
        draw()
      }
    })
    resizeObserver.observe(canvas.value.parentElement)
  }

  if (props.active && props.analyser) {
    start()
  }
})

onUnmounted(() => {
  stop()
  resizeObserver?.disconnect()
})
</script>
