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
            <h1 class="text-2xl font-bold text-stone-700">
              ReCord
            </h1>
          </div>
          
          <!-- Desktop Navigation: Single Dropdown -->
          <div class="hidden md:flex items-center relative">
            <span class="text-sm text-gray-700 mr-2">Hello, {{ store.user?.name }}</span>
            <div class="relative">
              <button @click="showDesktopMenu = !showDesktopMenu" class="p-2 text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-lg transition-colors duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <div v-if="showDesktopMenu" class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-[99999] flex flex-col py-2">
                <button @click="goToProfile" class="flex items-center gap-2 px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left">
                  <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span>Profile</span>
                </button>
                <button @click="goToSystemInfo" class="px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left">System Info</button>
                <button @click="handleLogoutAndClose" class="px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left">Logout</button>
              </div>
            </div>
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
      </div>
    </header>
    <!-- Mobile Menu Dropdown (moved outside header/main for full overlay) -->
    <div 
      v-show="isMobileMenuOpen"
      class="md:hidden fixed left-0 top-0 w-full h-full z-[99999] bg-stone-900 bg-opacity-95 border-t border-gray-200 pt-4 pb-4 overflow-y-auto transition-colors duration-500 flex flex-col"
      style="height: 100vh;"
    >
      <!-- ReCord logo top left -->
      <div class="absolute left-4 top-4 z-[100001]">
        <h1 class="text-2xl font-bold text-white">ReCord</h1>
      </div>
      <!-- Close (X) Button -->
      <button
        @click="isMobileMenuOpen = false"
        class="absolute top-4 right-4 z-[100000] bg-stone-800 text-white rounded-full p-2 shadow-lg hover:bg-stone-700 focus:outline-none"
        aria-label="Close menu"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <!-- Centered user profile above menu items -->
      <div class="flex flex-col items-center justify-center w-full flex-1 space-y-6 mt-20">
        <span class="text-lg text-white font-semibold text-center">Hello, {{ store.user?.name }}</span>
        <router-link 
          to="/profile"
          class="w-3/4 text-center bg-stone-700 hover:bg-stone-800 text-white px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md"
        >
          <svg class="w-5 h-5 inline-block mr-2 align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="align-middle">Profile</span>
        </router-link>
        <router-link 
          to="/system-info"
          class="w-3/4 text-center bg-stone-800 hover:bg-stone-900 text-stone-200 px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md"
        >
          System Info
        </router-link>
        <button 
          @click="handleLogout; isMobileMenuOpen = false"
          class="w-3/4 text-center bg-stone-700 hover:bg-stone-800 text-stone-200 px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md"
        >
          Logout
        </button>
      </div>
    </div>

    <!-- Header Toggle Tab (absolutely positioned) -->
    <div 
      class="fixed left-1/2 transform -translate-x-1/2 z-50 transition-all duration-500"
      :style="{ top: isHeaderExpanded ? '70px' : '0px' }"
    >
      <button
        @click="toggleHeader"
        class="bg-white hover:bg-gray-50 text-stone-600 hover:text-stone-800 px-4 py-2 rounded-b-lg border-l border-r border-b border-gray-200 shadow-lg transition-all duration-200 hover:shadow-xl hover:scale-105"
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
      <div class="flex w-full items-stretch">
        <!-- Left Half: Recording Controls -->
        <div 
          class="flex flex-col h-full transition-all duration-200"
          :class="{ 
            'w-full': !isDrawerExpanded
          }"
          :style="{ 
            width: isDrawerExpanded && !isMobileView ? `${100 - constrainedDrawerWidth}%` : '100%'
          }"
        >
          <RecordingControls 
            recording-mode="looped"
            @recording-complete="onRecordingComplete"
          />
        </div>

        <!-- Drawer Toggle Button (absolutely positioned) -->
        <div 
          v-show="!isMobileView"
          class="absolute top-1/2 z-20"
          :style="{
            right: isDrawerExpanded ? `${constrainedDrawerWidth}%` : '0px',
            transform: 'translateY(-50%)'
          }"
        >
          <button
            @click="toggleDrawer"
            class="bg-white hover:bg-gray-50 text-stone-600 hover:text-stone-800 px-2 py-4 rounded-l-lg border-l border-t border-b border-gray-200 shadow-sm transition-colors duration-200"
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

        <!-- Resize Handle -->
        <div 
          v-show="!isMobileView && isDrawerExpanded"
          class="absolute top-0 z-30 h-full cursor-col-resize group"
          :style="{
            right: `${constrainedDrawerWidth}%`,
            transform: 'translateX(50%)'
          }"
          @mousedown="startDragging"
        >
          <div 
            class="w-1 h-full bg-transparent group-hover:bg-stone-300 transition-colors duration-200"
            :class="{ 'bg-stone-400': isDragging }"
          ></div>
          <div class="absolute inset-y-0 -left-1 -right-1"></div> <!-- Invisible wider hit area -->
        </div>

        <!-- Mobile Drawer Toggle Button (floating) -->
        <div 
          v-show="isMobileView"
          class="fixed top-1/2 right-0 z-50 transform -translate-y-1/2"
        >
          <button
            @click="toggleDrawer"
            class="bg-white hover:bg-gray-50 text-stone-600 hover:text-stone-800 px-2 py-4 rounded-l-lg border-l border-t border-b border-gray-200 shadow-sm transition-colors duration-200"
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
          class="h-full bg-stone-50 shadow-lg flex flex-col transition-all duration-200"
          :class="{ 
            // Desktop styles
            'opacity-0 transform scale-x-0 pointer-events-none': !isDrawerExpanded && !isMobileView,
            // Mobile styles - floating overlay
            'fixed top-19 w-full z-40': isMobileView,
            'transform translate-x-full': isMobileView && !isDrawerExpanded,
            'transform translate-x-0': isMobileView && isDrawerExpanded
          }"
          :style="isMobileView
            ? (isDrawerExpanded ? { width: '92vw', maxWidth: '92vw' } : {})
            : { width: isDrawerExpanded ? `${constrainedDrawerWidth}%` : '0%', height: 'calc(100vh - 69px)' }"
        >
          <RecordingsDrawer
            recording-mode="looped"
            :is-mobile="isMobileView"
            @close="toggleDrawer"
            ref="recordingsDrawer"
          />
        </div>
      </div>
    </main>

    <!-- Dashboard Modal -->
    <!-- Removed dashboard modal - now using separate routes -->

  </div>
</template>

<script setup>
import { ref, onUnmounted, computed, onMounted } from 'vue';
import { useMainStore } from '../stores/main';
import { useRouter } from 'vue-router';
import RecordingControls from './RecordingControls.vue';
import RecordingsDrawer from './RecordingsDrawer.vue';

const store = useMainStore();
const router = useRouter();

// Mobile state
const isMobileMenuOpen = ref(false);
const windowWidth = ref(window.innerWidth);

// Computed for mobile view detection
const isMobileView = computed(() => windowWidth.value < 768);

// Drawer state
const isDrawerExpanded = ref(true);
const isHeaderCollapsed = ref(false);

// Draggable sidebar state
const drawerWidth = ref(30); // Default 30%
const isDragging = ref(false);
const dragStartX = ref(0);
const dragStartWidth = ref(0);

// Computed drawer width with constraints
const constrainedDrawerWidth = computed(() => {
  return Math.max(0, Math.min(48, drawerWidth.value));
});

// Header computed property for clearer template logic
const isHeaderExpanded = computed(() => !isHeaderCollapsed.value);

// Refs to child components
const recordingsDrawer = ref(null);
const showDesktopMenu = ref(false);

const goToProfile = () => {
  router.push('/profile');
  showDesktopMenu.value = false;
};
const goToSystemInfo = () => {
  router.push('/system-info');
  showDesktopMenu.value = false;
};
const handleLogoutAndClose = async () => {
  await handleLogout();
  showDesktopMenu.value = false;
};

// Handle window resize
const handleResize = () => {
  windowWidth.value = window.innerWidth;
  // Close mobile menu on resize to desktop
  if (!isMobileView.value) {
    isMobileMenuOpen.value = false;
  }
  // Close drawer on mobile by default
  if (isMobileView.value) {
    isDrawerExpanded.value = false;
  }
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
  // Set initial drawer state based on screen size
  if (isMobileView.value) {
    isDrawerExpanded.value = false;
  }
  
  // Add mouse event listeners for dragging
  document.addEventListener('mousemove', handleMouseMove);
  document.addEventListener('mouseup', handleMouseUp);
});

const toggleDrawer = () => {
  isDrawerExpanded.value = !isDrawerExpanded.value;
};

const toggleHeader = () => {
  isHeaderCollapsed.value = !isHeaderCollapsed.value;
};

// Draggable functionality
const startDragging = (event) => {
  if (isMobileView.value) return;
  
  isDragging.value = true;
  dragStartX.value = event.clientX;
  dragStartWidth.value = drawerWidth.value;
  event.preventDefault();
};

const handleMouseMove = (event) => {
  if (!isDragging.value || isMobileView.value) return;
  
  const deltaX = dragStartX.value - event.clientX;
  const windowWidthPx = windowWidth.value;
  const deltaPercent = (deltaX / windowWidthPx) * 100;
  
  drawerWidth.value = dragStartWidth.value + deltaPercent;
  
  // Auto-collapse if dragged to less than 5%
  if (constrainedDrawerWidth.value < 5) {
    isDrawerExpanded.value = false;
    drawerWidth.value = 30; // Reset to default
  } else if (!isDrawerExpanded.value && constrainedDrawerWidth.value >= 5) {
    isDrawerExpanded.value = true;
  }
};

const handleMouseUp = () => {
  if (isDragging.value) {
    isDragging.value = false;
  }
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
  document.removeEventListener('mousemove', handleMouseMove);
  document.removeEventListener('mouseup', handleMouseUp);
});
</script>
