document.addEventListener('DOMContentLoaded', () => {
    const cardDeck = document.getElementById('thumbnails');
    const btnLike = document.getElementById('btn-like');     // Flecha derecha (Blanco)
    const btnDislike = document.getElementById('btn-dislike'); // Flecha izquierda
    const btnReload = document.getElementById('btn-reload');   // Corazón rojo (Modal Fullscreen)
    
    if (!cardDeck) return;

    const originalCardsHTML = cardDeck.innerHTML;

    function initCardStyles() {
        const cards = cardDeck.querySelectorAll('.swipe-card');
        const totalCards = cards.length;
        cards.forEach((card, index) => {
            const scale = 1 - (totalCards - 1 - index) * 0.03;
            const translateY = (totalCards - 1 - index) * 8;
            card.style.transform = `scale(${scale}) translateY(${translateY}px)`;
            card.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
            card.style.opacity = '1';
        });
    }

    initCardStyles();

    let activeCard = null;

    function setupTopCardListeners() {
        const cards = cardDeck.querySelectorAll('.swipe-card');
        if (cards.length === 0) {
            // Carrusel infinito: reinicia el mazo automáticamente
            cardDeck.innerHTML = originalCardsHTML;
            initCardStyles();
            setupTopCardListeners();
            return;
        }

        activeCard = cards[cards.length - 1];

        activeCard.addEventListener('mousedown', onStart);
        activeCard.addEventListener('touchstart', onStart, { passive: true });
    }

    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let currentX = 0;
    let currentY = 0;

    function onStart(e) {
        if (e.target.closest('.btn-whatsapp')) return;
        if (!activeCard) return;

        isDragging = true;
        activeCard.classList.add('swiping');

        const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
        const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;

        startX = clientX;
        startY = clientY;

        document.addEventListener('mousemove', onMove);
        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('mouseup', onEnd);
        document.addEventListener('touchend', onEnd);
    }

    function onMove(e) {
        if (!isDragging || !activeCard) return;

        const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
        const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;

        currentX = clientX - startX;
        currentY = clientY - startY;

        const rotate = currentX * 0.08;
        activeCard.style.transform = `translate(${currentX}px, ${currentY}px) rotate(${rotate}deg)`;
    }

    function onEnd() {
        if (!isDragging || !activeCard) return;
        isDragging = false;

        activeCard.classList.remove('swiping');

        const threshold = 120;

        if (currentX > threshold) {
            completeSwipe('right');
        } else if (currentX < -threshold) {
            completeSwipe('left');
        } else {
            activeCard.style.transform = 'translate(0px, 0px) rotate(0deg)';
        }

        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('touchmove', onMove);
        document.removeEventListener('mouseup', onEnd);
        document.removeEventListener('touchend', onEnd);

        currentX = 0;
        currentY = 0;
    }

    function completeSwipe(direction) {
        if (!activeCard) return;

        const flyX = direction === 'right' ? window.innerWidth : -window.innerWidth;
        const rotate = direction === 'right' ? 25 : -25;

        activeCard.style.transition = 'transform 0.4s ease-out, opacity 0.3s ease-out';
        activeCard.style.transform = `translate(${flyX}px, ${currentY}px) rotate(${rotate}deg)`;
        activeCard.style.opacity = '0';

        // NOTA: El contador de visitas ya no se incrementa al hacer swipe.

        const targetCard = activeCard;
        activeCard = null;

        setTimeout(() => {
            targetCard.remove();
            setupTopCardListeners();
        }, 300);
    }

    // Controles de botones inferiores
    if (btnDislike) {
        btnDislike.addEventListener('click', () => {
            if (activeCard) completeSwipe('left');
        });
    }

    if (btnLike) {
        btnLike.addEventListener('click', () => {
            if (activeCard) completeSwipe('right');
        });
    }

    // Botón Corazón Rojo: Abre ventana modal full screen con la imagen del card actual
    if (btnReload) {
        btnReload.addEventListener('click', () => {
            if (!activeCard) {
                const cards = cardDeck.querySelectorAll('.swipe-card');
                if (cards.length > 0) {
                    activeCard = cards[cards.length - 1];
                }
            }
            if (activeCard) {
                const imgElement = activeCard.querySelector('img');
                const imgSrc = imgElement ? imgElement.src : '';
                const imgTitle = imgElement ? imgElement.getAttribute('data-card-title') : '';

                const modalImg = document.getElementById('modal-img-preview');
                const modalTitle = document.getElementById('modal-title-preview');

                if (modalImg) modalImg.src = imgSrc;
                if (modalTitle) modalTitle.textContent = imgTitle;

                const modalElement = document.getElementById('modalFullScreen');
                if (modalElement) {
                    const bootstrapModal = new bootstrap.Modal(modalElement);
                    bootstrapModal.show();
                }
            }
        });
    }

    setupTopCardListeners();
});