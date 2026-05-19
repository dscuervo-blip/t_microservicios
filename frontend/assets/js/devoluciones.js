const devoluciones = {

    async load() {
        const { ok, data } = await apiFetch('/devoluciones');
        if (!ok) { flash(data.error); return; }

        const rows = data.length
            ? data.map(d => `
                <tr>
                    <td>${d.id}</td>
                    <td>#${d.reserva_id}</td>
                    <td>${d.marca ?? ''} ${d.modelo ?? ''}<br><small>${d.placa ?? ''}</small></td>
                    <td>${d.apellido ?? ''}, ${d.nombre ?? ''}</td>
                    <td>${d.fecha_devolucion}</td>
                    <td>${d.km_recorridos} km</td>
                    <td>${badge(d.estado_vehiculo)}</td>
                    <td>${d.observaciones ?? '-'}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="devoluciones.remove(${d.id})">Eliminar</button>
                    </td>
                </tr>`).join('')
            : `<tr><td colspan="9" class="empty">No hay devoluciones registradas</td></tr>`;

        document.getElementById('view-container').innerHTML = `
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Reserva</th><th>Vehículo</th><th>Cliente</th>
                            <th>Fecha dev.</th><th>Km</th><th>Estado veh.</th><th>Observaciones</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    },

    async openForm() {
        const [rRes, vRes, cRes] = await Promise.all([
            apiFetch('/reservas'),
            apiFetch('/vehiculos'),
            apiFetch('/clientes'),
        ]);

        const rOptions = (rRes.data ?? [])
            .filter(r => r.estado === 'activa' || r.estado === 'pendiente')
            .map(r => `<option value="${r.id}">#${r.id} — ${r.marca ?? ''} ${r.modelo ?? ''} / ${r.apellido ?? ''}, ${r.nombre ?? ''}</option>`)
            .join('');
        const vOptions = (vRes.data ?? []).map(v =>
            `<option value="${v.id}">${v.marca} ${v.modelo} — ${v.placa}</option>`
        ).join('');
        const cOptions = (cRes.data ?? []).map(c =>
            `<option value="${c.id}">${c.apellido}, ${c.nombre}</option>`
        ).join('');

        openModal('Registrar Devolución', `
            <form id="form-devolucion">
                <div class="form-group">
                    <label>Reserva activa *</label>
                    <select name="reserva_id" required>${rOptions || '<option value="">Sin reservas activas</option>'}</select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Vehículo *</label>
                        <select name="vehiculo_id" required>${vOptions}</select>
                    </div>
                    <div class="form-group">
                        <label>Cliente *</label>
                        <select name="cliente_id" required>${cOptions}</select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha de devolución *</label>
                        <input name="fecha_devolucion" type="date" value="${new Date().toISOString().slice(0,10)}" required>
                    </div>
                    <div class="form-group">
                        <label>Km recorridos</label>
                        <input name="km_recorridos" type="number" min="0" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Estado del vehículo</label>
                    <select name="estado_vehiculo">
                        <option value="bueno">Bueno</option>
                        <option value="con_daños">Con daños</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="3" placeholder="Notas sobre la devolución..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>`);

        document.getElementById('form-devolucion').addEventListener('submit', async e => {
            e.preventDefault();
            const body = Object.fromEntries(new FormData(e.target));
            const { ok, data } = await apiFetch('/devoluciones', 'POST', body);
            if (!ok) { flash(data.error ?? 'Error al registrar'); return; }
            closeModal();
            this.load();
            flash('Devolución registrada correctamente', 'success');
        });
    },

    async remove(id) {
        if (!confirm(`¿Eliminar la devolución #${id}?`)) return;
        const { ok, data } = await apiFetch(`/devoluciones/${id}`, 'DELETE');
        ok ? (this.load(), flash('Devolución eliminada', 'success')) : flash(data.error);
    },
};
