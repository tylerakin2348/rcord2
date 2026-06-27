function parseFilenameFromDisposition(disposition) {
    if (!disposition) return null;

    const utf8Match = disposition.match(/filename\*=UTF-8''([^;]+)/i);
    if (utf8Match?.[1]) {
        return decodeURIComponent(utf8Match[1]);
    }

    const quotedMatch = disposition.match(/filename="([^"]+)"/i);
    if (quotedMatch?.[1]) {
        return quotedMatch[1];
    }

    const plainMatch = disposition.match(/filename=([^;]+)/i);
    if (plainMatch?.[1]) {
        return plainMatch[1].trim();
    }

    return null;
}

export function triggerBrowserDownload(url, filename) {
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = filename;
    anchor.rel = 'noopener';
    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
}

export async function downloadBlobFromApi(url, fallbackFilename = 'download') {
    const response = await window.axios.get(url, { responseType: 'blob' });
    const contentType = response.headers['content-type'] || '';

    if (contentType.includes('application/json')) {
        const message = JSON.parse(await response.data.text()).message || 'Download failed';
        throw new Error(message);
    }

    const filename = parseFilenameFromDisposition(response.headers['content-disposition'])
        || fallbackFilename;
    const blobUrl = URL.createObjectURL(response.data);
    triggerBrowserDownload(blobUrl, filename);
    URL.revokeObjectURL(blobUrl);
}

export function recordingDownloadFilename(recording) {
    if (recording?.filename) return recording.filename;
    if (recording?.title) return `${recording.title}.webm`;
    return 'recording.webm';
}

export function sessionDownloadFilename(session) {
    const base = (session?.title || 'session')
        .replace(/[^\w\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .toLowerCase() || 'session';

    return `${base}.zip`;
}
