const CardsModule = {
    // Ya no requiere crear elementos desde cero si Mustache los renderiza, 
    // pero mantenemos la lógica de inicialización y apilamiento para el efecto Swipe.
    initCardStyles() {
        const cards = document.querySelectorAll('.swipe-card');
        const totalCards = cards.length;
        cards.forEach((card, index) => {
            const scale = 1 - (totalCards - 1 - index) * 0.03;
            const translateY = (totalCards - 1 - index) * 8;
            card.style.transform = `scale(${scale}) translateY(${translateY}px)`;
        });
    }
};