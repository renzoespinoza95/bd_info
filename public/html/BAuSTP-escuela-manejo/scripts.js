document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Datos ---------- */
  const vehiclesByLicense = {
    'A-I':   ['Toyota Yaris (sedán)', 'Hyundai Accent', 'Kia Rio', 'Nissan Sentra', 'Toyota Hilux (camioneta)'],
    'A-IIa': ['Toyota Yaris (taxi)', 'Hyundai Grand i10 (taxi)', 'Toyota Hiace (combi)', 'Hyundai H1 (van)'],
    'A-IIb': ['Hyundai County (microbús)', 'Toyota Coaster', 'Hino 300 (camión liviano)', 'Hyundai HD78 (camión)'],
    'A-IIIa': ['Mercedes-Benz O500 (bus)', 'Volvo B270F (bus)', 'Scania K360 (bus interprovincial)'],
    'A-IIIb': ['Volvo FH (tractocamión)', 'Scania R450 (tractocamión)', 'Mercedes-Benz Actros', 'Camión con semirremolque'],
    'A-IIIc': ['Volvo FH (tractocamión)', 'Mercedes-Benz O500 (bus)', 'Hino 500 (camión)', 'Toyota Hilux (camioneta)'],
    'B-IIa': ['Honda XR 150L', 'Yamaha YBR 125', 'Suzuki GN 125', 'Bajaj Pulsar 200'],
    'B-IIb': ['Bajaj RE (mototaxi)', 'Honda Wave (mototaxi)', 'Zongshen Trimoto'],
    'B-IIc': ['Moto de carga Honda CG', 'Motocarguera Bajaj Maxima', 'Cuatrimoto utilitaria']
  };

  const teachers = [
    { id: 't1', nombre: 'Carlos Mendoza', anios: 12, foto: 'https://randomuser.me/api/portraits/men/32.jpg',
      descripcion: 'Especialista en conducción urbana y manejo defensivo en Lima.',
      licencias: ['A-I', 'A-IIa'], dias: ['Lunes', 'Martes', 'Miércoles', 'Viernes', 'Sábado'], rating: 5 },
    { id: 't2', nombre: 'Rosa Quispe', anios: 9, foto: 'https://randomuser.me/api/portraits/women/44.jpg',
      descripcion: 'Instructora paciente, ideal para quienes manejan por primera vez.',
      licencias: ['A-I', 'A-IIa', 'A-IIb'], dias: ['Lunes', 'Miércoles', 'Jueves', 'Sábado'], rating: 5 },
    { id: 't3', nombre: 'Jorge Huamán', anios: 20, foto: 'https://randomuser.me/api/portraits/men/65.jpg',
      descripcion: 'Ex conductor de buses interprovinciales y tractocamiones. Rutas de costa, sierra y selva.',
      licencias: ['A-IIb', 'A-IIIa', 'A-IIIb', 'A-IIIc'], dias: ['Martes', 'Jueves', 'Viernes', 'Sábado'], rating: 5 },
    { id: 't4', nombre: 'Luis Ramírez', anios: 7, foto: 'https://randomuser.me/api/portraits/men/75.jpg',
      descripcion: 'Instructor de motos, mototaxis y vehículos menores. Práctica en circuito cerrado.',
      licencias: ['B-IIa', 'B-IIb', 'B-IIc'], dias: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'], rating: 4 },
    { id: 't5', nombre: 'Ana Torres', anios: 11, foto: 'https://randomuser.me/api/portraits/women/68.jpg',
      descripcion: 'Preparación para el examen de manejo del MTC con simulacros reales.',
      licencias: ['A-I', 'A-IIa', 'A-IIIa', 'A-IIIc'], dias: ['Lunes', 'Martes', 'Viernes', 'Sábado'], rating: 5 }
  ];

  /* ---------- Referencias ---------- */
  const form = document.getElementById('bookingForm');
  const selLic = document.getElementById('licencia');
  const selVeh = document.getElementById('vehiculo');
  const teachersList = document.getElementById('teachersList');
  const diaError = document.getElementById('diaError');
  const profError = document.getElementById('profError');

  document.getElementById('year').textContent = new Date().getFullYear();

  /* ---------- Tarjetas de licencias: activar al pasar/clic ---------- */
  const planCards = document.querySelectorAll('.plan-card');
  planCards.forEach(card => {
    const activate = () => {
      planCards.forEach(c => c.classList.remove('active'));
      card.classList.add('active');
    };
    card.addEventListener('mouseenter', activate);
    card.addEventListener('click', activate);
  });

  // Botón "Más información": preselecciona la licencia en el formulario
  document.querySelectorAll('.btn-plan').forEach(btn => {
    btn.addEventListener('click', () => {
      selLic.value = btn.dataset.license;
      selLic.dispatchEvent(new Event('change'));
    });
  });

  /* ---------- Menú: enlace activo al hacer scroll ---------- */
  const navLinks = document.querySelectorAll('.menu-links .nav-link');
  navLinks.forEach(l => l.addEventListener('click', () => {
    navLinks.forEach(x => x.classList.remove('active'));
    l.classList.add('active');
  }));

  /* ---------- Renderizar instructores ---------- */
  function renderTeachers() {
    const dia = (form.querySelector('input[name="dia"]:checked') || {}).value;
    const lic = selLic.value;
    const currentSelected = (form.querySelector('input[name="profesor"]:checked') || {}).value;

    teachersList.innerHTML = teachers.map(t => {
      const okDia = !dia || t.dias.includes(dia);
      const okLic = !lic || t.licencias.includes(lic);
      const disponible = okDia && okLic;
      const motivo = !okLic ? 'No dicta esta licencia' : 'No disponible este día';
      const stars = '<i class="bi bi-star-fill"></i>'.repeat(t.rating) + '<i class="bi bi-star"></i>'.repeat(5 - t.rating);
      return `
        <div class="col-6 col-lg">
          <input type="radio" class="btn-check" name="profesor" id="${t.id}" value="${t.id}" ${disponible ? '' : 'disabled'} ${currentSelected === t.id && disponible ? 'checked' : ''}>
          <label class="teacher-card ${disponible ? '' : 'disabled'}" for="${t.id}">
            ${disponible ? '' : `<span class="badge bg-dark badge-no">${motivo}</span>`}
            <img src="${t.foto}" alt="Foto de ${t.nombre}" onerror="this.onerror=null;this.src='https://placehold.co/90x90/FDC82F/000000?text=${t.nombre.charAt(0)}'">
            <h5>${t.nombre}</h5>
            <span class="exp">${t.anios} años de experiencia</span>
            <div class="stars">${stars}</div>
            <p class="mt-1">${t.descripcion}</p>
            <p class="mt-1"><strong>Licencias:</strong> ${t.licencias.join(', ')}</p>
          </label>
        </div>`;
    }).join('');
  }
  renderTeachers();

  /* ---------- Eventos del formulario ---------- */
  selLic.addEventListener('change', () => {
    const lista = vehiclesByLicense[selLic.value] || [];
    selVeh.innerHTML = '<option value="">Selecciona...</option>' +
      lista.map(v => `<option>${v}</option>`).join('');
    selVeh.disabled = lista.length === 0;
    if (!lista.length) selVeh.innerHTML = '<option value="">Primero elige la licencia</option>';
    renderTeachers();
  });

  document.querySelectorAll('input[name="dia"]').forEach(r =>
    r.addEventListener('change', () => { diaError.classList.add('d-none'); renderTeachers(); })
  );

  teachersList.addEventListener('change', () => profError.classList.add('d-none'));

  // Solo números en DNI y celular
  ['dni', 'telefono'].forEach(id => {
    document.getElementById(id).addEventListener('input', e => {
      e.target.value = e.target.value.replace(/\D/g, '');
    });
  });

  /* ---------- Envío ---------- */
  form.addEventListener('submit', e => {
    e.preventDefault();

    const dia = form.querySelector('input[name="dia"]:checked');
    const prof = form.querySelector('input[name="profesor"]:checked');

    diaError.classList.toggle('d-none', !!dia);
    profError.classList.toggle('d-none', !!prof);

    form.classList.add('was-validated');
    if (!form.checkValidity() || !dia || !prof) return;

    const t = teachers.find(x => x.id === prof.value);
    const nombre = document.getElementById('nombre').value.trim();

    document.getElementById('modalBody').innerHTML = `
      <p>Hola <strong>${nombre}</strong>, tu clase quedó programada así:</p>
      <ul class="list-unstyled mb-3">
        <li><i class="bi bi-calendar-event me-2"></i><strong>Día:</strong> ${dia.value}, ${document.getElementById('hora').value}</li>
        <li><i class="bi bi-person-badge me-2"></i><strong>Instructor:</strong> ${t.nombre} (${t.anios} años de experiencia)</li>
        <li><i class="bi bi-card-heading me-2"></i><strong>Licencia:</strong> ${selLic.value}</li>
        <li><i class="bi bi-car-front-fill me-2"></i><strong>Vehículo:</strong> ${selVeh.value}</li>
      </ul>
      <p class="small text-muted mb-0">Te contactaremos al <strong>${document.getElementById('telefono').value}</strong> para confirmar el pago y la dirección de recojo.</p>`;

    new bootstrap.Modal(document.getElementById('confirmModal')).show();

    form.reset();
    form.classList.remove('was-validated');
    selVeh.disabled = true;
    selVeh.innerHTML = '<option value="">Primero elige la licencia</option>';
    renderTeachers();
  });
});