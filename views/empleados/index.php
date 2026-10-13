<?php
$tituloPagina = 'Empleados de Sucursal — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Empleados de Sucursal</h1>
        <p class="page-subtitulo">
            <?= count($empleados) ?> registro<?= count($empleados) !== 1 ? 's' : '' ?> encontrado<?= count($empleados) !== 1 ? 's' : '' ?>
        </p>
    </div>
</div>

<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>

<!-- Filtros -->
<form method="GET" action="/LOGITRACK/controllers/EmpleadoController.php" class="filtros-form">
    <input type="hidden" name="action" value="index">
    <div class="form-grupo">
        <label>Sucursal</label>
        <select name="id_sucursal">
            <option value="">Todas</option>
            <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s['id_sucursal'] ?>"
                    <?= (int)($_GET['id_sucursal'] ?? 0) === (int)$s['id_sucursal'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo" style="min-width:220px; max-width:380px;">
        <label>Buscar</label>
        <input type="text" name="buscar"
               placeholder="Nombre, apellido, legajo o DNI..."
               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Buscar</button>
    </div>
    <div class="spacer"></div>
    <a href="/LOGITRACK/controllers/UsuarioController.php?action=crear" class="btn-primario">+ Nuevo empleado</a>
</form>

<div class="tabla-contenedor">
    <?php if (empty($empleados)): ?>
        <div class="empty-state"><p>No se encontraron empleados con los filtros aplicados.</p></div>
    <?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th>Legajo</th>
                <th>Apellido y nombre</th>
                <th>Sucursal</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($empleados as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['legajo'] ?? '—') ?></td>
                <td>
                    <strong><?= htmlspecialchars(($e['apellido'] ?? '') . ', ' . ($e['nombre'] ?? '')) ?></strong>
                </td>
                <td><?= htmlspecialchars($e['sucursal_nombre'] ?? '—') ?></td>
                <td>
                    <?php if ($e['activo'] ?? false): ?>
                        <span class="badge badge-verde">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-gris">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="tabla-acciones">
                        <a href="/LOGITRACK/controllers/EmpleadoController.php?action=ver&id=<?= $e['id_empleado'] ?>"
                           class="btn btn-outline btn-sm">Ver</a>
                        <a href="/LOGITRACK/controllers/EmpleadoController.php?action=eliminar&id=<?= $e['id_empleado'] ?>"
                           class="btn btn-rojo btn-sm"
                           onclick="return confirm('¿Eliminar permanentemente a <?= htmlspecialchars(addslashes(($e['nombre'] ?? '') . ' ' . ($e['apellido'] ?? ''))) ?>?\nEsta acción no se puede deshacer.')">
                           Eliminar
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
