const vehiculos = {

    async load() {
        const { ok, data } = await apiFetch('/vehiculos');
        if (!ok) { flash(data.error); return; }

        const rows = data.length
            ? data.map(v => `
                <tr>
                    <td>${v.id}</td>
                    <td><strong>${v.marca}</strong> ${v.modelo}</td>
                    <td>${v.anio}</td>
                    <td style="text-transform:capitalize">${v.categoria}</td>
                    <td>${v.placa}</td>
                    <td>${badge(v.estado)}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="vehiculos.openForm(${v.id})">Editar</button>
                        <button class="btn btn-danger  btn-sm" onclick="vehiculos.remove(${v.id}, '${v.marca} ${v.modelo}')">Eliminar</button>
                    </td>
                </tr>`).join('')
            : `<tr><td colspan="7" class="empty">No hay vehículos registrados</td></tr>`;

        document.getElementById('view-container').innerHTML = `
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Vehículo</th><th>Año</th>
                            <th>Categoría</th><th>Placa</th><th>Estado</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    },

    async openForm(id = null) {
        const v = id ? (await apiFetch(`/vehiculos/${id}`)).data : {};

        openModal(id ? 'Editar Vehículo' : 'Nuevo Vehículo', `
            <form id="form-vehiculo">
                <div class="form-row">
                    <div class="form-group">
                        <label>Marca *</label>
                        <input name="marca" value="${v.marca ?? ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Modelo *</label>
                        <input name="modelo" value="${v.modelo ?? ''}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Año *</label>
                        <input name="anio" type="number" min="1990" max="2030" value="${v.anio ?? new Date().getFullYear()}" required>
                    </div>
                    <div class="form-group">
                        <label>Placa *</label>
                        <input name="placa" value="${v.placa ?? ''}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categoría *</label>
                        <select name="categoria" required>
                            ${['sedan','suv','camioneta','deportivo','furgon'].map(c =>
                                `<option value="${c}" ${v.categoria === c ? 'selected' : ''}>${c}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            ${['disponible','alquilado','mantenimiento'].map(e =>
                                `<option value="${e}" ${(v.estado ?? 'disponible') === e ? 'selected' : ''}>${e}</option>`
                            ).join('')}
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>`);

        document.getElementById('form-vehiculo').addEventListener('submit', async e => {
            e.preventDefault();
            const body   = Object.fromEntries(new FormData(e.target));
            const method = id ? 'PUT' : 'POST';
            const path   = id ? `/vehiculos/${id}` : '/vehiculos';
            const { ok, data } = await apiFetch(path, method, body);
            if (!ok) { flash(data.error ?? 'Error al guardar'); return; }
            closeModal();
            this.load();
            flash(`Vehículo ${id ? 'actualizado' : 'creado'} correctamente`, 'success');
        });
    },

    async remove(id, nombre) {
        if (!confirm(`¿Eliminar el vehículo "${nombre}"?`)) return;
        const { ok, data } = await apiFetch(`/vehiculos/${id}`, 'DELETE');
        ok ? (this.load(), flash('Vehículo eliminado', 'success')) : flash(data.error);
    },
};
