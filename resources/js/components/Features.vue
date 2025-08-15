<template>
  <section class="py-20">
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-6">
        Why Choose Rcord?
      </h2>
      <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
        Built with modern technologies and best practices to deliver exceptional user experiences
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
      <div 
        v-for="feature in store.features" 
        :key="feature.id"
        class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm p-8 rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300 hover:transform hover:scale-105"
      >
        <div class="text-4xl mb-4">{{ feature.icon }}</div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
          {{ feature.title }}
        </h3>
        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
          {{ feature.description }}
        </p>
      </div>
    </div>

    <!-- Additional Info Section -->
    <div class="mt-20 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-12 text-center text-white">
      <h3 class="text-3xl font-bold mb-4">Ready to Get Started?</h3>
      <p class="text-xl mb-8 text-blue-100">
        Join thousands of developers building amazing applications with our platform
      </p>
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <button class="bg-white text-blue-600 hover:bg-gray-50 px-8 py-3 rounded-xl font-semibold transition-colors">
          Start Building
        </button>
        <button class="border-2 border-white hover:bg-white/10 px-8 py-3 rounded-xl font-semibold transition-colors">
          View Documentation
        </button>
      </div>
    </div>

    <!-- Tech Stack -->
    <div class="mt-20 text-center">
      <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">
        Powered by Modern Technologies
      </h3>
      <div class="flex flex-wrap items-center justify-center gap-8">
        <div class="flex items-center gap-2 bg-white/60 dark:bg-gray-800/60 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
          <span class="text-2xl">⚡</span>
          <span class="font-semibold text-gray-900 dark:text-white">Laravel</span>
        </div>
        <div class="flex items-center gap-2 bg-white/60 dark:bg-gray-800/60 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
          <span class="text-2xl">🚀</span>
          <span class="font-semibold text-gray-900 dark:text-white">Vue 3</span>
        </div>
        <div class="flex items-center gap-2 bg-white/60 dark:bg-gray-800/60 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
          <span class="text-2xl">🍍</span>
          <span class="font-semibold text-gray-900 dark:text-white">Pinia</span>
        </div>
        <div class="flex items-center gap-2 bg-white/60 dark:bg-gray-800/60 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
          <span class="text-2xl">🎨</span>
          <span class="font-semibold text-gray-900 dark:text-white">Tailwind</span>
        </div>
        <div class="flex items-center gap-2 bg-white/60 dark:bg-gray-800/60 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
          <span class="text-2xl">⚡</span>
          <span class="font-semibold text-gray-900 dark:text-white">Vite</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useMainStore } from '../stores/main';

const store = useMainStore();
</script>
