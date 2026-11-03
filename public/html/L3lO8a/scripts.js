document.addEventListener('DOMContentLoaded', () => {
  // Año dinámico en el footer
  const year = document.getElementById('year');
  if (year) year.textContent = new Date().getFullYear();

  // Sombra del navbar al hacer scroll
  const nav = document.getElementById('mainNav');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 20);
  });

  // Animación de aparición al hacer scroll
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  // Link activo del menú según la sección visible
  const links = document.querySelectorAll('.navbar .nav-link');
  const sections = [...links].map(l => document.querySelector(l.getAttribute('href')));
  window.addEventListener('scroll', () => {
    const pos = window.scrollY + 120;
    sections.forEach((sec, i) => {
      if (sec && sec.offsetTop <= pos && sec.offsetTop + sec.offsetHeight > pos) {
        links.forEach(l => l.classList.remove('active'));
        links[i].classList.add('active');
      }
    });
  });

  // Cerrar menú móvil al elegir una opción
  const menu = document.getElementById('menu');
  links.forEach(link => link.addEventListener('click', () => {
    if (menu.classList.contains('show')) {
      bootstrap.Collapse.getOrCreateInstance(menu).hide();
    }
  }));
});