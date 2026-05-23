const vehiculos = {
  _data: [],

  async load() {
    const { ok, data, message } = await apiFetch('/vehiculos');
    if (!ok) { toast(message || 'Error cargando vehículos'); return; }
    this._data = Array.isArray(data) ? data : [];
    this._render(this._data);
  },

  _render(list) {
    const total = list.length;
    const disp  = list.filter(v => v.estado === 'disponible').length;
    const alq   = list.filter(v => v.estado === 'alquilado').length;
    const mant  = list.filter(v => v.estado === 'mantenimiento').length;

    const rows = list.map(v => `
      <tr>
        <td><span class="id-pill">#${v.id}</span></td>
        <td><strong>${v.marca}</strong> ${v.modelo}</td>
        <td>${v.anio}</td>
        <td>${v.placa ?? '—'}</td>
        <td><span class="capitalize">${v.categoria ?? '—'}</span></td>
        <td>${badge(v.estado)}</td>
        <td class="actions">
          <button class="btn-icon btn-edit" onclick="vehiculos.openForm(${v.id})" title="Editar">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
          </button>
          <button class="btn-icon btn-delete" onclick="vehiculos.remove(${v.id},'${v.marca} ${v.modelo}')" title="Eliminar">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          </button>
        </td>
      </tr>`);

    document.getElementById('view-container').innerHTML = `
      <div class="page-content">
        <div class="pill-filters">
          <button class="pill active" onclick="vehiculos._filter(this,'')">Todos <span class="pill-count">${total}</span></button>
          <button class="pill" onclick="vehiculos._filter(this,'disponible')">Disponibles <span class="pill-count">${disp}</span></button>
          <button class="pill" onclick="vehiculos._filter(this,'alquilado')">Alquilados <span class="pill-count">${alq}</span></button>
          <button class="pill" onclick="vehiculos._filter(this,'mantenimiento')">Mantenimiento <span class="pill-count">${mant}</span></button>
        </div>
        <div class="search-row">
          <div class="search-box">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
            <input id="vh-search" type="text" placeholder="Buscar por marca, modelo, placa…" oninput="vehiculos._search()">
          </div>
        </div>
        <div class="card" id="vh-table">
          ${buildTable(
            ['#','Vehículo','Año','Placa','Categoría','Estado',''],
            rows, 'No hay vehículos registrados'
          )}
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

  _estado: '',

  _applyFilters() {
    const q = (document.getElementById('vh-search')?.value ?? '').toLowerCase();
    const filtered = this._data.filter(v => {
      const match = `${v.marca} ${v.modelo} ${v.placa ?? ''} ${v.categoria ?? ''}`.toLowerCase().includes(q);
      return match && (!this._estado || v.estado === this._estado);
    });
    const rows = filtered.map(v => `
      <tr>
        <td><span class="id-pill">#${v.id}</span></td>
        <td><strong>${v.marca}</strong> ${v.modelo}</td>
        <td>${v.anio}</td>
        <td>${v.placa ?? '—'}</td>
        <td><span class="capitalize">${v.categoria ?? '—'}</span></td>
        <td>${badge(v.estado)}</td>
        <td class="actions">
          <button class="btn-icon btn-edit" onclick="vehiculos.openForm(${v.id})">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
          </button>
          <button class="btn-icon btn-delete" onclick="vehiculos.remove(${v.id},'${v.marca} ${v.modelo}')">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          </button>
        </td>
      </tr>`);
    const wrap = document.getElementById('vh-table');
    if (wrap) wrap.innerHTML = buildTable(['#','Vehículo','Año','Placa','Categoría','Estado',''], rows, 'Sin resultados');
  },

  async openForm(id = null) {
    const vd = id ? ((await apiFetch(`/vehiculos/${id}`)).data || {}) : {};
    openModal(id ? 'Editar vehículo' : 'Nuevo vehículo', `
      <form id="fv" class="modal-form">
        <div class="field-row">
          <div class="field"><label>Marca *</label><input name="marca" value="${vd.marca??''}" required></div>
          <div class="field"><label>Modelo *</label><input name="modelo" value="${vd.modelo??''}" required></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Año *</label><input name="anio" type="number" min="1990" max="2030" value="${vd.anio??new Date().getFullYear()}" required></div>
          <div class="field"><label>Placa</label><input name="placa" value="${vd.placa??''}" placeholder="ABC-123"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Categoría *</label>
            <select name="categoria" required>
              ${['sedan','suv','camioneta','deportivo','furgon'].map(c=>`<option value="${c}" ${vd.categoria===c?'selected':''}>${c}</option>`).join('')}
            </select>
          </div>
          <div class="field"><label>Estado</label>
            <select name="estado">
              ${['disponible','alquilado','mantenimiento'].map(e=>`<option value="${e}" ${(vd.estado??'disponible')===e?'selected':''}>${e}</option>`).join('')}
            </select>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>`);

    document.getElementById('fv').addEventListener('submit', async e => {
      e.preventDefault();
      const body = Object.fromEntries(new FormData(e.target));
      const { ok, message } = await apiFetch(id?`/vehiculos/${id}`:'/vehiculos', id?'PUT':'POST', body);
      if (!ok) { toast(message||'Error al guardar'); return; }
      closeModal(); this.load();
      toast(`Vehículo ${id?'actualizado':'creado'}`, 'success');
    });
  },

  async remove(id, nombre) {
    if (!confirm(`¿Eliminar "${nombre}"?`)) return;
    const { ok, message } = await apiFetch(`/vehiculos/${id}`, 'DELETE');
    ok ? (this.load(), toast('Vehículo eliminado', 'success')) : toast(message||'Error');
  },
};
