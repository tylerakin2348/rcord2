<template>
  <div>
    <Header @login="showLoginModal = true" />
    <main class="flex flex-col items-center justify-center min-h-[70vh] px-4 py-16 bg-gradient-to-br from-blue-50 to-indigo-100">
      <div class="max-w-2xl text-center">
        <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6 leading-tight">
          Effortless <span class="text-blue-600">Looped Voice Memos</span>
        </h1>
        <p class="text-xl text-gray-700 mb-8">
          Instantly record, loop, and organize your voice memos. Perfect for reminders, ideas, and capturing inspiration—on repeat.
        </p>
        <button
          class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-2xl font-bold text-2xl shadow-lg transition-all duration-200 transform hover:scale-105"
          @click="showLoginModal = true"
        >
          Get Started
        </button>
        <div class="mt-6 text-gray-500 text-base">
          No account needed to try the demo.
        </div>
      </div>
      <div class="mt-12 flex justify-center">
        <!-- Simple illustration of a looping waveform or memo card -->
        <svg width="220" height="60" viewBox="0 0 220 60" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="10" y="30" width="10" height="20" rx="4" fill="#3B82F6"/>
          <rect x="30" y="20" width="10" height="30" rx="4" fill="#6366F1"/>
          <rect x="50" y="10" width="10" height="40" rx="4" fill="#3B82F6"/>
          <rect x="70" y="5" width="10" height="50" rx="4" fill="#6366F1"/>
          <rect x="90" y="10" width="10" height="40" rx="4" fill="#3B82F6"/>
          <rect x="110" y="20" width="10" height="30" rx="4" fill="#6366F1"/>
          <rect x="130" y="30" width="10" height="20" rx="4" fill="#3B82F6"/>
          <rect x="150" y="20" width="10" height="30" rx="4" fill="#6366F1"/>
          <rect x="170" y="10" width="10" height="40" rx="4" fill="#3B82F6"/>
          <rect x="190" y="5" width="10" height="50" rx="4" fill="#6366F1"/>
        </svg>
      </div>
    </main>
    <Footer />
    <LoginModal 
      :isOpen="showLoginModal" 
      @close="showLoginModal = false"
      @success="handleAuthSuccess"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Header from './Header.vue';
import Footer from './Footer.vue';
import LoginModal from './LoginModal.vue';

const showLoginModal = ref(false);
const handleAuthSuccess = () => {
  showLoginModal.value = false;
};
</script>
