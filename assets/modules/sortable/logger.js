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
    // Log successful move operation
    static logMoveSuccess(response) {
        console.debug('Move saved successfully:', response);
    }
    // Log move operation errors with context
    static logMoveError(error, context = {}) {
        console.error('Move operation failed:', {
            error: error.message || error,
            context
        });
    }
    // Log network-related errors
    static logNetworkError(error) {
        console.error('Network error during move:', error);
    }
    // Log validation errors returned from server
    static logValidationError(errors) {
        console.warn('Move validation failed:', errors);
    }
}
