import { useToastStore } from '../stores/toasts';
import { downloadBlobFromApi } from './downloads';
import { displayRecordingTitle, displaySessionTitle } from './libraryDates';

const POLL_INTERVAL_MS = 2000;
const MAX_POLL_ATTEMPTS = 90;

const sleep = (ms) => new Promise((resolve) => {
    window.setTimeout(resolve, ms);
});

async function pollExport(exportMeta, label, toastId) {
    const toastStore = useToastStore();

    for (let attempt = 0; attempt < MAX_POLL_ATTEMPTS; attempt += 1) {
        await sleep(POLL_INTERVAL_MS);

        const { data } = await window.axios.get(`/api/exports/${exportMeta.id}`);
        const current = data.export;

        if (current.status === 'completed' && current.is_ready) {
            toastStore.ready(toastId, {
                message: `${label} is ready to download.`,
                onDownload: () => downloadBlobFromApi(
                    `/api/exports/${current.id}/download`,
                    current.download_filename,
                ),
            });
            return;
        }

        if (current.status === 'failed') {
            toastStore.fail(toastId, current.error_message || 'Export failed.');
            return;
        }
    }

    toastStore.fail(toastId, 'Export is taking longer than expected. Please try again in a moment.');
}

function queueExportTracking(exportMeta, label) {
    const toastStore = useToastStore();
    const toastId = toastStore.pending(`Preparing ${label}...`);
    pollExport(exportMeta, label, toastId).catch((error) => {
        console.error('Export polling failed:', error);
        toastStore.fail(toastId, 'Unable to check export status.');
    });
}

export async function requestRecordingExport(recording, format) {
    if (!recording?.id) return;

    const label = `${displayRecordingTitle(recording)} (${format.toUpperCase()})`;

    try {
        const { data } = await window.axios.post(`/api/exports/recording/${recording.id}`, { format });
        queueExportTracking(data.export, label);
    } catch (error) {
        useToastStore().error(
            error.response?.data?.message
            || 'Could not start download. Please try again.',
        );
        throw error;
    }
}

export async function requestSessionExport(session, format) {
    if (!session?.id) return;

    const label = `${displaySessionTitle(session)} (${format.toUpperCase()} ZIP)`;

    try {
        const { data } = await window.axios.post(`/api/exports/session/${session.id}`, { format });
        queueExportTracking(data.export, label);
    } catch (error) {
        useToastStore().error(
            error.response?.data?.message
            || 'Could not start download. Please try again.',
        );
        throw error;
    }
}
