// ── Configuración ─────────────────────────────────────────────────
const API = 'http://localhost/t_microservicios/gateway/public';

const VIEWS = {
  vehiculos:    { module: vehiculos,    title: 'Vehículos',    nuevo: true, label: 'Nuevo vehículo'       },
  clientes:     { module: clientes,     title: 'Clientes',     nuevo: true, label: 'Nuevo cliente'        },
  reservas:     { module: reservas,     title: 'Reservas',     nuevo: true, label: 'Nueva reserva'        },
  devoluciones: { module: devoluciones, title: 'Devoluciones', nuevo: true, label: 'Registrar devolución' },
};

let currentView = 'vehiculos';

// ── Navegación ────────────────────────────────────────────────────
function navigate(viewName) {
  const cfg = VIEWS[viewName];
  if (!cfg) return;

  currentView = viewName;

  // Marcar ítem activo en el nav
  document.querySelectorAll('.nav-link[data-view]').forEach(el =>
    el.classList.toggle('active', el.dataset.view === viewName)
  );

  // Título de la página
  document.getElementById('page-title').textContent = cfg.title;

  // Botón nuevo
  const btnNuevo = document.getElementById('btn-nuevo');
  btnNuevo.classList.remove('hidden');
  document.getElementById('btn-nuevo-text').textContent = cfg.label;

  // Mostrar spinner y cargar módulo
  document.getElementById('view-container').innerHTML =
    '<div class="loading"><div class="spinner"></div></div>';

  cfg.module.load();
}

// Clicks en los links del sidebar
document.querySelectorAll('.nav-link[data-view]').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    navigate(link.dataset.view);
  });
});

// Botón "+ Nuevo"
document.getElementById('btn-nuevo').addEventListener('click', () => {
  VIEWS[currentView]?.module?.openForm?.();
});

// ── Modal ─────────────────────────────────────────────────────────
const modalEl   = document.getElementById('modal');
const modalBody = document.getElementById('modal-body');

document.getElementById('modal-close').addEventListener('click', closeModal);
document.querySelector('.modal-overlay').addEventListener('click', closeModal);

function openModal(title, html) {
  document.getElementById('modal-title').textContent = title;
  modalBody.innerHTML = html;
  modalEl.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  modalEl.classList.add('hidden');
  modalBody.innerHTML = '';
  document.body.style.overflow = '';
}

// ── HTTP ──────────────────────────────────────────────────────────
async function apiFetch(path, method = 'GET', body = null) {
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  };
  if (body) opts.body = JSON.stringify(body);

  try {
    const res  = await fetch(API + path, opts);
    const json = await res.json();
    return {
      ok:      res.ok,
      status:  res.status,
      data:    json.hasOwnProperty('data') ? json.data : json,
      message: json.message || json.error || '',
    };
  } catch {
    return { ok: false, status: 0, data: null, message: 'Error de conexión con el servidor' };
  }
}

// ── Utilidades UI ─────────────────────────────────────────────────
function badge(value) {
  const cls = (value ?? '').toString().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
  return `<span class="badge badge-${cls}">${value ?? '—'}</span>`;
}

function toast(msg, type = 'error') {
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `
    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
      ${type === 'success'
        ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>'
        : '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>'}
    </svg>
    <span>${msg}</span>`;
  document.body.appendChild(t);
  requestAnimationFrame(() => t.classList.add('show'));
  setTimeout(() => {
    t.classList.remove('show');
    setTimeout(() => t.remove(), 300);
  }, 3200);
}

function flash(msg, type = 'error') { toast(msg, type); }

// ── Tabla helper ──────────────────────────────────────────────────
function buildTable(cols, rows, emptyMsg = 'Sin registros') {
  const thead = cols.map(c => `<th>${c}</th>`).join('');
  const tbody = rows.length
    ? rows.join('')
    : `<tr class="empty-row"><td colspan="${cols.length}">
        <div class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
              <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"/>
            </svg>
          </div>
          <p>${emptyMsg}</p>
        </div>
      </td></tr>`;
  return `
    <div class="table-wrap">
      <table>
        <thead><tr>${thead}</tr></thead>
        <tbody>${tbody}</tbody>
      </table>
    </div>`;
}
