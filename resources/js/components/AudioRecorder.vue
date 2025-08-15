<template>
  <div class="audio-recorder">
    <div class="recorder-container">
      <div class="recorder-header">
        <h2>Audio Recorder</h2>
      </div>

      <div class="recorder-controls">
        <button 
          @click="startRecording" 
          :disabled="isRecording || !microphoneAvailable"
          class="btn-record"
        >
          <i class="icon-mic"></i>
          {{ isRecording ? 'Recording...' : 'Start Recording' }}
        </button>

        <button 
          @click="stopRecording" 
          :disabled="!isRecording"
          class="btn-stop"
        >
          <i class="icon-stop"></i>
          Stop Recording
        </button>

        <button 
          @click="playRecording" 
          :disabled="!audioBlob || isPlaying"
          class="btn-play"
        >
          <i class="icon-play"></i>
          {{ isPlaying ? 'Playing...' : 'Play' }}
        </button>
      </div>

      <div v-if="isRecording" class="recording-indicator">
        <div class="pulse-dot"></div>
        <span>Recording... {{ formatTime(recordingTime) }}</span>
      </div>

      <div v-if="audioBlob && !isRecording" class="recording-preview">
        <div class="preview-info">
          <p>Recording captured: {{ formatTime(recordingDuration) }}</p>
          <audio ref="audioPlayer" :src="audioUrl" @ended="onPlaybackEnd"></audio>
        </div>

        <form @submit.prevent="saveRecording" class="save-form">
          <div class="form-group">
            <label for="title">Title *</label>
            <input 
              id="title"
              v-model="recordingForm.title" 
              type="text" 
              required 
              placeholder="Enter recording title"
            />
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea 
              id="description"
              v-model="recordingForm.description" 
              placeholder="Optional description"
              rows="3"
            ></textarea>
          </div>

          <div class="form-actions">
            <button type="button" @click="discardRecording" class="btn-secondary">
              Discard
            </button>
            <button type="submit" :disabled="saving" class="btn-primary">
              {{ saving ? 'Saving...' : 'Save Recording' }}
            </button>
          </div>
        </form>
      </div>

      <div v-if="!microphoneAvailable" class="error-message">
        <p>Microphone access is required for recording. Please enable microphone permissions.</p>
      </div>
    </div>

    <div class="recordings-list">
      <h3>Existing Recordings</h3>
      <div v-if="loadingRecordings" class="loading">Loading recordings...</div>
      <div v-else-if="recordings.length === 0" class="no-recordings">
        No recordings found.
      </div>
      <div v-else class="recordings-grid">
        <div 
          v-for="recording in recordings" 
          :key="recording.id" 
          class="recording-card"
        >
          <div class="recording-info">
            <h4>{{ recording.title }}</h4>
            <p v-if="recording.description">{{ recording.description }}</p>
            <div class="recording-meta">
              <span class="duration">{{ recording.formatted_duration }}</span>
              <span class="size">{{ recording.formatted_file_size }}</span>
              <span class="date">{{ formatDate(recording.created_at) }}</span>
            </div>
            <div v-if="recording.user" class="recording-user">
              By: {{ recording.user.name }}
            </div>
          </div>
          <div class="recording-actions">
            <button @click="playExistingRecording(recording)" class="btn-sm">
              Play
            </button>
            <button @click="editRecording(recording)" class="btn-sm">
              Edit
            </button>
            <button @click="deleteRecording(recording)" class="btn-sm btn-danger">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AudioRecorder',
  data() {
    return {
      isRecording: false,
      isPlaying: false,
      microphoneAvailable: false,
      mediaRecorder: null,
      audioChunks: [],
      audioBlob: null,
      audioUrl: null,
      recordingTime: 0,
      recordingDuration: 0,
      recordingInterval: null,
      saving: false,
      loadingRecordings: true,
      recordings: [],
      recordingForm: {
        title: '',
        description: ''
      }
    };
  },
  async mounted() {
    await this.checkMicrophoneAccess();
    await this.loadRecordings();
  },
  beforeUnmount() {
    if (this.recordingInterval) {
      clearInterval(this.recordingInterval);
    }
    if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
      this.mediaRecorder.stop();
    }
  },
  methods: {
    async checkMicrophoneAccess() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        this.microphoneAvailable = true;
        stream.getTracks().forEach(track => track.stop()); // Stop the test stream
      } catch (error) {
        console.error('Microphone access denied:', error);
        this.microphoneAvailable = false;
      }
    },

    async startRecording() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        
        this.mediaRecorder = new MediaRecorder(stream, {
          mimeType: 'audio/webm;codecs=opus'
        });
        
        this.audioChunks = [];
        this.recordingTime = 0;
        
        this.mediaRecorder.ondataavailable = (event) => {
          if (event.data.size > 0) {
            this.audioChunks.push(event.data);
          }
        };
        
        this.mediaRecorder.onstop = () => {
          this.audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
          this.audioUrl = URL.createObjectURL(this.audioBlob);
          this.recordingDuration = this.recordingTime;
          
          // Stop all tracks
          stream.getTracks().forEach(track => track.stop());
        };
        
        this.mediaRecorder.start();
        this.isRecording = true;
        
        // Start timer
        this.recordingInterval = setInterval(() => {
          this.recordingTime++;
        }, 1000);
        
      } catch (error) {
        console.error('Error starting recording:', error);
        alert('Failed to start recording. Please check microphone permissions.');
      }
    },

    stopRecording() {
      if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
        this.mediaRecorder.stop();
      }
      
      this.isRecording = false;
      
      if (this.recordingInterval) {
        clearInterval(this.recordingInterval);
      }
    },

    playRecording() {
      if (this.audioUrl) {
        this.isPlaying = true;
        this.$refs.audioPlayer.play();
      }
    },

    onPlaybackEnd() {
      this.isPlaying = false;
    },

    discardRecording() {
      this.audioBlob = null;
      this.audioUrl = null;
      this.recordingTime = 0;
      this.recordingDuration = 0;
      this.recordingForm = {
        title: '',
        description: ''
      };
    },

    async saveRecording() {
      if (!this.audioBlob) {
        alert('No recording to save');
        return;
      }

      this.saving = true;

      try {
        // Convert blob to base64
        const reader = new FileReader();
        const base64Data = await new Promise((resolve) => {
          reader.onload = () => resolve(reader.result);
          reader.readAsDataURL(this.audioBlob);
        });

        const response = await fetch('/api/recordings', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            title: this.recordingForm.title,
            description: this.recordingForm.description,
            audio_blob: base64Data,
            duration: this.recordingDuration,
            mime_type: 'audio/webm'
          })
        });

        if (response.ok) {
          const result = await response.json();
          alert('Recording saved successfully!');
          
          // Reset form
          this.discardRecording();
          
          // Reload recordings
          await this.loadRecordings();
        } else {
          const error = await response.json();
          throw new Error(error.message || 'Failed to save recording');
        }
      } catch (error) {
        console.error('Error saving recording:', error);
        alert('Failed to save recording: ' + error.message);
      } finally {
        this.saving = false;
      }
    },

    async loadRecordings() {
      this.loadingRecordings = true;
      
      try {
        const response = await fetch('/api/recordings');
        if (response.ok) {
          const result = await response.json();
          this.recordings = result.recordings;
        } else {
          throw new Error('Failed to load recordings');
        }
      } catch (error) {
        console.error('Error loading recordings:', error);
      } finally {
        this.loadingRecordings = false;
      }
    },

    async playExistingRecording(recording) {
      try {
        const audio = new Audio(`/api/recordings/${recording.id}/stream`);
        await audio.play();
      } catch (error) {
        console.error('Error playing recording:', error);
        alert('Failed to play recording');
      }
    },

    async editRecording(recording) {
      const title = prompt('Enter new title:', recording.title);
      const description = prompt('Enter new description:', recording.description || '');
      
      if (title !== null) {
        try {
          const response = await fetch(`/api/recordings/${recording.id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              title,
              description
            })
          });

          if (response.ok) {
            await this.loadRecordings();
            alert('Recording updated successfully!');
          } else {
            throw new Error('Failed to update recording');
          }
        } catch (error) {
          console.error('Error updating recording:', error);
          alert('Failed to update recording');
        }
      }
    },

    async deleteRecording(recording) {
      if (confirm(`Are you sure you want to delete "${recording.title}"?`)) {
        try {
          const response = await fetch(`/api/recordings/${recording.id}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          });

          if (response.ok) {
            await this.loadRecordings();
            alert('Recording deleted successfully!');
          } else {
            throw new Error('Failed to delete recording');
          }
        } catch (error) {
          console.error('Error deleting recording:', error);
          alert('Failed to delete recording');
        }
      }
    },

    formatTime(seconds) {
      const minutes = Math.floor(seconds / 60);
      const remainingSeconds = seconds % 60;
      return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    }
  }
};
</script>

<style scoped>
.audio-recorder {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.recorder-container {
  background: #fff;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.recorder-header {
  text-align: center;
  margin-bottom: 30px;
}

.recorder-header h2 {
  color: #333;
  margin: 0;
}

.recorder-controls {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
}

.btn-record, .btn-stop, .btn-play {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-record {
  background: #e74c3c;
  color: white;
}

.btn-record:hover:not(:disabled) {
  background: #c0392b;
}

.btn-stop {
  background: #95a5a6;
  color: white;
}

.btn-stop:hover:not(:disabled) {
  background: #7f8c8d;
}

.btn-play {
  background: #27ae60;
  color: white;
}

.btn-play:hover:not(:disabled) {
  background: #219a52;
}

.btn-record:disabled, .btn-stop:disabled, .btn-play:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.recording-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 15px;
  background: #ffe6e6;
  border-radius: 6px;
  color: #e74c3c;
  font-weight: 500;
}

.pulse-dot {
  width: 12px;
  height: 12px;
  background: #e74c3c;
  border-radius: 50%;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.2); }
  100% { opacity: 1; transform: scale(1); }
}

.recording-preview {
  border-top: 1px solid #eee;
  padding-top: 20px;
}

.preview-info {
  text-align: center;
  margin-bottom: 20px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 6px;
}

.save-form {
  max-width: 500px;
  margin: 0 auto;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #333;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.btn-primary, .btn-secondary {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #3498db;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2980b9;
}

.btn-secondary {
  background: #95a5a6;
  color: white;
}

.btn-secondary:hover {
  background: #7f8c8d;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  background: #ffe6e6;
  color: #e74c3c;
  padding: 15px;
  border-radius: 6px;
  text-align: center;
}

.recordings-list {
  background: #fff;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.recordings-list h3 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #333;
}

.loading, .no-recordings {
  text-align: center;
  padding: 40px;
  color: #666;
}

.recordings-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.recording-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  background: #f8f9fa;
}

.recording-info h4 {
  margin: 0 0 10px 0;
  color: #333;
}

.recording-info p {
  margin: 0 0 10px 0;
  color: #666;
  font-size: 14px;
}

.recording-meta {
  display: flex;
  gap: 15px;
  margin: 10px 0;
  font-size: 12px;
  color: #888;
}

.recording-user {
  font-size: 12px;
  color: #666;
  margin-top: 10px;
}

.recording-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.btn-sm {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  background: #3498db;
  color: white;
  transition: all 0.3s ease;
}

.btn-sm:hover {
  background: #2980b9;
}

.btn-danger {
  background: #e74c3c !important;
}

.btn-danger:hover {
  background: #c0392b !important;
}

/* Icons (you can replace these with actual icon fonts) */
.icon-mic::before { content: '🎤'; }
.icon-stop::before { content: '⏹️'; }
.icon-play::before { content: '▶️'; }
</style>
