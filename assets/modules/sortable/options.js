// assets/modules/sortable/options.js
import { validateMoveData, validateMoveEvent } from './moveValidator.js';
import { CardMoveService } from './cardMoveService.js';
import { MoveLogger } from './logger.js';

export const cardSortableOptions = {
    group: 'shared',   // allows dragging between lanes
    animation: 150,
    dropBubble: true,
    forceFallback: false,
    fallbackOnBody: true,
    swapThreshold: 0.65,
    direction: 'vertical',
    draggable: '[data-card-id]', // Only elements with data-card-id are draggable

    onEnd: async (evt) => {
        // Prevent duplicate events
        if (evt.item.dataset.processing === 'true') {
            console.log('Duplicate onEnd event ignored');
            return;
        }
        evt.item.dataset.processing = 'true';

        console.log('Move triggered:', { cardId: evt.item?.dataset?.cardId, from: evt.oldIndex, to: evt.newIndex });

        // Validate event structure
        const eventValidation = validateMoveEvent(evt);
        if (!eventValidation.isValid) {
            MoveLogger.logValidationError(eventValidation.errors);
            evt.item.dataset.processing = 'false';
            return;
        }

        // Extract move data from event
        const moveData = CardMoveService.extractMoveData(evt);
        MoveLogger.logMoveStart(moveData);

        // Validate extracted data
        const dataValidation = validateMoveData(moveData);
        if (!dataValidation.isValid) {
            MoveLogger.logValidationError(dataValidation.errors);
            evt.item.dataset.processing = 'false';
            return;
        }

        // Save move to server
        try {
            await CardMoveService.saveMove(moveData);
            console.log('Move completed successfully');
        } catch (error) {
            console.error('Failed to save card move:', error.message);
            // Revert the visual change on error
            if (evt.from !== evt.to) {
                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
            }
        } finally {
            // Reset processing flag
            setTimeout(() => {
                evt.item.dataset.processing = 'false';
            }, 100);
        }
    },
};
