<template>
  <div class="h-screen bg-stone-100 flex flex-col overflow-hidden relative">
    <LoggedInHeaderNav 
      :userName="store.user?.name" 
      :showHeaderToggle="true"
      :showViewSwitcher="!playbackRecording"
      v-model:activeView="activeView"
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
