<template>
  <div id="app" class="min-h-screen bg-white">
    <!-- Landing Page (when logged out) -->
    <div v-if="!store.isLoggedIn">
      <Header @login="showLoginModal = true" />
      <main class="container mx-auto px-6 py-12">
        <Hero @get-started="showLoginModal = true" />
        <Features />
      </main>
      <Footer />
    </div>

    <!-- Router View for Logged In Users -->
    <router-view v-else />

    <!-- Login/Register Modal -->
    <LoginModal 
      :isOpen="showLoginModal" 
      @close="showLoginModal = false"
      @success="handleAuthSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useMainStore } from './stores/main';
import Header from './components/Header.vue';
import Hero from './components/Hero.vue';
import Features from './components/Features.vue';
import Footer from './components/Footer.vue';
import LoginModal from './components/LoginModal.vue';

const store = useMainStore();
const showLoginModal = ref(false);

const handleAuthSuccess = () => {
  showLoginModal.value = false;
  // The store will automatically update the isLoggedIn state
};

onMounted(() => {
  store.initialize();
});
</script>

<style scoped>
/* Component-specific styles */
</style>
