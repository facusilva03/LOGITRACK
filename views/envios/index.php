<?php
// views/envios/index.php — Listado de envíos con filtros y tracking
$tituloPagina = 'Envíos — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

// Mapa de estados a clase de badge
$claseBadge = [
    'Enviado'                => 'badge-azul',
    'Recibido'               => 'badge-gris',
    'Demorado'               => 'badge-naranja',
    'En camino'              => 'badge-amarillo',
    'En sucursal de entrega' => 'badge-celeste',
    'Entregado'              => 'badge-verde',
    'Devuelto'               => 'badge-rojo',
    'No entregado'           => 'badge-rojo',
];
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo"><?= esCliente() ? 'Mis Envíos' : 'Envíos' ?></h1>
        <p class="page-subtitulo"><?= esCliente() ? 'Seguimiento de tus paquetes' : 'Gestión de envíos logísticos' ?></p>
    </div>
</div>

<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>

<!-- Filtros (solo para admin/empleado) -->
<?php if (!esCliente()): ?>
<form method="GET" action="/LOGITRACK/controllers/EnvioController.php" class="filtros-form">
    <input type="hidden" name="action" value="index">
    <div class="form-grupo">
        <label>Estado</label>
        <select name="id_estado">
            <option value="">Todos los estados</option>
            <?php foreach ($estados as $est): ?>
                <option value="<?= $est['id_estado'] ?>"
                    <?= (int)($_GET['id_estado'] ?? 0) === (int)$est['id_estado'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($est['nombre_estado']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo">
        <label>Sucursal origen</label>
        <select name="id_suc_origen">
            <option value="">Todas</option>
            <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s['id_sucursal'] ?>"
                    <?= (int)($_GET['id_suc_origen'] ?? 0) === (int)$s['id_sucursal'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo">
        <label>N° Tracking</label>
        <input type="text" name="tracking" placeholder="LT-XXXXXXXX"
               value="<?= htmlspecialchars($_GET['tracking'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Filtrar</button>
    </div>
    <div class="spacer"></div>
    <a href="/LOGITRACK/controllers/EnvioController.php?action=crear" class="btn-primario">+ Nuevo envío</a>
</form>
<?php endif; ?>

<div class="tabla-contenedor">
    <?php if (empty($envios)): ?>
        <div class="empty-state"><p>No se encontraron envíos.</p></div>
    <?php else: ?>
    <table class="tabla">
        <?php
        $ordenActual = $_GET['orden'] ?? 'desc';
        $ordenInverso = $ordenActual === 'desc' ? 'asc' : 'desc';
        $flecha = $ordenActual === 'desc' ? '↓' : '↑';
        $paramsOrden = array_merge($_GET, ['orden' => $ordenInverso]);
        $urlOrden = '/LOGITRACK/controllers/EnvioController.php?' . http_build_query($paramsOrden);
        ?>
        <thead>
            <tr>
                <th>
                    <a href="<?= htmlspecialchars($urlOrden) ?>"
                       style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:5px;">
                        N° Tracking <span style="font-size:11px; opacity:0.7;"><?= $flecha ?></span>
                    </a>
                </th>
                <th>Remitente</th>
                <th>Destinatario</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($envios as $e): ?>
            <tr>
                <td><strong><?= htmlspecialchars($e['nro_tracking']) ?></strong></td>
                <td><?= htmlspecialchars($e['remitente'] . ' ' . $e['remitente_ap']) ?></td>
                <td><?= htmlspecialchars($e['destinatario'] . ' ' . $e['destinatario_ap']) ?></td>
                <td><?= htmlspecialchars($e['suc_origen']) ?></td>
                <td><?= htmlspecialchars($e['suc_destino']) ?></td>
                <td>
                    <?php $estado = $e['estado_actual'] ?? 'Sin estado';
                          $clase  = $claseBadge[$estado] ?? 'badge-gris'; ?>
                    <span class="badge <?= $clase ?>"><?= htmlspecialchars($estado) ?></span>
                </td>
                <td>
                    <div class="tabla-acciones">
                        <a href="/LOGITRACK/controllers/EnvioController.php?action=detalle&id=<?= $e['id_envio'] ?>"
                           class="btn btn-outline btn-sm">Ver</a>
                        <?php if (!esCliente()): ?>
                        <a href="/LOGITRACK/controllers/EnvioController.php?action=editar&id=<?= $e['id_envio'] ?>"
                           class="btn btn-outline btn-sm">Editar</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
