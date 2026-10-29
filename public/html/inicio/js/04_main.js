document.addEventListener('DOMContentLoaded', () => {
    const cardDeck = document.getElementById('thumbnails');
    const btnLike = document.getElementById('btn-like');     
    const btnDislike = document.getElementById('btn-dislike'); 
    const btnReload = document.getElementById('btn-reload');   
    
    if (!cardDeck) return;

    const originalCardsHTML = cardDeck.innerHTML;

    function initCardStyles() {
        const cards = cardDeck.querySelectorAll('.swipe-card');
        const totalCards = cards.length;
        cards.forEach((card, index) => {
            // Mantiene el tamaño inicial fijo (scale(1)) para todas las tarjetas apiladas sin reducirse
            const translateY = (totalCards - 1 - index) * 6;
            card.style.transform = `scale(1) translateY(${translateY}px)`;
            card.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
            card.style.opacity = '1';
        });
    }

    initCardStyles();

    let activeCard = null;

    function setupTopCardListeners() {
        const cards = cardDeck.querySelectorAll('.swipe-card');
        if (cards.length === 0) {
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

        const rotate = currentX * 0.05;
        // Se mantiene estrictamente scale(1) para evitar cualquier reducción de tamaño al mover a la izquierda o derecha
        activeCard.style.transform = `translate(${currentX}px, ${currentY}px) scale(1) rotate(${rotate}deg)`;
    }

    function onEnd() {
        if (!isDragging || !activeCard) return;
        isDragging = false;

        activeCard.classList.remove('swiping');

        const threshold = 100;

        if (currentX > threshold) {
            completeSwipe('right');
        } else if (currentX < -threshold) {
            completeSwipe('left');
        } else {
            activeCard.style.transform = 'translate(0px, 0px) scale(1) rotate(0deg)';
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
        const rotate = direction === 'right' ? 20 : -20;

        activeCard.style.transition = 'transform 0.4s ease-out, opacity 0.3s ease-out';
        activeCard.style.transform = `translate(${flyX}px, ${currentY}px) scale(1) rotate(${rotate}deg)`;
        activeCard.style.opacity = '0';

        const targetCard = activeCard;
        activeCard = null;

        setTimeout(() => {
            targetCard.remove();
            setupTopCardListeners();
        }, 300);
    }

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