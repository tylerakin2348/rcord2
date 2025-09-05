<template>
  <div>
    <transition name="slide-up">
      <header v-show="isHeaderExpanded" class="bg-white shadow-sm border-b border-gray-200 z-10 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-4">
            <h1 class="text-2xl font-bold text-stone-700">ReCord</h1>
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center relative">
              <span class="text-sm text-gray-700 mr-2">Hello, {{ userName }}</span>
              <div class="relative">
                <button @click="toggleDesktopMenuAndEmit" class="p-2 text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-lg transition-colors duration-200">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  </svg>
                </button>
                <div v-if="showDesktopMenu" class="absolute right-0 mt-4 w-56 bg-white border border-gray-200 rounded-b-lg shadow-lg flex flex-col py-2 z-[99999]">
                  <button @click="goToAccount" class="flex items-center gap-2 px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left">
                    <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Account</span>
                  </button>
                  <button v-if="canManageSystemInfo" @click="goToSystemInfo" class="flex items-center gap-2 px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left">
                    <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <span>System Info</span>
                  </button>
                  <button @click="handleLogoutAndClose" class="px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left">Logout</button>
                </div>
              </div>
            </div>
            <!-- Mobile Hamburger -->
            <div class="md:hidden">
              <button @click="toggleMobileMenu" class="p-2 text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-lg transition-colors duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </header>
    </transition>
    <!-- Header Toggle Tab (conditionally visible) -->
    <div 
      v-if="showHeaderToggle"
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
    <!-- Mobile Menu Dropdown -->
    <div 
      v-show="isMobileMenuOpen"
      class="md:hidden fixed left-0 top-0 w-full h-full z-[99999] bg-stone-900 bg-opacity-95 border-t border-gray-200 pt-4 pb-4 overflow-y-auto transition-colors duration-500 flex flex-col"
      style="height: 100vh;"
    >
      <div class="absolute left-4 top-4 z-[100001]">
        <h1 class="text-2xl font-bold text-white">ReCord</h1>
      </div>
      <button
        @click="toggleMobileMenu"
        class="absolute top-4 right-4 z-[100000] bg-stone-800 text-white rounded-full p-2 shadow-lg hover:bg-stone-700 focus:outline-none"
        aria-label="Close menu"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <div class="flex flex-col items-center justify-center w-full flex-1 space-y-6 mt-20">
        <span class="text-lg text-white font-semibold text-center">Hello, {{ userName }}</span>
  <button @click="goToAccountAndCloseMobile" class="w-3/4 text-center bg-stone-700 hover:bg-stone-800 text-white px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md flex items-center gap-2">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span>Account</span>
        </button>
        <button @click="goToSystemInfoAndCloseMobile" class="w-3/4 text-center bg-stone-800 hover:bg-stone-900 text-stone-200 px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md flex items-center gap-2">
          <svg class="w-5 h-5 text-stone-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
          </svg>
          <span>System Info</span>
        </button>
        <button @click="handleLogoutAndCloseMobile" class="w-3/4 text-center bg-stone-700 hover:bg-stone-800 text-stone-200 px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md">
          Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useMainStore } from '../stores/main';
import { computed } from 'vue';
const props = defineProps({
  userName: {
    type: String,
    required: true
  },
  showHeaderToggle: {
    type: Boolean,
    default: false
  }
});
const store = useMainStore();
const router = useRouter();
const showDesktopMenu = ref(false);
const isMobileMenuOpen = ref(false);
const isHeaderCollapsed = ref(false);
const isHeaderExpanded = ref(true);
const toggleHeader = () => {
  isHeaderCollapsed.value = !isHeaderCollapsed.value;
  isHeaderExpanded.value = !isHeaderCollapsed.value;
};

const canManageSystemInfo = computed(() =>
  store.user && store.user.permissions && store.user.permissions.includes('manage_system_info')
);

const toggleDesktopMenu = () => {
  showDesktopMenu.value = !showDesktopMenu.value;
};
const toggleDesktopMenuAndEmit = () => {
  showDesktopMenu.value = !showDesktopMenu.value;
  // No emit needed, handled internally
};
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};
const goToAccount = () => {
  router.push('/account');
  showDesktopMenu.value = false;
};
const goToSystemInfo = () => {
  router.push('/system-info');
  showDesktopMenu.value = false;
};
const handleLogoutAndClose = async () => {
  await store.logout();
  showDesktopMenu.value = false;
};
const goToAccountAndCloseMobile = () => {
  router.push('/account');
  isMobileMenuOpen.value = false;
};
const goToSystemInfoAndCloseMobile = () => {
  router.push('/system-info');
  isMobileMenuOpen.value = false;
};
const handleLogoutAndCloseMobile = async () => {
  await store.logout();
  isMobileMenuOpen.value = false;
};
</script>

<style>
.slide-up-enter-active, .slide-up-leave-active {
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
.slide-up-enter-to, .slide-up-leave-from {
  transform: translateY(0);
  opacity: 1;
}
</style>
