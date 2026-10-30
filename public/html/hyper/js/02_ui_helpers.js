export function pad(n) {
  return (n < 9 ? "0" : "") + (n + 1);
}

export function debounce(fn, ms) {
  let t;
  return function() {
    clearTimeout(t);
    t = setTimeout(fn, ms);
  };
}

export function updateCounter(current, total) {
  const nowEl = document.querySelector(".counter .now");
  const progBar = document.getElementById("progBar");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");

  if (nowEl) nowEl.textContent = pad(current);
  if (progBar) progBar.style.width = ((current + 1) / total * 100) + "%";
  if (prevBtn) prevBtn.disabled = (current === 0);
  if (nextBtn) nextBtn.disabled = (current === total - 1);
}