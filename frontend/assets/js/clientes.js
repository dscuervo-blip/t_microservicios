const clientes = {
  _data: [],

  async load() {
    const { ok, data, message } = await apiFetch('/clientes');
    if (!ok) { toast(message||'Error cargando clientes'); return; }
    this._data = Array.isArray(data) ? data : [];
    this._render(this._data);
  },

  _render(list) {
    const rows = list.map(c => `
      <tr>
        <td><span class="id-pill">#${c.id}</span></td>
        <td>
          <strong>${c.apellido ? c.apellido+', ' : ''}${c.nombre}</strong>
          ${c.documento ? `<br><small class="text-muted">${c.documento}</small>` : ''}
        </td>
        <td>${c.email ?? c.correo ?? '—'}</td>
        <td>${c.telefono ?? '—'}</td>
        <td>${c.numero_licencia ?? '—'}</td>
        <td class="actions">
          <button class="btn-icon btn-edit" onclick="clientes.openForm(${c.id})" title="Editar">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
          </button>
          <button class="btn-icon btn-delete" onclick="clientes.remove(${c.id},'${c.nombre}')" title="Eliminar">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          </button>
        </td>
      </tr>`);

    document.getElementById('view-container').innerHTML = `
      <div class="page-content">
        <div class="search-row">
          <div class="search-box">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
            <input id="cl-search" type="text" placeholder="Buscar por nombre, email, documento…" oninput="clientes._search()">
          </div>
        </div>
        <div class="card" id="cl-table">
          ${buildTable(['#','Cliente','Email','Teléfono','Licencia',''], rows, 'No hay clientes registrados')}
        </div>
      </div>`;
  },

  _search() {
    const q = (document.getElementById('cl-search')?.value??'').toLowerCase();
    const filtered = this._data.filter(c =>
      `${c.nombre} ${c.apellido??''} ${c.email??''} ${c.correo??''} ${c.documento??''} ${c.telefono??''}`.toLowerCase().includes(q)
    );
    const rows = filtered.map(c => `
      <tr>
        <td><span class="id-pill">#${c.id}</span></td>
        <td>
          <strong>${c.apellido ? c.apellido+', ' : ''}${c.nombre}</strong>
          ${c.documento ? `<br><small class="text-muted">${c.documento}</small>` : ''}
        </td>
        <td>${c.email ?? c.correo ?? '—'}</td>
        <td>${c.telefono ?? '—'}</td>
        <td>${c.numero_licencia ?? '—'}</td>
        <td class="actions">
          <button class="btn-icon btn-edit" onclick="clientes.openForm(${c.id})">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
          </button>
          <button class="btn-icon btn-delete" onclick="clientes.remove(${c.id},'${c.nombre}')">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          </button>
        </td>
      </tr>`);
    const wrap = document.getElementById('cl-table');
    if (wrap) wrap.innerHTML = buildTable(['#','Cliente','Email','Teléfono','Licencia',''], rows, 'Sin resultados');
  },

  async openForm(id = null) {
    const cd = id ? ((await apiFetch(`/clientes/${id}`)).data || {}) : {};
    openModal(id ? 'Editar cliente' : 'Nuevo cliente', `
      <form id="fc" class="modal-form">
        <div class="field-row">
          <div class="field"><label>Nombre *</label><input name="nombre" value="${cd.nombre??''}" required></div>
          <div class="field"><label>Apellido</label><input name="apellido" value="${cd.apellido??''}"></div>
        </div>
        <div class="field"><label>Email</label><input name="email" type="email" value="${cd.email??cd.correo??''}"></div>
        <div class="field-row">
          <div class="field"><label>Teléfono</label><input name="telefono" value="${cd.telefono??''}"></div>
          <div class="field"><label>Documento</label><input name="documento" value="${cd.documento??''}"></div>
        </div>
        <div class="field"><label>Número de licencia</label><input name="numero_licencia" value="${cd.numero_licencia??''}"></div>
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>`);

    document.getElementById('fc').addEventListener('submit', async e => {
      e.preventDefault();
      const body = Object.fromEntries(new FormData(e.target));
      const { ok, message } = await apiFetch(id?`/clientes/${id}`:'/clientes', id?'PUT':'POST', body);
      if (!ok) { toast(message||'Error al guardar'); return; }
      closeModal(); this.load();
      toast(`Cliente ${id?'actualizado':'creado'}`, 'success');
    });
  },

  async remove(id, nombre) {
    if (!confirm(`¿Eliminar al cliente "${nombre}"?`)) return;
    const { ok, message } = await apiFetch(`/clientes/${id}`, 'DELETE');
    ok ? (this.load(), toast('Cliente eliminado', 'success')) : toast(message||'Error');
  },
};
