<template>
  <div class="p-6 h-full flex flex-col">
    <!-- Mobile close button -->
    <div v-if="isMobile" class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold text-stone-800">
        {{ recordingMode === 'looped' ? 'Recording Sessions' : 'Recent Recordings' }}
      </h3>
      <button
        @click="$emit('close')"
        class="p-2 text-stone-500 hover:text-stone-700 hover:bg-stone-200 rounded-full transition-colors duration-200"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    
    <!-- Desktop header -->
    <h3 v-if="!isMobile" class="text-xl font-semibold text-stone-800 mb-6">
      {{ recordingMode === 'looped' ? 'Recording Sessions' : 'Recent Recordings' }}
    </h3>
    
    <!-- Recordings List with Scroll -->
    <div class="flex-1 overflow-y-scroll">
      <!-- Single Mode: Show individual recordings -->
      <div v-if="recordingMode === 'single' && recordings.length > 0" class="space-y-3">
        <div 
          v-for="file in recordings" 
          :key="file.id" 
          class="flex items-center justify-between p-4 bg-white rounded-lg border border-stone-200 hover:border-stone-300 transition-colors duration-200"
        >
          <div class="flex items-center space-x-3">
            <div class="p-2 rounded-lg bg-amber-100">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
            </div>
            <div>
              <div class="font-medium text-stone-900">{{ file.title || file.name }}</div>
              <div class="text-sm text-stone-500">
                {{ file.formatted_duration || file.duration }} • {{ file.formatted_file_size || file.size }}
              </div>
              <div v-if="file.recording_type" class="text-xs text-stone-400">
                {{ file.recording_type.display_name }}
              </div>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <button 
              @click="playFile(file)"
              class="p-2 rounded-full text-amber-600 hover:text-amber-800 hover:bg-amber-50 transition-colors duration-200"
            >
              <!-- Play Icon -->
              <svg v-if="currentlyPlayingId !== file.id" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
              </svg>
              <!-- Pause Icon -->
              <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
              </svg>
            </button>
            <button 
              @click="downloadFile(file)"
              class="p-2 rounded-full text-amber-600 hover:text-amber-800 hover:bg-amber-50 transition-colors duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
            </button>
            <button 
              @click="deleteFile(file)"
              class="p-2 rounded-full text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Looped Mode: Show recording sessions -->
      <div v-if="recordingMode === 'looped' && sessions.length > 0" class="space-y-4">
        <div 
          v-for="session in sessions" 
          :key="session.id" 
          class="bg-white rounded-lg border border-stone-200 hover:border-stone-300 transition-colors duration-200"
        >
          <!-- Session Header -->
          <div class="p-4" :class="{ 'border-b border-stone-100': isSessionExpanded(session.id) }">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3 flex-1">
                <div class="p-2 rounded-lg bg-stone-100">
                  <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-stone-900">{{ session.title }}</div>
                  <div class="text-sm text-stone-500">
                    {{ session.formatted_session_duration }} • {{ session.total_loops }} loops • {{ session.recordings_count }} recordings
                  </div>
                  <div v-if="session.description" class="text-xs text-stone-400 mt-1">{{ session.description }}</div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button 
                  @click="toggleSessionExpanded(session.id)"
                  class="p-2 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-50 transition-colors duration-200"
                  :class="{ 'rotate-180': isSessionExpanded(session.id) }"
                >
                  <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                <button 
                  @click="deleteSession(session)"
                  class="p-2 rounded-full text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors duration-200"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Expandable Session Recordings -->
          <div v-if="isSessionExpanded(session.id)" class="p-4 pt-0 space-y-3">
            <div v-if="session.recordings && session.recordings.length > 0" class="space-y-2">
              <div class="text-xs font-medium text-stone-600 mb-2">Loop Recordings:</div>
              <div 
                v-for="recording in session.recordings" 
                :key="recording.id"
                class="flex items-center justify-between p-3 bg-stone-50 rounded-lg border border-stone-100 hover:border-stone-200 transition-colors duration-200"
              >
                <div class="flex items-center space-x-3">
                  <div class="p-1.5 rounded-lg bg-stone-200">
                    <div class="w-1.5 h-1.5 bg-stone-600 rounded-full"></div>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-stone-800">Loop {{ recording.loop_number }}</div>
                    <div class="text-xs text-stone-500">
                      {{ recording.formatted_duration }} • {{ recording.formatted_file_size }}
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-1">
                  <button 
                    @click="playFile(recording)"
                    class="p-1.5 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-100 transition-colors duration-200"
                  >
                    <!-- Play Icon -->
                    <svg v-if="currentlyPlayingId !== recording.id" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z"/>
                    </svg>
                    <!-- Pause Icon -->
                    <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                    </svg>
                  </button>
                  <button 
                    @click="downloadFile(recording)"
                    class="p-1.5 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-100 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                  </button>
                  <button 
                    @click="deleteFile(recording)"
                    class="p-1.5 rounded-full text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-sm text-stone-400">No recordings in this session</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="(recordingMode === 'single' && recordings.length === 0) || (recordingMode === 'looped' && sessions.length === 0)" class="text-center py-12">
        <div class="p-4 rounded-lg bg-stone-100 inline-block mb-4">
          <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
          </svg>
        </div>
        <p class="text-stone-500 mb-2">No {{ recordingMode === 'looped' ? 'sessions' : 'recordings' }} yet</p>
        <p class="text-stone-400 text-sm">{{ recordingMode === 'looped' ? 'Start a looped recording session' : 'Create your first recording' }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'

export default {
  name: 'RecordingsDrawer',
  props: {
    recordingMode: {
      type: String,
      required: true,
      validator: (value) => ['single', 'looped'].includes(value)
    },
    isMobile: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const recordings = ref([])
    const sessions = ref([])
    const expandedSessions = ref(new Set())
    const currentlyPlayingId = ref(null)
    const currentAudio = ref(null)
    
    // Load recordings from API on component mount
    onMounted(() => {
      loadDataFromAPI()
    })
    
    // Watch for recordingMode changes to reload data
    watch(() => props.recordingMode, () => {
      loadDataFromAPI()
      expandedSessions.value.clear() // Clear expanded state when switching modes
    })
    
    const loadDataFromAPI = async () => {
      if (props.recordingMode === 'looped') {
        await loadSessionsFromAPI()
      } else {
        await loadRecordingsFromAPI()
      }
    }
    
    const loadRecordingsFromAPI = async () => {
      try {
        const response = await window.axios.get('/api/recordings', {
          params: { mode: props.recordingMode }
        })
        
        recordings.value = response.data.recordings || []
        
      } catch (error) {
        console.error('Error loading recordings from API:', error)
        // Set empty array on error to show empty state
        recordings.value = []
      }
    }
    
    const loadSessionsFromAPI = async () => {
      try {
        const response = await window.axios.get('/api/recording-sessions', {
          params: { type: 'looped' }
        })
        
        sessions.value = response.data.sessions || []
        
      } catch (error) {
        console.error('Error loading sessions from API:', error)
        // Set empty array on error to show empty state
        sessions.value = []
      }
    }
    
    const playFile = async (file) => {
      try {
        // If this file is currently playing, pause it
        if (currentlyPlayingId.value === file.id && currentAudio.value) {
          currentAudio.value.pause()
          currentAudio.value = null
          currentlyPlayingId.value = null
          return
        }
        
        // Stop any currently playing audio
        if (currentAudio.value) {
          currentAudio.value.pause()
          currentAudio.value = null
        }
        
        let audioUrl = file.url
        
        // If file doesn't have a URL (from API), fetch it
        if (!audioUrl && file.id) {
          const response = await window.axios.get(`/api/recordings/${file.id}/stream`, {
            responseType: 'blob'
          })
          audioUrl = URL.createObjectURL(response.data)
        }
        
        if (audioUrl) {
          const audio = new Audio(audioUrl)
          
          // Set up event listeners
          audio.addEventListener('ended', () => {
            currentlyPlayingId.value = null
            currentAudio.value = null
          })
          
          audio.addEventListener('pause', () => {
            if (currentlyPlayingId.value === file.id) {
              currentlyPlayingId.value = null
              currentAudio.value = null
            }
          })
          
          // Start playing
          currentAudio.value = audio
          currentlyPlayingId.value = file.id
          
          audio.play().catch(error => {
            console.error('Error playing audio:', error)
            currentlyPlayingId.value = null
            currentAudio.value = null
          })
        }
      } catch (error) {
        console.error('Error playing file:', error)
      }
    }
    
    const downloadFile = async (file) => {
      try {
        let downloadUrl = file.url
        let fileName = file.name
        
        // If file doesn't have a URL (from API), fetch it
        if (!downloadUrl && file.id) {
          const response = await window.axios.get(`/api/recordings/${file.id}/stream`, {
            responseType: 'blob'
          })
          downloadUrl = URL.createObjectURL(response.data)
          
          // Get filename from response headers if available
          const disposition = response.headers['content-disposition']
          if (disposition && disposition.includes('filename=')) {
            fileName = disposition.split('filename=')[1].replace(/"/g, '')
          }
        }
        
        if (downloadUrl) {
          const a = document.createElement('a')
          a.href = downloadUrl
          a.download = fileName
          document.body.appendChild(a)
          a.click()
          document.body.removeChild(a)
          
          // Clean up blob URL if we created it
          if (!file.url) {
            URL.revokeObjectURL(downloadUrl)
          }
        }
      } catch (error) {
        console.error('Error downloading file:', error)
      }
    }
    
    const deleteSession = async (session) => {
      if (!confirm(`Are you sure you want to delete "${session.title}" and all its recordings?`)) {
        return
      }
      
      try {
        // Delete session from API (this will also delete associated recordings)
        await window.axios.delete(`/api/recording-sessions/${session.id}`)
        
        // Remove from local list
        sessions.value = sessions.value.filter(s => s.id !== session.id)
        
      } catch (error) {
        console.error('Error deleting session:', error)
        if (error.response?.status === 401) {
          alert('Authentication required. Please log in and try again.')
        } else {
          alert('Error deleting session. Please try again.')
        }
      }
    }
    
    const deleteFile = async (file) => {
      if (!confirm('Are you sure you want to delete this recording?')) {
        return
      }
      
      try {
        if (file.id) {
          // Delete from API using axios
          await window.axios.delete(`/api/recordings/${file.id}`)
        }
        
        // If in single mode, remove from local recordings list
        if (props.recordingMode === 'single') {
          recordings.value = recordings.value.filter(f => f.id !== file.id)
        } else if (props.recordingMode === 'looped') {
          // For looped recordings, refresh session data to update counts and recordings
          await loadSessionsFromAPI()
        }
        
        // Clean up blob URL if it exists
        if (file.url) {
          URL.revokeObjectURL(file.url)
        }
        
      } catch (error) {
        console.error('Error deleting file:', error)
        if (error.response?.status === 401) {
          alert('Authentication required. Please log in and try again.')
        } else {
          alert('Error deleting recording. Please try again.')
        }
      }
    }
    
    // Method to add a new recording (called from parent)
    const addRecording = (newRecording) => {
      recordings.value.unshift({
        ...newRecording,
        url: URL.createObjectURL(newRecording.blob)
      })
    }
    
    // Method to refresh data (called from parent when a new recording is saved)
    const refreshData = () => {
      loadDataFromAPI()
    }
    
    // Toggle session expansion
    const toggleSessionExpanded = (sessionId) => {
      if (expandedSessions.value.has(sessionId)) {
        expandedSessions.value.delete(sessionId)
      } else {
        expandedSessions.value.add(sessionId)
      }
    }
    
    // Check if session is expanded
    const isSessionExpanded = (sessionId) => {
      return expandedSessions.value.has(sessionId)
    }
    
    return {
      recordings,
      sessions,
      expandedSessions,
      currentlyPlayingId,
      playFile,
      downloadFile,
      deleteFile,
      deleteSession,
      addRecording,
      refreshData,
      toggleSessionExpanded,
      isSessionExpanded,
      loadRecordingsFromAPI,
      loadSessionsFromAPI
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-scroll::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-scroll::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-y-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

.overflow-y-scroll::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
