function ordinal(day) {
    const remainder = day % 100;
    if (remainder >= 11 && remainder <= 13) return `${day}th`;
    switch (day % 10) {
        case 1: return `${day}st`;
        case 2: return `${day}nd`;
        case 3: return `${day}rd`;
        default: return `${day}th`;
    }
}

/**
 * Parse API date values into a local Date.
 * Handles ISO strings, Laravel "Y-m-d H:i:s" UTC strings, and legacy object shapes.
 */
export function parseApiDate(value) {
    if (value == null) return null;
    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }
    if (typeof value === 'object') {
        if (value.date) return parseApiDate(value.date);
        if (value.iso) return parseApiDate(value.iso);
        return null;
    }
    if (typeof value === 'number') {
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? null : date;
    }
    if (typeof value !== 'string') return null;

    let normalized = value.trim();
    if (!normalized) return null;

    // Laravel often returns "2026-06-27 01:54:04" — treat as UTC from the server.
    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(normalized)) {
        normalized = `${normalized.replace(' ', 'T')}Z`;
    } else if (/^\d{4}-\d{2}-\d{2}T/.test(normalized) && !/[zZ]|[+-]\d{2}:\d{2}$/.test(normalized)) {
        normalized = `${normalized}Z`;
    }

    const date = new Date(normalized);
    return Number.isNaN(date.getTime()) ? null : date;
}

export function getDayKey(dateInput) {
    const date = parseApiDate(dateInput);
    if (!date) return 'unknown';

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function dateFromDayKey(key) {
    if (key === 'unknown') return null;
    const [year, month, day] = key.split('-').map(Number);
    return new Date(year, month - 1, day, 12, 0, 0, 0);
}

export function formatDayLabel(dateInput) {
    const date = parseApiDate(dateInput);
    if (!date) return 'Unknown date';

    const startOfDay = (d) => {
        const copy = new Date(d);
        copy.setHours(0, 0, 0, 0);
        return copy;
    };

    const today = startOfDay(new Date());
    const target = startOfDay(date);
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (target.getTime() === today.getTime()) return 'Today';
    if (target.getTime() === yesterday.getTime()) return 'Yesterday';

    const month = date.toLocaleDateString('en-US', { month: 'long' });
    return `${month} ${ordinal(date.getDate())}, ${date.getFullYear()}`;
}

export function labelFromDayKey(key) {
    if (key === 'unknown') return 'Unknown date';
    return formatDayLabel(dateFromDayKey(key));
}

export function formatItemTimestamp(value, options = {}) {
    const date = parseApiDate(value);
    if (!date) return '';

    const { includeTime = true } = options;
    if (includeTime) {
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
        });
    }

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

export function getSessionActivityDate(session) {
    if (!session) return null;

    const candidates = [
        session.updated_at,
        session.created_at,
        ...(session.recordings || []).map((recording) => recording.created_at),
    ];

    const dates = candidates.map(parseApiDate).filter(Boolean);
    if (!dates.length) return null;

    return dates.reduce((latest, current) => (current > latest ? current : latest));
}

export function groupItemsByDay(items, getDate = (item) => item.created_at) {
    const groupMap = new Map();

    for (const item of items) {
        const rawDate = getDate(item);
        const key = rawDate ? getDayKey(rawDate) : 'unknown';

        if (!groupMap.has(key)) {
            groupMap.set(key, {
                key,
                label: labelFromDayKey(key),
                items: [],
            });
        }

        groupMap.get(key).items.push(item);
    }

    return Array.from(groupMap.values()).sort((a, b) => b.key.localeCompare(a.key));
}
