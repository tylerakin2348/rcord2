<template>
  <!-- Modal Overlay -->
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>
      
      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-2xl">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
              <h3 class="text-xl font-bold text-white">Single Cord Recording</h3>
            </div>
            <button
              @click="$emit('close')"
              class="text-white hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 p-1"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-8">
          <!-- Recording Status -->
          <div class="text-center mb-8">
            <div class="relative inline-flex items-center justify-center w-32 h-32 bg-gray-100 rounded-full mb-4">
              <div 
                v-if="!isRecording" 
                class="w-20 h-20 rounded-full flex items-center justify-center cursor-pointer transition-colors duration-200"
                :class="microphoneAvailable ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-400 cursor-not-allowed'"
                @click="microphoneAvailable ? startRecording() : null"
              >
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10"/>
                </svg>
              </div>
              <div 
                v-else 
                class="w-20 h-20 bg-red-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-red-700 transition-colors duration-200 animate-pulse"
                @click="stopRecording"
              >
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <rect x="6" y="6" width="12" height="12" rx="2"/>
                </svg>
              </div>
            </div>
            
            <div class="text-sm text-gray-600 mb-2">
              {{ isRecording ? 'Recording...' : 'Click to start recording' }}
            </div>
            
            <div v-if="!microphoneAvailable" class="text-sm text-red-600 mb-2">
              ⚠️ Microphone access required for recording
            </div>
            
            <div class="text-2xl font-mono font-bold text-gray-900">
              {{ formatTime(recordingTime) }}
            </div>
          </div>

          <!-- Recording Controls -->
          <div class="space-y-4">
            <!-- Audio Level Indicator -->
            <div v-if="isRecording" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Audio Level</label>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-green-500 h-2 rounded-full transition-all duration-150" 
                  :style="{ width: audioLevel + '%' }"
                ></div>
              </div>
            </div>

            <!-- Recording Settings -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quality</label>
                <select 
                  v-model="recordingQuality" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :disabled="isRecording"
                >
                  <option value="high">High (48kHz)</option>
                  <option value="medium">Medium (44.1kHz)</option>
                  <option value="low">Low (22kHz)</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                <select 
                  v-model="recordingFormat" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :disabled="isRecording"
                >
                  <option value="wav">WAV</option>
                  <option value="mp3">MP3</option>
                  <option value="flac">FLAC</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Recorded Files -->
          <div v-if="recordedFiles.length > 0" class="mt-8">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Recorded Files</h4>
            <div class="space-y-3">
              <div 
                v-for="file in recordedFiles" 
                :key="file.id" 
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center space-x-3">
                  <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                  </svg>
                  <div>
                    <div class="font-medium text-gray-900">{{ file.name }}</div>
                    <div class="text-sm text-gray-500">{{ file.duration }} • {{ file.size }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button 
                    @click="playFile(file)"
                    class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded-full transition-colors duration-200"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m2-7a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button 
                    @click="downloadFile(file)"
                    class="text-green-600 hover:text-green-800 p-2 hover:bg-green-50 rounded-full transition-colors duration-200"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>
                  <button 
                    @click="deleteFile(file)"
                    class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-full transition-colors duration-200"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

// Define emits
const emit = defineEmits(['close'])

// Recording state
const isRecording = ref(false)
const recordingTime = ref(0)
const audioLevel = ref(0)
const recordingQuality = ref('high')
const recordingFormat = ref('wav')
const recordedFiles = ref([])
const microphoneAvailable = ref(false)

// Audio recording variables
let mediaRecorder = null
let audioChunks = []
let audioStream = null

// Timer for recording
let recordingInterval = null
let audioLevelInterval = null

// Check microphone access on mount
onMounted(async () => {
  await checkMicrophoneAccess()
})

const checkMicrophoneAccess = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    microphoneAvailable.value = true
    stream.getTracks().forEach(track => track.stop()) // Stop the test stream
  } catch (error) {
    console.error('Microphone access denied:', error)
    microphoneAvailable.value = false
  }
}

const startRecording = async () => {
  if (!microphoneAvailable.value) {
    alert('Microphone access is required for recording. Please enable microphone permissions.')
    return
  }

  try {
    // Get user media
    audioStream = await navigator.mediaDevices.getUserMedia({ audio: true })
    
    // Create MediaRecorder
    mediaRecorder = new MediaRecorder(audioStream, {
      mimeType: 'audio/webm;codecs=opus'
    })
    
    audioChunks = []
    recordingTime.value = 0
    
    // Set up MediaRecorder event handlers
    mediaRecorder.ondataavailable = (event) => {
      if (event.data.size > 0) {
        audioChunks.push(event.data)
      }
    }
    
    mediaRecorder.onstop = async () => {
      const audioBlob = new Blob(audioChunks, { type: 'audio/webm' })
      await saveRecordingToServer(audioBlob)
      
      // Stop all tracks
      if (audioStream) {
        audioStream.getTracks().forEach(track => track.stop())
      }
    }
    
    // Start recording
    mediaRecorder.start()
    isRecording.value = true
    
    // Start recording timer
    recordingInterval = setInterval(() => {
      recordingTime.value++
    }, 1000)
    
    // Simulate audio level updates (you could implement real audio analysis here)
    audioLevelInterval = setInterval(() => {
      audioLevel.value = Math.random() * 100
    }, 100)
    
    console.log('Started single cord recording...')
  } catch (error) {
    console.error('Error starting recording:', error)
    alert('Failed to start recording. Please check microphone permissions.')
  }
}

const stopRecording = () => {
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stop()
  }
  
  isRecording.value = false
  
  if (recordingInterval) {
    clearInterval(recordingInterval)
    recordingInterval = null
  }
  
  if (audioLevelInterval) {
    clearInterval(audioLevelInterval)
    audioLevelInterval = null
  }
  
  console.log('Stopped recording, saving file...')
}

const saveRecordingToServer = async (audioBlob) => {
  const title = prompt('Enter a title for this recording:', `Recording ${new Date().toLocaleString()}`)
  
  if (!title) {
    console.log('Recording cancelled by user')
    return
  }

  try {
    // Convert blob to base64
    const reader = new FileReader()
    const base64Data = await new Promise((resolve) => {
      reader.onload = () => resolve(reader.result)
      reader.readAsDataURL(audioBlob)
    })

    const response = await fetch('/api/recordings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        title: title,
        description: `Single cord recording from ${new Date().toLocaleString()}`,
        audio_blob: base64Data,
        duration: recordingTime.value,
        mime_type: 'audio/webm'
      })
    })

    if (response.ok) {
      const result = await response.json()
      
      // Add to local files list for display
      recordedFiles.value.push({
        id: result.recording.id,
        name: title,
        duration: formatTime(recordingTime.value),
        size: result.recording.formatted_file_size,
        timestamp: new Date().toLocaleString()
      })
      
      alert('Recording saved successfully!')
    } else {
      const error = await response.json()
      throw new Error(error.message || 'Failed to save recording')
    }
  } catch (error) {
    console.error('Error saving recording:', error)
    alert('Failed to save recording: ' + error.message)
  }
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const playFile = (file) => {
  console.log('Playing file:', file.name)
  // Implement audio playback
}

const downloadFile = (file) => {
  console.log('Downloading file:', file.name)
  // Implement file download
}

const deleteFile = (file) => {
  recordedFiles.value = recordedFiles.value.filter(f => f.id !== file.id)
  console.log('Deleted file:', file.name)
}

// Cleanup on component unmount
onUnmounted(() => {
  if (recordingInterval) {
    clearInterval(recordingInterval)
  }
  if (audioLevelInterval) {
    clearInterval(audioLevelInterval)
  }
})
</script>
