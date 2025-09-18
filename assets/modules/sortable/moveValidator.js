export function validateMoveData({ cardId, toLaneId, url }) {
    const errors = [
        !cardId && 'cardId is required',
        !toLaneId && 'toLaneId is required',
        !url && 'url is required'
    ].filter(Boolean);

    if (errors.length) {
        console.warn('Incomplete move payload:', errors.join(', '), { cardId, toLaneId, url });
        return { isValid: false, errors };
    }

    return { isValid: true, errors: [] };
}

export function validateMoveEvent(evt) {
    if (!evt?.item || !evt?.to) {
        console.warn('Invalid move event structure');
        return { isValid: false, errors: ['Invalid event structure'] };
    }

    return { isValid: true, errors: [] };
}
