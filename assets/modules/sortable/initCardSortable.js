// assets/modules/sortable/initCardSortable.js
import Sortable from 'sortablejs';
import { cardSortableOptions } from './options.js';

/**
 * Initialize SortableJS on a lane (container of cards)
 * @param {HTMLElement} element - the .lane-cards container
 */
export function initCardSortable(element) {
    new Sortable(element, cardSortableOptions);
}
