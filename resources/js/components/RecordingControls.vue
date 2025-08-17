<template>
  <!-- Recording Controls - centered in full space -->
  <div class="flex-1 flex flex-col items-center justify-center px-8">
    <!-- Circular Record Button -->
    <div class="relative mb-8">
      <button 
        class="rounded-full flex items-center justify-center transition-all duration-300 shadow-lg border-0 focus:outline-none focus:ring-4 focus:ring-opacity-50"
        :class="{
          'bg-amber-500 w-20 h-20 hover:bg-amber-600 focus:ring-amber-300': !isRecording && recordingMode === 'single',
          'bg-stone-500 w-20 h-20 hover:bg-stone-600 focus:ring-stone-300': !isRecording && recordingMode === 'looped',
          'bg-amber-600 w-16 h-16 animate-pulse focus:ring-amber-300': isRecording && recordingMode === 'single',
          'bg-stone-600 w-16 h-16 focus:ring-stone-300': isRecording && recordingMode === 'looped' && !savingLoop,
          'bg-stone-400 w-16 h-16 cursor-not-allowed focus:ring-stone-200': isRecording && recordingMode === 'looped' && savingLoop
        }"
        :disabled="savingLoop"
        @click="handleButtonClick"
        :aria-label="getButtonAriaLabel()"
      >
        <!-- Not Recording State -->
        <div 
          v-if="!isRecording"
          class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center"
        >
          <div class="w-16 h-16 bg-amber-500 rounded-full" v-if="recordingMode === 'single'"></div>
          <div class="w-16 h-16 bg-stone-500 rounded-full" v-else></div>
        </div>
        
        <!-- Recording State -->
        <div 
          v-else
          class="w-15 h-15 bg-stone-50 flex items-center justify-center"
          :class="{
            'rounded-sm': recordingMode === 'single',
            'rounded-full': recordingMode === 'looped'
          }"
        >
          <!-- Single Mode: Square Stop Button -->
          <div 
            v-if="recordingMode === 'single'"
            class="w-16 h-16 bg-amber-600 rounded-sm"
          ></div>
          
          <!-- Looped Mode: Loop Arrow Icon or Saving Spinner -->
          <div v-if="savingLoop" class="flex items-center justify-center pointer-events-none">
            <!-- <svg class="w-8 h-8 text-stone-600 animate-spin pointer-events-none" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> -->
          </div>
          <svg 
            v-else
            class="w-10 h-10 text-stone-600 pointer-events-none" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path 
              stroke-linecap="round" 
              stroke-linejoin="round" 
              stroke-width="2" 
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" 
            />
          </svg>
        </div>
      </button>
      
      <!-- Loop indicator for looped recording -->
      <!-- <div 
        v-if="isRecording && recordingMode === 'looped'" 
        class="absolute inset-0 border-4 border-stone-300 rounded-full animate-spin opacity-50"
      ></div> -->
    </div>

    <!-- Recording Status -->
    <div class="text-center mb-6">
      <div v-if="recordingMode === 'looped' && isRecording" class="text-sm text-stone-500 mt-2">
        <div>Loop {{ currentLoop }}</div>
        <!-- <div v-if="savingLoop" class="text-xs text-stone-400 mt-1">Saving loop...</div>
        <div v-else class="text-xs text-stone-400 mt-1">
          Click to save loop & continue<br>
          <span class="text-stone-300">Double-click to stop</span>
        </div> -->
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
    const savingLoop = ref(false) // Indicates when a loop is being saved
    const lastClickTime = ref(0) // Track click timing for double-click detection
    
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
        
        // Request data every second for loop saving capability
        if (props.recordingMode === 'looped') {
          mediaRecorder.start(1000) // Collect data every second
        } else {
          mediaRecorder.start()
        }
        
        mediaRecorder.onstop = () => {
          // Only process final recording if we're actually stopping (not just saving a loop)
          if (!isRecording.value) {
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
        }
        
        // MediaRecorder is started above based on mode
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
    
    const handleButtonClick = () => {
      console.log('clicking button')
      // Prevent clicks when saving a loop
      if (savingLoop.value) {
        return
      }
      
      const currentTime = Date.now()
      const timeDiff = currentTime - lastClickTime.value
      lastClickTime.value = currentTime
      
      // If not recording, just start recording
      if (!isRecording.value) {
        toggleRecording()
        return
      }
      
      // If recording and in single mode, stop
      if (props.recordingMode === 'single') {
        stopRecording()
        return
      }
      
      // If recording and in looped mode
      if (props.recordingMode === 'looped') {
        // Double click (within 500ms) stops recording
        if (timeDiff < 500) {
          console.log('Double click detected - stopping recording')
          stopRecording()
        } else {
          // Single click saves current loop and continues
          console.log('Single click - saving current loop')
          saveCurrentLoopAndContinue()
        }
      }
    }
    
    const getButtonAriaLabel = () => {
      if (savingLoop.value) {
        return 'Saving current loop...'
      }
      
      if (!isRecording.value) {
        return `Start ${props.recordingMode} recording`
      }
      
      if (props.recordingMode === 'single') {
        return 'Stop recording'
      } else {
        return 'Click to save loop and continue, double-click to stop recording'
      }
    }
    
    const toggleRecording = () => {
      if (isRecording.value) {
        if (props.recordingMode === 'looped') {
          // In looped mode, save current loop and start new one
          saveCurrentLoopAndContinue()
        } else {
          // In single mode, stop recording
          stopRecording()
        }
      } else {
        startRecording()
      }
    }
    
    const saveCurrentLoopAndContinue = async () => {
      if (!mediaRecorder || mediaRecorder.state !== 'recording') return
      
      savingLoop.value = true
      
      try {
        // Save the current loop as an individual recording
        const currentLoopBlob = new Blob(recordedChunks, { type: 'audio/webm' })
        
        // Create file record for current loop
        const now = new Date()
        const fileName = `loop-${currentLoop.value}-${now.toISOString().slice(0, 19).replace(/[:.]/g, '-')}.webm`
        const fileSize = formatFileSize(currentLoopBlob.size)
        
        const loopFileData = {
          id: Date.now(),
          name: fileName,
          blob: currentLoopBlob,
          duration: formatTime(recordingTime.value),
          size: fileSize,
          timestamp: now,
          mode: props.recordingMode,
          loops: 1, // This is a single loop
          loopNumber: currentLoop.value
        }
        
        // Save current loop to API
        await saveRecordingToAPI(loopFileData)
        
        // Emit completion event for current loop
        emit('recording-complete', loopFileData)
        
        // Reset for next loop but keep recording going
        recordedChunks = [] // Clear chunks for next loop
        recordingTime.value = 0 // Reset loop timer
        currentLoop.value++ // Increment loop counter
        
        console.log(`Saved loop ${currentLoop.value - 1}, starting loop ${currentLoop.value}`)
        
      } catch (error) {
        console.error('Error saving current loop:', error)
        alert('Error saving loop. Recording will continue.')
      } finally {
        savingLoop.value = false
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
      savingLoop,
      lastClickTime,
      handleButtonClick,
      getButtonAriaLabel,
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
