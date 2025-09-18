// assets/modules/sortable/options.js
export const cardSortableOptions = {
  group: 'shared',   // allows dragging between lanes
  animation: 150,
  onEnd: async (evt) => {
    const el = evt.item;                    // dragged element
    const cardId = el?.dataset?.cardId;

    const toLane = evt.to;                  // destination container
    const toLaneId = toLane?.dataset?.laneId;
    const newIndex = evt.newIndex;          // 0-based position
    const url = toLane?.dataset?.moveUrl;

    console.log('Debug move:', {
      cardId,
      toLaneId,
      newIndex,
      url,
      elDataset: el?.dataset,
      toLaneDataset: toLane?.dataset
    });

    if (!cardId || !toLaneId || !url) {
      console.warn('Incomplete move payload', { cardId, toLaneId, url });
      return;
    }

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          cardId: Number(cardId),
          toLaneId: Number(toLaneId),
          newIndex: Number(newIndex)
        })
      });

      if (!res.ok) {
        console.error('Save failed', res.status, await res.text());
      } else {
        console.debug('Move saved', await res.json());
      }
    } catch (e) {
      console.error('Network error', e);
    }
  }
};
