import { appState } from './01_state.js';
import { updateCounter, debounce } from './02_ui_helpers.js';
import { initGallery, fillMarquees } from './03_cards.js';

(function() {
  "use strict";

  const deck = document.getElementById("deck");
  if (!deck) return;

  const slides = Array.from(deck.querySelectorAll(".slide"));
  const total = slides.length;
  appState.totalSlides = total;

  const menuBtn = document.getElementById("menuBtn");
  const navPop = document.getElementById("navPop");
  const navClose = document.getElementById("navClose");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const stageBg = document.getElementById("stageBg");
  const rootCS = getComputedStyle(document.documentElement);
  const BGVAR = { "s-ink": "--ink", "s-orange": "--orange", "s-lime": "--lime", "s-bone": "--bone" };

  function slideColor(slide) {
    if (!slide || !slide.classList) return "#0B0B0B";
    const key = ["s-ink", "s-orange", "s-lime", "s-bone"].find(c => slide.classList.contains(c)) || "s-ink";
    return rootCS.getPropertyValue(BGVAR[key]).trim() || "#0B0B0B";
  }

  function reveal(slide) {
    if (!slide) return;
    const items = slide.querySelectorAll("[data-reveal]");
    items.forEach((el, i) => {
      if (appState.reducedMotion) { el.classList.add("in"); return; }
      setTimeout(() => { el.classList.add("in"); }, 90 + i * 80);
    });
  }

  setTimeout(() => {
    document.querySelectorAll("[data-reveal]").forEach(el => el.classList.add("in"));
  }, 3000);

  const STEP_OUT = 40;
  const STEP_IN = 55;
  const BG_GAP = 250;

  function goTo(index) {
    index = Math.max(0, Math.min(total - 1, index));
    if (index === appState.currentSlide) return;
    if (appState.animating) { appState.pendingTarget = index; return; }
    appState.animating = true;

    const prevS = slides[appState.currentSlide];
    const nextS = slides[index];
    if (!prevS || !nextS) { appState.animating = false; return; }

    appState.currentSlide = index;
    updateCounter(appState.currentSlide, total);

    const outItems = Array.from(prevS.querySelectorAll("[data-reveal].in"));
    const inItems = Array.from(nextS.querySelectorAll("[data-reveal]"));
    inItems.forEach(el => el.classList.remove("in"));
    const innerNext = nextS.querySelector(".inner");

    function finish() {
      slides.forEach(s => {
        if (s !== nextS) s.classList.remove("active", "leaving", "out");
      });
      appState.animating = false;
      if (appState.pendingTarget !== null) {
        const p = appState.pendingTarget;
        appState.pendingTarget = null;
        goTo(p);
      }
    }

    if (appState.reducedMotion) {
      if (stageBg) stageBg.style.backgroundColor = slideColor(nextS);
      prevS.classList.remove("active");
      nextS.classList.add("active");
      if (innerNext) innerNext.scrollTop = 0;
      inItems.forEach(el => el.classList.add("in"));
      finish();
      return;
    }

    prevS.classList.add("out");
    outItems.slice().reverse().forEach((el, i) => {
      setTimeout(() => { el.classList.remove("in"); }, i * STEP_OUT);
    });
    const outDone = (outItems.length ? (outItems.length - 1) * STEP_OUT : 0) + 180;

    setTimeout(() => {
      if (stageBg) stageBg.style.backgroundColor = slideColor(nextS);
      prevS.classList.remove("active");
      prevS.classList.add("leaving");
      nextS.classList.add("active");
      if (innerNext) innerNext.scrollTop = 0;
    }, outDone);

    setTimeout(() => {
      inItems.forEach((el, i) => {
        setTimeout(() => { el.classList.add("in"); }, i * STEP_IN);
      });
    }, outDone + BG_GAP);

    const inDone = (inItems.length ? (inItems.length - 1) * STEP_IN : 0) + 500;
    setTimeout(finish, outDone + BG_GAP + inDone);
  }

  function next() { if (appState.currentSlide < total - 1) goTo(appState.currentSlide + 1); }
  function prev() { if (appState.currentSlide > 0) goTo(appState.currentSlide - 1); }

  function openMenu() {
    if (!navPop || !menuBtn) return;
    navPop.classList.add("open");
    navPop.setAttribute("aria-hidden", "false");
    menuBtn.setAttribute("aria-expanded", "true");
  }
  function closeMenu() {
    if (!navPop || !menuBtn) return;
    navPop.classList.remove("open");
    navPop.setAttribute("aria-hidden", "true");
    menuBtn.setAttribute("aria-expanded", "false");
  }

  if (menuBtn) menuBtn.addEventListener("click", openMenu);
  if (navClose) navClose.addEventListener("click", closeMenu);

  document.addEventListener("click", (e) => {
    const t = e.target.closest("[data-goto]");
    if (!t) return;
    e.preventDefault();
    const idx = parseInt(t.getAttribute("data-goto"), 10);
    if (!isNaN(idx)) {
      if (navPop && navPop.classList.contains("open")) closeMenu();
      goTo(idx);
    }
  });

  if (prevBtn) prevBtn.addEventListener("click", prev);
  if (nextBtn) nextBtn.addEventListener("click", next);

  document.addEventListener("keydown", (e) => {
    if (navPop && navPop.classList.contains("open")) {
      if (e.key === "Escape") closeMenu();
      return;
    }
    switch (e.key) {
      case "ArrowRight": case "ArrowDown": case "PageDown":
        e.preventDefault(); next(); break;
      case "ArrowLeft": case "ArrowUp": case "PageUp":
        e.preventDefault(); prev(); break;
      case " ":
        e.preventDefault(); next(); break;
      case "Home":
        e.preventDefault(); goTo(0); break;
      case "End":
        e.preventDefault(); goTo(total - 1); break;
      default:
        if (/^[1-9]$/.test(e.key)) { e.preventDefault(); goTo(parseInt(e.key, 10) - 1); }
    }
  });

  let sx = 0, sy = 0, tracking = false;
  deck.addEventListener("touchstart", (e) => {
    if (e.touches.length !== 1 || e.target.closest("[data-noswipe]")) { tracking = false; return; }
    sx = e.touches[0].clientX; sy = e.touches[0].clientY; tracking = true;
  }, { passive: true });

  deck.addEventListener("touchend", (e) => {
    if (!tracking) return;
    tracking = false;
    const t = e.changedTouches[0];
    const dx = t.clientX - sx;
    const dy = t.clientY - sy;
    if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy) * 1.4) {
      if (dx < 0) next(); else prev();
    }
  }, { passive: true });

  initGallery(appState.reducedMotion);

  if (slides.length > 0) {
    if (stageBg) stageBg.style.backgroundColor = slideColor(slides[0]);
    reveal(slides[0]);
  }
  updateCounter(appState.currentSlide, total);
  fillMarquees();
  if (document.fonts && document.fonts.ready) { document.fonts.ready.then(fillMarquees); }
  window.addEventListener("resize", debounce(fillMarquees, 200));
})();