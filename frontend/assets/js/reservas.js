const reservas = {
  _data: [],
  _estado: '',

  async load() {
    const { ok, data, message } = await apiFetch('/reservas');
    if (!ok) { toast(message||'Error cargando reservas'); return; }
    this._data = Array.isArray(data) ? data : [];
    this._estado = '';
    this._render(this._data);
  },

  _render(list) {
    const act  = list.filter(r => r.estado==='activa').length;
    const comp = list.filter(r => r.estado==='completada').length;
    const canc = list.filter(r => r.estado==='cancelada').length;

    const rows = list.map(r => `
      <tr>
        <td><span class="id-pill">#${r.id}</span></td>
        <td><strong>${r.marca??''} ${r.modelo??''}</strong><br><small class="text-muted">${r.placa??''}</small></td>
        <td>${r.apellido ? r.apellido+', ' : ''}${r.nombre??'—'}</td>
        <td>${r.fecha_inicio??''}</td>
        <td>${r.fecha_fin??''}</td>
        <td>${badge(r.estado)}</td>
        <td class="text-right">$${Number(r.valor_total??0).toLocaleString('es-CO')}</td>
        <td class="actions">
          <button class="btn-icon btn-edit" onclick="reservas.openForm(${r.id})">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
          </button>
          <button class="btn-icon btn-delete" onclick="reservas.remove(${r.id})">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          </button>
        </td>
      </tr>`);

    document.getElementById('view-container').innerHTML = `
      <div class="page-content">
        <div class="pill-filters">
          <button class="pill active" onclick="reservas._filter(this,'')">Todas <span class="pill-count">${list.length}</span></button>
          <button class="pill" onclick="reservas._filter(this,'activa')">Activas <span class="pill-count">${act}</span></button>
          <button class="pill" onclick="reservas._filter(this,'completada')">Completadas <span class="pill-count">${comp}</span></button>
          <button class="pill" onclick="reservas._filter(this,'cancelada')">Canceladas <span class="pill-count">${canc}</span></button>
        </div>
        <div class="search-row">
          <div class="search-box">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
            <input id="rs-search" type="text" placeholder="Buscar por cliente o vehículo…" oninput="reservas._search()">
          </div>
        </div>
        <div class="card" id="rs-table">
          ${buildTable(['#','Vehículo','Cliente','Inicio','Fin','Estado','Total',''], rows, 'No hay reservas registradas')}
        </div>
      </div>`;
  },

  _filter(btn, estado) {
    document.querySelectorAll('.pill-filters .pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    this._estado = estado;
    this._applyFilters();
  },
  _search() { this._applyFilters(); },

  _applyFilters() {
    const q = (document.getElementById('rs-search')?.value??'').toLowerCase();
    const f = this._data.filter(r => {
      const txt = `${r.nombre??''} ${r.apellido??''} ${r.marca??''} ${r.modelo??''} ${r.placa??''}`.toLowerCase();
      return txt.includes(q) && (!this._estado || r.estado===this._estado);
    });
    const rows = f.map(r => `
      <tr>
        <td><span class="id-pill">#${r.id}</span></td>
        <td><strong>${r.marca??''} ${r.modelo??''}</strong><br><small class="text-muted">${r.placa??''}</small></td>
        <td>${r.apellido ? r.apellido+', ' : ''}${r.nombre??'—'}</td>
        <td>${r.fecha_inicio??''}</td>
        <td>${r.fecha_fin??''}</td>
        <td>${badge(r.estado)}</td>
        <td class="text-right">$${Number(r.valor_total??0).toLocaleString('es-CO')}</td>
        <td class="actions">
          <button class="btn-icon btn-edit" onclick="reservas.openForm(${r.id})">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
          </button>
          <button class="btn-icon btn-delete" onclick="reservas.remove(${r.id})">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          </button>
        </td>
      </tr>`);
    const wrap = document.getElementById('rs-table');
    if (wrap) wrap.innerHTML = buildTable(['#','Vehículo','Cliente','Inicio','Fin','Estado','Total',''], rows, 'Sin resultados');
  },

  async openForm(id = null) {
    const [rRes, vRes, cRes] = await Promise.all([
      id ? apiFetch(`/reservas/${id}`) : Promise.resolve({data:{}}),
      apiFetch('/vehiculos/disponibles'),
      apiFetch('/clientes'),
    ]);
    const r    = rRes.data || {};
    const vehs = Array.isArray(vRes.data) ? vRes.data : [];
    const clts = Array.isArray(cRes.data) ? cRes.data : [];

    const vOpts = vehs.map(v=>`<option value="${v.id}" ${r.vehiculo_id==v.id?'selected':''}>${v.marca} ${v.modelo}${v.placa?' — '+v.placa:''}</option>`).join('');
    const cOpts = clts.map(c=>`<option value="${c.id}" ${r.cliente_id==c.id?'selected':''}>${c.apellido?c.apellido+', ':''}${c.nombre}</option>`).join('');

    openModal(id ? 'Editar reserva' : 'Nueva reserva', `
      <form id="fr" class="modal-form">
        <div class="field"><label>Vehículo disponible *</label>
          <select name="vehiculo_id" required>${vOpts||'<option value="">Sin vehículos disponibles</option>'}</select>
        </div>
        <div class="field"><label>Cliente *</label>
          <select name="cliente_id" required>${cOpts||'<option value="">Sin clientes</option>'}</select>
        </div>
        <div class="field-row">
          <div class="field"><label>Fecha inicio *</label><input name="fecha_inicio" type="date" value="${r.fecha_inicio??''}" required></div>
          <div class="field"><label>Fecha fin *</label><input name="fecha_fin" type="date" value="${r.fecha_fin??''}" required></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Estado</label>
            <select name="estado">
              ${['activa','completada','cancelada'].map(e=>`<option value="${e}" ${(r.estado??'activa')===e?'selected':''}>${e}</option>`).join('')}
            </select>
          </div>
          <div class="field"><label>Valor total ($)</label>
            <input name="valor_total" type="number" min="0" step="1000" value="${r.valor_total??0}">
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>`);

    document.getElementById('fr').addEventListener('submit', async e => {
      e.preventDefault();
      const body = Object.fromEntries(new FormData(e.target));
      const { ok, message } = await apiFetch(id?`/reservas/${id}`:'/reservas', id?'PUT':'POST', body);
      if (!ok) { toast(message||'Error al guardar'); return; }
      closeModal(); this.load();
      toast(`Reserva ${id?'actualizada':'creada'}`, 'success');
    });
  },

  async remove(id) {
    if (!confirm(`¿Eliminar la reserva #${id}?`)) return;
    const { ok, message } = await apiFetch(`/reservas/${id}`, 'DELETE');
    ok ? (this.load(), toast('Reserva eliminada', 'success')) : toast(message||'Error');
  },
};
