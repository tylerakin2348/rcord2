<template>
  <div class="min-h-screen bg-stone-100 flex flex-col relative">
    <Header :showHeaderToggle="false" />

    <div class="bg-white border-b border-stone-200 px-4 py-2 shrink-0">
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
            @click="activeView = 'record'"
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
            @click="activeView = 'library'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Library
          </button>
        </div>
      </div>
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
