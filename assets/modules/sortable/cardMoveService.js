import { MoveLogger } from './logger.js';

export class CardMoveService {
    static extractMoveData(evt) {
        return {
            cardId: evt.item?.dataset?.cardId,
            toLaneId: evt.to?.dataset?.laneId,
            newIndex: evt.newIndex,
            url: evt.to?.dataset?.moveUrl,
            elDataset: evt.item?.dataset,
            toLaneDataset: evt.to?.dataset
        };
    }
    // Save the card move to the server
    static async saveMove({ cardId, toLaneId, newIndex, url }) {
        try {
            const payload = this._createPayload(cardId, toLaneId, newIndex);
            console.log('Current lane cards count:', this._getLaneCardCount(toLaneId));

            const response = await this._sendMoveRequest(url, payload);
            const result = await this._handleResponse(response, payload);

            this._showMoveSuccess(cardId);
            return result;
        } catch (error) {
            this._handleError(error);
            throw error;
        }
    }
    // Prepare payload for the move request
    static _createPayload(cardId, toLaneId, newIndex) {
        return {
            cardId: Number(cardId),
            toLaneId: Number(toLaneId),
            newIndex: Number(newIndex)
        };
    }
    // Count cards in the target lane for logging purposes
    static _getLaneCardCount(toLaneId) {
        return document.querySelectorAll(`[data-lane-id="${toLaneId}"] [data-card-id]`).length;
    }
    // Send POST request to server with move details
    static async _sendMoveRequest(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
    }
    // Handle server response and log results
    static async _handleResponse(response, payload) {
        if (!response.ok) {
            const errorText = await response.text();
            MoveLogger.logMoveError(`HTTP ${response.status}`, {
                status: response.status,
                errorText,
                payload
            });
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const result = await response.json();
        console.log('Move response data:', result);
        MoveLogger.logMoveSuccess(result);
        return result;
    }
    // Visual feedback for successful move
    static _showMoveSuccess(cardId) {
        const cardElement = document.querySelector(`[data-card-id="${cardId}"]`);
        if (cardElement) {
            cardElement.classList.add('move-success');
            setTimeout(() => cardElement.classList.remove('move-success'), 300);
        }
    }
    // Centralized error handling
    static _handleError(error) {
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            MoveLogger.logNetworkError(error);
        } else {
            MoveLogger.logMoveError(error);
        }
    }
}
