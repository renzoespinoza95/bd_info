(function() {
  "use strict";

  const openSubcatModal = document.getElementById("openSubcatModal");
  const closeSubcatModal = document.getElementById("closeSubcatModal");
  const subcatModal = document.getElementById("subcatModal");

  const imageModal = document.getElementById("imageModal");
  const closeImageModal = document.getElementById("closeImageModal");
  const vimeoContainer = document.getElementById("vimeoContainer");

  function openSubcatPopup() {
    if (!subcatModal) return;
    subcatModal.classList.add("open");
    subcatModal.setAttribute("aria-hidden", "false");
  }

  function closeSubcatPopup() {
    if (!subcatModal) return;
    subcatModal.classList.remove("open");
    subcatModal.setAttribute("aria-hidden", "true");
  }

  if (openSubcatModal) openSubcatModal.addEventListener("click", openSubcatPopup);
  if (closeSubcatModal) closeSubcatModal.addEventListener("click", closeSubcatPopup);

  function openVimeoModal(videoCode) {
    if (!imageModal || !vimeoContainer) return;
    const vId = videoCode ? videoCode.trim() : "1216655914";
    vimeoContainer.innerHTML = `<iframe src="https://player.vimeo.com/video/${vId}?autoplay=1" width="100%" height="100%" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="display:block;"></iframe>`;
    imageModal.style.display = "flex";
    imageModal.setAttribute("aria-hidden", "false");
  }

  function closeVimeoModal() {
    if (!imageModal || !vimeoContainer) return;
    imageModal.style.display = "none";
    imageModal.setAttribute("aria-hidden", "true");
    vimeoContainer.innerHTML = '';
  }

  // Delegación de eventos para el botón dinámico de Vimeo y soporte táctil
  document.addEventListener("click", (e) => {
    const btnVisualizar = e.target.closest(".btn-visualizar-vimeo");
    if (btnVisualizar) {
      e.preventDefault();
      const code = btnVisualizar.getAttribute("data-vimeo-code");
      openVimeoModal(code);
      return;
    }

    const shot = e.target.closest(".shot");
    if (shot && !e.target.closest(".btn-visualizar-vimeo")) {
      document.querySelectorAll(".shot").forEach(s => {
        if (s !== shot) s.classList.remove("active-touch");
      });
      shot.classList.toggle("active-touch");
    }
  });

  if (closeImageModal) closeImageModal.addEventListener("click", closeVimeoModal);
  
  if (imageModal) {
    imageModal.addEventListener("click", (e) => {
      if (e.target === imageModal) closeVimeoModal();
    });
  }

  // Lógica de desplazamiento por flechas y touch para el carrusel de slide_10
  document.addEventListener("click", (e) => {
    const prevBtn = e.target.closest("#carouselPrev");
    const nextBtn = e.target.closest("#carouselNext");
    const strip = document.getElementById("galSubStrip");
    if (!strip) return;

    if (prevBtn) {
      e.preventDefault();
      strip.scrollBy({ left: -320, behavior: 'smooth' });
    }
    if (nextBtn) {
      e.preventDefault();
      strip.scrollBy({ left: 320, behavior: 'smooth' });
    }
  });

  window.closeSubcatPopup = closeSubcatPopup;
  window.openVimeoModal = openVimeoModal;
  window.closeVimeoModal = closeVimeoModal;
})();