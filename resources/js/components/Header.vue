<template>
  <header class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
    <nav class="container mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-lg">R</span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Rcord</h1>
        </div>

        <div class="flex items-center space-x-4">
          <button 
            @click="$emit('login')"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
          >
            Get Started
          </button>
          <router-link
            to="/demo"
            class="bg-gray-200 hover:bg-gray-300 text-gray-900 px-4 py-2 rounded-lg transition-colors dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600"
          >
            Demo
          </router-link>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useMainStore } from '../stores/main';

defineEmits(['login'])

const isDark = ref(false);
const store = useMainStore();

const canManageSystemInfo = computed(() => {
  return store.user && store.permissions && store.permissions.includes('manage_system_info');
});

const toggleTheme = () => {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  isDark.value = savedTheme ? savedTheme === 'dark' : prefersDark;
  document.documentElement.classList.toggle('dark', isDark.value);
});
</script>
