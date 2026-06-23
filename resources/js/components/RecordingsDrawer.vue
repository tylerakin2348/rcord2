<template>
    <div 
        class="p-6 h-full flex flex-col">
        <!-- Mobile close button -->
        <div
            v-if="isMobile"
            class="flex justify-between items-center mb-4"
        >
            <h3 class="text-xl font-semibold text-stone-800">
               Saved Recordings
            </h3>
        </div>

        <!-- Desktop header -->
        <h3
            v-if="!isMobile"
            class="text-xl font-semibold text-stone-800 mb-6"
        >
           Saved Recordings
        </h3>

        <!-- Recordings List with Scroll -->
        <div
            class="flex-1 overflow-y-scroll pr-2"
            ref="scrollContainer"
        >
            <!-- Single Mode: Show individual recordings -->
            <div
                v-if="recordingMode === 'single'"
                class="space-y-3"
            >
                <!-- Loading state for initial load -->
                <div
                    v-if="loadingRecordings && recordings.length === 0"
                    class="flex justify-center py-12"
                >
                    <div class="flex items-center space-x-3 text-stone-500">
                        <svg
                            class="w-6 h-6 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        <span class="text-sm font-medium"
                            >Loading recordings...</span
                        >
                    </div>
                </div>

                <!-- Recordings list -->
                <div
                    v-else-if="recordings.length > 0"
                    class="space-y-3"
                >
                    <div
                        v-for="file in recordings"
                        :key="file.id"
                        class="flex items-center justify-between p-4 bg-white rounded-lg border border-stone-200 hover:border-stone-300 transition-colors duration-200"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="p-2 rounded-lg bg-stone-100">
                                <svg
                                    class="w-5 h-5 text-stone-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-stone-900">
                                    {{ file.title || file.name }}
                                </div>
                                <div class="text-sm text-stone-500">
                                    {{
                                        file.formatted_duration || file.duration
                                    }}
                                    •
                                    {{ file.formatted_file_size || file.size }}
                                </div>
                                <div
                                    v-if="file.recording_type"
                                    class="text-xs text-stone-400"
                                >
                                    {{ file.recording_type.display_name }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="playFile(file)"
                                class="p-2 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-100 transition-colors duration-200"
                            >
                                <!-- Play Icon -->
                                <svg
                                    v-if="!(currentlyPlayingId === file.id && isPlayerPlaying)"
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                                <!-- Pause Icon -->
                                <svg
                                    v-else
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                                </svg>
                            </button>
                            <button
                                @click="downloadFile(file)"
                                class="p-2 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-100 transition-colors duration-200"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="deleteFile(file)"
                                class="p-2 rounded-full text-stone-600 hover:text-red-600 hover:bg-red-50 transition-colors duration-200"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Loading more indicator -->
                    <div
                        v-if="loadingMore"
                        class="flex justify-center py-6"
                    >
                        <div class="flex items-center space-x-3 text-stone-500">
                            <svg
                                class="w-6 h-6 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <span class="text-sm font-medium"
                                >Loading more recordings...</span
                            >
                        </div>
                    </div>

                    <!-- End of results indicator -->
                    <div
                        v-else-if="!hasMoreRecordings && recordings.length > 0"
                        class="text-center py-4"
                    >
                        <span class="text-xs text-stone-400"
                            >You've reached the end of your recordings</span
                        >
                    </div>

                    <!-- Scroll trigger element -->
                    <div
                        ref="scrollTrigger"
                        class="h-1 w-full"
                    ></div>
                </div>
            </div>

            <!-- Looped Mode: Show recording sessions -->
            <div
                v-if="recordingMode === 'looped'"
                class="space-y-4"
            >
                <!-- Loading state for initial load -->
                <div
                    v-if="loadingSessions && sessions.length === 0"
                    class="flex justify-center py-12"
                >
                    <div class="flex items-center space-x-3 text-stone-500">
                        <svg
                            class="w-6 h-6 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        <span class="text-sm font-medium"
                            >Loading sessions...</span
                        >
                    </div>
                </div>

                <!-- Sessions list -->
                <div
                    v-else-if="sessions.length > 0"
                    class="space-y-4"
                >
                    <div
                        v-for="session in sessions"
                        :key="session.id"
                    >
                        <!-- Simple Card for Single Recording Sessions -->
                        <div
                            v-if="session.recordings_count === 1"
                            class="bg-white rounded-lg border border-stone-200 hover:border-stone-300 transition-colors duration-200 p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="">
                                        <svg
                                            class="w-5 h-5 text-stone-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-stone-900">
                                            {{ session.title }}
                                        </div>
                                        <div class="text-sm text-stone-500">
                                            {{ session.formatted_session_duration }}
                                            • {{ session.recordings[0]?.formatted_file_size }}
                                        </div>
                                        <div
                                            v-if="session.description"
                                            class="text-xs text-stone-400 mt-1"
                                        >
                                            {{ session.description }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <!-- Play/Pause Button -->
                                    <button
                                        @click="playFile(session.recordings[0])"
                                        class="p-1.5 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-100 transition-colors duration-200"
                                    >
                                        <svg
                                            v-if="!(currentlyPlayingId === session.recordings[0]?.id && isPlayerPlaying)"
                                            class="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                                        </svg>
                                    </button>

                                    <!-- Download Button -->
                                    <button
                                        @click="downloadFile(session.recordings[0])"
                                        class="p-2 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-100 transition-colors duration-200"
                                    >
                                        <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                                />
                                            </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button
                                        @click="deleteSession(session)"
                                        class="p-2 rounded-full text-stone-600 hover:text-red-600 hover:bg-red-50 transition-colors duration-200"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Expandable Session for Multiple Recordings -->
                        <div
                            v-else
                            class="bg-white rounded-lg border border-stone-200 hover:border-stone-300 transition-colors duration-200"
                        >
                        <!-- Session Header -->
                        <div
                            class="p-4"
                            :class="{
                                'border-b border-stone-100': isSessionExpanded(
                                    session.id
                                ),
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="p-2">
                                        <svg
                                            class="w-5 h-5 text-stone-600"
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
                                    <div class="flex-1">
                                        <div class="font-medium text-stone-900">
                                            Grouped Memos | {{ session.title }}
                                        </div>
                                        <div class="text-sm text-stone-500">
                                            {{
                                                session.formatted_session_duration
                                            }}
                                            • {{ session.total_loops }} loops •
                                            {{ session.recordings_count }}
                                            recordings
                                        </div>
                                        <div
                                            v-if="session.description"
                                            class="text-xs text-stone-400 mt-1"
                                        >
                                            {{ session.description }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="
                                            toggleSessionExpanded(session.id)
                                        "
                                        class="p-2 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-50 transition-colors duration-200"
                                        :class="{
                                            'rotate-180': isSessionExpanded(
                                                session.id
                                            ),
                                        }"
                                    >
                                        <svg
                                            class="w-5 h-5 transition-transform duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteSession(session)"
                                        class="p-2 rounded-full text-stone-600 hover:text-red-600 hover:bg-red-50 transition-colors duration-200"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Expandable Session Recordings -->
                        <div
                            v-if="isSessionExpanded(session.id)"
                            class="p-4 pt-0 space-y-3"
                        >
                            <div
                                v-if="
                                    session.recordings &&
                                    session.recordings.length > 0
                                "
                                class="space-y-2"
                            >
                                <div
                                    class="text-xs font-medium text-stone-600 mb-2"
                                >
                                    Loop Recordings:
                                </div>
                                <div
                                    v-for="recording in session.recordings"
                                    :key="recording.id"
                                    class="flex items-center justify-between p-3 bg-stone-50 rounded-lg border border-stone-100 hover:border-stone-200 transition-colors duration-200"
                                >
                                    <div class="flex items-center space-x-3">
                                         <svg
                                            class="w-5 h-5 text-stone-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                                            />
                                        </svg>
                                        <div>
                                            <div
                                                class="text-sm font-medium text-stone-800"
                                            >
                                                Loop {{ recording.loop_number }}
                                            </div>
                                            <div class="text-xs text-stone-500">
                                                {{
                                                    recording.formatted_duration
                                                }}
                                                •
                                                {{
                                                    recording.formatted_file_size
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <button
                                            @click="playFile(recording)"
                                            class="p-1.5 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-100 transition-colors duration-200"
                                        >
                                            <!-- Play Icon -->
                                            <svg
                                                v-if="
                                                    !(
                                                        currentlyPlayingId === recording.id &&
                                                        isPlayerPlaying
                                                    )
                                                "
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                            <!-- Pause Icon -->
                                            <svg
                                                v-else
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            @click="downloadFile(recording)"
                                            class="p-1.5 rounded-full text-stone-600 hover:text-stone-800 hover:bg-stone-100 transition-colors duration-200"
                                        >
                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteFile(recording)"
                                            class="p-2 rounded-full text-stone-600 hover:text-red-600 hover:bg-red-50 transition-colors duration-200"
                                        >
                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="text-center py-4"
                            >
                                <p class="text-sm text-stone-400">
                                    No recordings in this session
                                </p>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Loading more sessions indicator -->
                    <div
                        v-if="loadingMoreSessions"
                        class="flex justify-center py-6"
                    >
                        <div class="flex items-center space-x-3 text-stone-500">
                            <svg
                                class="w-6 h-6 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <span class="text-sm font-medium"
                                >Loading more sessions...</span
                            >
                        </div>
                    </div>

                    <!-- End of sessions indicator -->
                    <div
                        v-else-if="!hasMoreSessions && sessions.length > 0"
                        class="text-center py-4"
                    >
                        <span class="text-xs text-stone-400"
                            >You've reached the end of your sessions</span
                        >
                    </div>

                    <!-- Scroll trigger element for sessions -->
                    <div
                        ref="sessionsScrollTrigger"
                        class="h-1 w-full"
                    ></div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="
                    (recordingMode === 'single' &&
                        recordings.length === 0 &&
                        !loadingRecordings) ||
                    (recordingMode === 'looped' &&
                        sessions.length === 0 &&
                        !loadingSessions)
                "
                class="text-center py-12"
            >
                <div class="p-4 rounded-lg bg-stone-100 inline-block mb-4">
                    <svg
                        class="w-8 h-8 text-stone-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                        />
                    </svg>
                </div>
                <p class="text-stone-500 mb-2">
                    No
                    {{ recordingMode === 'looped' ? 'sessions' : 'recordings' }}
                    yet
                </p>
                <p class="text-stone-400 text-sm">
                    {{
                        recordingMode === 'looped'
                            ? 'Start a looped recording session'
                            : 'Create your first recording'
                    }}
                </p>
            </div>
        </div>

        <!-- Now playing -->
        <div
            v-if="currentlyPlayingFile"
            class="mt-4 pt-4 border-t border-stone-200 shrink-0"
        >
            <div class="text-sm font-medium text-stone-700 mb-2 truncate">
                {{ currentlyPlayingFile.title }}
            </div>
            <WaveformPlayer
                ref="waveformPlayer"
                :key="currentlyPlayingFile.id"
                :url="currentlyPlayingFile.url"
                @finish="stopPlayback"
                @play="isPlayerPlaying = true"
                @pause="isPlayerPlaying = false"
            />
        </div>

        <!-- Delete Confirmation Modal -->
        <div 
            v-if="showDeleteModal"
            class="fixed inset-0 flex items-center justify-center z-50"
            style="background-color: rgba(0, 0, 0, 0.6);"
            @click="cancelDelete"
        >
            <div 
                class="bg-white rounded-lg shadow-xl p-6 m-4 max-w-md w-full"
                @click.stop
            >
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-red-100 rounded-full mr-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ deleteModalTitle }}
                    </h3>
                </div>
                
                <p class="text-gray-700 mb-6">
                    {{ deleteModalMessage }}
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button
                        @click="cancelDelete"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmDelete"
                        class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import WaveformPlayer from './WaveformPlayer.vue';

export default {
    name: 'RecordingsDrawer',
    components: {
        WaveformPlayer,
    },
    props: {
        recordingMode: {
            type: String,
            required: true,
            validator: (value) => ['single', 'looped'].includes(value),
        },
        isMobile: {
            type: Boolean,
            default: false,
        },
        useIndexedDb: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close'],
    setup(props) {
        const recordings = ref([]);
        const sessions = ref([]);
        const expandedSessions = ref(new Set());
        const currentlyPlayingId = ref(null);
        const currentlyPlayingFile = ref(null);
        const currentlyPlayingBlobUrl = ref(null);
        const isPlayerPlaying = ref(false);
        const waveformPlayer = ref(null);

        // Infinite scroll state for recordings
        const loadingRecordings = ref(false);
        const loadingMore = ref(false);
        const currentRecordingsPage = ref(1);
        const hasMoreRecordings = ref(true);

        // Infinite scroll state for sessions
        const loadingSessions = ref(false);
        const loadingMoreSessions = ref(false);
        const currentSessionsPage = ref(1);
        const hasMoreSessions = ref(true);

        // Intersection observers
        const recordingsObserver = ref(null);
        const sessionsObserver = ref(null);
        const scrollTrigger = ref(null);
        const sessionsScrollTrigger = ref(null);
        const scrollContainer = ref(null);

        // Delete modal state
        const showDeleteModal = ref(false);
        const deleteModalTitle = ref('');
        const deleteModalMessage = ref('');
        const pendingDeleteAction = ref(null);

        // Load recordings from API on component mount
        onMounted(() => {
            loadDataFromAPI();
            setupInfiniteScroll();
        });

        // Cleanup on unmount
        onUnmounted(() => {
            if (recordingsObserver.value) {
                recordingsObserver.value.disconnect();
            }
            if (sessionsObserver.value) {
                sessionsObserver.value.disconnect();
            }
            stopPlayback();
        });

        // Watch for recordingMode changes to reload data
        watch(
            () => props.recordingMode,
            () => {
                // Reset pagination state
                currentRecordingsPage.value = 1;
                currentSessionsPage.value = 1;
                hasMoreRecordings.value = true;
                hasMoreSessions.value = true;

                loadDataFromAPI();
                expandedSessions.value.clear(); // Clear expanded state when switching modes

                // Re-setup infinite scroll after mode change
                setTimeout(() => {
                    setupInfiniteScroll();
                }, 200);
            }
        );

        // Watch for data changes to re-setup intersection observer
        watch(
            [recordings, sessions],
            () => {
                setTimeout(() => {
                    setupInfiniteScroll();
                }, 100);
            },
            { deep: false }
        );

        const loadDataFromAPI = async () => {
            if (props.recordingMode === 'looped') {
                await loadSessionsFromAPI();
            } else {
                await loadRecordingsFromAPI();
            }
        };

        const loadRecordingsFromAPI = async (page = 1, append = false) => {
            if (page === 1) {
                loadingRecordings.value = true;
            } else {
                loadingMore.value = true;
            }

            try {
                const response = await window.axios.get('/api/recordings', {
                    params: {
                        mode: props.recordingMode,
                        page: page,
                        per_page: 15,
                    },
                });

                if (append) {
                    recordings.value = [
                        ...recordings.value,
                        ...(response.data.recordings || []),
                    ];
                } else {
                    recordings.value = response.data.recordings || [];
                }

                // Update pagination state
                const pagination = response.data.pagination || {};
                currentRecordingsPage.value = pagination.current_page || 1;
                hasMoreRecordings.value = pagination.has_more_pages || false;
            } catch (error) {
                console.error('Error loading recordings from API:', error);
                // Set empty array on error to show empty state
                if (!append) {
                    recordings.value = [];
                }
            } finally {
                loadingRecordings.value = false;
                loadingMore.value = false;
            }
        };

        const loadSessionsFromAPI = async (page = 1, append = false) => {
            if (page === 1) {
                loadingSessions.value = true;
            } else {
                loadingMoreSessions.value = true;
            }

         try {
                const response = await window.axios.get(
                    '/api/recording-sessions',
                    {
                        // headers: { Authorization: `Bearer ${token}` },
                        params: {
                            type: 'looped',
                            page: page,
                            per_page: 15,
                        },
                    }
                );

                if (append) {
                    sessions.value = [
                        ...sessions.value,
                        ...(response.data.sessions || []),
                    ];
                } else {
                    sessions.value = response.data.sessions || [];
                }

                // Update pagination state (assuming sessions API also supports pagination)
                const pagination = response.data.pagination || {};
                currentSessionsPage.value = pagination.current_page || 1;
                hasMoreSessions.value = pagination.has_more_pages || false;
            } catch (error) {
                console.error('Error loading sessions from API:', error);
                // Set empty array on error to show empty state
                if (!append) {
                    sessions.value = [];
                }
            } finally {
                loadingSessions.value = false;
                loadingMoreSessions.value = false;
            }
        };

        const setupInfiniteScroll = () => {
            // Disconnect existing observers
            if (recordingsObserver.value) {
                recordingsObserver.value.disconnect();
            }
            if (sessionsObserver.value) {
                sessionsObserver.value.disconnect();
            }

            // Setup recordings infinite scroll
            recordingsObserver.value = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (
                            entry.isIntersecting &&
                            hasMoreRecordings.value &&
                            !loadingMore.value &&
                            props.recordingMode === 'single'
                        ) {
                            loadMoreRecordings();
                        }
                    });
                },
                {
                    root: null, // Use viewport instead of scrollContainer
                    rootMargin: '50px',
                    threshold: 0.1,
                }
            );

            // Setup sessions infinite scroll
            sessionsObserver.value = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (
                            entry.isIntersecting &&
                            hasMoreSessions.value &&
                            !loadingMoreSessions.value &&
                            props.recordingMode === 'looped'
                        ) {
                            loadMoreSessions();
                        }
                    });
                },
                {
                    root: null, // Use viewport instead of scrollContainer
                    rootMargin: '50px',
                    threshold: 0.1,
                }
            );

            // Observe scroll triggers when they're available with a delay
            setTimeout(() => {
                if (scrollTrigger.value && props.recordingMode === 'single') {
                    recordingsObserver.value.observe(scrollTrigger.value);
                }
                if (
                    sessionsScrollTrigger.value &&
                    props.recordingMode === 'looped'
                ) {
                    sessionsObserver.value.observe(sessionsScrollTrigger.value);
                }
            }, 100);
        };

        const loadMoreRecordings = async () => {
            if (!hasMoreRecordings.value || loadingMore.value) {
                return;
            }
            const nextPage = currentRecordingsPage.value + 1;
            await loadRecordingsFromAPI(nextPage, true);

            // Re-observe the scroll trigger after a delay
            setTimeout(() => {
                if (scrollTrigger.value && recordingsObserver.value) {
                    recordingsObserver.value.observe(scrollTrigger.value);
                }
            }, 100);
        };

        const loadMoreSessions = async () => {
            if (!hasMoreSessions.value || loadingMoreSessions.value) {
                return;
            }
            const nextPage = currentSessionsPage.value + 1;
            await loadSessionsFromAPI(nextPage, true);

            // Re-observe the scroll trigger after a delay
            setTimeout(() => {
                if (sessionsScrollTrigger.value && sessionsObserver.value) {
                    sessionsObserver.value.observe(sessionsScrollTrigger.value);
                }
            }, 100);
        };

        const stopPlayback = () => {
            currentlyPlayingId.value = null;
            currentlyPlayingFile.value = null;
            isPlayerPlaying.value = false;

            if (currentlyPlayingBlobUrl.value) {
                URL.revokeObjectURL(currentlyPlayingBlobUrl.value);
                currentlyPlayingBlobUrl.value = null;
            }
        };

        const resolveAudioUrl = async (file) => {
            if (file.url) {
                return file.url;
            }

            if (!file.id) {
                return null;
            }

            const response = await window.axios.get(
                `/api/recordings/${file.id}/stream`,
                { responseType: 'blob' }
            );
            const blobUrl = URL.createObjectURL(response.data);
            currentlyPlayingBlobUrl.value = blobUrl;
            return blobUrl;
        };

        const playFile = async (file) => {
            try {
                if (
                    currentlyPlayingId.value === file.id &&
                    waveformPlayer.value
                ) {
                    waveformPlayer.value.toggle();
                    return;
                }

                stopPlayback();

                const audioUrl = await resolveAudioUrl(file);
                if (!audioUrl) return;

                currentlyPlayingId.value = file.id;
                currentlyPlayingFile.value = {
                    id: file.id,
                    url: audioUrl,
                    title: file.title || file.name,
                };

                await nextTick();
                isPlayerPlaying.value = waveformPlayer.value?.isPlaying() ?? false;
            } catch (error) {
                console.error('Error playing file:', error);
                stopPlayback();
            }
        };

        const downloadFile = async (file) => {
            try {
                let downloadUrl = file.url;
                let fileName = file.name;

                // If file doesn't have a URL (from API), fetch it
                if (!downloadUrl && file.id) {
                    const response = await window.axios.get(
                        `/api/recordings/${file.id}/stream`,
                        {
                            responseType: 'blob',
                        }
                    );
                    downloadUrl = URL.createObjectURL(response.data);

                    // Get filename from response headers if available
                    const disposition = response.headers['content-disposition'];
                    if (disposition && disposition.includes('filename=')) {
                        fileName = disposition
                            .split('filename=')[1]
                            .replace(/"/g, '');
                    }
                }

                if (downloadUrl) {
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);

                    // Clean up blob URL if we created it
                    if (!file.url) {
                        URL.revokeObjectURL(downloadUrl);
                    }
                }
            } catch (error) {
                console.error('Error downloading file:', error);
            }
        };

        // Modal helper functions
        const showDeleteConfirmation = (title, message, deleteAction) => {
            deleteModalTitle.value = title;
            deleteModalMessage.value = message;
            pendingDeleteAction.value = deleteAction;
            showDeleteModal.value = true;
        };

        const confirmDelete = async () => {
            if (pendingDeleteAction.value) {
                await pendingDeleteAction.value();
            }
            cancelDelete();
        };

        const cancelDelete = () => {
            showDeleteModal.value = false;
            deleteModalTitle.value = '';
            deleteModalMessage.value = '';
            pendingDeleteAction.value = null;
        };

        const deleteSession = async (session) => {
            const actualDeleteSession = async () => {
                try {
                    // Delete session from API (this will also delete associated recordings)
                    await window.axios.delete(
                        `/api/recording-sessions/${session.id}`
                    );

                    // Remove from local list
                    sessions.value = sessions.value.filter(
                        (s) => s.id !== session.id
                    );
                } catch (error) {
                    console.error('Error deleting session:', error);
                    if (error.response?.status === 401) {
                        if (!props.useIndexedDb) {
                            alert('Authentication required. Please log in and try again.');
                        }
                    } else {
                        alert('Error deleting session. Please try again.');
                    }
                }
            };

            showDeleteConfirmation(
                'Delete Session',
                `Are you sure you want to delete "${session.title}" and all its recordings? This action cannot be undone.`,
                actualDeleteSession
            );
        };

        const deleteFile = async (file) => {
            const actualDeleteFile = async () => {
                try {
                    if (file.id) {
                        // Delete from API using axios
                        await window.axios.delete(`/api/recordings/${file.id}`);
                    }

                    // If in single mode, remove from local recordings list
                    if (props.recordingMode === 'single') {
                        recordings.value = recordings.value.filter(
                            (f) => f.id !== file.id
                        );
                    } else if (props.recordingMode === 'looped') {
                        // For looped recordings, refresh session data to update counts and recordings
                        await loadSessionsFromAPI(1, false);
                    }

                    // Clean up blob URL if it exists
                    if (file.url) {
                        URL.revokeObjectURL(file.url);
                    }
                } catch (error) {
                    console.error('Error deleting file:', error);
                    if (error.response?.status === 401) {
                        if (!props.useIndexedDb) {
                            alert('Authentication required. Please log in and try again.');
                        }
                    } else {
                        alert('Error deleting recording. Please try again.');
                    }
                }
            };

            showDeleteConfirmation(
                'Delete Recording',
                `Are you sure you want to delete "${file.title || file.name}"? This action cannot be undone.`,
                actualDeleteFile
            );
        };

        // Method to add a new recording (called from parent)
        const addRecording = (newRecording) => {
            recordings.value.unshift({
                ...newRecording,
                url: URL.createObjectURL(newRecording.blob),
            });
        };

        // Method to refresh data (called from parent when a new recording is saved)
        const refreshData = async () => {
            currentRecordingsPage.value = 1;
            currentSessionsPage.value = 1;
            hasMoreRecordings.value = true;
            hasMoreSessions.value = true;
            await loadDataFromAPI();
        };

        // Toggle session expansion
        const toggleSessionExpanded = (sessionId) => {
            if (expandedSessions.value.has(sessionId)) {
                expandedSessions.value.delete(sessionId);
            } else {
                expandedSessions.value.add(sessionId);
            }
        };

        // Check if session is expanded
        const isSessionExpanded = (sessionId) => {
            return expandedSessions.value.has(sessionId);
        };

        return {
            recordings,
            sessions,
            expandedSessions,
            currentlyPlayingId,
            currentlyPlayingFile,
            isPlayerPlaying,
            waveformPlayer,
            stopPlayback,
            loadingRecordings,
            loadingMore,
            hasMoreRecordings,
            loadingSessions,
            loadingMoreSessions,
            hasMoreSessions,
            scrollTrigger,
            sessionsScrollTrigger,
            scrollContainer,
            playFile,
            downloadFile,
            deleteFile,
            deleteSession,
            addRecording,
            refreshData,
            toggleSessionExpanded,
            isSessionExpanded,
            loadRecordingsFromAPI,
            loadSessionsFromAPI,

            // Modal state and functions
            showDeleteModal,
            deleteModalTitle,
            deleteModalMessage,
            confirmDelete,
            cancelDelete,
        };
    },
};
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
