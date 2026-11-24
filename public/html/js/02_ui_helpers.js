// Utilidades de interfaz
(function (A) {
  const $ = s => document.querySelector(s);
  let toastT;

  A.ui = {
    money(n) { return (A.data.moneda || 'S/') + ' ' + Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); },
    // Muestra u oculta un estado vacio
    empty(sel, show, msg, icon) {
      const el = $(sel); if (!el) return;
      if (show) { el.innerHTML = '<i class="bi ' + (icon || 'bi-inbox') + '"></i>' + (msg || 'No hay elementos para mostrar.'); }
      el.classList.toggle('d-none', !show);
    },
    toast(msg) {
      const t = $('#gc-toast'); if (!t) return;
      t.textContent = msg; t.classList.add('show');
      clearTimeout(toastT); toastT = setTimeout(() => t.classList.remove('show'), 2200);
    },
    badge() {
      const n = A.cart.reduce((s, i) => s + i.qty, 0);
      const el = $('#cart-count'); if (el) el.textContent = n;
    }
  };

  // Imagen rota -> picsum
  document.addEventListener('error', e => {
    const im = e.target;
    if (im.tagName === 'IMG' && !im.dataset.fb) {
      im.dataset.fb = 1;
      im.src = 'https://picsum.photos/seed/fb' + Math.floor(Math.random() * 9999) + '/800/600';
    }
  }, true);
})(window.APP);