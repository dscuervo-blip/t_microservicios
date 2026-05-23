const devoluciones = {
  _data: [],

  async load() {
    const { ok, data, message } = await apiFetch('/devoluciones');
    if (!ok) { toast(message||'Error cargando devoluciones'); return; }
    this._data = Array.isArray(data) ? data : [];
    this._render(this._data);
  },

  _render(list) {
    const rows = list.map(d => `
      <tr>
        <td><span class="id-pill">#${d.id}</span></td>
        <td><span class="id-pill id-pill-ghost">#${d.reserva_id}</span></td>
        <td><strong>${d.marca??''} ${d.modelo??''}</strong><br><small class="text-muted">${d.placa??''}</small></td>
        <td>${d.apellido ? d.apellido+', ' : ''}${d.nombre??'—'}</td>
        <td>${d.fecha_devolucion??''}</td>
        <td>${d.km_recorridos??0} km</td>
        <td>${badge(d.estado_vehiculo)}</td>
        <td class="text-muted">${d.observaciones??'—'}</td>
        <td class="actions">
          <button class="btn-icon btn-delete" onclick="devoluciones.remove(${d.id})" title="Eliminar">
            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          </button>
        </td>
      </tr>`);

    document.getElementById('view-container').innerHTML = `
      <div class="page-content">
        <div class="card">
          ${buildTable(
            ['#','Reserva','Vehículo','Cliente','Fecha dev.','Km','Estado veh.','Observaciones',''],
            rows, 'No hay devoluciones registradas'
          )}
        </div>
      </div>`;
  },

  async openForm() {
    const [rRes, vRes, cRes] = await Promise.all([
      apiFetch('/reservas'),
      apiFetch('/vehiculos'),
      apiFetch('/clientes'),
    ]);

    const activas = (Array.isArray(rRes.data) ? rRes.data : []).filter(r => r.estado==='activa');
    const vehs    = Array.isArray(vRes.data) ? vRes.data : [];
    const clts    = Array.isArray(cRes.data) ? cRes.data : [];

    if (!activas.length) {
      toast('No hay reservas activas para devolver', 'error');
      return;
    }

    const rOpts = activas.map(r =>
      `<option value="${r.id}" data-vid="${r.vehiculo_id}" data-cid="${r.cliente_id}">
        #${r.id} — ${r.marca??''} ${r.modelo??''}${r.placa?' ['+r.placa+']':''} / ${r.apellido?r.apellido+', ':''}${r.nombre??''}
      </option>`
    ).join('');
    const vOpts = vehs.map(v=>`<option value="${v.id}">${v.marca} ${v.modelo}${v.placa?' — '+v.placa:''}</option>`).join('');
    const cOpts = clts.map(c=>`<option value="${c.id}">${c.apellido?c.apellido+', ':''}${c.nombre}</option>`).join('');

    openModal('Registrar devolución', `
      <form id="fd" class="modal-form">
        <div class="field"><label>Reserva activa *</label>
          <select name="reserva_id" id="sel-res" required>${rOpts}</select>
        </div>
        <div class="field-row">
          <div class="field"><label>Vehículo *</label><select name="vehiculo_id" id="sel-veh" required>${vOpts}</select></div>
          <div class="field"><label>Cliente *</label><select name="cliente_id" id="sel-cli" required>${cOpts}</select></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Fecha de devolución *</label>
            <input name="fecha_devolucion" type="date" value="${new Date().toISOString().slice(0,10)}" required>
          </div>
          <div class="field"><label>Km recorridos</label>
            <input name="km_recorridos" type="number" min="0" value="0">
          </div>
        </div>
        <div class="field"><label>Estado del vehículo</label>
          <select name="estado_vehiculo">
            <option value="bueno">Bueno</option>
            <option value="con_daños">Con daños</option>
          </select>
        </div>
        <div class="field"><label>Observaciones</label>
          <textarea name="observaciones" rows="2" placeholder="Notas opcionales…"></textarea>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Registrar</button>
        </div>
      </form>`);

    // Auto-seleccionar vehículo y cliente según reserva
    const sync = () => {
      const opt = document.getElementById('sel-res')?.selectedOptions[0];
      if (!opt) return;
      const sv = document.getElementById('sel-veh');
      const sc = document.getElementById('sel-cli');
      [...(sv?.options??[])].forEach(o => o.selected = o.value==opt.dataset.vid);
      [...(sc?.options??[])].forEach(o => o.selected = o.value==opt.dataset.cid);
    };
    document.getElementById('sel-res')?.addEventListener('change', sync);
    sync();

    document.getElementById('fd').addEventListener('submit', async e => {
      e.preventDefault();
      const body = Object.fromEntries(new FormData(e.target));
      const { ok, message } = await apiFetch('/devoluciones', 'POST', body);
      if (!ok) { toast(message||'Error al registrar'); return; }
      closeModal(); this.load();
      toast('Devolución registrada. Vehículo liberado.', 'success');
    });
  },

  async remove(id) {
    if (!confirm(`¿Eliminar la devolución #${id}?`)) return;
    const { ok, message } = await apiFetch(`/devoluciones/${id}`, 'DELETE');
    ok ? (this.load(), toast('Devolución eliminada', 'success')) : toast(message||'Error');
  },
};
