<template>
  <div class="min-h-screen bg-stone-100 flex flex-col relative">
    <Header :showHeaderToggle="false" />
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

      <nav class="shrink-0 bg-white border-t border-stone-200">
        <div class="flex max-w-lg mx-auto">
          <button
            type="button"
            class="flex-1 flex flex-col items-center gap-1 py-3 transition-colors duration-200"
            :class="activeView === 'record' ? 'text-red-600' : 'text-stone-400 hover:text-stone-600'"
            @click="activeView = 'record'"
          >
            <div class="p-1.5 rounded-full" :class="activeView === 'record' ? 'bg-red-50' : ''">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
            </div>
            <span class="text-xs font-medium">Record</span>
          </button>
          <button
            type="button"
            class="flex-1 flex flex-col items-center gap-1 py-3 transition-colors duration-200"
            :class="activeView === 'library' ? 'text-stone-800' : 'text-stone-400 hover:text-stone-600'"
            @click="activeView = 'library'"
          >
            <div class="p-1.5 rounded-full" :class="activeView === 'library' ? 'bg-stone-100' : ''">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </div>
            <span class="text-xs font-medium">Library</span>
          </button>
        </div>
      </nav>
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
