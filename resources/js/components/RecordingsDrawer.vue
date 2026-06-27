<template>
    <div class="h-full flex flex-col bg-stone-50">
        <!-- Toolbar -->
        <div class="shrink-0 px-4 sm:px-6 pt-4 pb-3 border-b border-stone-200 bg-white">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="min-w-0 flex-1">
                    <nav v-if="currentFolder" class="flex items-center gap-1.5 text-sm mb-1">
                        <button
                            type="button"
                            class="text-stone-500 hover:text-stone-800 transition-colors truncate"
                            @click="closeFolder"
                        >
                            Library
                        </button>
                        <svg class="w-4 h-4 text-stone-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="font-medium text-stone-800 truncate">{{ currentFolder.title }}</span>
                    </nav>
                    <h2 class="text-lg font-semibold text-stone-800">
                        {{ currentFolder ? currentFolder.title : 'Saved Recordings' }}
                    </h2>
                    <p v-if="currentFolder" class="text-xs text-stone-500 mt-0.5">
                        {{ currentFolder.recordings_count }} loops · {{ currentFolder.formatted_session_duration }}
                    </p>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    <button
                        type="button"
                        class="p-2 rounded-lg transition-colors"
                        :class="viewMode === 'grid' ? 'bg-stone-200 text-stone-800' : 'text-stone-400 hover:text-stone-600 hover:bg-stone-100'"
                        aria-label="Grid view"
                        @click="viewMode = 'grid'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="p-2 rounded-lg transition-colors"
                        :class="viewMode === 'list' ? 'bg-stone-200 text-stone-800' : 'text-stone-400 hover:text-stone-600 hover:bg-stone-100'"
                        aria-label="List view"
                        @click="viewMode = 'list'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <button
                v-if="currentFolder"
                type="button"
                class="flex items-center gap-1.5 text-sm text-stone-600 hover:text-stone-900 transition-colors"
                @click="closeFolder"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to library
            </button>
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
                        <div :class="gridClasses">
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
                                    @download="downloadFile(recording)"
                                    @delete="deleteFile(recording)"
                                    @waveform-play="onWaveformPlay(recording.id)"
                                    @waveform-pause="onWaveformPause"
                                    @waveform-finish="onWaveformFinish(recording.id)"
                                />
                            </div>
                        </div>
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
                        <div :class="gridClasses">
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
                                    @download="downloadFile(file)"
                                    @delete="deleteFile(file)"
                                    @waveform-play="onWaveformPlay(file.id)"
                                    @waveform-pause="onWaveformPause"
                                    @waveform-finish="onWaveformFinish(file.id)"
                                />
                            </div>
                        </div>
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
                        <div :class="gridClasses">
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
                                    @download="downloadFile(session.recordings[0])"
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
                                    @delete="deleteSession(session)"
                                />
                            </div>
                        </div>
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

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 flex items-center justify-center z-50"
            style="background-color: rgba(0, 0, 0, 0.6);"
            @click="cancelDelete"
        >
            <div class="bg-white rounded-lg shadow-xl p-6 m-4 max-w-md w-full" @click.stop>
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-red-100 rounded-full mr-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ deleteModalTitle }}</h3>
                </div>
                <p class="text-gray-700 mb-6">{{ deleteModalMessage }}</p>
                <div class="flex justify-end gap-3">
                    <button @click="cancelDelete" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">Cancel</button>
                    <button @click="confirmDelete" class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200">Delete</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import LibraryRecordingCard from './LibraryRecordingCard.vue';
import LibraryFolderCard from './LibraryFolderCard.vue';
import {
    groupItemsByDay,
    getSessionActivityDate,
    formatItemTimestamp,
    displayRecordingTitle,
    displaySessionTitle,
} from '../utils/libraryDates.js';
import { LIBRARY_PAGE_SIZE } from '../utils/libraryPagination.js';

export default {
    name: 'RecordingsDrawer',
    components: { LibraryRecordingCard, LibraryFolderCard },
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

        const downloadFile = async (file) => {
            try {
                let downloadUrl = file.url;
                let fileName = file.name;
                if (!downloadUrl && file.id) {
                    downloadUrl = await resolveUrl(file);
                    const response = await window.axios.get(`/api/recordings/${file.id}/stream`, { responseType: 'blob' });
                    const disposition = response.headers['content-disposition'];
                    if (disposition?.includes('filename=')) {
                        fileName = disposition.split('filename=')[1].replace(/"/g, '');
                    }
                }
                if (downloadUrl) {
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            } catch (error) {
                console.error('Error downloading file:', error);
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
                        } else {
                            await loadSessionsFromAPI(1, false);
                            if (currentFolder.value) {
                                const updated = sessions.value.find((s) => s.id === currentFolder.value.id);
                                currentFolder.value = updated || null;
                            }
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
