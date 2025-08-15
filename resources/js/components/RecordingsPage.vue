<template>
  <div class="min-h-screen bg-stone-100 flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <button
              @click="goBack"
              class="p-2 text-stone-500 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors duration-200 mr-3"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>
            <h1 class="text-2xl font-bold text-blue-700">
              Recordings Library
            </h1>
          </div>
          
          <!-- User Profile Button -->
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">Hello, {{ store.user?.name }}</span>
            <button 
              @click="handleLogout"
              class="text-stone-500 hover:text-stone-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-hidden">
      <div class="h-full">
        <!-- Recordings Library Component (no recording functionality) -->
        <RecordingsLibrary />
      </div>
    </main>
  </div>
</template>

<script setup>
import { useMainStore } from '../stores/main';
import { useRouter } from 'vue-router';
import RecordingsLibrary from './RecordingsLibrary.vue';

const store = useMainStore();
const router = useRouter();

const goBack = () => {
  router.push('/');
};

const handleLogout = async () => {
  try {
    await store.logout();
    router.push('/');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};
</script>

<style scoped>
/* Component-specific styles */
</style>
