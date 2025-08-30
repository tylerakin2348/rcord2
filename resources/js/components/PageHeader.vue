<template>
  <div class="flex items-center gap-4 py-4 px-6 bg-white border-gray-400">
    <button @click="goBack" class="text-stone-600 hover:text-stone-800 bg-stone-100 hover:bg-stone-200 rounded-full p-2 transition-colors duration-200">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>
    <h2 class="text-xl font-semibold text-stone-700">{{ pageTitle }}</h2>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
const props = defineProps({
  route: {
    type: String,
    default: '/'
  },
  pageTitle: {
    type: String,
    required: true
  }
});
const router = useRouter();
function goBack() {
  router.push(props.route);
}
</script>
