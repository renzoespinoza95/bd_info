// Eventos principales: slider, tarjetas, carrito, modales
document.addEventListener('DOMContentLoaded', function () {
  const A = window.APP, U = A.ui;
  const $ = s => document.querySelector(s);
  const $$ = s => [...document.querySelectorAll(s)];

  // ---------- Slider a todo el ancho, automatico cada 4 s ----------
  (function initSlider() {
    const root = $('.hero-slider'); if (!root) return;
    const slides = $$('.hs-slide'), dots = $$('.hs-dot');
    const S = A.slider;
    S.ms = parseInt(root.dataset.interval, 10) || 4000;

    const go = n => {
      S.i = (n + slides.length) % slides.length;
      slides.forEach((s, k) => s.classList.toggle('is-active', k === S.i));
      dots.forEach((d, k) => d.classList.toggle('on', k === S.i));
    };
    const stop = () => clearInterval(S.timer);
    const play = () => { stop(); if (slides.length > 1) S.timer = setInterval(() => go(S.i + 1), S.ms); };

    if (slides.length < 2) { $$('.hs-btn,.hs-dots').forEach(e => e.classList.add('d-none')); return; }

    root.querySelector('.prev').addEventListener('click', () => { go(S.i - 1); play(); });
    root.querySelector('.next').addEventListener('click', () => { go(S.i + 1); play(); });
    dots.forEach(d => d.addEventListener('click', () => { go(+d.dataset.n); play(); }));
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', play);

    let x0 = null;
    root.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; stop(); }, { passive: true });
    root.addEventListener('touchend', e => {
      if (x0 !== null) { const dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 40) go(S.i + (dx < 0 ? 1 : -1)); }
      x0 = null; play();
    });
    document.addEventListener('visibilitychange', () => document.hidden ? stop() : play());
    play();
  })();

  // ---------- Tabs y tarjetas de producto ----------
  $$('.tab-cat').forEach(b => b.addEventListener('click', () => A.cards.filter(b.dataset.cat)));

  $('#prod-grid') && $('#prod-grid').addEventListener('click', e => {
    const add = e.target.closest('[data-add]');
    const card = e.target.closest('.prod');
    if (!card) return;
    const p = A.prods[card.dataset.id]; if (!p) return;
    if (add && !(p.opciones && p.opciones.length)) { e.stopPropagation(); A.cartApi.add(p, '', 1); return; }
    A.cards.open(p.id);
  });
  $('#prod-grid') && $('#prod-grid').addEventListener('keydown', e => {
    if (e.key === 'Enter' && e.target.classList.contains('prod')) A.cards.open(e.target.dataset.id);
  });

  $('#pm-add').addEventListener('click', () => {
    if (!A.current) return;
    const sel = $('#pm-var');
    const v = sel.classList.contains('d-none') ? '' : $('#pm-var-sel').value;
    const q = Math.max(1, parseInt($('#pm-qty').value, 10) || 1);
    A.cartApi.add(A.current, v, q);
    bootstrap.Modal.getInstance('#modalProducto').hide();
  });

  // ---------- Carrito ----------
  $('#cart-items').addEventListener('click', e => {
    const b = e.target.closest('[data-act]'); if (!b) return;
    const i = +b.closest('.cart-row').dataset.i;
    if (b.dataset.act === 'plus') A.cartApi.change(i, 1);
    else if (b.dataset.act === 'minus') A.cartApi.change(i, -1);
    else A.cartApi.remove(i);
  });

  $('#cart-send').addEventListener('click', () => {
    const dir = $('#cart-dir'), tel = $('#cart-tel'), promo = $('#cart-promo');
    [dir, tel].forEach(i => i.classList.remove('err'));
    if (!A.cart.length) return;
    if (!dir.value.trim()) { dir.classList.add('err'); dir.focus(); return; }
    if (tel.value.replace(/\D/g, '').length < 7) { tel.classList.add('err'); tel.focus(); return; }

    const lines = A.cart.map(i =>
      '• ' + i.qty + ' x ' + i.name + (i.variante ? ' (' + i.attr + ': ' + i.variante + ')' : '') + ' — ' + U.money(i.price * i.qty));
    const txt = [
      A.data.msg, '', '*Pedido*', ...lines, '',
      '*Total:* ' + U.money(A.cartApi.total()),
      '*Dirección:* ' + dir.value.trim(),
      '*Teléfono:* ' + tel.value.trim(),
      promo.value.trim() ? '*Código promocional:* ' + promo.value.trim() : ''
    ].filter((l, i, a) => l !== '' || a[i - 1] !== '').join('\n');
    window.open('https://wa.me/' + A.data.wa + '?text=' + encodeURIComponent(txt), '_blank', 'noopener');
  });

  // ---------- Videos (modal) ----------
  const frame = $('#video-frame');
  if (frame) {
    const items = $$('.video-item');
    const modalV = $('#modalVideo');
    modalV.addEventListener('show.bs.modal', () => { if (items[0] && !frame.src.startsWith('http')) frame.src = items[0].dataset.embed; });
    modalV.addEventListener('hidden.bs.modal', () => { frame.src = ''; });
    items.forEach(b => b.addEventListener('click', () => { frame.src = b.dataset.embed + (b.dataset.embed.includes('?') ? '&' : '?') + 'autoplay=1'; }));
  }

  // ---------- Navegacion ----------
  $$('#offcanvasMenu .gc-link').forEach(a => a.addEventListener('click', () => {
    const oc = bootstrap.Offcanvas.getInstance('#offcanvasMenu');
    if (oc && $('#offcanvasMenu').classList.contains('show')) oc.hide();
  }));

  // ---------- Selector de tema ----------
  const ts = $('#tema-select');
  if (ts) ts.addEventListener('change', () => { location.href = location.pathname + '?tema=' + ts.value; });

  // ---------- Inicio ----------
  A.cards.filter('all');
  A.cartApi.render();
});