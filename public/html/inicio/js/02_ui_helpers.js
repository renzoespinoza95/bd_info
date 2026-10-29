const UIHelpers = {
    renderEmptyState(cardDeck) {
        cardDeck.innerHTML = `
            <div class="deck-empty">
                <i class="bi bi-images fs-1 text-muted mb-3"></i>
                <h4 class="fw-bold text-dark">¡Has visto todas!</h4>
                <p class="text-muted fs-7 mb-3">No hay más tarjetas por el momento.</p>
                <button class="btn btn-sm btn-dark rounded-pill px-4" onclick="document.getElementById('btn-reload').click()">
                    Reiniciar
                </button>
            </div>
        `;
    }
};