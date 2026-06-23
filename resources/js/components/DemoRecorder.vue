<template>
  <div class="demo-recorder">
    <h2>Demo Recorder</h2>
    <button @click="startRecording" :disabled="isRecording">Start Recording</button>
    <button @click="stopRecording" :disabled="!isRecording">Stop Recording</button>
    <div v-if="audioUrl">
      <audio :src="audioUrl" controls></audio>
      <button @click="saveRecording">Save Recording</button>
    </div>
    <h3>Saved Recordings</h3>
    <ul>
      <li v-for="rec in recordings" :key="rec.id">
        <audio :src="rec.url" controls></audio>
        <button @click="deleteRecording(rec.id)">Delete</button>
      </li>
    </ul>
  </div>
</template>

<script>
import db from './indexedDbUtil';

export default {
  name: 'DemoRecorder',
  data() {
    return {
      isRecording: false,
      mediaRecorder: null,
      audioChunks: [],
      audioUrl: null,
      recordings: []
    };
  },
  methods: {
    async fetchRecordings() {
      this.recordings = (await db.getRecordings()).map(r => ({
        ...r,
        url: URL.createObjectURL(r.blob)
      }));
    },
    async startRecording() {
      this.audioUrl = null;
      this.audioChunks = [];
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      this.mediaRecorder = new MediaRecorder(stream);
      this.mediaRecorder.ondataavailable = e => this.audioChunks.push(e.data);
      this.mediaRecorder.onstop = () => {
        const blob = new Blob(this.audioChunks, { type: 'audio/webm' });
        this.audioUrl = URL.createObjectURL(blob);
        this.currentBlob = blob;
      };
      this.mediaRecorder.start();
      this.isRecording = true;
    },
    stopRecording() {
      if (this.mediaRecorder) {
        this.mediaRecorder.stop();
        this.isRecording = false;
      }
    },
    async saveRecording() {
      if (this.currentBlob) {
        await db.addRecording({ blob: this.currentBlob, created: Date.now() });
        this.audioUrl = null;
        this.currentBlob = null;
        await this.fetchRecordings();
      }
    },
    async deleteRecording(id) {
      await db.deleteRecording(id);
      await this.fetchRecordings();
    }
  },
  async mounted() {
    await this.fetchRecordings();
  }
};
</script>

<style scoped>
.demo-recorder {
  max-width: 500px;
  margin: 2rem auto;
  padding: 2rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  background: #fafafa;
}
</style>
