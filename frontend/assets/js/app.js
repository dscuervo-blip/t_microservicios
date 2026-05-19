const API = 'http://localhost/t_microservicios/gateway/public';

// ── Navegación ────────────────────────────────────────────────────
const views = { vehiculos, clientes, reservas, devoluciones };
const titles = {
    vehiculos:    'Vehículos',
    clientes:     'Clientes',
    reservas:     'Reservas',
    devoluciones: 'Devoluciones',
};

document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const view = link.dataset.view;

        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        document.getElementById('page-title').textContent = titles[view];
        views[view]?.load();
    });
});

document.getElementById('btn-nuevo').addEventListener('click', () => {
    const active = document.querySelector('.nav-link.active')?.dataset.view;
    views[active]?.openForm();
});

// ── Modal ─────────────────────────────────────────────────────────
const modal     = document.getElementById('modal');
const modalBody = document.getElementById('modal-body');

document.getElementById('modal-close').addEventListener('click', closeModal);
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

function openModal(title, html) {
    document.getElementById('modal-title').textContent = title;
    modalBody.innerHTML = html;
    modal.classList.remove('hidden');
}

function closeModal() {
    modal.classList.add('hidden');
    modalBody.innerHTML = '';
}

// ── HTTP ──────────────────────────────────────────────────────────
async function apiFetch(path, method = 'GET', body = null) {
    const opts = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) opts.body = JSON.stringify(body);
    try {
        const res  = await fetch(API + path, opts);
        const data = await res.json();
        return { ok: res.ok, status: res.status, data };
    } catch (err) {
        return { ok: false, status: 0, data: { error: 'Error de conexión' } };
    }
}

// ── Utilidades ────────────────────────────────────────────────────
function badge(value) {
    const cls = (value ?? '').toString().replace(/\s/g, '_');
    return `<span class="badge badge-${cls}">${value ?? '-'}</span>`;
}

function flash(html, type = 'error') {
    const div = Object.assign(document.createElement('div'), {
        className: `alert alert-${type}`,
        innerHTML: html,
    });
    const vc = document.getElementById('view-container');
    vc.prepend(div);
    setTimeout(() => div.remove(), 4000);
}
