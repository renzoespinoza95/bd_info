(function() {
  "use strict";

  document.addEventListener("click", (e) => {
    const card = e.target.closest(".select-subcat-card");
    if (!card) return;

    const title = card.getAttribute("data-subcat-title");
    const descEl = card.querySelector(".card-html-desc");
    const jsonScript = card.querySelector(".subcat-json-script");

    const titleTarget = document.getElementById("subcatDetailTitle");
    const descTarget = document.getElementById("subcatDetailDesc");
    const stripTarget = document.getElementById("galSubStrip");

    if (titleTarget && title) titleTarget.textContent = title;
    if (descTarget && descEl) descTarget.innerHTML = descEl.innerHTML;

    if (stripTarget && jsonScript) {
      try {
        const items = JSON.parse(jsonScript.textContent.trim());
        if (Array.isArray(items) && items.length > 0) {
          stripTarget.innerHTML = items.map(it => {
            let buttonsHtml = '';
            if (it.tiene_videos && Array.isArray(it.videos) && it.videos.length > 0) {
              buttonsHtml = it.videos.map(v => `
                <button class="btn fill btn-visualizar-vimeo" data-vimeo-code="${v.codigo_web}" style="background:var(--lime); color:var(--ink); border:2px solid var(--ink); box-shadow:3px 3px 0 var(--orange); font-family:var(--font-pixel); font-size:14px; padding:8px 14px; cursor:pointer; width:100%; text-align:center;">
                  ${v.titulo} &rarr;
                </button>
              `).join("");
            } else {
              buttonsHtml = `<span style="font-family:var(--font-body); font-size:14px; color:#fff; text-align:center;">Sin videos disponibles</span>`;
            }

            return `
              <figure class="shot" style="flex: 0 0 calc(50% - 10px); min-width: 280px; scroll-snap-align: start; position: relative; overflow: hidden; border: 3px solid var(--ink); background: var(--ink-2); box-shadow: 4px 4px 0 var(--ink); aspect-ratio: 4/3;">
                <img src="${it.img}" alt="${it.titulo}" loading="lazy" style="display:block; width:100%; height:100%; object-fit:cover;">
                <div class="shot-overlay" style="position:absolute; inset:0; background:rgba(11,11,25,0.88); display:flex; flex-direction:column; justify-content:center; align-items:center; gap:10px; padding:15px; opacity:0; transition:opacity 0.3s ease;">
                  <span style="font-family:var(--font-display); font-size:18px; color:var(--lime); text-transform:uppercase; text-align:center;">${it.titulo}</span>
                  ${buttonsHtml}
                </div>
                <figcaption class="cap" style="position:absolute; bottom:0; left:0; right:0; background:rgba(11,11,25,0.85); color:#fff; padding:8px 12px; font-family:var(--font-display); font-size:15px;">${it.titulo}</figcaption>
              </figure>
            `;
          }).join("");
        } else {
          stripTarget.innerHTML = `<figure class="shot" style="flex:0 0 100%; border:3px solid var(--ink); background:var(--ink-2);"><img src="https://picsum.photos/seed/subdefault/600/800" alt="Sin imágenes" loading="lazy"><figcaption class="cap">Sin registros</figcaption></figure>`;
        }
      } catch (err) {
        console.error("Error parsing subcat items securely from script tag", err);
      }
    }

    const subcatModal = document.getElementById("subcatModal");
    if (subcatModal && subcatModal.contains(card)) {
      if (typeof window.closeSubcatPopup === "function") {
        window.closeSubcatPopup();
      }
      if (typeof window.appGoTo === "function") {
        window.appGoTo(9);
      }
    }
  });
})();