// assets/modules/sortable/initCardSortable.js
import Sortable from 'sortablejs';
import { cardSortableOptions } from './options.js';

/**
 * Initialize SortableJS on a lane (container of cards)
 * @param {HTMLElement} element - the .lane-cards container
 * @returns {Sortable} The Sortable instance
 */
export function initCardSortable(element) {
    return new Sortable(element, cardSortableOptions);
}
