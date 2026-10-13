<?php
// views/vehiculos/index.php — Listado de flota
$tituloPagina = 'Vehículos — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$badgeEstado = [
    'Activo'        => 'badge-verde',
    'Averiado'      => 'badge-rojo',
    'En Reparación' => 'badge-naranja',
    'Inactivo'      => 'badge-gris',
];
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Vehículos</h1>
        <p class="page-subtitulo">Gestión de la flota logística</p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Filtros -->
<form method="GET" action="/LOGITRACK/controllers/VehiculoController.php" class="filtros-form">
    <div class="form-grupo">
        <label>Sucursal</label>
        <select name="id_sucursal">
            <option value="">Todas</option>
            <?php foreach ($sucursales as $s): ?>
                <option value="<?= (int)$s['id_sucursal'] ?>"
                    <?= (int)($_GET['id_sucursal'] ?? 0) === (int)$s['id_sucursal'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo">
        <label>Estado</label>
        <select name="estado_vehiculo">
            <option value="">Todos</option>
            <?php foreach (['Activo', 'Averiado', 'En Reparación', 'Inactivo'] as $est): ?>
                <option value="<?= $est ?>"
                    <?= ($_GET['estado_vehiculo'] ?? '') === $est ? 'selected' : '' ?>>
                    <?= $est ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo">
        <label>Buscar</label>
        <input type="text" name="buscar" placeholder="Patente, marca o modelo"
               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Filtrar</button>
    </div>
    <div class="spacer"></div>
    <a href="/LOGITRACK/controllers/VehiculoController.php?action=crear" class="btn-primario">+ Nuevo vehículo</a>
</form>

<div class="tabla-contenedor">
    <?php if (empty($vehiculos)): ?>
        <div class="empty-state"><p>No se encontraron vehículos con los filtros aplicados.</p></div>
    <?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th>Patente</th>
                <th>Marca / Modelo</th>
                <th>Tipo</th>
                <th>Capacidad</th>
                <th>Sucursal base</th>
                <th>Aseguradora</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($vehiculos as $v): ?>
            <?php $badgeClass = $badgeEstado[$v['estado_vehiculo']] ?? 'badge-gris'; ?>
            <tr>
                <td style="font-weight:700; font-family:monospace;">
                    <?= htmlspecialchars($v['patente']) ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($v['marca']) ?></strong>
                    <span style="color:var(--texto-suave);"> <?= htmlspecialchars($v['modelo']) ?></span>
                </td>
                <td><?= htmlspecialchars($v['tipo_vehiculo']) ?></td>
                <td style="text-align:right;">
                    <?= number_format((float)$v['capacidad_kg_max'], 0, ',', '.') ?> kg
                </td>
                <td><?= htmlspecialchars($v['sucursal']) ?></td>
                <td><?= $v['aseguradora'] ? htmlspecialchars($v['aseguradora']) : '<span style="color:var(--texto-suave);">—</span>' ?></td>
                <td>
                    <span class="badge <?= $badgeClass ?>">
                        <?= htmlspecialchars($v['estado_vehiculo']) ?>
                    </span>
                </td>
                <td>
                    <div class="tabla-acciones">
                        <a href="/LOGITRACK/controllers/VehiculoController.php?action=editar&patente=<?= urlencode($v['patente']) ?>"
                           class="btn btn-outline btn-sm">Editar</a>
                        <?php if ($v['estado_vehiculo'] !== 'Inactivo'): ?>
                        <form method="POST" action="/LOGITRACK/controllers/VehiculoController.php?action=desactivar"
                              style="display:inline;"
                              onsubmit="return confirm('¿Dar de baja el vehículo <?= htmlspecialchars(addslashes($v['patente'])) ?>?\nNo podrá usarse en nuevos viajes.')">
                            <input type="hidden" name="patente" value="<?= htmlspecialchars($v['patente']) ?>">
                            <button type="submit" class="btn btn-rojo btn-sm">Dar de baja</button>
                        </form>
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
