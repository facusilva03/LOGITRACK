<?php
// views/dashboard/index.php — Vista del panel principal según el rol
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<?php if (esAdmin()): ?>
<!-- ===================== VISTA ADMINISTRADOR ===================== -->
<div class="page-header">
    <div>
        <h1 class="page-titulo">Panel de Administración</h1>
        <p class="page-subtitulo">Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></p>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card oscuro">
        <div class="kpi-valor"><?= $kpis['usuarios_activos'] ?></div>
        <div class="kpi-etiqueta">Usuarios activos</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-valor"><?= $kpis['total_envios'] ?></div>
        <div class="kpi-etiqueta">Envíos registrados</div>
    </div>
    <div class="kpi-card medio">
        <div class="kpi-valor"><?= $kpis['viajes_activos'] ?></div>
        <div class="kpi-etiqueta">Viajes en curso</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-valor"><?= $kpis['total_clientes'] ?></div>
        <div class="kpi-etiqueta">Clientes registrados</div>
    </div>
</div>

<div class="flex-gap mt-2">
    <a href="/LOGITRACK/controllers/EnvioController.php?action=crear" class="btn btn-verde">+ Nuevo envío</a>
    <a href="/LOGITRACK/controllers/ViajeController.php?action=crear" class="btn btn-outline">+ Nuevo viaje</a>
    <a href="/LOGITRACK/controllers/UsuarioController.php?action=crear" class="btn btn-outline">+ Nuevo usuario</a>
</div>

<?php elseif (esEmpleado()): ?>
<!-- ===================== VISTA EMPLEADO ===================== -->
<div class="page-header">
    <div>
        <h1 class="page-titulo">Panel de Sucursal</h1>
        <p class="page-subtitulo">Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></p>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-valor"><?= $kpis['envios_hoy'] ?></div>
        <div class="kpi-etiqueta">Envíos registrados hoy</div>
    </div>
    <div class="kpi-card medio">
        <div class="kpi-valor"><?= $kpis['viajes_activos'] ?></div>
        <div class="kpi-etiqueta">Viajes en curso</div>
    </div>
    <div class="kpi-card naranja">
        <div class="kpi-valor"><?= $kpis['estados_pendientes'] ?></div>
        <div class="kpi-etiqueta">Envíos pendientes (Enviado / En camino)</div>
    </div>
</div>

<div class="flex-gap mt-2">
    <a href="/LOGITRACK/controllers/EnvioController.php?action=crear" class="btn btn-verde">+ Nuevo envío</a>
    <a href="/LOGITRACK/controllers/ViajeController.php?action=crear" class="btn btn-outline">+ Nuevo viaje</a>
</div>

<?php elseif (esChofer()): ?>
<!-- ===================== VISTA CHOFER ===================== -->
<div class="page-header">
    <div>
        <h1 class="page-titulo">Mi Panel</h1>
        <p class="page-subtitulo">Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></p>
    </div>
</div>

<?php if (!$usuarioInfo || !$usuarioInfo['id_chofer']): ?>
    <div class="alerta alerta-aviso">Tu usuario no tiene un chofer asignado. Consultá al administrador.</div>
<?php else: ?>

<div class="kpi-grid">
    <div class="kpi-card oscuro">
        <div class="kpi-valor"><?= $kpis['total_viajes'] ?></div>
        <div class="kpi-etiqueta">Viajes realizados</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-valor"><?= $kpis['viajes_finalizados'] ?></div>
        <div class="kpi-etiqueta">Viajes finalizados</div>
    </div>
    <div class="kpi-card rojo">
        <div class="kpi-valor"><?= $kpis['incidentes'] ?></div>
        <div class="kpi-etiqueta">Incidentes registrados</div>
    </div>
</div>

<?php if (!empty($datos['ultimo_viaje'])): $uv = $datos['ultimo_viaje']; ?>
<div class="form-card mt-3">
    <h2 class="seccion-titulo">Último viaje</h2>
    <p><strong>Viaje #<?= $uv['id_viaje'] ?></strong> &mdash; Patente: <?= htmlspecialchars($uv['patente']) ?></p>
    <p class="mt-1">Fecha salida: <?= htmlspecialchars($uv['fecha_salida'] ?? '—') ?></p>
    <p class="mt-1">Estado: <span class="badge <?= $uv['estado_viaje'] === 'Finalizado' ? 'badge-verde' : ($uv['estado_viaje'] === 'Cancelado' ? 'badge-rojo' : 'badge-azul') ?>"><?= htmlspecialchars($uv['estado_viaje']) ?></span></p>
    <div class="mt-2">
        <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-outline btn-sm">Ver todos mis viajes</a>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php elseif (esCliente()): ?>
<!-- ===================== VISTA CLIENTE ===================== -->

<?php if (!$usuarioInfo || !$usuarioInfo['id_cliente']): ?>
    <div class="alerta alerta-aviso">Tu usuario no tiene un cliente asignado. Consultá al administrador.</div>
<?php else:
    $perfil    = $datos['perfil_cliente'] ?? null;
    $actividad = $datos['actividad'] ?? [];

    $nombreMostrar = $perfil
        ? htmlspecialchars($perfil['nombre'] . ' ' . $perfil['apellido'])
        : htmlspecialchars($_SESSION['username']);

    $ubicacion = ($perfil && !empty($perfil['localidad']))
        ? htmlspecialchars($perfil['localidad'] . ', ' . $perfil['provincia'])
        : 'Cliente LogiTrack';

    $badgeEstado = [
        'Pre-Admisión'           => 'badge-gris',
        'Enviado'                => 'badge-azul',
        'Recibido'               => 'badge-gris',
        'Demorado'               => 'badge-naranja',
        'En camino'              => 'badge-verde',
        'En sucursal de entrega' => 'badge-celeste',
        'Entregado'              => 'badge-verde',
        'Devuelto'               => 'badge-rojo',
        'No entregado'           => 'badge-rojo',
    ];
?>

<!-- ── Banner de bienvenida ── -->
<div class="client-welcome">
    <div class="client-welcome-texto">
        <h2>Bienvenido/a, <?= $nombreMostrar ?></h2>
        <p><?= $ubicacion ?> &nbsp;&middot;&nbsp; Cliente LogiTrack</p>
    </div>
    <div class="client-welcome-acciones">
        <a href="/LOGITRACK/controllers/EnvioController.php" class="client-welcome-cta">
            Mis envíos
        </a>
        <a href="/LOGITRACK/controllers/EnvioController.php?action=crear" class="client-welcome-cta">
            + Nuevo envío
        </a>
    </div>
</div>

<!-- ── 4 KPIs en fila ── -->
<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card oscuro">
        <div class="kpi-valor"><?= $kpis['total_envios'] ?></div>
        <div class="kpi-etiqueta">Envíos totales</div>
    </div>
    <div class="kpi-card naranja">
        <div class="kpi-valor"><?= $kpis['en_camino'] ?></div>
        <div class="kpi-etiqueta">En camino</div>
    </div>
    <div class="kpi-card medio">
        <div class="kpi-valor"><?= $kpis['entregados'] ?></div>
        <div class="kpi-etiqueta">Entregados</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-valor"><?= $kpis['recibidos'] ?></div>
        <div class="kpi-etiqueta">Recibidos</div>
    </div>
</div>

<!-- ── Sección 2 columnas: Rastreo Rápido | Actividad Reciente ── -->
<div class="dash-dos-col">

    <!-- Columna izquierda: Rastreo rápido -->
    <div class="form-card">
        <h2 class="seccion-titulo">Rastreo rápido</h2>
        <form method="GET" action="/LOGITRACK/controllers/TrackingController.php">
            <div class="form-grupo">
                <label for="codigo">Código de seguimiento</label>
                <input
                    type="text"
                    id="codigo"
                    name="codigo"
                    placeholder="Ej: LT-A3F8B2C1"
                    maxlength="12"
                    style="text-transform:uppercase; font-family:monospace; letter-spacing:1px;"
                    value="<?= htmlspecialchars($_GET['codigo'] ?? '') ?>">
                <small class="form-hint">Ingresá el código del paquete sin guiones adicionales</small>
            </div>
            <button type="submit" class="btn btn-verde">Rastrear envío</button>
        </form>
    </div>

    <!-- Columna derecha: Actividad reciente -->
    <div class="form-card">
        <h2 class="seccion-titulo">Actividad reciente</h2>
        <?php if (empty($actividad)): ?>
            <p class="text-suave" style="padding:16px 0 8px;">Sin actividad reciente registrada.</p>
        <?php else: ?>
            <?php foreach ($actividad as $mov):
                $badge = $badgeEstado[$mov['nombre_estado']] ?? 'badge-gris';
                $fecha = date('d/m/Y H:i', strtotime($mov['fecha_hora']));
            ?>
                <div class="actividad-item">
                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($mov['nombre_estado']) ?></span>
                    <div>
                        <div class="actividad-tracking"><?= htmlspecialchars($mov['nro_tracking']) ?></div>
                        <div class="actividad-detalle">
                            <?= $fecha ?>
                            <?php if (!empty($mov['sucursal_actual'])): ?>
                                &middot; <?= htmlspecialchars($mov['sucursal_actual']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
