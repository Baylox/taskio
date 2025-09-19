// Assets/app.js
import '@hotwired/turbo';
import { Application } from '@hotwired/stimulus';
import { registerControllers } from 'stimulus-vite-helpers';
import './styles/app.css';
import './styles/styles.scss';


// Enable Turbo (replaces traditional redirects)
Turbo.start()

// Stimulus
const app = Application.start();
registerControllers(app, import.meta.glob('./controllers/**/*_controller.js'));


// Additional JavaScript files
import { initCardSortable } from './modules/index.js';
import.meta.glob(['./images/**']);

// Store sortable instances to manage lifecycle
const sortableInstances = new Map();
/**
 * Initialize Sortable on all .lane-cards containers
 * Prevents duplicate initialization
 */
function initAllSortables() {
  document.querySelectorAll('.lane-cards').forEach(lane => {
    if (!sortableInstances.has(lane)) {
      const sortable = initCardSortable(lane);
      sortableInstances.set(lane, sortable);
    }
  });
}

/**
 * Destroy all Sortable instances and clear the map
 * Used when modals open to prevent interference
 */
function destroyAllSortables() {
  sortableInstances.forEach((sortable) => {
    if (sortable && sortable.destroy) {
      sortable.destroy();
    }
  });
  sortableInstances.clear();
}

/**
 * Destroy and recreate all Sortable instances
 * Used after modal close or page navigation
 */
function reinitAllSortables() {
  destroyAllSortables();
  setTimeout(() => {
    initAllSortables();
  }, 50);
}

// Handle DaisyUI modal toggle events (checkbox-based modals)
document.addEventListener('change', (event) => {
  if (event.target.matches('.modal-toggle')) {
    if (event.target.checked) {
      destroyAllSortables(); // Modal opened: disable drag-and-drop
    } else {
      reinitAllSortables(); // Modal closed: re-enable drag-and-drop
    }
  }
});

// Handle Turbo navigation events (form submissions, redirects)
document.addEventListener('turbo:load', initAllSortables);
document.addEventListener('turbo:render', reinitAllSortables);

// Fallback for non-Turbo environments
document.addEventListener('DOMContentLoaded', initAllSortables);


console.log('App loaded with Vite + Tailwind + DaisyUI');
