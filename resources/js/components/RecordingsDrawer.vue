<template>
    <div class="h-full flex flex-col bg-stone-50">
        <!-- Toolbar -->
        <div class="shrink-0 px-4 sm:px-6 py-3 border-b border-stone-200/80 bg-white">
            <template v-if="currentFolder">
                <div class="flex items-center justify-between gap-3 mb-2.5">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800 transition-colors -ml-1"
                        @click="closeFolder"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Library
                    </button>
                    <div class="inline-flex rounded-md border border-stone-200 bg-stone-50 p-0.5 shrink-0">
                        <button
                            type="button"
                            class="p-1.5 rounded transition-colors"
                            :class="viewMode === 'grid' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-400 hover:text-stone-600'"
                            aria-label="Grid view"
                            @click="viewMode = 'grid'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="p-1.5 rounded transition-colors"
                            :class="viewMode === 'list' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-400 hover:text-stone-600'"
                            aria-label="List view"
                            @click="viewMode = 'list'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="min-w-0 pl-0">
                    <h2 class="text-base font-semibold text-stone-900 truncate">
                        {{ displaySessionTitle(currentFolder) }}
                    </h2>
                    <p class="text-sm text-stone-400 mt-0.5">
                        {{ currentFolder.recordings_count }} loops · {{ currentFolder.formatted_session_duration }}
                    </p>
                    <DownloadFormatMenu
                        variant="inline"
                        aria-label="Download all loops"
                        button-class="mt-2 inline-flex items-center gap-1.5 text-sm text-stone-600 hover:text-stone-900 transition-colors"
                        @download="(format) => downloadSession(currentFolder, format)"
                    >
                        <template #icon>
                            <span class="inline-flex items-center gap-1.5 px-1 py-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download all
                            </span>
                        </template>
                    </DownloadFormatMenu>
                </div>
            </template>

            <div v-else class="flex items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-stone-900">Saved Recordings</h2>
                <div class="inline-flex rounded-md border border-stone-200 bg-stone-50 p-0.5 shrink-0">
                    <button
                        type="button"
                        class="p-1.5 rounded transition-colors"
                        :class="viewMode === 'grid' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-400 hover:text-stone-600'"
                        aria-label="Grid view"
                        @click="viewMode = 'grid'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="p-1.5 rounded transition-colors"
                        :class="viewMode === 'list' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-400 hover:text-stone-600'"
                        aria-label="List view"
                        @click="viewMode = 'list'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Scrollable content -->
        <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4" ref="scrollContainer">
            <div v-if="isLoading" class="flex justify-center py-16">
                <div class="flex items-center gap-3 text-stone-500">
                    <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span class="text-sm font-medium">Loading...</span>
                </div>
            </div>

            <template v-else-if="currentFolder">
                <div v-if="groupedFolderRecordings.length">
                    <section
                        v-for="group in groupedFolderRecordings"
                        :key="group.key"
                        class="mb-8 last:mb-0"
                    >
                        <h3 class="text-sm font-semibold text-stone-700 mb-3">
                            {{ group.label }}
                        </h3>
                        <TransitionGroup
                            name="library-item"
                            tag="div"
                            :class="gridClasses"
                        >
                            <div
                                v-for="recording in group.items"
                                :key="recording.id"
                                :class="cellClasses(recording.id)"
                            >
                                <LibraryRecordingCard
                                    :recording="recording"
                                    :title="`Loop ${recording.loop_number}`"
                                    :subtitle="formatItemTimestamp(recording.created_at)"
                                    :view-mode="viewMode"
                                    :is-playing="inlinePlayingId === recording.id && inlinePlaying"
                                    :is-expanded="expandedId === recording.id"
                                    :playback-url="urlCache[recording.id] || ''"
                                    @toggle-play="toggleInlinePlay(recording)"
                                    @toggle-expand="toggleExpand(recording)"
                                    @open-full="openFullPlayer(recording)"
                                    @download="(format) => downloadFile(recording, format)"
                                    @delete="deleteFile(recording)"
                                    @waveform-play="onWaveformPlay(recording.id)"
                                    @waveform-pause="onWaveformPause"
                                    @waveform-finish="onWaveformFinish(recording.id)"
                                />
                            </div>
                        </TransitionGroup>
                    </section>
                </div>
                <p v-else class="text-center py-16 text-stone-400 text-sm">No recordings in this session</p>
            </template>

            <template v-else>
                <div v-if="recordingMode === 'single' && groupedRecordings.length">
                    <section
                        v-for="group in groupedRecordings"
                        :key="group.key"
                        class="mb-8 last:mb-0"
                    >
                        <h3 class="text-sm font-semibold text-stone-700 mb-3">
                            {{ group.label }}
                        </h3>
                        <TransitionGroup
                            name="library-item"
                            tag="div"
                            :class="gridClasses"
                        >
                            <div
                                v-for="file in group.items"
                                :key="file.id"
                                :class="cellClasses(file.id)"
                            >
                                <LibraryRecordingCard
                                    :recording="file"
                                    :title="displayRecordingTitle(file)"
                                    :subtitle="recordingSubtitle(file, file.recording_type?.display_name)"
                                    :view-mode="viewMode"
                                    :is-playing="inlinePlayingId === file.id && inlinePlaying"
                                    :is-expanded="expandedId === file.id"
                                    :playback-url="urlCache[file.id] || ''"
                                    @toggle-play="toggleInlinePlay(file)"
                                    @toggle-expand="toggleExpand(file)"
                                    @open-full="openFullPlayer(file)"
                                    @download="(format) => downloadFile(file, format)"
                                    @delete="deleteFile(file)"
                                    @waveform-play="onWaveformPlay(file.id)"
                                    @waveform-pause="onWaveformPause"
                                    @waveform-finish="onWaveformFinish(file.id)"
                                />
                            </div>
                        </TransitionGroup>
                    </section>
                </div>

                <div v-else-if="recordingMode === 'looped' && groupedSessions.length">
                    <section
                        v-for="group in groupedSessions"
                        :key="group.key"
                        class="mb-8 last:mb-0"
                    >
                        <h3 class="text-sm font-semibold text-stone-700 mb-3">
                            {{ group.label }}
                        </h3>
                        <TransitionGroup
                            name="library-item"
                            tag="div"
                            :class="gridClasses"
                        >
                            <div
                                v-for="session in group.items"
                                :key="session.id"
                                :class="viewMode === 'grid' ? 'h-full min-h-[152px]' : ''"
                            >
                                <LibraryRecordingCard
                                    v-if="session.recordings_count === 1"
                                    :recording="session.recordings[0]"
                                    :title="displaySessionTitle(session)"
                                    :subtitle="recordingSubtitle(session.recordings[0], session.formatted_session_duration)"
                                    :view-mode="viewMode"
                                    :is-playing="inlinePlayingId === session.recordings[0]?.id && inlinePlaying"
                                    :is-expanded="expandedId === session.recordings[0]?.id"
                                    :playback-url="urlCache[session.recordings[0]?.id] || ''"
                                    @toggle-play="toggleInlinePlay(session.recordings[0])"
                                    @toggle-expand="toggleExpand(session.recordings[0])"
                                    @open-full="openFullPlayer(session.recordings[0])"
                                    @download="(format) => downloadFile(session.recordings[0], format)"
                                    @delete="deleteSession(session)"
                                    @waveform-play="onWaveformPlay(session.recordings[0]?.id)"
                                    @waveform-pause="onWaveformPause"
                                    @waveform-finish="onWaveformFinish(session.recordings[0]?.id)"
                                />
                                <LibraryFolderCard
                                    v-else
                                    :session="session"
                                    :view-mode="viewMode"
                                    :recorded-at="formatItemTimestamp(getSessionActivityDate(session))"
                                    @open="openFolder(session)"
                                    @download="(format) => downloadSession(session, format)"
                                    @delete="deleteSession(session)"
                                />
                            </div>
                        </TransitionGroup>
                    </section>
                </div>

                <div
                    v-else-if="!isLoading"
                    class="text-center py-16"
                >
                    <div class="p-4 rounded-xl bg-stone-100 inline-block mb-4">
                        <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <p class="text-stone-500 mb-1">
                        {{ recordingMode === 'looped' ? 'No sessions yet' : 'No recordings yet' }}
                    </p>
                    <p class="text-stone-400 text-sm">
                        {{ recordingMode === 'looped' ? 'Start a looped recording session' : 'Create your first recording' }}
                    </p>
                </div>
            </template>

            <div v-if="isLoadingMore" class="flex justify-center py-8">
                <div class="flex items-center gap-3 text-stone-500">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span class="text-sm">Loading more...</span>
                </div>
            </div>
            <div v-else-if="!hasMore && hasItems && !currentFolder" class="text-center py-6">
                <span class="text-xs text-stone-400">You've reached the end</span>
            </div>
            <div ref="scrollTrigger" class="h-1 w-full" />
        </div>

        <ConfirmModal
            :is-open="showDeleteModal"
            :title="deleteModalTitle"
            :message="deleteModalMessage"
            confirm-text="Delete"
            @close="cancelDelete"
            @confirm="confirmDelete"
        />
    </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import LibraryRecordingCard from './LibraryRecordingCard.vue';
import LibraryFolderCard from './LibraryFolderCard.vue';
import ConfirmModal from './ConfirmModal.vue';
import DownloadFormatMenu from './DownloadFormatMenu.vue';
import {
    groupItemsByDay,
    getSessionActivityDate,
    formatItemTimestamp,
    displayRecordingTitle,
    displaySessionTitle,
} from '../utils/libraryDates.js';
import { LIBRARY_PAGE_SIZE } from '../utils/libraryPagination.js';
import {
    requestRecordingExport,
    requestSessionExport,
} from '../utils/exports.js';

export default {
    name: 'RecordingsDrawer',
    components: { LibraryRecordingCard, LibraryFolderCard, ConfirmModal, DownloadFormatMenu },
    props: {
        recordingMode: {
            type: String,
            required: true,
            validator: (value) => ['single', 'looped'].includes(value),
        },
        useIndexedDb: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['expand-recording'],
    setup(props, { emit }) {
        const recordings = ref([]);
        const sessions = ref([]);
        const viewMode = ref('grid');
        const currentFolder = ref(null);

        const inlinePlayingId = ref(null);
        const inlinePlaying = ref(false);
        const expandedId = ref(null);
        const urlCache = ref({});
        let inlineAudio = null;
        const blobUrls = ref([]);

        const loadingRecordings = ref(false);
        const loadingMore = ref(false);
        const currentRecordingsPage = ref(1);
        const hasMoreRecordings = ref(true);

        const loadingSessions = ref(false);
        const loadingMoreSessions = ref(false);
        const currentSessionsPage = ref(1);
        const hasMoreSessions = ref(true);

        const scrollObserver = ref(null);
        const scrollTrigger = ref(null);
        const scrollContainer = ref(null);

        const showDeleteModal = ref(false);
        const deleteModalTitle = ref('');
        const deleteModalMessage = ref('');
        const pendingDeleteAction = ref(null);

        const isLoading = computed(() =>
            props.recordingMode === 'looped' ? loadingSessions.value : loadingRecordings.value
        );
        const isLoadingMore = computed(() =>
            props.recordingMode === 'looped' ? loadingMoreSessions.value : loadingMore.value
        );
        const hasMore = computed(() =>
            props.recordingMode === 'looped' ? hasMoreSessions.value : hasMoreRecordings.value
        );
        const hasItems = computed(() =>
            props.recordingMode === 'looped' ? sessions.value.length > 0 : recordings.value.length > 0
        );
        const gridClasses = computed(() =>
            viewMode.value === 'grid'
                ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 items-stretch auto-rows-fr'
                : 'space-y-2'
        );

        const cellClasses = (id) => {
            if (viewMode.value !== 'grid') return '';
            return [
                'h-full min-h-[152px]',
                expandedId.value === id ? 'col-span-full' : '',
            ];
        };

        const groupedRecordings = computed(() => groupItemsByDay(recordings.value));
        const groupedSessions = computed(() =>
            groupItemsByDay(sessions.value, getSessionActivityDate)
        );
        const groupedFolderRecordings = computed(() =>
            groupItemsByDay(currentFolder.value?.recordings || [])
        );

        onMounted(() => {
            loadDataFromAPI();
            setupInfiniteScroll();
        });

        onUnmounted(() => {
            scrollObserver.value?.disconnect();
            stopInlineAudio();
            revokeBlobUrls();
        });

        watch(() => props.recordingMode, () => {
            currentRecordingsPage.value = 1;
            currentSessionsPage.value = 1;
            hasMoreRecordings.value = true;
            hasMoreSessions.value = true;
            currentFolder.value = null;
            stopInlineAudio();
            expandedId.value = null;
            loadDataFromAPI();
            nextTick(() => setupInfiniteScroll());
        });

        watch([recordings, sessions, groupedSessions, groupedRecordings], () => {
            nextTick(() => setupInfiniteScroll());
        }, { deep: false });

        const revokeBlobUrls = () => {
            blobUrls.value.forEach((url) => URL.revokeObjectURL(url));
            blobUrls.value = [];
            urlCache.value = {};
        };

        const resolveUrl = async (file) => {
            if (!file?.id) return null;
            if (urlCache.value[file.id]) return urlCache.value[file.id];
            if (file.url) {
                urlCache.value[file.id] = file.url;
                return file.url;
            }

            const response = await window.axios.get(`/api/recordings/${file.id}/stream`, {
                responseType: 'blob',
            });
            const blobUrl = URL.createObjectURL(response.data);
            blobUrls.value.push(blobUrl);
            urlCache.value[file.id] = blobUrl;
            return blobUrl;
        };

        const stopInlineAudio = () => {
            if (inlineAudio) {
                inlineAudio.pause();
                inlineAudio.src = '';
                inlineAudio = null;
            }
            inlinePlayingId.value = null;
            inlinePlaying.value = false;
        };

        const toggleInlinePlay = async (file) => {
            if (!file?.id) return;

            if (expandedId.value === file.id) return;

            if (inlinePlayingId.value === file.id && inlineAudio) {
                if (inlineAudio.paused) {
                    await inlineAudio.play();
                    inlinePlaying.value = true;
                } else {
                    inlineAudio.pause();
                    inlinePlaying.value = false;
                }
                return;
            }

            stopInlineAudio();

            try {
                const url = await resolveUrl(file);
                if (!url) return;

                inlineAudio = new Audio(url);
                inlinePlayingId.value = file.id;

                inlineAudio.addEventListener('play', () => { inlinePlaying.value = true; });
                inlineAudio.addEventListener('pause', () => { inlinePlaying.value = false; });
                inlineAudio.addEventListener('ended', () => {
                    inlinePlayingId.value = null;
                    inlinePlaying.value = false;
                    inlineAudio = null;
                });

                await inlineAudio.play();
            } catch (error) {
                console.error('Error playing recording:', error);
                stopInlineAudio();
            }
        };

        const toggleExpand = async (file) => {
            if (!file?.id) return;

            if (expandedId.value === file.id) {
                expandedId.value = null;
                return;
            }

            stopInlineAudio();
            expandedId.value = file.id;

            try {
                await resolveUrl(file);
            } catch (error) {
                console.error('Error loading recording for expand:', error);
            }
        };

        const openFullPlayer = async (file) => {
            if (!file?.id) return;
            stopInlineAudio();
            expandedId.value = null;

            try {
                const url = await resolveUrl(file);
                if (!url) return;

                emit('expand-recording', {
                    ...file,
                    url,
                    title: file.title || file.name,
                });
            } catch (error) {
                console.error('Error opening full player:', error);
            }
        };

        const onWaveformPlay = (id) => {
            stopInlineAudio();
            inlinePlayingId.value = id;
            inlinePlaying.value = true;
        };

        const onWaveformPause = () => {
            inlinePlaying.value = false;
        };

        const onWaveformFinish = (id) => {
            if (inlinePlayingId.value === id) {
                inlinePlayingId.value = null;
                inlinePlaying.value = false;
            }
        };

        const loadDataFromAPI = async () => {
            if (props.recordingMode === 'looped') await loadSessionsFromAPI();
            else await loadRecordingsFromAPI();
        };

        const loadRecordingsFromAPI = async (page = 1, append = false) => {
            if (page === 1) loadingRecordings.value = true;
            else loadingMore.value = true;

            try {
                const response = await window.axios.get('/api/recordings', {
                    params: { mode: props.recordingMode, page, per_page: LIBRARY_PAGE_SIZE },
                });
                recordings.value = append
                    ? [...recordings.value, ...(response.data.recordings || [])]
                    : response.data.recordings || [];
                const pagination = response.data.pagination || {};
                currentRecordingsPage.value = pagination.current_page || 1;
                hasMoreRecordings.value = pagination.has_more_pages || false;
            } catch (error) {
                console.error('Error loading recordings from API:', error);
                if (!append) recordings.value = [];
            } finally {
                loadingRecordings.value = false;
                loadingMore.value = false;
                nextTick(() => setupInfiniteScroll());
            }
        };

        const loadSessionsFromAPI = async (page = 1, append = false) => {
            if (page === 1) loadingSessions.value = true;
            else loadingMoreSessions.value = true;

            try {
                const response = await window.axios.get('/api/recording-sessions', {
                    params: { type: 'looped', page, per_page: LIBRARY_PAGE_SIZE },
                });
                sessions.value = append
                    ? [...sessions.value, ...(response.data.sessions || [])]
                    : response.data.sessions || [];
                const pagination = response.data.pagination || {};
                currentSessionsPage.value = pagination.current_page || 1;
                hasMoreSessions.value = pagination.has_more_pages || false;
            } catch (error) {
                console.error('Error loading sessions from API:', error);
                if (!append) sessions.value = [];
            } finally {
                loadingSessions.value = false;
                loadingMoreSessions.value = false;
                nextTick(() => setupInfiniteScroll());
            }
        };

        const setupInfiniteScroll = () => {
            scrollObserver.value?.disconnect();
            scrollObserver.value = new IntersectionObserver(
                (entries) => {
                    if (!entries[0]?.isIntersecting || currentFolder.value) return;
                    if (props.recordingMode === 'single' && hasMoreRecordings.value && !loadingMore.value) {
                        loadMoreRecordings();
                    } else if (props.recordingMode === 'looped' && hasMoreSessions.value && !loadingMoreSessions.value) {
                        loadMoreSessions();
                    }
                },
                { root: scrollContainer.value, rootMargin: '100px', threshold: 0.1 }
            );
            nextTick(() => {
                if (scrollTrigger.value) scrollObserver.value.observe(scrollTrigger.value);
            });
        };

        const loadMoreRecordings = async () => {
            if (!hasMoreRecordings.value || loadingMore.value) return;
            await loadRecordingsFromAPI(currentRecordingsPage.value + 1, true);
        };

        const loadMoreSessions = async () => {
            if (!hasMoreSessions.value || loadingMoreSessions.value) return;
            await loadSessionsFromAPI(currentSessionsPage.value + 1, true);
        };

        const openFolder = (session) => {
            stopInlineAudio();
            expandedId.value = null;
            currentFolder.value = session;
        };

        const closeFolder = () => {
            stopInlineAudio();
            expandedId.value = null;
            currentFolder.value = null;
        };

        const downloadFile = async (file, format = 'mp3') => {
            if (!file?.id) return;

            try {
                await requestRecordingExport(file, format);
            } catch (error) {
                console.error('Error requesting recording export:', error);
            }
        };

        const downloadSession = async (session, format = 'mp3') => {
            if (!session?.id) return;

            try {
                await requestSessionExport(session, format);
            } catch (error) {
                console.error('Error requesting session export:', error);
            }
        };

        const removeRecordingFromSessionState = (file) => {
            if (!file?.id) return;

            sessions.value = sessions.value.map((session) => {
                if (!session.recordings?.some((recording) => recording.id === file.id)) {
                    return session;
                }

                const recordings = session.recordings.filter((recording) => recording.id !== file.id);

                return {
                    ...session,
                    recordings,
                    recordings_count: recordings.length,
                };
            });

            if (currentFolder.value?.recordings?.some((recording) => recording.id === file.id)) {
                const recordings = currentFolder.value.recordings.filter((recording) => recording.id !== file.id);
                currentFolder.value = {
                    ...currentFolder.value,
                    recordings,
                    recordings_count: recordings.length,
                };
            }
        };

        const showDeleteConfirmation = (title, message, deleteAction) => {
            deleteModalTitle.value = title;
            deleteModalMessage.value = message;
            pendingDeleteAction.value = deleteAction;
            showDeleteModal.value = true;
        };

        const confirmDelete = async () => {
            if (pendingDeleteAction.value) await pendingDeleteAction.value();
            cancelDelete();
        };

        const cancelDelete = () => {
            showDeleteModal.value = false;
            deleteModalTitle.value = '';
            deleteModalMessage.value = '';
            pendingDeleteAction.value = null;
        };

        const deleteSession = async (session) => {
            showDeleteConfirmation(
                'Delete Session',
                `Are you sure you want to delete "${session.title}" and all its recordings? This action cannot be undone.`,
                async () => {
                    try {
                        await window.axios.delete(`/api/recording-sessions/${session.id}`);
                        sessions.value = sessions.value.filter((s) => s.id !== session.id);
                        if (currentFolder.value?.id === session.id) currentFolder.value = null;
                    } catch (error) {
                        console.error('Error deleting session:', error);
                        alert(error.response?.status === 401 && !props.useIndexedDb
                            ? 'Authentication required. Please log in and try again.'
                            : 'Error deleting session. Please try again.');
                    }
                }
            );
        };

        const deleteFile = async (file) => {
            showDeleteConfirmation(
                'Delete Recording',
                `Are you sure you want to delete "${file.title || file.name}"? This action cannot be undone.`,
                async () => {
                    try {
                        if (file.id) await window.axios.delete(`/api/recordings/${file.id}`);
                        if (inlinePlayingId.value === file.id) stopInlineAudio();
                        if (expandedId.value === file.id) expandedId.value = null;
                        delete urlCache.value[file.id];

                        if (props.recordingMode === 'single') {
                            recordings.value = recordings.value.filter((f) => f.id !== file.id);
                        } else if (currentFolder.value) {
                            removeRecordingFromSessionState(file);
                        } else {
                            await loadSessionsFromAPI(1, false);
                        }
                    } catch (error) {
                        console.error('Error deleting file:', error);
                        alert(error.response?.status === 401 && !props.useIndexedDb
                            ? 'Authentication required. Please log in and try again.'
                            : 'Error deleting recording. Please try again.');
                    }
                }
            );
        };

        const recordingSubtitle = (recording, extra = '') => {
            const timestamp = formatItemTimestamp(recording?.created_at);
            return [extra, timestamp].filter(Boolean).join(' · ');
        };

        const refreshData = async () => {
            currentRecordingsPage.value = 1;
            currentSessionsPage.value = 1;
            hasMoreRecordings.value = true;
            hasMoreSessions.value = true;
            await loadDataFromAPI();
        };

        return {
            recordings,
            sessions,
            viewMode,
            currentFolder,
            inlinePlayingId,
            inlinePlaying,
            expandedId,
            urlCache,
            isLoading,
            isLoadingMore,
            hasMore,
            hasItems,
            gridClasses,
            cellClasses,
            groupedRecordings,
            groupedSessions,
            groupedFolderRecordings,
            scrollTrigger,
            scrollContainer,
            openFolder,
            closeFolder,
            toggleInlinePlay,
            toggleExpand,
            openFullPlayer,
            onWaveformPlay,
            onWaveformPause,
            onWaveformFinish,
            downloadFile,
            downloadSession,
            deleteFile,
            deleteSession,
            refreshData,
            showDeleteModal,
            deleteModalTitle,
            deleteModalMessage,
            confirmDelete,
            cancelDelete,
            recordingSubtitle,
            formatItemTimestamp,
            getSessionActivityDate,
            displayRecordingTitle,
            displaySessionTitle,
        };
    },
};
</script>

<style scoped>
.overflow-y-auto::-webkit-scrollbar { width: 4px; }
.overflow-y-auto::-webkit-scrollbar-track { background: #f1f5f9; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
</style>
