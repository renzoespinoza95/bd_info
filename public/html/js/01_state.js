// Estado global de la aplicacion
window.APP = (function () {
  const el = document.getElementById('page-data');
  let data = { productos: [] };
  try { data = JSON.parse(el.textContent); } catch (e) { console.error('page-data invalido', e); }

  const prods = {};
  (data.productos || []).forEach(p => { prods[p.id] = p; });

  const KEY = 'gc_cart_' + (data.neg_id || 0);
  let cart = [];
  try { cart = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { cart = []; }
  if (!Array.isArray(cart)) cart = [];

  return {
    data, prods, cart,
    filtro: 'all',          // categoria activa
    current: null,          // producto abierto en el modal
    slider: { i: 0, timer: null, ms: 4000 },
    save() { try { localStorage.setItem(KEY, JSON.stringify(this.cart)); } catch (e) {} }
  };
})();