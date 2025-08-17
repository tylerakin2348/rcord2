<template>
  <!-- Recording Controls - centered in full space -->
  <div class="flex-1 flex flex-col items-center justify-center px-8">
    <!-- Circular Record Button -->
    <div class="relative mb-8">
      <div 
        class="rounded-full flex items-center justify-center cursor-pointer transition-all duration-300 shadow-lg"
        :class="{
          'bg-amber-500 w-20 h-20 hover:bg-amber-600': !isRecording && recordingMode === 'single',
          'bg-stone-500 w-20 h-20 hover:bg-stone-600': !isRecording && recordingMode === 'looped',
          'bg-amber-600 w-16 h-16 animate-pulse': isRecording && recordingMode === 'single',
          'bg-stone-600 w-16 h-16 ': isRecording && recordingMode === 'looped'
        }"
        @click="toggleRecording"
      >
        <div 
          v-if="!isRecording"
          class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center"
        >
          <div class="w-16 h-16 bg-amber-500 rounded-full" v-if="recordingMode === 'single'"></div>
          <div class="w-16 h-16 bg-stone-500 rounded-full" v-else></div>
        </div>
        <div 
          v-else
          class="w-20 h-20 bg-stone-50 rounded-sm flex items-center justify-center"
        >
          <div class="w-16 h-16 bg-amber-600 rounded-sm" v-if="recordingMode === 'single'"></div>
          <div class="w-16 h-16 bg-stone-600 rounded-sm" v-else></div>
        </div>
      </div>
      
      <!-- Loop indicator for looped recording -->
      <div 
        v-if="isRecording && recordingMode === 'looped'" 
        class="absolute inset-0 border-4 border-stone-300 rounded-full animate-spin opacity-50"
      ></div>
    </div>

    <!-- Recording Status -->
    <div class="text-center mb-6">
      <div v-if="recordingMode === 'looped' && isRecording" class="text-sm text-stone-500 mt-2">
        Loop {{ currentLoop }}
      </div>
    </div>
  </div>

  <!-- Footer Section with Time Counter and Progress Indicators -->
  <div class="p-4 border-t border-stone-200 bg-stone-50">
    <!-- Looped Recording Layout -->
    <div v-if="recordingMode === 'looped'" class="flex items-center justify-between">
      <!-- Loop Progress (Left) -->
      <div class="flex flex-col items-center" :class="{ 'opacity-40': !isRecording }">
        <div class="text-xs mb-2" :class="isRecording ? 'text-stone-600' : 'text-stone-400'">Loop Progress</div>
        <div class="relative w-16 h-16">
          <!-- Background circle -->
          <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
            <circle
              cx="32"
              cy="32"
              r="28"
              fill="none"
              :stroke="isRecording ? '#d6d3d1' : '#e7e5e4'"
              stroke-width="3"
            />
            <!-- Loop progress fill circle -->
            <circle
              cx="32"
              cy="32"
              r="28"
              fill="none"
              :stroke="isRecording ? '#78716c' : '#a8a29e'"
              stroke-width="3"
              stroke-linecap="round"
              :stroke-dasharray="`${2 * Math.PI * 28}`"
              :stroke-dashoffset="isRecording ? `${2 * Math.PI * 28 * (1 - ((recordingTime % loopDuration) / loopDuration))}` : `${2 * Math.PI * 28}`"
              class="transition-all duration-1000"
            />
          </svg>
          <!-- Center percentage -->
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-xs font-mono" :class="isRecording ? 'text-stone-700' : 'text-stone-400'">
              {{ isRecording ? Math.round(((recordingTime % loopDuration) / loopDuration) * 100) : 0 }}%
            </span>
          </div>
        </div>
      </div>

      <!-- Recording Time Counter (Center) -->
      <div class="text-center flex-1 mx-4">
        <div class="text-3xl font-mono font-bold text-stone-800">
          {{ formatTime(recordingTime) }}
        </div>
        <div v-if="isRecording" class="text-sm text-stone-500 mt-1">
          Total: {{ formatTime(totalRecordingTime) }}
        </div>
      </div>

      <!-- Audio Level (Right) -->
      <div class="flex flex-col items-center" :class="{ 'opacity-40': !isRecording }">
        <div class="text-xs mb-2" :class="isRecording ? 'text-stone-600' : 'text-stone-400'">Audio Level</div>
        <div class="relative w-16 h-16">
          <!-- Background circle -->
          <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
            <circle
              cx="32"
              cy="32"
              r="28"
              fill="none"
              :stroke="isRecording ? '#d6d3d1' : '#e7e5e4'"
              stroke-width="3"
            />
            <!-- Audio level fill circle -->
            <circle
              cx="32"
              cy="32"
              r="28"
              fill="none"
              :stroke="isRecording ? '#78716c' : '#a8a29e'"
              stroke-width="3"
              stroke-linecap="round"
              :stroke-dasharray="`${2 * Math.PI * 28}`"
              :stroke-dashoffset="isRecording ? `${2 * Math.PI * 28 * (1 - audioLevel / 100)}` : `${2 * Math.PI * 28}`"
              class="transition-all duration-150"
            />
          </svg>
          <!-- Center percentage text -->
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-xs font-mono" :class="isRecording ? 'text-stone-700' : 'text-stone-400'">
              {{ isRecording ? Math.round(audioLevel) : 0 }}%
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Single Recording Layout -->
    <div v-else-if="recordingMode === 'single'" class="flex items-center justify-between">
      <!-- Recording Time Counter (Left) -->
      <div class="text-left">
        <div class="text-3xl font-mono font-bold text-stone-800">
          {{ formatTime(recordingTime) }}
        </div>
      </div>

      <!-- Audio Level (Right) -->
      <div class="flex flex-col items-center" :class="{ 'opacity-40': !isRecording }">
        <div class="text-xs mb-2" :class="isRecording ? 'text-stone-600' : 'text-stone-400'">Audio Level</div>
        <div class="relative w-16 h-16">
          <!-- Background circle -->
          <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
            <circle
              cx="32"
              cy="32"
              r="28"
              fill="none"
              :stroke="isRecording ? '#d6d3d1' : '#e7e5e4'"
              stroke-width="3"
            />
            <!-- Audio level fill circle -->
            <circle
              cx="32"
              cy="32"
              r="28"
              fill="none"
              :stroke="isRecording ? '#f59e0b' : '#a8a29e'"
              stroke-width="3"
              stroke-linecap="round"
              :stroke-dasharray="`${2 * Math.PI * 28}`"
              :stroke-dashoffset="isRecording ? `${2 * Math.PI * 28 * (1 - audioLevel / 100)}` : `${2 * Math.PI * 28}`"
              class="transition-all duration-150"
            />
          </svg>
          <!-- Center percentage text -->
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-xs font-mono" :class="isRecording ? 'text-stone-700' : 'text-stone-400'">
              {{ isRecording ? Math.round(audioLevel) : 0 }}%
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Fallback (should not normally be reached) -->
    <div v-else class="text-center">
      <div class="text-3xl font-mono font-bold text-stone-800">
        {{ formatTime(recordingTime) }}
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch } from 'vue'

export default {
  name: 'RecordingControls',
  props: {
    recordingMode: {
      type: String,
      required: true,
      validator: (value) => ['single', 'looped'].includes(value)
    }
  },
  emits: ['recording-complete'],
  setup(props, { emit }) {
    const isRecording = ref(false)
    const recordingTime = ref(0)
    const totalRecordingTime = ref(0)
    const audioLevel = ref(0)
    const currentLoop = ref(1)
    const loopDuration = ref(10) // 10 seconds per loop
    
    // Session management for looped recordings
    const currentSession = ref(null)
    const sessionTitle = ref('')
    const sessionDescription = ref('')
    
    // MediaRecorder and Audio
    let mediaRecorder = null
    let audioContext = null
    let analyser = null
    let microphone = null
    let recordingTimer = null
    let recordedChunks = []
    let allRecordedChunks = []
    
    const startRecording = async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
        
        // Set up MediaRecorder
        mediaRecorder = new MediaRecorder(stream, {
          mimeType: 'audio/webm;codecs=opus'
        })
        
        // Set up audio analysis
        audioContext = new (window.AudioContext || window.webkitAudioContext)()
        analyser = audioContext.createAnalyser()
        microphone = audioContext.createMediaStreamSource(stream)
        microphone.connect(analyser)
        
        analyser.fftSize = 256
        const bufferLength = analyser.frequencyBinCount
        const dataArray = new Uint8Array(bufferLength)
        
        // Audio level monitoring
        const updateAudioLevel = () => {
          if (!isRecording.value) return
          
          analyser.getByteFrequencyData(dataArray)
          const average = dataArray.reduce((sum, value) => sum + value, 0) / bufferLength
          audioLevel.value = (average / 255) * 100
          
          requestAnimationFrame(updateAudioLevel)
        }
        
        // Recording event handlers
        mediaRecorder.ondataavailable = (event) => {
          if (event.data.size > 0) {
            recordedChunks.push(event.data)
            allRecordedChunks.push(event.data)
          }
        }
        
        mediaRecorder.onstop = () => {
          const blob = new Blob(allRecordedChunks, { type: 'audio/webm' })
          
          // Create file record
          const now = new Date()
          const fileName = `${props.recordingMode}-recording-${now.toISOString().slice(0, 19).replace(/[:.]/g, '-')}.webm`
          const fileSize = formatFileSize(blob.size)
          
          // Calculate final loop count for looped recordings
          const finalLoopCount = props.recordingMode === 'looped' ? currentLoop.value : 1
          const duration = formatTime(props.recordingMode === 'looped' ? totalRecordingTime.value : recordingTime.value)
          
          const fileData = {
            id: Date.now(),
            name: fileName,
            blob: blob,
            duration: duration,
            size: fileSize,
            timestamp: now,
            mode: props.recordingMode
          }
          
          // Add loops info for looped recordings
          if (props.recordingMode === 'looped') {
            fileData.loops = finalLoopCount
          }
          
          // Save to API
          saveRecordingToAPI(fileData)
          
          // Emit completion event
          emit('recording-complete', fileData)
          
          // Reset after successful save
          recordedChunks = []
          allRecordedChunks = []
          recordingTime.value = 0
          totalRecordingTime.value = 0
          currentLoop.value = 1
          audioLevel.value = 0
          
          // Clean up
          stream.getTracks().forEach(track => track.stop())
          if (audioContext) {
            audioContext.close()
            audioContext = null
          }
        }
        
        // Start recording
        mediaRecorder.start()
        isRecording.value = true
        
        // Start timer with loop handling for looped recordings
        recordingTimer = setInterval(() => {
          recordingTime.value++
          if (props.recordingMode === 'looped') {
            totalRecordingTime.value++
            
            // Check if we've completed a loop
            if (recordingTime.value >= loopDuration.value) {
              recordingTime.value = 0
              currentLoop.value++
            }
          }
        }, 1000)
        
        // Start audio level monitoring
        updateAudioLevel()
        
      } catch (error) {
        console.error('Error starting recording:', error)
        alert('Error accessing microphone. Please check permissions.')
      }
    }
    
    const stopRecording = () => {
      if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop()
      }
      
      isRecording.value = false
      
      if (recordingTimer) {
        clearInterval(recordingTimer)
        recordingTimer = null
      }
    }
    
    const toggleRecording = () => {
      if (isRecording.value) {
        stopRecording()
      } else {
        startRecording()
      }
    }
    
    const saveRecordingToAPI = async (fileData) => {
      try {
        // Create FormData to send the audio file
        const formData = new FormData()
        formData.append('audio', fileData.blob, fileData.name)
        formData.append('duration', fileData.duration)
        formData.append('size', fileData.size)
        formData.append('mode', fileData.mode)
        
        if (fileData.mode === 'looped') {
          formData.append('loops', fileData.loops)
          
          // If we have a current session, use it
          if (currentSession.value) {
            formData.append('session_id', currentSession.value.id)
            // Loop number should be the next one in the session
            formData.append('loop_number', (currentSession.value.recordings_count || 0) + 1)
          } else {
            // Create session title and description for new session
            const timestamp = new Date().toLocaleString()
            const title = sessionTitle.value || `Looped Session - ${timestamp}`
            const description = sessionDescription.value || 'Looped recording session'
            
            formData.append('session_title', title)
            formData.append('session_description', description)
            formData.append('loop_number', 1) // First loop in new session
          }
        }
        
        // Use axios instead of fetch for better authentication handling
        const response = await window.axios.post('/api/recordings', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          }
        })
        
        console.log('Recording saved successfully:', response.data)
        
        // If this was a looped recording and we got a session back, store it
        if (fileData.mode === 'looped' && response.data.recording.session_id) {
          if (!currentSession.value) {
            // Fetch the session details for future loops
            try {
              const sessionResponse = await window.axios.get(`/api/recording-sessions/${response.data.recording.session_id}`)
              currentSession.value = sessionResponse.data.session
            } catch (sessionError) {
              console.error('Error fetching session details:', sessionError)
            }
          } else {
            // Update the current session's recording count
            currentSession.value.recordings_count = (currentSession.value.recordings_count || 0) + 1
          }
        }
        
      } catch (error) {
        console.error('Error saving recording to API:', error)
        
        // More specific error handling
        if (error.response) {
          console.error('Response status:', error.response.status)
          console.error('Response data:', error.response.data)
          
          if (error.response.status === 401) {
            alert('Authentication required. Please log in and try again.')
          } else if (error.response.status === 422) {
            console.error('Validation errors:', error.response.data.errors)
            alert('Invalid recording data. Please check the console for details.')
          } else {
            alert(`Error saving recording: ${error.response.data.message || 'Unknown error'}`)
          }
        } else {
          alert('Network error. Please check your connection and try again.')
        }
      }
    }
    
    const formatTime = (seconds) => {
      const mins = Math.floor(seconds / 60)
      const secs = seconds % 60
      return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
    }
    
    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }
    
    // Reset session when switching modes
    const resetSession = () => {
      currentSession.value = null
      currentLoop.value = 1
      sessionTitle.value = ''
      sessionDescription.value = ''
      totalRecordingTime.value = 0
    }
    
    // Watch for recording mode changes to reset session
    watch(() => props.recordingMode, (newMode, oldMode) => {
      if (oldMode && newMode !== oldMode) {
        resetSession()
      }
    })
    
    // Cleanup on unmount
    onUnmounted(() => {
      if (isRecording.value) {
        stopRecording()
      }
    })
    
    return {
      isRecording,
      recordingTime,
      totalRecordingTime,
      audioLevel,
      currentLoop,
      loopDuration,
      currentSession,
      sessionTitle,
      sessionDescription,
      toggleRecording,
      resetSession,
      formatTime
    }
  }
}
</script>

<style scoped>
/* Any component-specific styles can be added here */
</style>
