<template>
  <div class="shrink-0 relative">
    <div
      class="grid transition-[grid-template-rows] duration-[400ms] ease-[cubic-bezier(0.4,0,0.2,1)]"
      :style="{ gridTemplateRows: isHeaderExpanded ? '1fr' : '0fr' }"
    >
      <div class="overflow-hidden min-h-0">
        <header ref="headerEl" class="bg-white shadow-sm border-b border-gray-200 z-10 relative">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
              <h1 class="text-2xl font-bold text-stone-700">ReCord</h1>
              <!-- Desktop Navigation -->
              <div class="hidden md:flex items-center relative">
                <span class="text-sm text-gray-700 mr-2">Hello, {{ userName }}</span>
                <div class="relative">
                  <button
                    ref="desktopMenuButton"
                    type="button"
                    class="p-2 text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-lg transition-colors duration-200"
                    aria-haspopup="menu"
                    :aria-expanded="showDesktopMenu"
                    @click="toggleDesktopMenu"
                  >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                  </button>
                </div>
              </div>
              <!-- Mobile Hamburger -->
              <div class="md:hidden">
                <button
                  type="button"
                  class="p-2 text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-lg transition-colors duration-200"
                  @click="toggleMobileMenu"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </header>
      </div>
    </div>

    <!-- Record / Library switcher — always visible, outside collapsible header -->
    <div
      v-if="showViewSwitcher"
      ref="viewSwitcherEl"
      class="bg-white border-b border-stone-200 px-4 py-2"
    >
      <div class="max-w-xs mx-auto">
        <div
          class="flex rounded-lg border border-stone-200 bg-stone-100 p-0.5"
          role="tablist"
          aria-label="Main view"
        >
          <button
            type="button"
            role="tab"
            class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-150"
            :class="activeView === 'record'
              ? 'bg-white text-red-600 shadow-sm'
              : 'text-stone-500 hover:text-stone-700'"
            :aria-selected="activeView === 'record'"
            @click="setActiveView('record')"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
            Record
          </button>
          <button
            type="button"
            role="tab"
            class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-150"
            :class="activeView === 'library'
              ? 'bg-white text-stone-800 shadow-sm'
              : 'text-stone-500 hover:text-stone-700'"
            :aria-selected="activeView === 'library'"
            @click="setActiveView('library')"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Library
          </button>
        </div>
      </div>
    </div>

    <!-- Header Toggle Tab (conditionally visible) -->
    <div
      v-if="showHeaderToggle"
      class="fixed left-1/2 -translate-x-1/2 z-50 transition-[top] duration-[400ms] ease-[cubic-bezier(0.4,0,0.2,1)]"
      :style="{ top: headerToggleTop }"
    >
      <button
        type="button"
        class="bg-white hover:bg-gray-50 text-stone-600 hover:text-stone-800 px-4 py-2 rounded-b-lg border-l border-r border-b border-gray-200 shadow-lg transition-all duration-200 hover:shadow-xl hover:scale-105"
        @click="toggleHeader"
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

    <!-- Desktop menu — teleported so it isn't clipped by overflow-hidden -->
    <Teleport to="body">
      <div
        v-if="showDesktopMenu"
        class="fixed inset-0 z-[99998]"
        @click="showDesktopMenu = false"
      />
      <div
        v-if="showDesktopMenu"
        ref="desktopMenuPanel"
        class="fixed z-[99999] w-56 bg-white border border-gray-200 rounded-lg shadow-lg flex flex-col py-2"
        :style="desktopMenuStyle"
        role="menu"
      >
        <button
          type="button"
          role="menuitem"
          class="flex items-center gap-2 px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left"
          @click="goToAccount"
        >
          <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span>Account</span>
        </button>
        <button
          v-if="canManageSystemInfo"
          type="button"
          role="menuitem"
          class="flex items-center gap-2 px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left"
          @click="goToSystemInfo"
        >
          <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
          </svg>
          <span>System Info</span>
        </button>
        <button
          type="button"
          role="menuitem"
          class="px-4 py-2 text-stone-700 hover:bg-stone-100 transition-colors duration-200 text-left"
          @click="handleLogoutAndClose"
        >
          Logout
        </button>
      </div>
    </Teleport>

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
        type="button"
        class="absolute top-4 right-4 z-[100000] bg-stone-800 text-white rounded-full p-2 shadow-lg hover:bg-stone-700 focus:outline-none"
        aria-label="Close menu"
        @click="toggleMobileMenu"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <div class="flex flex-col items-center justify-center w-full flex-1 space-y-6 mt-20">
        <span class="text-lg text-white font-semibold text-center">Hello, {{ userName }}</span>
        <button
          type="button"
          class="w-3/4 text-center bg-stone-700 hover:bg-stone-800 text-white px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md flex items-center gap-2"
          @click="goToAccountAndCloseMobile"
        >
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span>Account</span>
        </button>
        <button
          v-if="canManageSystemInfo"
          type="button"
          class="w-3/4 text-center bg-stone-800 hover:bg-stone-900 text-stone-200 px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md flex items-center gap-2"
          @click="goToSystemInfoAndCloseMobile"
        >
          <svg class="w-5 h-5 text-stone-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
          </svg>
          <span>System Info</span>
        </button>
        <button
          type="button"
          class="w-3/4 text-center bg-stone-700 hover:bg-stone-800 text-stone-200 px-4 py-3 rounded-xl text-base font-semibold transition-colors duration-200 shadow-md"
          @click="handleLogoutAndCloseMobile"
        >
          Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useMainStore } from '../stores/main';

const props = defineProps({
  userName: {
    type: String,
    required: true,
  },
  showHeaderToggle: {
    type: Boolean,
    default: false,
  },
  showViewSwitcher: {
    type: Boolean,
    default: false,
  },
  activeView: {
    type: String,
    default: 'record',
    validator: (value) => ['record', 'library'].includes(value),
  },
});

const emit = defineEmits(['header-toggle', 'update:activeView']);

const store = useMainStore();
const router = useRouter();
const showDesktopMenu = ref(false);
const isMobileMenuOpen = ref(false);
const isHeaderCollapsed = ref(false);
const isHeaderExpanded = ref(true);
const headerEl = ref(null);
const viewSwitcherEl = ref(null);
const desktopMenuButton = ref(null);
const headerHeight = ref(69);
const viewSwitcherHeight = ref(0);
const desktopMenuStyle = ref({ top: '0px', right: '0px' });
let headerResizeObserver = null;

const headerToggleTop = computed(() => {
  const headerPart = isHeaderExpanded.value ? headerHeight.value : 0;
  const switcherPart = props.showViewSwitcher ? viewSwitcherHeight.value : 0;
  return `${headerPart + switcherPart}px`;
});

const measureHeader = () => {
  if (headerEl.value) {
    headerHeight.value = headerEl.value.offsetHeight;
  }
};

const measureViewSwitcher = () => {
  viewSwitcherHeight.value = viewSwitcherEl.value?.offsetHeight ?? 0;
};

const updateDesktopMenuPosition = () => {
  if (!desktopMenuButton.value) return;
  const rect = desktopMenuButton.value.getBoundingClientRect();
  desktopMenuStyle.value = {
    top: `${rect.bottom + 8}px`,
    right: `${window.innerWidth - rect.right}px`,
  };
};

const toggleDesktopMenu = async () => {
  showDesktopMenu.value = !showDesktopMenu.value;
  if (showDesktopMenu.value) {
    await nextTick();
    updateDesktopMenuPosition();
  }
};

const toggleHeader = () => {
  isHeaderCollapsed.value = !isHeaderCollapsed.value;
  isHeaderExpanded.value = !isHeaderCollapsed.value;
  emit('header-toggle', isHeaderExpanded.value);
};

const setActiveView = (view) => {
  emit('update:activeView', view);
};

onMounted(() => {
  nextTick(() => {
    measureHeader();
    measureViewSwitcher();
  });
  if (headerEl.value) {
    headerResizeObserver = new ResizeObserver(() => {
      measureHeader();
      measureViewSwitcher();
    });
    headerResizeObserver.observe(headerEl.value);
  }
  nextTick(() => {
    if (viewSwitcherEl.value && headerResizeObserver) {
      headerResizeObserver.observe(viewSwitcherEl.value);
    }
  });
  window.addEventListener('resize', updateDesktopMenuPosition);
  window.addEventListener('scroll', updateDesktopMenuPosition, true);
});

onUnmounted(() => {
  headerResizeObserver?.disconnect();
  window.removeEventListener('resize', updateDesktopMenuPosition);
  window.removeEventListener('scroll', updateDesktopMenuPosition, true);
});

watch(isHeaderExpanded, () => {
  nextTick(measureHeader);
});

watch(() => props.showViewSwitcher, () => {
  nextTick(() => {
    measureViewSwitcher();
    if (viewSwitcherEl.value && headerResizeObserver) {
      headerResizeObserver.observe(viewSwitcherEl.value);
    }
  });
});

watch(showDesktopMenu, (open) => {
  if (open) {
    nextTick(updateDesktopMenuPosition);
  }
});

const canManageSystemInfo = computed(() =>
  store.user && store.user.permissions && store.user.permissions.includes('manage_system_info')
);

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
