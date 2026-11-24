// Tarjetas de producto, modal de detalle y carrito
(function (A) {
  const $ = s => document.querySelector(s);
  const $$ = s => [...document.querySelectorAll(s)];
  const U = A.ui;

  A.cards = {
    filter(cat) {
      A.filtro = cat;
      $$('.tab-cat').forEach(b => b.classList.toggle('active', b.dataset.cat === cat));
      let n = 0;
      $$('.prod-col').forEach(c => {
        const ok = cat === 'all' || (' ' + c.dataset.cats + ' ').includes(' ' + cat + ' ');
        c.classList.toggle('d-none', !ok);
        if (ok) n++;
      });
      U.empty('#prod-empty', n === 0, 'No hay productos en esta categoría.', 'bi-bag-x');
    },

    open(id) {
      const p = A.prods[id]; if (!p) return;
      A.current = p;
      $('#pm-img').src = p.img;
      $('#pm-img').alt = p.name;
      $('#pm-name').textContent = p.name;
      $('#pm-marca').textContent = p.marca || '';
      $('#pm-desc').textContent = p.desc || '';
      $('#pm-price').textContent = U.money(p.price);
      $('#pm-qty').value = 1;
      const box = $('#pm-var');
      if (p.opciones && p.opciones.length) {
        $('#pm-var-label').textContent = p.atributo;
        $('#pm-var-sel').innerHTML = p.opciones.map(o => '<option>' + U.esc(o) + '</option>').join('');
        box.classList.remove('d-none');
      } else { box.classList.add('d-none'); }
      bootstrap.Modal.getOrCreateInstance('#modalProducto').show();
    }
  };

  A.cartApi = {
    add(p, variante, qty) {
      const key = p.id + '|' + (variante || '');
      const ex = A.cart.find(i => i.key === key);
      if (ex) ex.qty += qty;
      else A.cart.push({ key, id: p.id, name: p.name, price: p.price, img: p.img, attr: p.atributo || '', variante: variante || '', qty });
      A.save(); this.render();
      U.toast('Agregado: ' + p.name);
    },
    change(i, d) {
      const it = A.cart[i]; if (!it) return;
      it.qty += d;
      if (it.qty < 1) A.cart.splice(i, 1);
      A.save(); this.render();
    },
    remove(i) { A.cart.splice(i, 1); A.save(); this.render(); },
    total() { return A.cart.reduce((s, i) => s + i.price * i.qty, 0); },
    render() {
      const list = A.cart;
      $('#cart-items').innerHTML = list.map((it, i) =>
        '<li class="cart-row" data-i="' + i + '">' +
        '<img src="' + U.esc(it.img) + '" alt="">' +
        '<div class="cr-info"><b>' + U.esc(it.name) + '</b>' +
        (it.variante ? '<small>' + U.esc(it.attr) + ': ' + U.esc(it.variante) + '</small>' : '') +
        '<small>' + U.money(it.price) + '</small></div>' +
        '<div class="qty"><button type="button" data-act="minus" aria-label="Menos">−</button><span>' + it.qty + '</span><button type="button" data-act="plus" aria-label="Más">+</button></div>' +
        '<button type="button" class="cr-del" data-act="del" aria-label="Quitar"><i class="bi bi-x-lg"></i></button></li>'
      ).join('');
      U.empty('#cart-empty', !list.length, 'Tu carrito está vacío.', 'bi-bag');
      $('#cart-form').classList.toggle('d-none', !list.length);
      $('#cart-total').textContent = U.money(this.total());
      U.badge();
    }
  };
})(window.APP);