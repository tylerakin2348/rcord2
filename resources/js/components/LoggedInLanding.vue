<template>
  <div class="min-h-screen bg-stone-100 flex flex-col">
    <!-- Header with User Profile Button -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <h1 
              class="text-2xl font-bold transition-all duration-500"
              :class="{
                'text-gray-900': !selectedMode,
                'text-amber-700': selectedMode === 'single',
                'text-stone-700': selectedMode === 'looped'
              }"
            >
              <span v-if="!selectedMode">Welcome Back!</span>
              <span v-else-if="selectedMode === 'single'">Single Cord Recording</span>
              <span v-else-if="selectedMode === 'looped'">Looped Cord Recording</span>
            </h1>
          </div>
          
          <!-- User Profile Button -->
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">Hello, {{ store.user?.name }}</span>
            <button 
              @click="showDashboard = true"
              class="bg-stone-600 hover:bg-stone-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>Dashboard</span>
            </button>
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
    <main class="flex-1 flex px-4 sm:px-6 lg:px-8 overflow-hidden">
      <!-- Main rcord Interface -->
      <div 
        class="flex w-full transition-all duration-500"
        :class="{
          'items-center justify-center': !selectedMode,
          'items-stretch': selectedMode
        }"
      >
        <!-- Center container for mode selection -->
        <div 
          v-show="!selectedMode"
          class="flex flex-col items-center justify-center w-full max-w-4xl mx-auto p-12"
        >
          <!-- Recording Mode Buttons -->
          <div class="flex flex-col sm:flex-row gap-8 items-center">
            <!-- Single Cord Button -->
            <button 
              @click="selectRecordingMode('single')"
              class="group relative bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white px-12 py-6 rounded-2xl text-xl font-semibold transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
            >
              <div class="flex items-center space-x-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
              </div>
              <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 text-sm text-amber-200 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                Record once
              </div>
            </button>

            <!-- Looped Cord Button -->
            <button 
              @click="selectRecordingMode('looped')"
              class="group relative bg-gradient-to-r from-stone-600 to-stone-700 hover:from-stone-700 hover:to-stone-800 text-white px-12 py-6 rounded-2xl text-xl font-semibold transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
            >
              <div class="flex items-center space-x-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </div>
              <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 text-sm text-stone-200 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                Continuous recording
              </div>
            </button>
          </div>
        </div>

        <!-- Left Half: Recording Controls (shown when mode is selected) -->
        <div 
          v-show="selectedMode"
          class="flex flex-col h-full transition-all duration-500"
          :class="{ 
            'opacity-100 transform scale-100': selectedMode,
            'opacity-0 transform scale-0 pointer-events-none': !selectedMode,
            'w-1/2': isDrawerExpanded,
            'w-full': !isDrawerExpanded
          }"
        >
          <!-- Back Button at top -->
          <div class="flex justify-start p-4">
            <button
              @click="goBack"
              class="p-3 text-stone-500 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors duration-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>
          </div>

          <!-- Recording Controls - centered in remaining space -->
          <div class="flex-1 flex flex-col items-center justify-center"
               :class="{ 'px-8': !isDrawerExpanded }">
            <!-- Circular Record Button -->
            <div class="relative mb-8">
              <div 
                class="w-32 h-32 rounded-full flex items-center justify-center cursor-pointer transition-all duration-300 shadow-lg"
                :class="{
                  'bg-amber-500 hover:bg-amber-600': !isRecording && selectedMode === 'single',
                  'bg-stone-500 hover:bg-stone-600': !isRecording && selectedMode === 'looped',
                  'bg-amber-600 animate-pulse': isRecording && selectedMode === 'single',
                  'bg-stone-600': isRecording && selectedMode === 'looped'
                }"
                @click="toggleRecording"
              >
                <div 
                  v-if="!isRecording"
                  class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center"
                >
                  <div class="w-8 h-8 bg-amber-500 rounded-full" v-if="selectedMode === 'single'"></div>
                  <div class="w-8 h-8 bg-stone-500 rounded-full" v-else></div>
                </div>
                <div 
                  v-else
                  class="w-16 h-16 bg-stone-50 rounded-sm flex items-center justify-center"
                >
                  <div class="w-8 h-8 bg-amber-600 rounded-sm" v-if="selectedMode === 'single'"></div>
                  <div class="w-8 h-8 bg-stone-600 rounded-sm" v-else></div>
                </div>
              </div>
              
              <!-- Loop indicator for looped recording -->
              <div 
                v-if="isRecording && selectedMode === 'looped'" 
                class="absolute inset-0 border-4 border-stone-300 rounded-full animate-spin opacity-50"
              ></div>
            </div>

            <!-- Recording Status -->
            <div class="text-center mb-6">
              <div v-if="selectedMode === 'looped' && isRecording" class="text-sm text-stone-500 mt-2">
                Loop {{ currentLoop }}
              </div>
            </div>

            <!-- Loop Progress (for looped recording) -->
            <div v-if="isRecording && selectedMode === 'looped'" class="w-full max-w-sm mt-4">
              <div class="flex justify-between text-sm text-stone-600 mb-2">
                <span>Loop Progress</span>
                <span>{{ Math.round((recordingTime / loopDuration) * 100) }}%</span>
              </div>
              <div class="w-full bg-stone-200 rounded-full h-2">
                <div 
                  class="bg-gradient-to-r from-stone-400 to-stone-500 h-2 rounded-full transition-all duration-1000" 
                  :style="{ width: ((recordingTime % loopDuration) / loopDuration) * 100 + '%' }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Footer Section with Time Counter and Audio Level -->
          <div class="p-4 border-t border-stone-200 bg-stone-50">
            <!-- Recording Time Counter -->
            <div class="text-center mb-4">
              <div class="text-3xl font-mono font-bold text-stone-800">
                {{ formatTime(recordingTime) }}
              </div>
              <div v-if="selectedMode === 'looped' && isRecording" class="text-sm text-stone-500 mt-1">
                Total: {{ formatTime(totalRecordingTime) }}
              </div>
            </div>

            <!-- Audio Level Indicator -->
            <div v-if="isRecording" class="w-full">
              <div class="text-center text-sm text-stone-600 mb-2">Audio Level</div>
              <div class="w-full bg-stone-200 rounded-full h-3">
                <div 
                  class="h-3 rounded-full transition-all duration-150" 
                  :class="{
                    'bg-gradient-to-r from-amber-400 to-amber-500': selectedMode === 'single',
                    'bg-gradient-to-r from-stone-400 to-stone-500': selectedMode === 'looped'
                  }"
                  :style="{ width: audioLevel + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Drawer Toggle Button -->
        <div 
          v-show="selectedMode"
          class="flex items-center transition-all duration-500"
          :class="{
            'opacity-100': selectedMode,
            'opacity-0 pointer-events-none': !selectedMode
          }"
        >
          <button
            @click="toggleDrawer"
            class="p-2 bg-stone-200 hover:bg-stone-300 text-stone-600 hover:text-stone-800 rounded-l-lg border-r border-stone-300 transition-colors duration-200 z-10"
            :class="{
              'rounded-r-none': isDrawerExpanded,
              'rounded-r-lg': !isDrawerExpanded
            }"
          >
            <svg 
              class="w-5 h-5 transition-transform duration-300" 
              :class="{ 'rotate-180': !isDrawerExpanded }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
        </div>

        <!-- Right Half: Recordings Drawer -->
        <div 
          v-show="selectedMode"
          class="h-full transition-all duration-500 bg-stone-50 rounded-lg shadow-sm flex flex-col"
          :class="{ 
            'opacity-100 transform scale-100': selectedMode && isDrawerExpanded,
            'opacity-0 transform scale-x-0 pointer-events-none': !selectedMode || !isDrawerExpanded,
            'w-1/2 pl-0': isDrawerExpanded,
            'w-0': !isDrawerExpanded
          }"
          style="height: calc(100vh - 69px);"
        >
          <div class="p-6 h-full flex flex-col">
            <h3 class="text-xl font-semibold text-stone-800 mb-6">Recent Recordings</h3>
            
            <!-- Recordings List with Scroll -->
            <div class="flex-1 overflow-y-scroll">
              <div v-if="recordedFiles.length > 0" class="space-y-3">
                <div 
                  v-for="file in recordedFiles" 
                  :key="file.id" 
                  class="flex items-center justify-between p-4 bg-white rounded-lg border border-stone-200 hover:border-stone-300 transition-colors duration-200"
                >
                  <div class="flex items-center space-x-3">
                    <div 
                      class="p-2 rounded-lg"
                      :class="{
                        'bg-amber-100': selectedMode === 'single',
                        'bg-stone-100': selectedMode === 'looped'
                      }"
                    >
                      <svg class="w-5 h-5" :class="{
                        'text-amber-600': selectedMode === 'single',
                        'text-stone-600': selectedMode === 'looped'
                      }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" v-if="selectedMode === 'single'" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" v-else />
                      </svg>
                    </div>
                    <div>
                      <div class="font-medium text-stone-900">{{ file.name }}</div>
                      <div class="text-sm text-stone-500">{{ file.duration }} • {{ file.size }}</div>
                      <div v-if="selectedMode === 'looped' && file.loops" class="text-xs text-stone-400">{{ file.loops }} loops</div>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
                    <button 
                      @click="playFile(file)"
                      class="p-2 rounded-full transition-colors duration-200"
                      :class="{
                        'text-amber-600 hover:text-amber-800 hover:bg-amber-50': selectedMode === 'single',
                        'text-stone-600 hover:text-stone-800 hover:bg-stone-50': selectedMode === 'looped'
                      }"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m2-7a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>
                    <button 
                      @click="downloadFile(file)"
                      class="p-2 rounded-full transition-colors duration-200"
                      :class="{
                        'text-amber-600 hover:text-amber-800 hover:bg-amber-50': selectedMode === 'single',
                        'text-stone-600 hover:text-stone-800 hover:bg-stone-50': selectedMode === 'looped'
                      }"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </button>
                    <button 
                      @click="deleteFile(file)"
                      class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors duration-200"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="flex items-center justify-center h-full">
                <div class="text-center">
                  <div 
                    class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-4"
                    :class="{
                      'bg-amber-100': selectedMode === 'single',
                      'bg-stone-100': selectedMode === 'looped'
                    }"
                  >
                    <svg class="w-8 h-8" :class="{
                      'text-amber-400': selectedMode === 'single',
                      'text-stone-400': selectedMode === 'looped'
                    }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" v-if="selectedMode === 'single'" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" v-else />
                    </svg>
                  </div>
                  <p class="text-stone-500">No recordings yet</p>
                  <p class="text-sm text-stone-400 mt-1">Start recording to see your files here</p>
                </div>
              </div>
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
import { useRouter } from 'vue-router';
import DashboardModal from './DashboardModal.vue';
import SingleCordModal from './SingleCordModal.vue';
import LoopedCordModal from './LoopedCordModal.vue';

const store = useMainStore();
const router = useRouter();
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
const recordedFiles = ref([]); // List of recorded files

// Drawer state
const isDrawerExpanded = ref(true);

// Browser recording state
let mediaRecorder = null;
let audioStream = null;
let audioContext = null;
let analyser = null;
let audioChunks = [];

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

const startRecording = async () => {
  try {
    // Request microphone access
    audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
    
    // Setup audio analysis for level meter
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    analyser = audioContext.createAnalyser();
    const microphone = audioContext.createMediaStreamSource(audioStream);
    microphone.connect(analyser);
    analyser.fftSize = 256;
    
    // Setup MediaRecorder
    mediaRecorder = new MediaRecorder(audioStream);
    audioChunks = [];
    
    mediaRecorder.ondataavailable = (event) => {
      if (event.data.size > 0) {
        audioChunks.push(event.data);
      }
    };
    
    mediaRecorder.onstop = () => {
      createRecordingFile();
    };
    
    // Start recording
    mediaRecorder.start();
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
    
    // Start audio level monitoring
    audioLevelInterval = setInterval(() => {
      updateAudioLevel();
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
    
  } catch (error) {
    console.error('Error starting recording:', error);
    alert('Error accessing microphone. Please ensure you have granted permission and try again.');
    resetRecordingState();
  }
};

const stopRecording = () => {
  isRecording.value = false;
  
  // Stop MediaRecorder
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stop();
  }
  
  // Stop audio stream
  if (audioStream) {
    audioStream.getTracks().forEach(track => track.stop());
    audioStream = null;
  }
  
  // Close audio context
  if (audioContext && audioContext.state !== 'closed') {
    audioContext.close();
    audioContext = null;
  }
  
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

const toggleDrawer = () => {
  isDrawerExpanded.value = !isDrawerExpanded.value;
};

const updateAudioLevel = () => {
  if (analyser) {
    const bufferLength = analyser.frequencyBinCount;
    const dataArray = new Uint8Array(bufferLength);
    analyser.getByteFrequencyData(dataArray);
    
    // Calculate average volume
    let sum = 0;
    for (let i = 0; i < bufferLength; i++) {
      sum += dataArray[i];
    }
    const average = sum / bufferLength;
    audioLevel.value = (average / 255) * 100; // Convert to percentage
  }
};

const createRecordingFile = () => {
  if (audioChunks.length === 0 || totalRecordingTime.value === 0) return;
  
  const blob = new Blob(audioChunks, { type: 'audio/wav' });
  const url = URL.createObjectURL(blob);
  
  const newFile = {
    id: Date.now(),
    name: `${selectedMode.value}_recording_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.wav`,
    duration: formatTime(totalRecordingTime.value),
    size: `${(blob.size / (1024 * 1024)).toFixed(2)}MB`,
    timestamp: Date.now(),
    loops: selectedMode.value === 'looped' ? currentLoop.value - 1 : null,
    blob: blob,
    url: url
  };
  
  recordedFiles.value.unshift(newFile);
  audioChunks = []; // Clear chunks for next recording
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const playFile = (file) => {
  console.log('Playing file:', file.name);
  
  if (file.url) {
    // Create audio element and play
    const audio = new Audio(file.url);
    audio.play().catch(error => {
      console.error('Error playing audio:', error);
      alert('Error playing audio file');
    });
  }
};

const downloadFile = (file) => {
  console.log('Downloading file:', file.name);
  
  if (file.url) {
    // Create download link
    const link = document.createElement('a');
    link.href = file.url;
    link.download = file.name;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};

const deleteFile = (file) => {
  const index = recordedFiles.value.findIndex(f => f.id === file.id);
  if (index !== -1) {
    // Revoke the blob URL to free memory
    if (file.url) {
      URL.revokeObjectURL(file.url);
    }
    recordedFiles.value.splice(index, 1);
    console.log('Deleted file:', file.name);
  }
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
  // Stop recording if active
  if (isRecording.value) {
    stopRecording();
  }
  
  // Clear intervals
  if (recordingInterval) {
    clearInterval(recordingInterval);
  }
  if (audioLevelInterval) {
    clearInterval(audioLevelInterval);
  }
  if (loopCheckInterval) {
    clearInterval(loopCheckInterval);
  }
  
  // Clean up blob URLs
  recordedFiles.value.forEach(file => {
    if (file.url) {
      URL.revokeObjectURL(file.url);
    }
  });
});
</script>
