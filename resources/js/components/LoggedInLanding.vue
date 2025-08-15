<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
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
    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8">
      <!-- Main rcord Interface -->
      <div class="flex flex-col items-center justify-center w-full max-w-4xl rounded-lg p-12">
        <!-- Title and Description (show only when no mode is selected) -->
      
        <!-- Recording Mode Buttons -->
        <div 
          class="flex flex-col sm:flex-row gap-8 items-center mb-8 transition-all duration-500"
          :class="{ 
            'transform scale-0 opacity-0 pointer-events-none': selectedMode,
            'transform scale-100 opacity-100': !selectedMode 
          }"
        >
          <!-- Single Cord Button -->
          <button 
            @click="selectRecordingMode('single')"
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
            @click="selectRecordingMode('looped')"
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

        <!-- Animated Record Button (shown when mode is selected) -->
        <div 
          v-show="selectedMode"
          class="flex flex-col items-center transition-all duration-500"
          :class="{ 
            'opacity-100 transform scale-100': selectedMode,
            'opacity-0 transform scale-0 pointer-events-none': !selectedMode 
          }"
        >
          <!-- Record Button with Back Button -->
          <div class="flex items-center space-x-6 mb-8">
            <button
              @click="goBack"
              class="p-3 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors duration-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>
            
            <!-- Circular Record Button -->
            <div class="relative">
              <div 
                class="w-32 h-32 rounded-full flex items-center justify-center cursor-pointer transition-all duration-300 shadow-lg"
                :class="{
                  'bg-red-500 hover:bg-red-600': !isRecording && selectedMode === 'single',
                  'bg-purple-500 hover:bg-purple-600': !isRecording && selectedMode === 'looped',
                  'bg-red-600 animate-pulse': isRecording && selectedMode === 'single',
                  'bg-purple-600': isRecording && selectedMode === 'looped'
                }"
                @click="toggleRecording"
              >
                <div 
                  v-if="!isRecording"
                  class="w-20 h-20 bg-white rounded-full flex items-center justify-center"
                >
                  <div class="w-8 h-8 bg-red-500 rounded-full" v-if="selectedMode === 'single'"></div>
                  <div class="w-8 h-8 bg-purple-500 rounded-full" v-else></div>
                </div>
                <div 
                  v-else
                  class="w-16 h-16 bg-white rounded-sm flex items-center justify-center"
                >
                  <div class="w-8 h-8 bg-red-600 rounded-sm" v-if="selectedMode === 'single'"></div>
                  <div class="w-8 h-8 bg-purple-600 rounded-sm" v-else></div>
                </div>
              </div>
              
              <!-- Loop indicator for looped recording -->
              <div 
                v-if="isRecording && selectedMode === 'looped'" 
                class="absolute inset-0 border-4 border-purple-300 rounded-full animate-spin opacity-50"
              ></div>
            </div>
          </div>

          <!-- Recording Status -->
          <div class="text-center">
            <div class="text-xl font-semibold mb-2" :class="{
              'text-red-600': selectedMode === 'single',
              'text-purple-600': selectedMode === 'looped'
            }">
              {{ selectedMode === 'single' ? 'Single Cord Recording' : 'Looped Cord Recording' }}
            </div>
            <div class="text-sm text-gray-600 mb-4">
              {{ isRecording ? 'Recording...' : 'Click the button to start recording' }}
            </div>
            <div class="text-3xl font-mono font-bold text-gray-900">
              {{ formatTime(recordingTime) }}
            </div>
            <div v-if="selectedMode === 'looped' && isRecording" class="text-sm text-gray-500 mt-2">
              Loop {{ currentLoop }} • Total: {{ formatTime(totalRecordingTime) }}
            </div>
          </div>

          <!-- Audio Level Indicator -->
          <div v-if="isRecording" class="w-full max-w-md mt-6">
            <div class="text-center text-sm text-gray-600 mb-2">Audio Level</div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div 
                class="h-3 rounded-full transition-all duration-150" 
                :class="{
                  'bg-gradient-to-r from-green-400 to-green-600': selectedMode === 'single',
                  'bg-gradient-to-r from-purple-400 to-purple-600': selectedMode === 'looped'
                }"
                :style="{ width: audioLevel + '%' }"
              ></div>
            </div>
          </div>

          <!-- Loop Progress (for looped recording) -->
          <div v-if="isRecording && selectedMode === 'looped'" class="w-full max-w-md mt-4">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
              <span>Loop Progress</span>
              <span>{{ Math.round((recordingTime / loopDuration) * 100) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-gradient-to-r from-purple-400 to-purple-600 h-2 rounded-full transition-all duration-1000" 
                :style="{ width: ((recordingTime % loopDuration) / loopDuration) * 100 + '%' }"
              ></div>
            </div>
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
import { ref, onUnmounted } from 'vue';
import { useMainStore } from '../stores/main';
import DashboardModal from './DashboardModal.vue';
import SingleCordModal from './SingleCordModal.vue';
import LoopedCordModal from './LoopedCordModal.vue';

const store = useMainStore();
const showDashboard = ref(false);
const showSingleCord = ref(false);
const showLoopedCord = ref(false);

// Recording state
const selectedMode = ref(null); // 'single' or 'looped'
const isRecording = ref(false);
const recordingTime = ref(0);
const totalRecordingTime = ref(0);
const currentLoop = ref(0);
const audioLevel = ref(0);
const loopDuration = ref(30); // seconds per loop

// Timers
let recordingInterval = null;
let audioLevelInterval = null;
let loopCheckInterval = null;

const selectRecordingMode = (mode) => {
  selectedMode.value = mode;
  // Reset all recording state when switching modes
  resetRecordingState();
};

const goBack = () => {
  // Stop recording if active
  if (isRecording.value) {
    stopRecording();
  }
  // Clear selected mode to return to main menu
  selectedMode.value = null;
  resetRecordingState();
};

const toggleRecording = () => {
  if (isRecording.value) {
    stopRecording();
  } else {
    startRecording();
  }
};

const startRecording = () => {
  isRecording.value = true;
  recordingTime.value = 0;
  totalRecordingTime.value = 0;
  
  if (selectedMode.value === 'looped') {
    currentLoop.value = 1;
  }
  
  // Start recording timer
  recordingInterval = setInterval(() => {
    recordingTime.value++;
    totalRecordingTime.value++;
  }, 1000);
  
  // Simulate audio level updates
  audioLevelInterval = setInterval(() => {
    audioLevel.value = Math.random() * 100;
  }, 100);
  
  // For looped recording, handle loop completion
  if (selectedMode.value === 'looped') {
    loopCheckInterval = setInterval(() => {
      if (recordingTime.value >= loopDuration.value) {
        // Complete current loop
        recordingTime.value = 0;
        currentLoop.value++;
      }
    }, 1000);
  }
  
  console.log(`Started ${selectedMode.value} recording...`);
};

const stopRecording = () => {
  isRecording.value = false;
  
  // Clear all intervals
  if (recordingInterval) {
    clearInterval(recordingInterval);
    recordingInterval = null;
  }
  
  if (audioLevelInterval) {
    clearInterval(audioLevelInterval);
    audioLevelInterval = null;
  }
  
  if (loopCheckInterval) {
    clearInterval(loopCheckInterval);
    loopCheckInterval = null;
  }
  
  console.log(`Stopped ${selectedMode.value} recording. Total time: ${formatTime(totalRecordingTime.value)}`);
  
  // For looped recording, show loop info
  if (selectedMode.value === 'looped' && currentLoop.value > 1) {
    console.log(`Completed ${currentLoop.value - 1} loops`);
  }
};

const resetRecordingState = () => {
  recordingTime.value = 0;
  totalRecordingTime.value = 0;
  currentLoop.value = 0;
  audioLevel.value = 0;
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const handleLogout = async () => {
  try {
    await store.logout();
  } catch (error) {
    console.error('Logout failed:', error);
  }
};

// Cleanup on component unmount
onUnmounted(() => {
  if (recordingInterval) {
    clearInterval(recordingInterval);
  }
  if (audioLevelInterval) {
    clearInterval(audioLevelInterval);
  }
  if (loopCheckInterval) {
    clearInterval(loopCheckInterval);
  }
});
</script>
