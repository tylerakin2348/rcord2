<template>
  <div class="recordings-library">
    <div class="recordings-list">
      <h3>Recordings Library</h3>
      <div v-if="loadingRecordings" class="loading">Loading recordings...</div>
      <div v-else-if="recordings.length === 0" class="no-recordings">
        No recordings found. Use Single Cord or Looped Cord modes to create recordings.
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
            <button @click="playRecording(recording)" class="btn-sm">
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
  name: 'RecordingsLibrary',
  data() {
    return {
      loadingRecordings: true,
      recordings: []
    };
  },
  async mounted() {
    await this.loadRecordings();
  },
  methods: {
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

    async playRecording(recording) {
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

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    }
  }
};
</script>

<style scoped>
.recordings-library {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
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
  font-size: 1.5rem;
}

.loading, .no-recordings {
  text-align: center;
  padding: 40px;
  color: #666;
}

.no-recordings {
  background: #f8f9fa;
  border-radius: 8px;
  border: 2px dashed #dee2e6;
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
  transition: all 0.3s ease;
}

.recording-card:hover {
  border-color: #3498db;
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
}

.recording-info h4 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 1.1rem;
}

.recording-info p {
  margin: 0 0 10px 0;
  color: #666;
  font-size: 14px;
  line-height: 1.4;
}

.recording-meta {
  display: flex;
  gap: 15px;
  margin: 10px 0;
  font-size: 12px;
  color: #888;
}

.recording-meta span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.recording-user {
  font-size: 12px;
  color: #666;
  margin-top: 10px;
  font-style: italic;
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
  transform: translateY(-1px);
}

.btn-danger {
  background: #e74c3c !important;
}

.btn-danger:hover {
  background: #c0392b !important;
}
</style>
