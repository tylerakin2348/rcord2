<template>
  <div class="shrink-0 bg-white border-b border-stone-200/80">
    <!-- Collapsible account row -->
    <div
      class="grid transition-[grid-template-rows] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]"
      :style="{ gridTemplateRows: isHeaderExpanded ? '1fr' : '0fr' }"
    >
      <div class="overflow-hidden min-h-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-3.5">
            <h1 class="text-lg font-semibold tracking-tight text-stone-800">ReCord</h1>

            <div class="hidden md:flex items-center gap-3">
              <span class="text-sm text-stone-500">{{ userName }}</span>
              <button
                ref="desktopMenuButton"
                type="button"
                class="p-1.5 text-stone-500 hover:text-stone-800 rounded-md transition-colors"
                aria-haspopup="menu"
                :aria-expanded="showDesktopMenu"
                @click="toggleDesktopMenu"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
            </div>

            <div class="md:hidden">
              <button
                type="button"
                class="p-1.5 text-stone-500 hover:text-stone-800 rounded-md transition-colors"
                @click="toggleMobileMenu"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Persistent nav row -->
    <div
      v-if="showViewSwitcher"
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-11 border-t border-stone-100"
      :class="{ 'border-t-0': !isHeaderExpanded }"
    >
      <button
        v-if="showHeaderToggle"
        type="button"
        class="p-1.5 -ml-1.5 text-stone-400 hover:text-stone-600 rounded-md transition-colors shrink-0"
        :aria-label="isHeaderExpanded ? 'Hide header' : 'Show header'"
        @click="toggleHeader"
      >
        <svg
          class="w-4 h-4 transition-transform duration-300"
          :class="{ 'rotate-180': !isHeaderExpanded }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
        </svg>
      </button>
      <div v-else class="w-7 shrink-0" />

      <nav class="flex items-center gap-7" role="tablist" aria-label="Main view">
        <button
          type="button"
          role="tab"
          class="relative py-2 text-sm transition-colors duration-200"
          :class="activeView === 'record'
            ? 'text-stone-900 font-medium'
            : 'text-stone-400 hover:text-stone-600 font-normal'"
          :aria-selected="activeView === 'record'"
          @click="setActiveView('record')"
        >
          Record
          <span
            class="absolute -bottom-px left-0 right-0 h-px rounded-full transition-opacity duration-200"
            :class="activeView === 'record' ? 'bg-stone-800 opacity-100' : 'opacity-0'"
          />
        </button>
        <button
          type="button"
          role="tab"
          class="relative py-2 text-sm transition-colors duration-200"
          :class="activeView === 'library'
            ? 'text-stone-900 font-medium'
            : 'text-stone-400 hover:text-stone-600 font-normal'"
          :aria-selected="activeView === 'library'"
          @click="setActiveView('library')"
        >
          Library
          <span
            class="absolute -bottom-px left-0 right-0 h-px rounded-full transition-opacity duration-200"
            :class="activeView === 'library' ? 'bg-stone-800 opacity-100' : 'opacity-0'"
          />
        </button>
      </nav>

      <!-- Balance collapse button; show menu when header collapsed on desktop -->
      <div class="w-7 shrink-0 flex justify-end">
        <button
          v-if="showHeaderToggle && !isHeaderExpanded"
          ref="collapsedMenuButton"
          type="button"
          class="hidden md:flex p-1.5 text-stone-400 hover:text-stone-600 rounded-md transition-colors"
          aria-haspopup="menu"
          :aria-expanded="showDesktopMenu"
          @click="toggleDesktopMenu"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Desktop menu -->
    <Teleport to="body">
      <div
        v-if="showDesktopMenu"
        class="fixed inset-0 z-[99998]"
        @click="showDesktopMenu = false"
      />
      <div
        v-if="showDesktopMenu"
        class="fixed z-[99999] w-52 bg-white border border-stone-200/80 rounded-xl shadow-lg shadow-stone-900/5 flex flex-col py-1.5"
        :style="desktopMenuStyle"
        role="menu"
      >
        <button
          type="button"
          role="menuitem"
          class="flex items-center gap-2.5 mx-1.5 px-3 py-2 text-sm text-stone-700 hover:bg-stone-50 rounded-lg transition-colors text-left"
          @click="goToAccount"
        >
          Account
        </button>
        <button
          v-if="canManageSystemInfo"
          type="button"
          role="menuitem"
          class="flex items-center gap-2.5 mx-1.5 px-3 py-2 text-sm text-stone-700 hover:bg-stone-50 rounded-lg transition-colors text-left"
          @click="goToSystemInfo"
        >
          System Info
        </button>
        <div class="my-1 mx-3 border-t border-stone-100" />
        <button
          type="button"
          role="menuitem"
          class="mx-1.5 px-3 py-2 text-sm text-stone-600 hover:bg-stone-50 rounded-lg transition-colors text-left"
          @click="handleLogoutAndClose"
        >
          Log out
        </button>
      </div>
    </Teleport>

    <!-- Mobile menu -->
    <div
      v-show="isMobileMenuOpen"
      class="md:hidden fixed inset-0 z-[99999] bg-stone-900/95 flex flex-col"
    >
      <div class="flex items-center justify-between px-4 py-3.5">
        <span class="text-lg font-semibold text-white tracking-tight">ReCord</span>
        <button
          type="button"
          class="p-2 text-stone-300 hover:text-white rounded-md transition-colors"
          aria-label="Close menu"
          @click="toggleMobileMenu"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="flex flex-col px-4 pt-6 gap-1">
        <p class="text-sm text-stone-400 mb-4 px-3">{{ userName }}</p>
        <button
          type="button"
          class="w-full text-left px-3 py-3 text-base text-white hover:bg-white/5 rounded-lg transition-colors"
          @click="goToAccountAndCloseMobile"
        >
          Account
        </button>
        <button
          v-if="canManageSystemInfo"
          type="button"
          class="w-full text-left px-3 py-3 text-base text-white hover:bg-white/5 rounded-lg transition-colors"
          @click="goToSystemInfoAndCloseMobile"
        >
          System Info
        </button>
        <button
          type="button"
          class="w-full text-left px-3 py-3 text-base text-stone-400 hover:bg-white/5 rounded-lg transition-colors mt-2"
          @click="handleLogoutAndCloseMobile"
        >
          Log out
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
const desktopMenuButton = ref(null);
const collapsedMenuButton = ref(null);
const desktopMenuStyle = ref({ top: '0px', right: '0px' });

const menuAnchor = computed(() =>
  (!isHeaderExpanded.value && collapsedMenuButton.value)
    ? collapsedMenuButton.value
    : desktopMenuButton.value
);

const updateDesktopMenuPosition = () => {
  if (!menuAnchor.value) return;

  const rect = menuAnchor.value.getBoundingClientRect();
  desktopMenuStyle.value = {
    top: `${rect.bottom + 6}px`,
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
  window.addEventListener('resize', updateDesktopMenuPosition);
  window.addEventListener('scroll', updateDesktopMenuPosition, true);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateDesktopMenuPosition);
  window.removeEventListener('scroll', updateDesktopMenuPosition, true);
});

watch(isHeaderExpanded, () => {
  if (showDesktopMenu.value) {
    nextTick(updateDesktopMenuPosition);
  }
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
