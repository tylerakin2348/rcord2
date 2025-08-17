<template>
  <div class="min-h-screen bg-stone-100 flex flex-col relative">
    <!-- Header with User Profile Button -->
    <header 
      class="bg-white shadow-sm border-b border-gray-200 transition-all duration-500"
      :class="{
        'transform translate-y-0': isHeaderExpanded,
        'transform -translate-y-full': !isHeaderExpanded
      }"
    >
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
          
          <!-- Desktop Navigation -->
          <div class="hidden md:flex items-center space-x-4">
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

          <!-- Mobile Hamburger Menu -->
          <div class="md:hidden">
            <button
              @click="isMobileMenuOpen = !isMobileMenuOpen"
              class="p-2 text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-lg transition-colors duration-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path 
                  v-if="!isMobileMenuOpen"
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M4 6h16M4 12h16M4 18h16" 
                />
                <path 
                  v-else
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M6 18L18 6M6 6l12 12" 
                />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div 
          v-show="isMobileMenuOpen"
          class="md:hidden border-t border-gray-200 pt-4 pb-4 space-y-2"
        >
          <div class="text-sm text-gray-700 px-3 py-2">Hello, {{ store.user?.name }}</div>
          <button 
            @click="showDashboard = true; isMobileMenuOpen = false"
            class="w-full text-left bg-stone-600 hover:bg-stone-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Dashboard</span>
          </button>
          <button 
            @click="handleLogout; isMobileMenuOpen = false"
            class="w-full text-left text-stone-500 hover:text-stone-700 hover:bg-stone-50 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
          >
            Logout
          </button>
        </div>
      </div>
    </header>

    <!-- Header Toggle Tab (absolutely positioned) -->
    <div 
      class="absolute left-1/2 transform -translate-x-1/2 z-30 transition-all duration-500"
      :style="{ top: isHeaderExpanded ? '100%' : '0px' }"
    >
      <button
        @click="toggleHeader"
        class="bg-white hover:bg-gray-50 text-stone-600 hover:text-stone-800 px-4 py-2 rounded-b-lg border-l border-r border-b border-gray-200 shadow-sm transition-colors duration-200"
      >
        <svg 
          class="w-5 h-5 transition-transform duration-300" 
          :class="{ 'rotate-180': !isHeaderExpanded }"
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 14l5-5 5 5" />
        </svg>
      </button>
    </div>

    <!-- Main Content -->
    <main class="flex-1 flex overflow-hidden relative">
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

        <!-- Back Button (absolutely positioned) -->
        <div 
          v-show="selectedMode"
          class="absolute top-4 left-4 z-20"
        >
          <button
            @click="goBack"
            class="p-3 text-stone-500 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors duration-200 shadow-sm"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
        </div>

        <!-- Left Half: Recording Controls (shown when mode is selected) -->
        <div 
          v-show="selectedMode"
          class="flex flex-col h-full transition-all duration-500"
          :class="{ 
            'opacity-100 transform scale-100': selectedMode,
            'opacity-0 transform scale-0 pointer-events-none': !selectedMode,
            'w-1/2': isDrawerExpanded && !isMobileView,
            'w-full': !isDrawerExpanded
          }"
        >
          <RecordingControls 
            v-if="selectedMode"
            :recording-mode="selectedMode"
            @recording-complete="onRecordingComplete"
          />
        </div>

        <!-- Drawer Toggle Button (absolutely positioned) -->
        <div 
          v-show="selectedMode && !isMobileView"
          class="absolute top-1/2 z-20 transition-all duration-500"
          :class="{
            'opacity-100': selectedMode,
            'opacity-0 pointer-events-none': !selectedMode,
            'right-1/2 transform -translate-y-1/2': isDrawerExpanded,
            'right-0 transform -translate-y-1/2': !isDrawerExpanded
          }"
        >
          <button
            @click="toggleDrawer"
            class="bg-white hover:bg-gray-50 text-stone-600 hover:text-stone-800 px-2 py-8 rounded-l-lg border-l border-t border-b border-gray-200 shadow-sm transition-colors duration-200"
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

        <!-- Mobile Drawer Toggle Button (floating) -->
        <div 
          v-show="selectedMode && isMobileView"
          class="fixed top-1/2 right-4 z-50 transform -translate-y-1/2"
        >
          <button
            @click="toggleDrawer"
            class="p-3 bg-stone-600 hover:bg-stone-700 text-white rounded-full shadow-lg transition-all duration-200"
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
          class="h-full transition-all duration-500 bg-stone-50 shadow-lg flex flex-col"
          :class="{ 
            'opacity-100 transform scale-100': selectedMode && isDrawerExpanded,
            'opacity-0 transform scale-x-0 pointer-events-none': !selectedMode || (!isDrawerExpanded && !isMobileView),
            // Desktop styles
            'w-1/2 pl-0': isDrawerExpanded && !isMobileView,
            'w-0': !isDrawerExpanded && !isMobileView,
            // Mobile styles - floating overlay
            'fixed inset-0 top-16 w-full z-40': isMobileView,
            'transform translate-x-full': isMobileView && !isDrawerExpanded,
            'transform translate-x-0': isMobileView && isDrawerExpanded
          }"
          :style="isMobileView ? {} : { height: 'calc(100vh - 69px)' }"
        >
          <RecordingsDrawer
            v-if="selectedMode"
            :recording-mode="selectedMode"
            :is-mobile="isMobileView"
            @close="toggleDrawer"
            ref="recordingsDrawer"
          />
        </div>
      </div>
    </main>

    <!-- Dashboard Modal -->
    <DashboardModal 
      v-if="showDashboard"
      @close="showDashboard = false"
    />

  </div>
</template>

<script setup>
import { ref, onUnmounted, computed, onMounted } from 'vue';
import { useMainStore } from '../stores/main';
import { useRouter } from 'vue-router';
import DashboardModal from './DashboardModal.vue';
import RecordingControls from './RecordingControls.vue';
import RecordingsDrawer from './RecordingsDrawer.vue';

const store = useMainStore();
const router = useRouter();
const showDashboard = ref(false);
const showSingleCord = ref(false);
const showLoopedCord = ref(false);

// Mobile state
const isMobileMenuOpen = ref(false);
const windowWidth = ref(window.innerWidth);

// Computed for mobile view detection
const isMobileView = computed(() => windowWidth.value < 768);

// Recording mode selection (for controlling which component is shown)
const selectedMode = ref(null); // 'single' or 'looped'

// Drawer state
const isDrawerExpanded = ref(true);
const isHeaderCollapsed = ref(false);

// Header computed property for clearer template logic
const isHeaderExpanded = computed(() => !isHeaderCollapsed.value);

// Refs to child components
const recordingsDrawer = ref(null);

// Handle window resize
const handleResize = () => {
  windowWidth.value = window.innerWidth;
  // Close mobile menu on resize to desktop
  if (!isMobileView.value) {
    isMobileMenuOpen.value = false;
  }
  // Close drawer on mobile by default
  if (isMobileView.value && !selectedMode.value) {
    isDrawerExpanded.value = false;
  }
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
  // Set initial drawer state based on screen size
  if (isMobileView.value) {
    isDrawerExpanded.value = false;
  }
});

const selectRecordingMode = (mode) => {
  selectedMode.value = mode;
  // Close mobile menu if open
  isMobileMenuOpen.value = false;
  // On mobile, close drawer by default when selecting mode
  if (isMobileView.value) {
    isDrawerExpanded.value = false;
  }
};

const goBack = () => {
  // Clear selected mode to return to main menu
  selectedMode.value = null;
  // Reset drawer state
  isDrawerExpanded.value = !isMobileView.value;
};

const toggleDrawer = () => {
  isDrawerExpanded.value = !isDrawerExpanded.value;
};

const toggleHeader = () => {
  isHeaderCollapsed.value = !isHeaderCollapsed.value;
};

const onRecordingComplete = (recordingData) => {
  // Refresh the recordings drawer to show the new recording
  if (recordingsDrawer.value) {
    recordingsDrawer.value.refreshData();
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
  // Remove event listeners
  window.removeEventListener('resize', handleResize);
});
</script>
