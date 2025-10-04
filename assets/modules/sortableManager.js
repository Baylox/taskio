import { initCardSortable } from './index.js';

class SortableManager {
    constructor() {
        this.instances = new Map();
    }

    initAll() {
        document.querySelectorAll('.lane-cards').forEach(lane => {
            if (!this.instances.has(lane)) {
                const sortable = initCardSortable(lane);
                this.instances.set(lane, sortable);
            }
        });
    }

    destroyAll() {
        this.instances.forEach((sortable) => {
            if (sortable && sortable.destroy) {
                sortable.destroy();
            }
        });
        this.instances.clear();
    }

    reinitAll() {
        this.destroyAll();
        setTimeout(() => {
            this.initAll();
        }, 50);
    }

    setupEventListeners() {
        // Handle DaisyUI modal toggle events (checkbox-based modals)
        document.addEventListener('change', (event) => {
            if (event.target.matches('.modal-toggle')) {
                if (event.target.checked) {
                    this.destroyAll(); // Modal opened: disable drag-and-drop
                } else {
                    this.reinitAll(); // Modal closed: re-enable drag-and-drop
                }
            }
        });

        // Handle Turbo navigation events (form submissions, redirects)
        document.addEventListener('turbo:load', () => this.initAll());
        document.addEventListener('turbo:render', () => this.reinitAll());

        // Fallback for non-Turbo environments
        document.addEventListener('DOMContentLoaded', () => this.initAll());
    }
}

export const sortableManager = new SortableManager();
