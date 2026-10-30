import { debounce } from './02_ui_helpers.js';

export function initGallery(reduced) {
  const strip = document.getElementById("galStrip");
  const gp = document.getElementById("galPrev");
  const gn = document.getElementById("galNext");
  if (!strip || !gp || !gn) return;

  function step() {
    const card = strip.querySelector(".shot");
    const cs = getComputedStyle(strip);
    const gap = parseFloat(cs.columnGap || cs.gap) || 14;
    return card ? card.getBoundingClientRect().width + gap : strip.clientWidth * 0.8;
  }

  function sync() {
    const max = strip.scrollWidth - strip.clientWidth - 1;
    gp.disabled = strip.scrollLeft <= 0;
    gn.disabled = strip.scrollLeft >= max;
  }

  const behavior = reduced ? "auto" : "smooth";
  gp.addEventListener("click", () => { strip.scrollBy({ left: -step(), behavior }); });
  gn.addEventListener("click", () => { strip.scrollBy({ left: step(), behavior }); });
  strip.addEventListener("scroll", () => { window.requestAnimationFrame(sync); }, { passive: true });
  window.addEventListener("resize", debounce(sync, 200));
  sync();
}

export function fillMarquees() {
  document.querySelectorAll(".marquee").forEach(m => {
    const track = m.querySelector(".track");
    if (!track) return;
    if (track.dataset.base === undefined) { track.dataset.base = track.innerHTML; }
    const w = m.clientWidth;
    if (track.dataset.fillW !== String(w)) {
      track.dataset.fillW = String(w);
      track.innerHTML = track.dataset.base;
    }
    let guard = 0;
    while (track.scrollWidth < w * 2 && guard < 40) {
      track.insertAdjacentHTML("beforeend", track.dataset.base);
      guard++;
    }
    track.style.animationDuration = Math.max(14, track.scrollWidth / 110).toFixed(1) + "s";
  });
}