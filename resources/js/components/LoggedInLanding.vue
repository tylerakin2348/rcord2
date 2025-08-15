<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header with User Profile Button -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-900">Welcome Back!</h1>
          </div>
          
          <!-- User Profile Button -->
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">Hello, {{ store.user?.name }}</span>
            <button 
              @click="showDashboard = true"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>Dashboard</span>
            </button>
            <button 
              @click="handleLogout"
              class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Main rcord Interface -->
      <div class="flex flex-col items-center justify-center min-h-96 bg-white rounded-lg shadow-sm p-12">
        <h2 class="text-4xl font-bold text-gray-900 mb-4 text-center">rcord</h2>
        <p class="text-lg text-gray-600 mb-12 text-center max-w-2xl">
          Professional audio recording made simple. Choose your recording mode and start creating.
        </p>
        
        <!-- Recording Mode Buttons -->
        <div class="flex flex-col sm:flex-row gap-8 items-center mb-8">
          <!-- Single Cord Button -->
          <button 
            @click="showSingleCord = true"
            class="group relative bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-12 py-6 rounded-2xl text-xl font-semibold transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div class="flex items-center space-x-3">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
              <span>Single Cord</span>
            </div>
            <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 text-sm text-blue-200 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              Record once
            </div>
          </button>

          <!-- Looped Cord Button -->
          <button 
            @click="showLoopedCord = true"
            class="group relative bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-12 py-6 rounded-2xl text-xl font-semibold transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div class="flex items-center space-x-3">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>Looped Cord</span>
            </div>
            <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 text-sm text-purple-200 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              Continuous recording
            </div>
          </button>
        </div>
        
        <!-- Quick Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-4xl">
          <div class="text-center p-4">
            <div class="bg-blue-100 rounded-lg p-3 w-12 h-12 mx-auto mb-3 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Quick & Easy</h3>
            <p class="text-sm text-gray-600">Start recording with just one click. No complex setup required.</p>
          </div>
          
          <div class="text-center p-4">
            <div class="bg-purple-100 rounded-lg p-3 w-12 h-12 mx-auto mb-3 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">High Quality</h3>
            <p class="text-sm text-gray-600">Professional audio quality with multiple format options.</p>
          </div>
        </div>
      </div>
    </main>

    <!-- Dashboard Modal -->
    <DashboardModal 
      v-if="showDashboard"
      @close="showDashboard = false"
    />

    <!-- Single Cord Recording Modal -->
    <SingleCordModal 
      v-if="showSingleCord"
      @close="showSingleCord = false"
    />

    <!-- Looped Cord Recording Modal -->
    <LoopedCordModal 
      v-if="showLoopedCord"
      @close="showLoopedCord = false"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useMainStore } from '../stores/main';
import DashboardModal from './DashboardModal.vue';
import SingleCordModal from './SingleCordModal.vue';
import LoopedCordModal from './LoopedCordModal.vue';

const store = useMainStore();
const showDashboard = ref(false);
const showSingleCord = ref(false);
const showLoopedCord = ref(false);

const handleLogout = async () => {
  try {
    await store.logout();
  } catch (error) {
    console.error('Logout failed:', error);
  }
};
</script>
