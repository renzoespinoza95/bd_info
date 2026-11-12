(function() {
  "use strict";

  const modal = document.getElementById("imageModal");
  const scroller = document.getElementById("designScroller");
  const modalImg = document.getElementById("designModalImg");
  const btnClose = document.getElementById("closeImageModal");
  const btnPrev = document.getElementById("designPrev");
  const btnNext = document.getElementById("designNext");
  const btnWant = document.getElementById("designWant");

  if (!modal || !scroller || !modalImg) return;

  // Elementos que abren el modal: tarjetas de diseños y la imagen de "Quién soy"
  const TRIGGER = ".design-card, .zoom-img";

  let cards = [];
  let current = 0;

  function isOpen() {
    return modal.style.display === "flex";
  }

  // Arma el enlace de WhatsApp reutilizando el número de url_whatsapp (se descarta su ?text=)
  function buildWhatsappUrl(title) {
    const base = (modal.getAttribute("data-wa-url") || "").split("?")[0];
    const msg = 'Estimados, quiero la plantilla "' + title + '". ¿Me pueden brindar más información?';
    return base + "?text=" + encodeURIComponent(msg);
  }

  function show(index) {
    if (!cards.length) return;
    current = (index + cards.length) % cards.length;
    const card = cards[current];
    const title = card.getAttribute("data-title") || "";

    modalImg.src = card.getAttribute("data-img") || "";
    modalImg.alt = title;
    scroller.scrollTop = 0;

    if (btnWant) {
      btnWant.href = buildWhatsappUrl(title);
      // "Lo quiero!" solo aplica a diseños, no a la imagen de "Quién soy"
      btnWant.style.display = card.hasAttribute("data-nowant") ? "none" : "inline-block";
    }
  }

  function openModal(card) {
    // Diseños: la navegación recorre la cuadrícula donde se hizo clic.
    // Imagen suelta (Quién soy): solo esa imagen.
    const grid = card.closest(".design-grid");
    cards = grid ? Array.from(grid.querySelectorAll(".design-card")) : [card];
    const i = cards.indexOf(card);

    // Con una sola imagen no hacen falta las flechas
    const arrowDisplay = cards.length > 1 ? "flex" : "none";
    if (btnPrev) btnPrev.style.display = arrowDisplay;
    if (btnNext) btnNext.style.display = arrowDisplay;

    show(i < 0 ? 0 : i);

    modal.style.display = "flex";
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
    scroller.scrollTop = 0;
    scroller.focus({ preventScroll: true });
  }

  function closeModal() {
    modal.style.display = "none";
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
    modalImg.removeAttribute("src");
  }

  // Abrir al tocar una tarjeta o la imagen de "Quién soy"
  document.addEventListener("click", (e) => {
    const card = e.target.closest(TRIGGER);
    if (card) openModal(card);
  });

  if (btnPrev) btnPrev.addEventListener("click", () => show(current - 1));
  if (btnNext) btnNext.addEventListener("click", () => show(current + 1));
  if (btnClose) btnClose.addEventListener("click", closeModal);

  // Fase de captura: corre antes que el manejador de teclado de 04_main.js
  document.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      const card = e.target.closest && e.target.closest(TRIGGER);
      if (card && !isOpen()) {
        e.preventDefault();
        openModal(card);
        return;
      }
    }
    if (!isOpen()) return;
    if (e.key === "Escape") {
      e.preventDefault();
      closeModal();
    } else if (e.key === "ArrowRight") {
      e.preventDefault();
      if (cards.length > 1) show(current + 1);
    } else if (e.key === "ArrowLeft") {
      e.preventDefault();
      if (cards.length > 1) show(current - 1);
    }
  }, true);

  window.closeDesignModal = closeModal;
})();