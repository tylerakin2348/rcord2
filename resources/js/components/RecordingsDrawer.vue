<template>
  <div class="p-6 h-full flex flex-col">
    <!-- Mobile close button -->
    <div v-if="isMobile" class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold text-stone-800">Recent Recordings</h3>
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
    <h3 v-if="!isMobile" class="text-xl font-semibold text-stone-800 mb-6">Recent Recordings</h3>
    
    <!-- Recordings List with Scroll -->
    <div class="flex-1 overflow-y-scroll">
      <div v-if="recordings.length > 0" class="space-y-3">
        <div 
          v-for="file in recordings" 
          :key="file.id" 
          class="flex items-center justify-between p-4 bg-white rounded-lg border border-stone-200 hover:border-stone-300 transition-colors duration-200"
        >
          <div class="flex items-center space-x-3">
            <div 
              class="p-2 rounded-lg"
              :class="{
                'bg-amber-100': recordingMode === 'single',
                'bg-stone-100': recordingMode === 'looped'
              }"
            >
              <svg class="w-5 h-5" :class="{
                'text-amber-600': recordingMode === 'single',
                'text-stone-600': recordingMode === 'looped'
              }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" v-if="recordingMode === 'single'" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" v-else />
              </svg>
            </div>
            <div>
              <div class="font-medium text-stone-900">{{ file.name }}</div>
              <div class="text-sm text-stone-500">{{ file.duration }} • {{ file.size }}</div>
              <div v-if="recordingMode === 'looped' && file.loops" class="text-xs text-stone-400">{{ file.loops }} loops</div>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <button 
              @click="playFile(file)"
              class="p-2 rounded-full transition-colors duration-200"
              :class="{
                'text-amber-600 hover:text-amber-800 hover:bg-amber-50': recordingMode === 'single',
                'text-stone-600 hover:text-stone-800 hover:bg-stone-50': recordingMode === 'looped'
              }"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m2-7a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
            <button 
              @click="downloadFile(file)"
              class="p-2 rounded-full transition-colors duration-200"
              :class="{
                'text-amber-600 hover:text-amber-800 hover:bg-amber-50': recordingMode === 'single',
                'text-stone-600 hover:text-stone-800 hover:bg-stone-50': recordingMode === 'looped'
              }"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </button>
            <button 
              @click="deleteFile(file)"
              class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex items-center justify-center h-full">
        <div class="text-center">
          <div 
            class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-4"
            :class="{
              'bg-amber-100': recordingMode === 'single',
              'bg-stone-100': recordingMode === 'looped'
            }"
          >
            <svg class="w-8 h-8" :class="{
              'text-amber-400': recordingMode === 'single',
              'text-stone-400': recordingMode === 'looped'
            }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" v-if="recordingMode === 'single'" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" v-else />
            </svg>
          </div>
          <p class="text-stone-500">No recordings yet</p>
          <p class="text-sm text-stone-400 mt-1">Start recording to see your files here</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

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
    
    // Load recordings from API on component mount
    onMounted(() => {
      loadRecordingsFromAPI()
    })
    
    const loadRecordingsFromAPI = async () => {
      try {
        const response = await fetch(`/api/recordings?mode=${props.recordingMode}`)
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        
        const data = await response.json()
        recordings.value = data.recordings || []
        
      } catch (error) {
        console.error('Error loading recordings from API:', error)
        // Set empty array on error to show empty state
        recordings.value = []
      }
    }
    
    const playFile = async (file) => {
      try {
        let audioUrl = file.url
        
        // If file doesn't have a URL (from API), fetch it
        if (!audioUrl && file.id) {
          const response = await fetch(`/api/recordings/${file.id}/download`)
          if (response.ok) {
            const blob = await response.blob()
            audioUrl = URL.createObjectURL(blob)
          }
        }
        
        if (audioUrl) {
          const audio = new Audio(audioUrl)
          audio.play().catch(error => {
            console.error('Error playing audio:', error)
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
          const response = await fetch(`/api/recordings/${file.id}/download`)
          if (response.ok) {
            const blob = await response.blob()
            downloadUrl = URL.createObjectURL(blob)
            
            // Get filename from response headers if available
            const disposition = response.headers.get('content-disposition')
            if (disposition && disposition.includes('filename=')) {
              fileName = disposition.split('filename=')[1].replace(/"/g, '')
            }
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
    
    const deleteFile = async (file) => {
      if (!confirm('Are you sure you want to delete this recording?')) {
        return
      }
      
      try {
        if (file.id) {
          // Delete from API
          const response = await fetch(`/api/recordings/${file.id}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            }
          })
          
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
          }
        }
        
        // Remove from local list
        recordings.value = recordings.value.filter(f => f.id !== file.id)
        
        // Clean up blob URL if it exists
        if (file.url) {
          URL.revokeObjectURL(file.url)
        }
        
      } catch (error) {
        console.error('Error deleting file:', error)
        alert('Error deleting recording. Please try again.')
      }
    }
    
    // Method to add a new recording (called from parent)
    const addRecording = (newRecording) => {
      recordings.value.unshift({
        ...newRecording,
        url: URL.createObjectURL(newRecording.blob)
      })
    }
    
    return {
      recordings,
      playFile,
      downloadFile,
      deleteFile,
      addRecording,
      loadRecordingsFromAPI
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
