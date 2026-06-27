import { defineStore } from 'pinia';

let nextToastId = 1;

export const useToastStore = defineStore('toasts', {
    state: () => ({
        items: [],
    }),

    actions: {
        push(toast) {
            const id = nextToastId++;
            this.items.push({
                id,
                type: toast.type || 'info',
                message: toast.message,
                actionLabel: toast.actionLabel || null,
                onAction: toast.onAction || null,
                duration: toast.duration ?? null,
            });

            if (toast.duration) {
                window.setTimeout(() => this.dismiss(id), toast.duration);
            }

            return id;
        },

        pending(message) {
            return this.push({
                type: 'pending',
                message,
            });
        },

        update(id, patch) {
            const item = this.items.find((toast) => toast.id === id);
            if (!item) return;
            Object.assign(item, patch);
        },

        ready(id, { message, onDownload }) {
            this.update(id, {
                type: 'ready',
                message,
                actionLabel: 'Download',
                onAction: onDownload,
            });
        },

        fail(id, message) {
            this.update(id, {
                type: 'error',
                message,
                actionLabel: null,
                onAction: null,
                duration: 8000,
            });

            window.setTimeout(() => this.dismiss(id), 8000);
        },

        error(message) {
            return this.push({
                type: 'error',
                message,
                duration: 8000,
            });
        },

        dismiss(id) {
            this.items = this.items.filter((toast) => toast.id !== id);
        },
    },
});
