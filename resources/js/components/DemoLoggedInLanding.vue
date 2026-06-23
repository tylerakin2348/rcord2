<template>
  <div class="min-h-screen bg-stone-100 flex flex-col relative">
    <Header :showHeaderToggle="false" />
    <main class="flex-1 flex overflow-hidden relative">
      <div class="flex w-full items-stretch">
        <!-- Left Half: Recording Controls -->
        <div 
          class="flex flex-col h-full transition-all duration-200"
          :class="{ 'w-full': !isDrawerExpanded }"
          :style="{ width: isDrawerExpanded && !isMobileView ? `${100 - constrainedDrawerWidth}%` : '100%' }"
        >
          <RecordingControls 
            recording-mode="looped"
            @recording-complete="onDemoRecordingComplete"
            :useIndexedDb="true"
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
        <!-- Right Half: Recordings Drawer -->
        <div 
          class="h-full bg-stone-50 shadow-lg flex flex-col transition-all duration-200"
          :class="{ 
            'opacity-0 transform scale-x-0 pointer-events-none': !isDrawerExpanded && !isMobileView,
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
            :useIndexedDb="true"
          />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Header from './Header.vue';
import RecordingControls from './RecordingControls.vue';
import RecordingsDrawer from './RecordingsDrawer.vue';

// Mobile state
const windowWidth = ref(window.innerWidth);
const isMobileView = computed(() => windowWidth.value < 768);

// Drawer state
const isDrawerExpanded = ref(true);
const drawerWidth = ref(30); // Default 30%
const isDragging = ref(false);
const dragStartX = ref(0);
const dragStartWidth = ref(0);
const constrainedDrawerWidth = computed(() => Math.max(0, Math.min(48, drawerWidth.value)));
const recordingsDrawer = ref(null);

const handleResize = () => {
  windowWidth.value = window.innerWidth;
  if (isMobileView.value) isDrawerExpanded.value = false;
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
  if (isMobileView.value) isDrawerExpanded.value = false;
});
onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});

const toggleDrawer = () => {
  isDrawerExpanded.value = !isDrawerExpanded.value;
};

const onDemoRecordingComplete = () => {
  if (recordingsDrawer.value) {
    recordingsDrawer.value.refreshData();
  }
};
</script>
