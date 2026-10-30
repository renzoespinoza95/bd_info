export const appState = {
  currentSlide: 0,
  totalSlides: 9,
  animating: false,
  pendingTarget: null,
  reducedMotion: window.matchMedia("(prefers-reduced-motion: reduce)").matches
};