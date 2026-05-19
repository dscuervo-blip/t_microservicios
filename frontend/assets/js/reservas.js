const reservas = {

    async load() {
        const { ok, data } = await apiFetch('/reservas');
        if (!ok) { flash(data.error); return; }

        const rows = data.length
            ? data.map(r => `
                <tr>
                    <td>${r.id}</td>
                    <td>${r.marca ?? ''} ${r.modelo ?? ''}<br><small>${r.placa ?? ''}</small></td>
                    <td>${r.apellido ?? ''}, ${r.nombre ?? ''}</td>
                    <td>${r.fecha_inicio}</td>
                    <td>${r.fecha_fin}</td>
                    <td>${badge(r.estado)}</td>
                    <td>$${Number(r.valor_total).toLocaleString('es-CO')}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="reservas.openForm(${r.id})">Editar</button>
                        <button class="btn btn-danger  btn-sm" onclick="reservas.remove(${r.id})">Eliminar</button>
                    </td>
                </tr>`).join('')
            : `<tr><td colspan="8" class="empty">No hay reservas registradas</td></tr>`;

        document.getElementById('view-container').innerHTML = `
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Vehículo</th><th>Cliente</th>
                            <th>Inicio</th><th>Fin</th><th>Estado</th><th>Total</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    },

    async openForm(id = null) {
        const [rRes, vRes, cRes] = await Promise.all([
            id ? apiFetch(`/reservas/${id}`) : Promise.resolve({ data: {} }),
            apiFetch('/vehiculos/disponibles'),
            apiFetch('/clientes'),
        ]);

        const r = rRes.data;
        const vOptions = (vRes.data ?? []).map(v =>
            `<option value="${v.id}" ${r.vehiculo_id == v.id ? 'selected' : ''}>${v.marca} ${v.modelo} — ${v.placa}</option>`
        ).join('');
        const cOptions = (cRes.data ?? []).map(c =>
            `<option value="${c.id}" ${r.cliente_id == c.id ? 'selected' : ''}>${c.apellido}, ${c.nombre}</option>`
        ).join('');

        openModal(id ? 'Editar Reserva' : 'Nueva Reserva', `
            <form id="form-reserva">
                <div class="form-group">
                    <label>Vehículo disponible *</label>
                    <select name="vehiculo_id" required>${vOptions || '<option value="">Sin vehículos disponibles</option>'}</select>
                </div>
                <div class="form-group">
                    <label>Cliente *</label>
                    <select name="cliente_id" required>${cOptions || '<option value="">Sin clientes</option>'}</select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha inicio *</label>
                        <input name="fecha_inicio" type="date" value="${r.fecha_inicio ?? ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha fin *</label>
                        <input name="fecha_fin" type="date" value="${r.fecha_fin ?? ''}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            ${['pendiente','activa','completada','cancelada'].map(e =>
                                `<option value="${e}" ${(r.estado ?? 'pendiente') === e ? 'selected' : ''}>${e}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Valor total ($)</label>
                        <input name="valor_total" type="number" min="0" step="1000" value="${r.valor_total ?? 0}">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>`);

        document.getElementById('form-reserva').addEventListener('submit', async e => {
            e.preventDefault();
            const body   = Object.fromEntries(new FormData(e.target));
            const method = id ? 'PUT' : 'POST';
            const path   = id ? `/reservas/${id}` : '/reservas';
            const { ok, data } = await apiFetch(path, method, body);
            if (!ok) { flash(data.error ?? 'Error al guardar'); return; }
            closeModal();
            this.load();
            flash(`Reserva ${id ? 'actualizada' : 'creada'} correctamente`, 'success');
        });
    },

    async remove(id) {
        if (!confirm(`¿Eliminar la reserva #${id}?`)) return;
        const { ok, data } = await apiFetch(`/reservas/${id}`, 'DELETE');
        ok ? (this.load(), flash('Reserva eliminada', 'success')) : flash(data.error);
    },
};
