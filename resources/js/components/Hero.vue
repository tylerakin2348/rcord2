<template>
  <section class="text-center py-20">
    <div class="max-w-4xl mx-auto">
      <!-- Badge -->
      <div class="inline-flex items-center gap-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-4 py-2 rounded-full text-sm font-medium mb-6">
        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
        Now with Vue 3 & Pinia support
      </div>

      <!-- Main Heading -->
      <h1 class="text-5xl md:text-7xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
        Build Amazing
        <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
          Web Apps
        </span>
      </h1>

      <!-- Subtitle -->
      <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto leading-relaxed">
        A modern full-stack application built with Laravel, Vue 3, Pinia, and Tailwind CSS. 
        Fast, scalable, and beautiful by design.
      </p>

      <!-- CTA Buttons -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
        <button 
          class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
          @click="$emit('get-started')"
          :disabled="store.isLoading"
        >
          <span v-if="!store.isLoading">Get Started</span>
          <span v-else class="flex items-center gap-2">
            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            Loading...
          </span>
        </button>
        
        <button class="border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 text-gray-700 dark:text-gray-300 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800">
          View Demo
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-2xl mx-auto">
        <div class="text-center">
          <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ formatNumber(store.stats.users) }}+</div>
          <div class="text-gray-600 dark:text-gray-400">Active Users</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ formatNumber(store.stats.projects) }}+</div>
          <div class="text-gray-600 dark:text-gray-400">Projects</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ store.stats.uptime }}%</div>
          <div class="text-gray-600 dark:text-gray-400">Uptime</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useMainStore } from '../stores/main';

const store = useMainStore();

defineEmits(['get-started'])

const formatNumber = (num) => {
  return num.toLocaleString();
};
</script>
