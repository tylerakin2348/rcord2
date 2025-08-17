<template>
  <!-- Recording Controls - centered in full space -->
  <div class="flex-1 flex flex-col items-center justify-center px-8">
    <!-- Recording Button Group -->
    <div class="relative mb-8 flex items-center gap-4">
      <!-- Loop Button (only visible for looped mode when recording and first loop) -->
      <button 
        v-if="isRecording && recordingMode === 'looped' && currentLoop === 1"
        class="rounded-full flex items-center justify-center transition-all duration-300 shadow-lg border-0 focus:outline-none focus:ring-4 focus:ring-opacity-50 bg-stone-500 w-16 h-16 hover:bg-stone-600 focus:ring-stone-300"
        :class="{ 'bg-stone-400 cursor-not-allowed focus:ring-stone-200': savingLoop }"
        :disabled="savingLoop"
        @click="saveCurrentLoopAndContinue"
        :aria-label="'Save current loop and continue recording'"
      >
        <div class="w-15 h-15 bg-stone-50 rounded-full flex items-center justify-center">
          <svg 
            class="w-8 h-8 text-stone-600 pointer-events-none" 
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
      
      <!-- Main Record/Stop Button -->
      <button 
        class="rounded-full flex items-center justify-center transition-all duration-300 shadow-lg border-0 focus:outline-none focus:ring-4 focus:ring-opacity-50"
        :class="{
          'bg-red-100 w-20 h-20 hover:bg-red-200 focus:ring-red-300': !isRecording && recordingMode === 'single',
          'bg-red-100 w-20 h-20 hover:bg-red-200 focus:ring-red-300': !isRecording && recordingMode === 'looped',
          'bg-red-200 w-16 h-16 animate-pulse focus:ring-red-300': isRecording && recordingMode === 'single',
          'bg-red-200 w-16 h-16 focus:ring-red-300': isRecording && recordingMode === 'looped'
        }"
        @click="handleMainButtonClick"
        :aria-label="getMainButtonAriaLabel()"
      >
        <!-- Not Recording State -->
        <div 
          v-if="!isRecording"
          class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center"
        >
          <div class="w-16 h-16 bg-red-500 rounded-full" v-if="recordingMode === 'single'"></div>
          <div class="w-16 h-16 bg-red-500 rounded-full" v-else></div>
        </div>
        
        <!-- Recording State - Always show stop button (square) when recording -->
        <div 
          v-else
          class="w-5 h-5 bg-stone-50 flex items-center justify-center rounded-sm"
        >
          <div 
            class="w-0 h-0 rounded-sm"
            :class="{
              'bg-red-600': recordingMode === 'single',
              'bg-red-600': recordingMode === 'looped'
            }"
          ></div>
        </div>
      </button>
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
      <div class="flex flex-col items-center" :class="{ 'opacity-40': !isRecording || currentLoop === 1 }">
        <div class="text-xs mb-2" :class="(isRecording && currentLoop > 1) ? 'text-stone-600' : 'text-stone-400'">Loop Progress</div>
        <div class="relative w-16 h-16">
          <!-- Background circle -->
          <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
            <circle
              cx="32"
              cy="32"
              r="28"
              fill="none"
              :stroke="(isRecording && currentLoop > 1) ? '#d6d3d1' : '#e7e5e4'"
              stroke-width="3"
            />
            <!-- Loop progress fill circle - only show if we have a loop duration and are on loop 2+ -->
            <circle
              v-if="loopDuration && currentLoop > 1"
              cx="32"
              cy="32"
              r="28"
              fill="none"
              :stroke="isRecording ? '#78716c' : '#a8a29e'"
              stroke-width="3"
              stroke-linecap="round"
              :stroke-dasharray="`${2 * Math.PI * 28}`"
              :stroke-dashoffset="isRecording ? `${2 * Math.PI * 28 * (1 - (recordingTime / loopDuration))}` : `${2 * Math.PI * 28}`"
              class="transition-all duration-1000"
            />
          </svg>
          <!-- Center percentage -->
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-xs font-mono" :class="(isRecording && currentLoop > 1 && loopDuration) ? 'text-stone-700' : 'text-stone-400'">
              {{ (isRecording && currentLoop > 1 && loopDuration) ? Math.round((recordingTime / loopDuration) * 100) : 0 }}%
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
    const loopDuration = ref(null) // Will be set based on first loop completion
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
          // console.log(`Data available: ${event.data.size} bytes, timecode: ${event.timecode}`)
          if (event.data.size > 0) {
            recordedChunks.push(event.data)
            allRecordedChunks.push(event.data)
            // console.log(`Total chunks: ${recordedChunks.length}, All chunks: ${allRecordedChunks.length}`)
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
            // For looped recordings, only save the current incomplete loop (if any)
            // For single recordings, save the complete recording
            const chunksToUse = props.recordingMode === 'looped' ? recordedChunks : allRecordedChunks
            const blob = new Blob(chunksToUse, { type: 'audio/webm' })
            
            // Skip saving if there's no audio data (empty current loop)
            if (blob.size === 0 && props.recordingMode === 'looped') {
              console.log('No final loop audio to save')
              
              // Reset after successful save
              recordedChunks = []
              allRecordedChunks = []
              recordingTime.value = 0
              totalRecordingTime.value = 0
              currentLoop.value = 1
              loopDuration.value = null // Reset loop duration for next session
              audioLevel.value = 0
              
              return
            }
            
            // Create file record
            const now = new Date()
            let fileName, duration
            
            if (props.recordingMode === 'looped') {
              // For looped mode, this is the final partial loop
              fileName = `loop-${currentLoop.value}-final-${now.toISOString().slice(0, 19).replace(/[:.]/g, '-')}.webm`
              duration = formatTime(recordingTime.value)
            } else {
              // For single mode, this is the complete recording
              fileName = `${props.recordingMode}-recording-${now.toISOString().slice(0, 19).replace(/[:.]/g, '-')}.webm`
              duration = formatTime(recordingTime.value)
            }
            
            const fileSize = formatFileSize(blob.size)
            
            const fileData = {
              id: Date.now(),
              name: fileName,
              blob: blob,
              duration: duration,
              size: fileSize,
              timestamp: now,
              mode: props.recordingMode
            }
            
            // Add loop info for looped recordings
            if (props.recordingMode === 'looped') {
              fileData.loops = 1 // This is just the final partial loop
              fileData.loopNumber = currentLoop.value
            }
            
            // Save to API
          console.log(fileData, '333333fkaldxf - FINAL SAVE')
          console.log('before 11 - FINAL SAVE')
          saveRecordingToAPI(fileData)
          console.log('after 22 - FINAL SAVE')
            
            // Emit completion event
            emit('recording-complete', fileData)
            
            // Reset after successful save
            recordedChunks = []
            allRecordedChunks = []
            recordingTime.value = 0
            totalRecordingTime.value = 0
            currentLoop.value = 1
            loopDuration.value = null // Reset loop duration for next session
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
        recordingTimer = setInterval(async () => {
          recordingTime.value++
          if (props.recordingMode === 'looped') {
            totalRecordingTime.value++
            
            // Check if we've completed a loop
            if (loopDuration.value && recordingTime.value >= loopDuration.value) {
              // console.log(`Auto-saving loop ${currentLoop.value} after ${loopDuration.value} seconds`)
              // console.log(`recordedChunks length: ${recordedChunks.length}`)
              
              // Make sure we have data to save
              if (recordedChunks.length === 0) {
                // console.warn('No recorded chunks available for auto-save, skipping...')
                // Reset for next loop anyway
                recordingTime.value = 0
                currentLoop.value++
                return
              }
              
              // Save the current loop automatically
              try {
                // Stop the current recording to get final data and start fresh for next loop
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                  // Temporarily store current onstop handler
                  const originalOnStop = mediaRecorder.onstop
                  
                  mediaRecorder.onstop = async () => {
                    try {
                      // Save the current loop as an individual recording
                      const currentLoopBlob = new Blob(recordedChunks, { type: 'audio/webm' })
                      console.log(`Loop blob size: ${currentLoopBlob.size} bytes`)
                      
                      // Make sure we have a valid blob with data
                      if (currentLoopBlob.size === 0) {
                        console.warn('Empty blob created, skipping save...')
                        // Reset for next loop anyway
                        recordedChunks = []
                        recordingTime.value = 0
                        currentLoop.value++
                        
                        // Restart recording for next loop
                        if (isRecording.value) {
                          mediaRecorder.start(1000)
                        }
                        return
                      }
                      
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
                      
                      console.log(loopFileData, '1111fkaldxf - AUTO SAVE')
                      // Save current loop to API
                      console.log('before - AUTO SAVE')
                      await saveRecordingToAPI(loopFileData)
                      console.log('after - AUTO SAVE')
                      
                      // Emit completion event for current loop
                      emit('recording-complete', loopFileData)
                      
                      console.log(`Auto-saved loop ${currentLoop.value}, starting loop ${currentLoop.value + 1}`)
                      
                    } catch (error) {
                      console.error('Error auto-saving current loop:', error)
                      if (error.response) {
                        console.error('Response status:', error.response.status)
                        console.error('Response data:', error.response.data)
                      }
                    }
                    
                    // Reset for next loop and restart recording
                    recordedChunks = [] // Clear chunks for next loop
                    recordingTime.value = 0 // Reset loop timer
                    currentLoop.value++ // Increment loop counter
                    
                    // Restore original onstop handler
                    mediaRecorder.onstop = originalOnStop
                    
                    // Restart recording for next loop if still recording
                    if (isRecording.value) {
                      mediaRecorder.start(1000)
                    }
                  }
                  
                  // Stop to trigger the save and restart cycle
                  mediaRecorder.stop()
                }
              } catch (error) {
                console.error('Error in auto-save process:', error)
                // Reset for next loop anyway
                recordedChunks = []
                recordingTime.value = 0
                currentLoop.value++
              }
            } else if (!loopDuration.value && currentLoop.value === 1) {
              // First loop - duration will be set when user saves the loop
              // No automatic loop progression yet
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
      // console.log('clicking button')
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
        // If we have a loop duration set and we're on loop 2+, act as stop button
        if (loopDuration.value && currentLoop.value > 1) {
          // console.log('Stop button clicked - stopping recording')
          stopRecording()
        } else {
          // First loop - check for double click to stop, single click to save and continue
          if (timeDiff < 500) {
            // console.log('Double click detected - stopping recording')
            stopRecording()
          } else {
            // Single click saves current loop and continues
            // console.log('Single click - saving current loop')
            saveCurrentLoopAndContinue()
          }
        }
      }
    }
    
    const handleMainButtonClick = () => {
      // If not recording, start recording
      if (!isRecording.value) {
        toggleRecording()
        return
      }
      
      // If recording, always stop (for both single and looped modes)
      stopRecording()
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
        // Looped mode
        if (loopDuration.value && currentLoop.value > 1) {
          return 'Stop recording'
        } else {
          return 'Click to save loop and continue, double-click to stop recording'
        }
      }
    }
    
    const getMainButtonAriaLabel = () => {
      if (!isRecording.value) {
        return `Start ${props.recordingMode} recording`
      }
      
      return 'Stop recording'
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
        // If this is the first loop completion, set the loop duration
        if (currentLoop.value === 1 && !loopDuration.value) {
          loopDuration.value = recordingTime.value
          // console.log(`Loop duration set to ${loopDuration.value} seconds based on first loop`)
        }
        
        // Save current onstop handler for restoration later
        const originalOnStop = mediaRecorder.onstop
        
        // Set temporary onstop handler for loop save and restart
        mediaRecorder.onstop = async () => {
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
            
            console.log(loopFileData, '22222fkaldxf - MANUAL SAVE')
            // Save current loop to API
            console.log('before 1 - MANUAL SAVE')
            await saveRecordingToAPI(loopFileData)
            console.log('after 2 - MANUAL SAVE')
            
            // Emit completion event for current loop
            emit('recording-complete', loopFileData)
            
            console.log(`Manual saved loop ${currentLoop.value}, starting loop ${currentLoop.value + 1}`)
            
          } catch (error) {
            console.error('Error saving current loop:', error)
            if (error.response) {
              console.error('Response status:', error.response.status)
              console.error('Response data:', error.response.data)
            }
          }
          
          // Reset for next loop and restart recording
          recordedChunks = [] // Clear chunks for next loop
          recordingTime.value = 0 // Reset loop timer
          currentLoop.value++ // Increment loop counter
          
          // Restore original onstop handler
          mediaRecorder.onstop = originalOnStop
          
          // Restart recording for next loop if still recording
          if (isRecording.value) {
            mediaRecorder.start(1000)
          }
          
          savingLoop.value = false
        }
        
        // Stop to trigger the save and restart cycle
        mediaRecorder.stop()
        
      } catch (error) {
        console.error('Error in manual save process:', error)
        savingLoop.value = false
      }
    }
    
    const saveRecordingToAPI = async (fileData) => {
      try {
        console.log('saveRecordingToAPI called with:', {
          name: fileData.name,
          duration: fileData.duration,
          size: fileData.size,
          mode: fileData.mode,
          loops: fileData.loops,
          loopNumber: fileData.loopNumber,
          blobSize: fileData.blob.size,
          blobType: fileData.blob.type,
          currentSession: currentSession.value ? currentSession.value.id : null
        })
        
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
            // console.log('Using existing session:', currentSession.value.id, 'Loop number:', (currentSession.value.recordings_count || 0) + 1)
          } else {
            // Create session title and description for new session
            const timestamp = new Date().toLocaleString()
            const title = sessionTitle.value || `Looped Session - ${timestamp}`
            const description = sessionDescription.value || 'Looped recording session'
            
            formData.append('session_title', title)
            formData.append('session_description', description)
            formData.append('loop_number', 1) // First loop in new session
            // console.log('Creating new session:', title, 'Loop number: 1')
          }
        }
        
        console.log('FormData entries:')
        for (let [key, value] of formData.entries()) {
          console.log(key, typeof value === 'object' && value instanceof File ? `File: ${value.name} (${value.size} bytes)` : value)
        }
        
        // Use axios instead of fetch for better authentication handling
        const response = await window.axios.post('/api/recordings', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          }
        })
        
        // console.log('Recording saved successfully:', response.data)
        
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
      loopDuration.value = null // Reset loop duration
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
      handleMainButtonClick,
      getButtonAriaLabel,
      getMainButtonAriaLabel,
      toggleRecording,
      resetSession,
      saveCurrentLoopAndContinue,
      formatTime
    }
  }
}
</script>

<style scoped>
/* Any component-specific styles can be added here */
</style>
