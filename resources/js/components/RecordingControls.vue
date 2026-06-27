<template>
  <div class="flex flex-col h-full min-h-0">
    <div class="flex-1 relative overflow-hidden min-h-0 bg-stone-100">
      <LiveWaveform
        v-if="isRecording && analyserNode"
        :analyser="analyserNode"
        :active="isCapturing && !isCountingIn"
        variant="background"
      />

      <div class="relative z-10 flex flex-col items-center justify-center h-full px-8 pointer-events-none">
        <div class="pointer-events-auto flex flex-col items-center">
          <div class="relative mb-8 flex items-center gap-4">
            <div class="w-16 h-16 flex items-center justify-center shrink-0">
              <button
                v-if="isRecording && recordingMode === 'looped' && currentLoop === 1"
                class="rounded-full flex items-center justify-center transition-all duration-300 shadow-lg border-0 focus:outline-none focus:ring-4 focus:ring-opacity-50 bg-stone-500 w-16 h-16 hover:bg-stone-600 focus:ring-stone-300 hover:rotate-12 hover:scale-105"
                :class="{ 'bg-stone-400 cursor-not-allowed focus:ring-stone-200': savingLoop }"
                :disabled="savingLoop"
                @click="saveCurrentLoopAndContinue"
                aria-label="Save current loop and continue recording"
              >
                <div class="w-15 h-15 bg-stone-50 rounded-full flex items-center justify-center">
                  <svg
                    class="w-8 h-8 text-stone-600 pointer-events-none transition-transform duration-300"
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
            </div>

            <button
              class="rounded-full flex items-center justify-center transition-all duration-300 shadow-lg border-0 focus:outline-none focus:ring-4 focus:ring-opacity-50 shrink-0"
              :class="{
                'bg-red-100 w-20 h-20 hover:bg-red-200 focus:ring-red-300': !isRecording && recordingMode === 'single',
                'bg-red-100 w-20 h-20 hover:bg-red-200 focus:ring-red-300': !isRecording && recordingMode === 'looped',
                'bg-red-200 w-16 h-16 animate-pulse focus:ring-red-300': isRecording && recordingMode === 'single',
                'bg-red-200 w-16 h-16 focus:ring-red-300': isRecording && recordingMode === 'looped'
              }"
              @click="handleMainButtonClick"
              :aria-label="getMainButtonAriaLabel()"
            >
              <div
                v-if="!isRecording"
                class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center"
              >
                <div class="w-16 h-16 bg-red-500 rounded-full"></div>
              </div>

              <div
                v-else
                class="w-5 h-5 bg-stone-50 flex items-center justify-center rounded-sm"
              >
                <div class="w-0 h-0 rounded-sm bg-red-600"></div>
              </div>
            </button>

            <div class="w-16 h-16 flex items-center justify-center shrink-0">
              <button
                v-if="showExtendLoopButton"
                class="rounded-full flex flex-col items-center justify-center transition-all duration-200 shadow-md border border-amber-200/80 focus:outline-none focus:ring-4 focus:ring-amber-200 w-16 h-16 select-none touch-none"
                :class="isExtendingLoop
                  ? 'bg-amber-500 border-amber-400 scale-105 shadow-lg'
                  : 'bg-amber-50 hover:bg-amber-100'"
                aria-label="Hold to extend loop"
                @mousedown.prevent="startExtendLoop"
                @mouseup.prevent="releaseExtendLoop"
                @mouseleave="releaseExtendLoop"
                @touchstart.prevent="startExtendLoop"
                @touchend.prevent="releaseExtendLoop"
                @touchcancel.prevent="releaseExtendLoop"
              >
                <svg
                  class="w-7 h-7 pointer-events-none transition-colors"
                  :class="isExtendingLoop ? 'text-white' : 'text-amber-700'"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <!-- Loop arc with extension past the boundary -->
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.75"
                    d="M7 12a5 5 0 0110 0"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.75"
                    d="M17 12h4"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.75"
                    d="M19.5 9.5L22 12l-2.5 2.5"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-width="1.75"
                    d="M12 7V5"
                  />
                </svg>
                <span
                  class="text-[9px] font-semibold uppercase tracking-wide pointer-events-none mt-0.5"
                  :class="isExtendingLoop ? 'text-amber-50' : 'text-amber-600/80'"
                >
                  Hold
                </span>
              </button>
            </div>
          </div>

          <div
            v-if="isCountingIn"
            class="text-center"
          >
            <div class="text-5xl font-mono font-bold text-stone-700 tabular-nums">
              {{ countInRemaining }}
            </div>
            <div class="text-xs uppercase tracking-wide text-stone-400 mt-2">
              Count-in
            </div>
          </div>

          <div
            v-else-if="recordingMode === 'looped' && isRecording"
            class="text-center text-sm text-stone-500"
          >
            Loop {{ currentLoop }}
            <span v-if="isExtendingLoop" class="block text-amber-600 text-xs mt-1">Extending…</span>
          </div>
        </div>
      </div>
    </div>

    <div class="relative z-10 p-4 border-t border-stone-200 bg-stone-50 shrink-0">
      <div v-if="recordingMode === 'looped'" class="flex items-center justify-between">
        <div class="flex flex-col items-center" :class="{ 'opacity-40': !isRecording || currentLoop === 1 }">
          <div class="text-xs mb-2" :class="(isRecording && currentLoop > 1) ? 'text-stone-600' : 'text-stone-400'">Loop Progress</div>
          <div class="relative w-16 h-16">
            <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
              <circle
                cx="32"
                cy="32"
                r="28"
                fill="none"
                :stroke="(isRecording && currentLoop > 1) ? '#d6d3d1' : '#e7e5e4'"
                stroke-width="3"
              />
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
                :stroke-dashoffset="isRecording ? `${2 * Math.PI * 28 * (1 - loopProgressRatio)}` : `${2 * Math.PI * 28}`"
                class="transition-all duration-1000"
              />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
              <span class="text-xs font-mono" :class="(isRecording && currentLoop > 1 && loopDuration) ? 'text-stone-700' : 'text-stone-400'">
                {{ (isRecording && currentLoop > 1 && loopDuration) ? Math.round(loopProgressRatio * 100) : 0 }}%
              </span>
            </div>
          </div>
        </div>

        <div class="text-center flex-1 mx-4">
          <div class="text-3xl font-mono font-bold text-stone-800">
            {{ formatTime(recordingTime) }}
          </div>
          <div v-if="isRecording" class="text-sm text-stone-500 mt-1">
            Total: {{ formatTime(totalRecordingTime) }}
          </div>
          <div v-if="!isRecording" class="mt-3">
            <div class="text-xs text-stone-500 mb-1.5">Count-in</div>
            <div
              class="inline-flex rounded-lg border border-stone-200 bg-stone-100 p-0.5"
              role="group"
              aria-label="Count-in duration"
            >
              <button
                v-for="option in countInOptions"
                :key="option.value"
                type="button"
                class="px-3 py-1 text-xs font-medium rounded-md transition-colors duration-150"
                :class="countInSeconds === option.value
                  ? 'bg-white text-stone-800 shadow-sm'
                  : 'text-stone-500 hover:text-stone-700'"
                @click="countInSeconds = option.value"
              >
                {{ option.label }}
              </button>
            </div>
          </div>
          <div
            v-else-if="countInSeconds > 0"
            class="text-xs text-stone-400 mt-2"
          >
            {{ countInSeconds }}s count-in
          </div>
        </div>

        <div class="flex flex-col items-center" :class="{ 'opacity-40': !isRecording }">
          <div class="text-xs mb-2" :class="isRecording ? 'text-stone-600' : 'text-stone-400'">Audio Level</div>
          <div class="relative w-16 h-16">
            <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
              <circle
                cx="32"
                cy="32"
                r="28"
                fill="none"
                :stroke="isRecording ? '#d6d3d1' : '#e7e5e4'"
                stroke-width="3"
              />
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
            <div class="absolute inset-0 flex items-center justify-center">
              <span class="text-xs font-mono" :class="isRecording ? 'text-stone-700' : 'text-stone-400'">
                {{ isRecording ? Math.round(audioLevel) : 0 }}%
              </span>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center">
        <div class="text-3xl font-mono font-bold text-stone-800">
          {{ formatTime(recordingTime) }}
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { ref, computed, onUnmounted, watch } from 'vue'
import LiveWaveform from './LiveWaveform.vue'

export default {
  name: 'RecordingControls',
  components: {
    LiveWaveform,
  },
  props: {
    recordingMode: {
      type: String,
      required: true,
      validator: (value) => ['single', 'looped'].includes(value)
    },
    useIndexedDb: {
      type: Boolean,
      default: false
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
    const savingLoop = ref(false)
    const lastClickTime = ref(0)
    const analyserNode = ref(null)
    const countInSeconds = ref(0)
    const isCountingIn = ref(false)
    const countInRemaining = ref(0)
    const isCapturing = ref(false)
    const isExtendingLoop = ref(false)
    const loopBoundaryPassedWhileExtending = ref(false)

    const countInOptions = [
      { value: 0, label: 'Off' },
      { value: 2, label: '2s' },
      { value: 4, label: '4s' },
      { value: 8, label: '8s' },
    ]

    const showWaveform = computed(() =>
      isRecording.value && isCapturing.value && !isCountingIn.value
    )

    const currentSession = ref(null)
    const sessionTitle = ref('')
    const sessionDescription = ref('')

    let mediaRecorder = null
    let audioContext = null
    let analyser = null
    let microphone = null
    let recordingTimer = null
    let countInTimer = null
    let recordedChunks = []
    let allRecordedChunks = []
    let activeStream = null

    const showExtendLoopButton = computed(() =>
      props.recordingMode === 'looped'
      && isRecording.value
      && loopDuration.value
      && currentLoop.value > 1
      && !isCountingIn.value
      && !savingLoop.value
    )

    const loopProgressRatio = computed(() => {
      if (!loopDuration.value || currentLoop.value <= 1) return 0
      return Math.min(1, recordingTime.value / loopDuration.value)
    })

    const clearCountInTimer = () => {
      if (countInTimer) {
        clearTimeout(countInTimer)
        countInTimer = null
      }
    }

    const runCountIn = (onComplete) => new Promise((resolve) => {
      if (!countInSeconds.value) {
        onComplete()
        resolve()
        return
      }

      clearCountInTimer()
      isCapturing.value = false
      audioLevel.value = 0
      isCountingIn.value = true

      let remaining = countInSeconds.value
      countInRemaining.value = remaining

      const finish = async () => {
        clearCountInTimer()
        isCountingIn.value = false
        countInRemaining.value = 0
        await onComplete()
        resolve()
      }

      const tick = () => {
        remaining--
        if (remaining <= 0) {
          finish()
          return
        }
        countInRemaining.value = remaining
        countInTimer = setTimeout(tick, 1000)
      }

      countInTimer = setTimeout(tick, 1000)
    })

    const restartLoopCapture = async () => {
      if (!isRecording.value || !mediaRecorder || mediaRecorder.state !== 'inactive') return

      if (audioContext?.state === 'suspended') {
        await audioContext.resume()
      }

      mediaRecorder.start(1000)
      isCapturing.value = true
    }

    const afterLoopSaveRestart = () => {
      savingLoop.value = false

      const resume = () => restartLoopCapture()

      if (countInSeconds.value > 0) {
        runCountIn(resume)
      } else {
        resume()
      }
    }

    const initiateLoopSave = () => {
      if (savingLoop.value) return
      if (!mediaRecorder || mediaRecorder.state !== 'recording') return

      if (recordedChunks.length === 0) {
        recordingTime.value = 0
        currentLoop.value++
        afterLoopSaveRestart()
        return
      }

      savingLoop.value = true
      isExtendingLoop.value = false
      loopBoundaryPassedWhileExtending.value = false
      isCapturing.value = false
      audioLevel.value = 0

      const originalOnStop = mediaRecorder.onstop
      const savedLoopNumber = currentLoop.value
      const savedDuration = recordingTime.value

      mediaRecorder.onstop = async () => {
        try {
          const currentLoopBlob = new Blob(recordedChunks, { type: 'audio/webm' })

          if (currentLoopBlob.size === 0) {
            recordedChunks = []
            recordingTime.value = 0
            currentLoop.value++
            return
          }

          const now = new Date()
          const fileName = `loop-${savedLoopNumber}-${now.toISOString().slice(0, 19).replace(/[:.]/g, '-')}.webm`
          const loopFileData = {
            id: Date.now(),
            name: fileName,
            blob: currentLoopBlob,
            duration: formatTime(savedDuration),
            size: formatFileSize(currentLoopBlob.size),
            timestamp: now,
            mode: props.recordingMode,
            loops: 1,
            loopNumber: savedLoopNumber,
          }

          await saveRecordingToAPI(loopFileData)
          emit('recording-complete', loopFileData)
        } catch (error) {
          console.error('Error saving loop:', error)
        } finally {
          recordedChunks = []
          recordingTime.value = 0
          currentLoop.value++
          mediaRecorder.onstop = originalOnStop
          afterLoopSaveRestart()
        }
      }

      mediaRecorder.stop()
    }

    const maybeCompleteLoop = () => {
      if (!loopDuration.value || recordingTime.value < loopDuration.value) return
      if (isExtendingLoop.value) {
        loopBoundaryPassedWhileExtending.value = true
        return
      }
      initiateLoopSave()
    }

    const startExtendLoop = () => {
      if (!showExtendLoopButton.value) return
      isExtendingLoop.value = true
      if (recordingTime.value >= loopDuration.value) {
        loopBoundaryPassedWhileExtending.value = true
      }
    }

    const releaseExtendLoop = () => {
      if (!isExtendingLoop.value) return
      isExtendingLoop.value = false

      if (loopBoundaryPassedWhileExtending.value || recordingTime.value >= loopDuration.value) {
        loopBoundaryPassedWhileExtending.value = false
        initiateLoopSave()
      }
    }

    const startRecordingTimer = () => {
      recordingTimer = setInterval(() => {
        if (isCountingIn.value) return

        recordingTime.value++
        if (props.recordingMode === 'looped') {
          totalRecordingTime.value++
          maybeCompleteLoop()
        }
      }, 1000)
    }
    
    const startRecording = async () => {
      try {
        savingLoop.value = false
        isCapturing.value = false
        isCountingIn.value = false
        countInRemaining.value = 0

        const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
        activeStream = stream

        mediaRecorder = new MediaRecorder(stream, {
          mimeType: 'audio/webm;codecs=opus'
        })

        audioContext = new (window.AudioContext || window.webkitAudioContext)()
        if (audioContext.state === 'suspended') {
          await audioContext.resume()
        }
        analyser = audioContext.createAnalyser()
        microphone = audioContext.createMediaStreamSource(stream)
        microphone.connect(analyser)

        analyser.fftSize = 2048
        analyserNode.value = analyser
        const bufferLength = analyser.frequencyBinCount
        const dataArray = new Uint8Array(bufferLength)

        const updateAudioLevel = () => {
          if (!isRecording.value) return

          if (isCapturing.value && !isCountingIn.value) {
            analyser.getByteFrequencyData(dataArray)
            const average = dataArray.reduce((sum, value) => sum + value, 0) / bufferLength
            audioLevel.value = (average / 255) * 100
          } else {
            audioLevel.value = 0
          }

          requestAnimationFrame(updateAudioLevel)
        }

        mediaRecorder.ondataavailable = (event) => {
          if (event.data.size > 0) {
            recordedChunks.push(event.data)
            allRecordedChunks.push(event.data)
          }
        }

        mediaRecorder.onstop = async () => {
          if (!isRecording.value) {
            const chunksToUse = props.recordingMode === 'looped' ? recordedChunks : allRecordedChunks
            const blob = new Blob(chunksToUse, { type: 'audio/webm' })

            if (blob.size === 0 && props.recordingMode === 'looped') {
              recordedChunks = []
              allRecordedChunks = []
              recordingTime.value = 0
              totalRecordingTime.value = 0
              currentLoop.value = 1
              loopDuration.value = null
              audioLevel.value = 0
              return
            }

            const now = new Date()
            let fileName, duration

            if (props.recordingMode === 'looped') {
              fileName = `loop-${currentLoop.value}-final-${now.toISOString().slice(0, 19).replace(/[:.]/g, '-')}.webm`
              duration = formatTime(recordingTime.value)
            } else {
              fileName = `${props.recordingMode}-recording-${now.toISOString().slice(0, 19).replace(/[:.]/g, '-')}.webm`
              duration = formatTime(recordingTime.value)
            }

            const fileData = {
              id: Date.now(),
              name: fileName,
              blob,
              duration,
              size: formatFileSize(blob.size),
              timestamp: now,
              mode: props.recordingMode,
            }

            if (props.recordingMode === 'looped') {
              fileData.loops = 1
              fileData.loopNumber = currentLoop.value
            }

            const saved = await saveRecordingToAPI(fileData)
            if (saved) {
              emit('recording-complete', fileData)
            }

            recordedChunks = []
            allRecordedChunks = []
            recordingTime.value = 0
            totalRecordingTime.value = 0
            currentLoop.value = 1
            loopDuration.value = null
            audioLevel.value = 0
            resetSession()

            activeStream?.getTracks().forEach(track => track.stop())
            activeStream = null
            if (audioContext) {
              audioContext.close()
              audioContext = null
            }
            analyserNode.value = null
          }
        }

        isRecording.value = true
        startRecordingTimer()
        updateAudioLevel()

        const beginCapture = async () => {
          if (audioContext?.state === 'suspended') {
            await audioContext.resume()
          }
          if (props.recordingMode === 'looped') {
            mediaRecorder.start(1000)
          } else {
            mediaRecorder.start()
          }
          isCapturing.value = true
        }

        if (props.recordingMode === 'looped' && countInSeconds.value > 0) {
          await runCountIn(() => beginCapture())
        } else {
          await beginCapture()
        }
      } catch (error) {
        console.error('Error starting recording:', error)
        alert('Error accessing microphone. Please check permissions.')
      }
    }
    
    const stopRecording = () => {
      isExtendingLoop.value = false
      loopBoundaryPassedWhileExtending.value = false
      clearCountInTimer()
      isCountingIn.value = false
      countInRemaining.value = 0
      isCapturing.value = false
      savingLoop.value = false
      audioLevel.value = 0

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
      if (savingLoop.value) return

      if (currentLoop.value === 1 && !loopDuration.value) {
        if (recordingTime.value < 1) return
        loopDuration.value = recordingTime.value
      }

      initiateLoopSave()
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
            const title = sessionTitle.value || 'Looped Session'
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

        return true
      } catch (error) {
        console.error('Error saving recording to API:', error)
        
        // More specific error handling
        if (error.response) {
          console.error('Response status:', error.response.status)
          console.error('Response data:', error.response.data)
          
          if (error.response.status === 401) {
            if (!props.useIndexedDb) {
              alert('Authentication required. Please log in and try again.')
            }
          } else if (error.response.status === 422) {
            console.error('Validation errors:', error.response.data.errors)
            alert('Invalid recording data. Please check the console for details.')
          } else {
            alert(`Error saving recording: ${error.response.data.message || 'Unknown error'}`)
          }
        } else {
          alert('Network error. Please check your connection and try again.')
        }

        return false
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
      loopDuration.value = null
      isExtendingLoop.value = false
      loopBoundaryPassedWhileExtending.value = false
      savingLoop.value = false
      isCapturing.value = false
      clearCountInTimer()
      isCountingIn.value = false
      countInRemaining.value = 0
    }
    
    // Watch for recording mode changes to reset session
    watch(() => props.recordingMode, (newMode, oldMode) => {
      if (oldMode && newMode !== oldMode) {
        resetSession()
      }
    })
    
    // Cleanup on unmount
    onUnmounted(() => {
      clearCountInTimer()
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
      loopProgressRatio,
      currentSession,
      sessionTitle,
      sessionDescription,
      savingLoop,
      lastClickTime,
      analyserNode,
      countInSeconds,
      countInOptions,
      isCountingIn,
      countInRemaining,
      showWaveform,
      isCapturing,
      isExtendingLoop,
      showExtendLoopButton,
      handleButtonClick,
      handleMainButtonClick,
      getButtonAriaLabel,
      getMainButtonAriaLabel,
      toggleRecording,
      resetSession,
      saveCurrentLoopAndContinue,
      startExtendLoop,
      releaseExtendLoop,
      formatTime
    }
  }
}
</script>

<style scoped>
/* Any component-specific styles can be added here */
</style>
