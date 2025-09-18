export class MoveLogger {
    static logMoveStart(moveData) {
        const { cardId, toLaneId, newIndex, url, elDataset, toLaneDataset } = moveData;
        console.log('Card move initiated:', {
            cardId,
            toLaneId,
            newIndex,
            url,
            elDataset,
            toLaneDataset
        });
    }

    static logMoveSuccess(response) {
        console.debug('Move saved successfully:', response);
    }

    static logMoveError(error, context = {}) {
        console.error('Move operation failed:', {
            error: error.message || error,
            context
        });
    }

    static logNetworkError(error) {
        console.error('Network error during move:', error);
    }

    static logValidationError(errors) {
        console.warn('Move validation failed:', errors);
    }
}
