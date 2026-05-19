const clientes = {

    async load() {
        const { ok, data } = await apiFetch('/clientes');
        if (!ok) { flash(data.error); return; }

        const rows = data.length
            ? data.map(c => `
                <tr>
                    <td>${c.id}</td>
                    <td><strong>${c.apellido}</strong>, ${c.nombre}</td>
                    <td>${c.email}</td>
                    <td>${c.telefono ?? '-'}</td>
                    <td>${c.documento}</td>
                    <td>${c.numero_licencia}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="clientes.openForm(${c.id})">Editar</button>
                        <button class="btn btn-danger  btn-sm" onclick="clientes.remove(${c.id}, '${c.nombre} ${c.apellido}')">Eliminar</button>
                    </td>
                </tr>`).join('')
            : `<tr><td colspan="7" class="empty">No hay clientes registrados</td></tr>`;

        document.getElementById('view-container').innerHTML = `
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Nombre</th><th>Email</th>
                            <th>Teléfono</th><th>Documento</th><th>Licencia</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    },

    async openForm(id = null) {
        const c = id ? (await apiFetch(`/clientes/${id}`)).data : {};

        openModal(id ? 'Editar Cliente' : 'Nuevo Cliente', `
            <form id="form-cliente">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input name="nombre" value="${c.nombre ?? ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido *</label>
                        <input name="apellido" value="${c.apellido ?? ''}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input name="email" type="email" value="${c.email ?? ''}" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input name="telefono" value="${c.telefono ?? ''}">
                    </div>
                    <div class="form-group">
                        <label>Documento *</label>
                        <input name="documento" value="${c.documento ?? ''}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Número de licencia *</label>
                    <input name="numero_licencia" value="${c.numero_licencia ?? ''}" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>`);

        document.getElementById('form-cliente').addEventListener('submit', async e => {
            e.preventDefault();
            const body   = Object.fromEntries(new FormData(e.target));
            const method = id ? 'PUT' : 'POST';
            const path   = id ? `/clientes/${id}` : '/clientes';
            const { ok, data } = await apiFetch(path, method, body);
            if (!ok) { flash(data.error ?? 'Error al guardar'); return; }
            closeModal();
            this.load();
            flash(`Cliente ${id ? 'actualizado' : 'creado'} correctamente`, 'success');
        });
    },

    async remove(id, nombre) {
        if (!confirm(`¿Eliminar al cliente "${nombre}"?`)) return;
        const { ok, data } = await apiFetch(`/clientes/${id}`, 'DELETE');
        ok ? (this.load(), flash('Cliente eliminado', 'success')) : flash(data.error);
    },
};
