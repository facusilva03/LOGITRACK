<?php
// views/dashboard/empleado.php — Dashboard del Empleado de Sucursal
$tituloPagina = 'Panel de Sucursal — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$nombreMostrar = htmlspecialchars($_SESSION['nombre_real'] ?? $_SESSION['username']);

// Mapa de estado → clase de badge
$badgeEstado = [
    'Enviado'                => 'badge-emp-naranja',
    'En camino'              => 'badge-emp-verde',
    'Recibido'               => 'badge-emp-gris',
    'Demorado'               => 'badge-emp-naranja',
    'En sucursal de entrega' => 'badge-emp-celeste',
    'Entregado'              => 'badge-emp-gris',
    'Devuelto'               => 'badge-emp-naranja',
    'No entregado'           => 'badge-emp-naranja',
    'Sin estado'    => 'badge-emp-gris',
];
?>

<!-- ══ Encabezado ══ -->
<div class="page-header">
    <div>
        <h1 class="page-titulo">Panel de Sucursal</h1>
        <p class="page-subtitulo">Bienvenido/a, <?= $nombreMostrar ?> &nbsp;·&nbsp; <?= date('d/m/Y') ?></p>
    </div>
</div>

<!-- ══════════════════════════════════════════
     SECCIÓN 1 — TU SUCURSAL HOY
══════════════════════════════════════════ -->
<div class="dash-seccion-titulo">TU SUCURSAL HOY</div>

<div class="emp-kpi-grid">

    <!-- Card 1: Envíos registrados hoy -->
    <div class="emp-kpi-card">
        <div class="emp-kpi-icono">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="3" width="15" height="13" rx="2"/>
                <path d="M16 8h4l3 5v3h-7V8Z"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
        </div>
        <div class="emp-kpi-valor"><?= $kpis['envios_hoy'] ?></div>
        <div class="emp-kpi-label">Envíos registrados hoy</div>
    </div>

    <!-- Card 2: Pendientes de despacho (warning) -->
    <div class="emp-kpi-card warning">
        <div class="emp-kpi-icono">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="emp-kpi-valor"><?= $kpis['pendientes'] ?></div>
        <div class="emp-kpi-label">Pendientes de despacho</div>
    </div>

    <!-- Card 3: Pre-Admisiones pendientes -->
    <div class="emp-kpi-card">
        <div class="emp-kpi-icono">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="12" y1="18" x2="12" y2="12"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
        </div>
        <div class="emp-kpi-valor"><?= $kpis['pre_admisiones'] ?></div>
        <div class="emp-kpi-label">Pre-Admisiones a recibir</div>
    </div>

    <!-- Card 4: Viajes en curso -->
    <div class="emp-kpi-card">
        <div class="emp-kpi-icono">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="emp-kpi-valor"><?= $kpis['viajes_activos'] ?></div>
        <div class="emp-kpi-label">Viajes en curso</div>
    </div>

</div>

<!-- ══════════════════════════════════════════
     SECCIÓN 2 — ACCIONES RÁPIDAS
══════════════════════════════════════════ -->
<div class="emp-acciones">
    <a href="/LOGITRACK/controllers/EnvioController.php?action=crear"
       class="btn-emp-primario">+ Nuevo envío</a>
    <a href="/LOGITRACK/controllers/ViajeController.php?action=crear"
       class="btn-emp-secundario">+ Nuevo viaje</a>
    <a href="/LOGITRACK/controllers/ClienteController.php?action=crear"
       class="btn-emp-secundario">+ Nuevo cliente</a>
</div>

<!-- ══════════════════════════════════════════
     SECCIÓN 3 — MIS ÚLTIMOS ENVÍOS REGISTRADOS
══════════════════════════════════════════ -->
<div class="dash-seccion-titulo">MIS ÚLTIMOS ENVÍOS REGISTRADOS</div>

<div class="emp-tabla-contenedor">
    <?php if (empty($datos['ultimos_envios'])): ?>
        <div class="empty-state">
            <p>No hay envíos registrados en esta sucursal todavía.</p>
        </div>
    <?php else: ?>
    <table class="emp-tabla">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>N° Tracking</th>
                <th>Cliente</th>
                <th>Destino</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($datos['ultimos_envios'] as $env):
            $fechaFormateada = !empty($env['fecha_registro'])
                ? date('d/m/Y H:i', strtotime($env['fecha_registro']))
                : '—';
            $clienteNombre = htmlspecialchars($env['cliente_nombre'] . ' ' . $env['cliente_ap']);
            $estado = $env['estado_actual'];
            $badge  = $badgeEstado[$estado] ?? 'badge-emp-gris';
        ?>
            <tr>
                <td style="color:#607a6d;"><?= $fechaFormateada ?></td>
                <td><strong style="font-family:monospace; font-size:11px; letter-spacing:0.5px;"><?= htmlspecialchars($env['nro_tracking']) ?></strong></td>
                <td><?= $clienteNombre ?></td>
                <td><?= htmlspecialchars($env['destino']) ?></td>
                <td><span class="<?= $badge ?>"><?= htmlspecialchars($estado) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
