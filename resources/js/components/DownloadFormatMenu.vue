<template>
  <div ref="root" class="relative w-fit max-w-full">
    <button
      type="button"
      class="transition-colors"
      :class="buttonClasses"
      :aria-label="ariaLabel"
      :disabled="disabled"
      @click.stop="toggleMenu"
    >
      <slot name="icon">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
      </slot>
    </button>

    <div
      v-if="open"
      class="absolute z-20 w-36 rounded-lg border border-stone-200 bg-white shadow-lg py-1"
      :class="menuClasses"
    >
      <button
        type="button"
        class="w-full px-3 py-2 text-left text-sm text-stone-700 hover:bg-stone-50 transition-colors"
        @click.stop="choose('mp3')"
      >
        Download MP3
      </button>
      <button
        type="button"
        class="w-full px-3 py-2 text-left text-sm text-stone-700 hover:bg-stone-50 transition-colors"
        @click.stop="choose('wav')"
      >
        Download WAV
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
  ariaLabel: { type: String, default: 'Download' },
  buttonClass: { type: [String, Array, Object], default: '' },
  disabled: { type: Boolean, default: false },
  variant: {
    type: String,
    default: 'icon',
    validator: (value) => ['icon', 'inline'].includes(value),
  },
})

const emit = defineEmits(['download'])

const open = ref(false)
const root = ref(null)

const buttonClasses = computed(() => {
  if (props.variant === 'inline') {
    return props.buttonClass
  }

  return [
    'p-2 rounded-full text-stone-400 hover:text-stone-800 hover:bg-stone-100',
    props.buttonClass,
  ]
})

const menuClasses = computed(() =>
  props.variant === 'inline'
    ? 'top-full left-0 mt-1'
    : 'bottom-full right-0 mb-2'
)

const toggleMenu = () => {
  open.value = !open.value
}

const choose = (format) => {
  open.value = false
  emit('download', format)
}

const handleClickOutside = (event) => {
  if (!root.value?.contains(event.target)) {
    open.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
