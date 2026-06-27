<template>
  <div class="min-h-screen bg-stone-100 flex flex-col relative">
    <Header :showHeaderToggle="false" />

    <div class="bg-white border-b border-stone-200/80">
      <nav
        class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-center gap-7 h-11"
        role="tablist"
        aria-label="Main view"
      >
        <button
          type="button"
          role="tab"
          class="relative py-2 text-sm transition-colors duration-200"
          :class="activeView === 'record'
            ? 'text-stone-900 font-medium'
            : 'text-stone-400 hover:text-stone-600 font-normal'"
          :aria-selected="activeView === 'record'"
          @click="activeView = 'record'"
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
          @click="activeView = 'library'"
        >
          Library
          <span
            class="absolute -bottom-px left-0 right-0 h-px rounded-full transition-opacity duration-200"
            :class="activeView === 'library' ? 'bg-stone-800 opacity-100' : 'opacity-0'"
          />
        </button>
      </nav>
    </div>

    <main class="flex-1 min-h-0 flex flex-col overflow-hidden">
      <div class="flex-1 min-h-0 overflow-hidden">
        <RecordingControls
          v-show="activeView === 'record'"
          recording-mode="looped"
          @recording-complete="onDemoRecordingComplete"
          :useIndexedDb="true"
        />
        <RecordingsDrawer
          v-show="activeView === 'library'"
          recording-mode="looped"
          ref="recordingsDrawer"
          :useIndexedDb="true"
        />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Header from './Header.vue';
import RecordingControls from './RecordingControls.vue';
import RecordingsDrawer from './RecordingsDrawer.vue';

const activeView = ref('record');
const recordingsDrawer = ref(null);

const onDemoRecordingComplete = () => {
  if (recordingsDrawer.value) {
    recordingsDrawer.value.refreshData();
  }
};
</script>
