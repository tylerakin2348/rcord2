<template>
  <div class="h-screen bg-stone-100 flex flex-col overflow-hidden relative">
    <LoggedInHeaderNav 
      :userName="store.user?.name" 
      :showHeaderToggle="true"
    />

    <main
      class="flex-1 min-h-0 flex flex-col overflow-hidden relative transition-[flex-grow] duration-[400ms] ease-[cubic-bezier(0.4,0,0.2,1)]"
    >
      <div class="flex-1 min-h-0 overflow-hidden relative">
        <RecordingPlayback
          v-if="playbackRecording"
          class="h-full"
          ref="playbackView"
          :title="playbackRecording.title"
          :subtitle="playbackRecording.subtitle"
          :url="playbackRecording.url"
          back-label="Back to library"
          @close="stopPlayback"
          @play="isPlaybackPlaying = true"
          @pause="isPlaybackPlaying = false"
          @finish="stopPlayback"
        />
        <RecordingControls
          v-show="!playbackRecording && activeView === 'record'"
          class="h-full"
          recording-mode="looped"
          @recording-complete="onRecordingComplete"
        />
        <RecordingsDrawer
          v-show="!playbackRecording && activeView === 'library'"
          class="h-full"
          recording-mode="looped"
          @expand-recording="onExpandRecording"
          ref="recordingsDrawer"
        />
      </div>

      <nav
        v-if="!playbackRecording"
        class="shrink-0 bg-white border-t border-stone-200 safe-area-bottom"
      >
        <div class="flex max-w-lg mx-auto">
          <button
            type="button"
            class="flex-1 flex flex-col items-center gap-1 py-3 transition-colors duration-200"
            :class="activeView === 'record'
              ? 'text-red-600'
              : 'text-stone-400 hover:text-stone-600'"
            @click="activeView = 'record'"
          >
            <div
              class="p-1.5 rounded-full transition-colors"
              :class="activeView === 'record' ? 'bg-red-50' : ''"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
            </div>
            <span class="text-xs font-medium">Record</span>
          </button>

          <button
            type="button"
            class="flex-1 flex flex-col items-center gap-1 py-3 transition-colors duration-200"
            :class="activeView === 'library'
              ? 'text-stone-800'
              : 'text-stone-400 hover:text-stone-600'"
            @click="activeView = 'library'"
          >
            <div
              class="p-1.5 rounded-full transition-colors"
              :class="activeView === 'library' ? 'bg-stone-100' : ''"
            >
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
import { ref, onUnmounted } from 'vue';
import { useMainStore } from '../stores/main';
import RecordingControls from './RecordingControls.vue';
import RecordingPlayback from './RecordingPlayback.vue';
import RecordingsDrawer from './RecordingsDrawer.vue';
import LoggedInHeaderNav from './LoggedInHeaderNav.vue';

const store = useMainStore();

const activeView = ref('record');

const recordingsDrawer = ref(null);
const playbackView = ref(null);
const playbackRecording = ref(null);
const playbackBlobUrl = ref(null);
const isPlaybackPlaying = ref(false);

const onRecordingComplete = async () => {
  if (recordingsDrawer.value) {
    await recordingsDrawer.value.refreshData();
  }
};

const stopPlayback = () => {
  playbackView.value?.stop();
  playbackRecording.value = null;
  isPlaybackPlaying.value = false;
  activeView.value = 'library';

  if (playbackBlobUrl.value) {
    URL.revokeObjectURL(playbackBlobUrl.value);
    playbackBlobUrl.value = null;
  }
};

const onExpandRecording = (file) => {
  playbackView.value?.stop();

  if (playbackBlobUrl.value && playbackBlobUrl.value !== file.url) {
    URL.revokeObjectURL(playbackBlobUrl.value);
  }
  playbackBlobUrl.value = file.url?.startsWith('blob:') ? file.url : null;

  const subtitle = [
    file.formatted_duration || file.duration,
    file.formatted_file_size || file.size,
  ]
    .filter(Boolean)
    .join(' • ');

  playbackRecording.value = {
    id: file.id,
    title: file.title || file.name,
    subtitle,
    url: file.url,
  };
};

onUnmounted(() => {
  stopPlayback();
});
</script>
