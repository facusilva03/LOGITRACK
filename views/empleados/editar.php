<?php
$tituloPagina = 'Editar empleado — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Editar empleado</h1>
        <p class="page-subtitulo"><?= htmlspecialchars(($empleado['apellido'] ?? '') . ', ' . ($empleado['nombre'] ?? '')) ?></p>
    </div>
    <a href="/LOGITRACK/controllers/EmpleadoController.php?action=ver&id=<?= (int)$empleado['id_empleado'] ?>"
       class="btn btn-gris">Volver a la ficha</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/EmpleadoController.php?action=actualizar">
        <input type="hidden" name="id_empleado" value="<?= (int)$empleado['id_empleado'] ?>">

        <!-- Legajo y DNI (solo lectura) -->
        <div class="form-fila">
            <div class="form-grupo">
                <label>Legajo</label>
                <input type="text" value="<?= htmlspecialchars($empleado['legajo'] ?? '—') ?>"
                       readonly style="background:#f4f4f4; font-family:monospace; font-weight:700; cursor:not-allowed;">
            </div>
            <div class="form-grupo">
                <label>DNI</label>
                <input type="text" value="<?= htmlspecialchars($empleado['dni'] ?? '—') ?>"
                       readonly style="background:#f4f4f4; cursor:not-allowed;">
                <small class="form-hint">El DNI es el identificador del empleado y no puede modificarse.</small>
            </div>
        </div>

        <!-- Nombre y Apellido -->
        <div class="form-fila">
            <div class="form-grupo">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" required maxlength="100"
                       value="<?= htmlspecialchars($_POST['nombre'] ?? $empleado['nombre']) ?>">
            </div>
            <div class="form-grupo">
                <label for="apellido">Apellido *</label>
                <input type="text" id="apellido" name="apellido" required maxlength="100"
                       value="<?= htmlspecialchars($_POST['apellido'] ?? $empleado['apellido']) ?>">
            </div>
        </div>

        <!-- Teléfono y Localidad -->
        <div class="form-fila">
            <div class="form-grupo">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" maxlength="50"
                       value="<?= htmlspecialchars($_POST['telefono'] ?? $empleado['telefono'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label for="localidad">Localidad</label>
                <input type="text" id="localidad" name="localidad" maxlength="20"
                       value="<?= htmlspecialchars($_POST['localidad'] ?? $empleado['localidad'] ?? '') ?>">
            </div>
        </div>

        <!-- Dirección -->
        <div class="form-grupo">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" maxlength="20"
                   value="<?= htmlspecialchars($_POST['direccion'] ?? $empleado['direccion'] ?? '') ?>">
        </div>

        <!-- Sucursal base -->
        <div class="form-grupo">
            <label for="id_sucursal">Sucursal base *</label>
            <select id="id_sucursal" name="id_sucursal" required>
                <option value="">— Seleccioná —</option>
                <?php foreach ($sucursales as $s): ?>
                    <option value="<?= (int)$s['id_sucursal'] ?>"
                        <?= ((int)($_POST['id_sucursal'] ?? $empleado['id_sucursal'] ?? 0)) === (int)$s['id_sucursal'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Guardar cambios</button>
            <a href="/LOGITRACK/controllers/EmpleadoController.php?action=ver&id=<?= (int)$empleado['id_empleado'] ?>"
               class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
