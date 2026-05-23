const dashboard = {
  openForm() {},

  async load() {
    const [vR, cR, rR, dR] = await Promise.all([
      apiFetch('/vehiculos'),
      apiFetch('/clientes'),
      apiFetch('/reservas'),
      apiFetch('/devoluciones'),
    ]);

    const vehs = Array.isArray(vR.data) ? vR.data : [];
    const clts = Array.isArray(cR.data) ? cR.data : [];
    const revs = Array.isArray(rR.data) ? rR.data : [];
    const devs = Array.isArray(dR.data) ? dR.data : [];

    const disp = vehs.filter(v => v.estado === 'disponible').length;
    const alq  = vehs.filter(v => v.estado === 'alquilado').length;
    const mant = vehs.filter(v => v.estado === 'mantenimiento').length;
    const act  = revs.filter(r => r.estado === 'activa').length;
    const comp = revs.filter(r => r.estado === 'completada').length;
    const canc = revs.filter(r => r.estado === 'cancelada').length;
    const pct  = vehs.length ? Math.round((disp / vehs.length) * 100) : 0;
    const color = pct >= 50 ? '#16A34A' : '#D97706';

    const recientes = [...revs].sort((a, b) => b.id - a.id).slice(0, 6);
    const filas = recientes.map(r => `
      <tr>
        <td><span class="id-pill">#${r.id}</span></td>
        <td><strong>${r.marca ?? ''} ${r.modelo ?? ''}</strong><br><small class="text-muted">${r.placa ?? ''}</small></td>
        <td>${r.apellido ? r.apellido + ', ' : ''}${r.nombre ?? '—'}</td>
        <td>${r.fecha_inicio ?? ''} → ${r.fecha_fin ?? ''}</td>
        <td>${badge(r.estado)}</td>
        <td class="text-right">$${Number(r.valor_total ?? 0).toLocaleString('es-CO')}</td>
      </tr>`);

    document.getElementById('view-container').innerHTML = `
      <div class="page-content">

        <div class="kpi-grid">
          ${kpi('Total vehículos',  vehs.length, 'indigo', iconCar())}
          ${kpi('Disponibles',      disp,        'green',  iconCheck())}
          ${kpi('Alquilados',       alq,         'yellow', iconKey())}
          ${kpi('Mantenimiento',    mant,        'red',    iconWrench())}
        </div>

        <div class="dash-row">
          <div class="card">
            <div class="card-header">
              <span class="card-title">Disponibilidad de flota</span>
              <span style="font-weight:700;color:${color}">${pct}%</span>
            </div>
            <div style="padding:16px 18px">
              <div class="avail-bar">
                <div class="avail-fill" style="width:${pct}%;background:${color}"></div>
              </div>
              <div class="avail-legend">
                <span class="leg"><span class="dot dot-green"></span>${disp} disponibles</span>
                <span class="leg"><span class="dot dot-yellow"></span>${alq} alquilados</span>
                <span class="leg"><span class="dot dot-red"></span>${mant} mantenimiento</span>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <span class="card-title">Resumen operativo</span>
            </div>
            <div class="op-row"><span>Clientes registrados</span>     <strong>${clts.length}</strong></div>
            <div class="op-row"><span>Reservas activas</span>         <strong style="color:#16A34A">${act}</strong></div>
            <div class="op-row"><span>Reservas completadas</span>     <strong>${comp}</strong></div>
            <div class="op-row"><span>Reservas canceladas</span>      <strong style="color:#DC2626">${canc}</strong></div>
            <div class="op-row"><span>Devoluciones registradas</span> <strong>${devs.length}</strong></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Reservas recientes</span>
            <a href="#" class="card-action" onclick="navigate('reservas');return false">Ver todas →</a>
          </div>
          ${buildTable(
            ['#', 'Vehículo', 'Cliente', 'Período', 'Estado', 'Total'],
            filas,
            'Sin reservas registradas'
          )}
        </div>

      </div>`;
  },
};

function kpi(label, val, color, icon) {
  return `
    <div class="kpi-card kpi-${color}">
      <div class="kpi-icon">${icon}</div>
      <div class="kpi-val">${val}</div>
      <div class="kpi-lbl">${label}</div>
    </div>`;
}
function iconCar()    { return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 01-2-2V9a2 2 0 012-2h14l4 4v4a2 2 0 01-2 2h-2"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>`; }
function iconCheck()  { return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`; }
function iconKey()    { return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>`; }
function iconWrench() { return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>`; }
