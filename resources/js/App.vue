<template>
  <div id="app" class="min-h-screen bg-white">
    <router-view />
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useMainStore } from './stores/main';
import ToastContainer from './components/ToastContainer.vue';

const store = useMainStore();
const route = useRoute();
const showLoginModal = ref(false);

// Routes that should allow scrolling (remove overflow-hidden)
const scrollableRoutes = ['/recordings', '/profile', '/system-info', '/account'];

const handleAuthSuccess = () => {
  showLoginModal.value = false;
  // The store will automatically update the isLoggedIn state
};

// Watch for route changes and update body overflow
watch(
  () => route.path,
  (newPath) => {
    const body = document.getElementById('app-body') || document.body
    if (scrollableRoutes.includes(newPath)) {
      body.classList.remove('overflow-hidden')
    } else {
      body.classList.add('overflow-hidden')
    }
  },
  { immediate: true }
)

onMounted(() => {
  store.initialize();
});
</script>

<style scoped>
/* Component-specific styles */
</style>
