<?php
// views/viajes/editar.php — Formulario para editar un viaje existente
$tituloPagina = 'Editar viaje — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Editar viaje #<?= $viaje['id_viaje'] ?></h1>
        <p class="page-subtitulo">Patente: <?= htmlspecialchars($viaje['patente']) ?></p>
    </div>
    <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/ViajeController.php?action=actualizar">
        <input type="hidden" name="id_viaje" value="<?= $viaje['id_viaje'] ?>">

        <div class="form-fila">
            <div class="form-grupo">
                <label for="patente">Vehículo (patente) *</label>
                <select id="patente" name="patente" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($vehiculos as $veh): ?>
                        <option value="<?= htmlspecialchars($veh['patente']) ?>"
                            <?= ($_POST['patente'] ?? $viaje['patente']) === $veh['patente'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($veh['patente'] . ' — ' . $veh['tipo'] . ' ' . $veh['marca'] . ' ' . $veh['modelo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_chofer">Chofer *</label>
                <select id="id_chofer" name="id_chofer" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($choferes as $ch): ?>
                        <option value="<?= $ch['id_chofer'] ?>"
                            <?= ($_POST['id_chofer'] ?? $viaje['id_chofer']) == $ch['id_chofer'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ch['apellido'] . ', ' . $ch['nombre'] . ' (Leg. ' . $ch['legajo'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="fecha_salida">Fecha y hora de salida *</label>
                <input type="datetime-local" id="fecha_salida" name="fecha_salida" required
                       value="<?= htmlspecialchars($_POST['fecha_salida'] ?? str_replace(' ', 'T', substr($viaje['fecha_salida'] ?? '', 0, 16))) ?>">
            </div>
            <div class="form-grupo">
                <label for="estado_viaje">Estado *</label>
                <select id="estado_viaje" name="estado_viaje" required>
                    <?php foreach (['Planificado', 'En curso', 'Demorado', 'Finalizado', 'Cancelado'] as $est): ?>
                        <option value="<?= $est ?>"
                            <?= ($_POST['estado_viaje'] ?? $viaje['estado_viaje']) === $est ? 'selected' : '' ?>>
                            <?= $est ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_suc_origen">Sucursal de origen *</label>
                <select id="id_suc_origen" name="id_suc_origen" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= (int)$s['id_sucursal'] ?>"
                            <?= ($_POST['id_suc_origen'] ?? $viaje['id_suc_origen']) == $s['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_suc_destino">Sucursal de destino *</label>
                <select id="id_suc_destino" name="id_suc_destino" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= (int)$s['id_sucursal'] ?>"
                            <?= ($_POST['id_suc_destino'] ?? $viaje['id_suc_destino']) == $s['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Guardar cambios</button>
            <a href="/LOGITRACK/controllers/ViajeController.php?action=detalle&id=<?= $viaje['id_viaje'] ?>"
               class="btn btn-outline">Ver detalle</a>
            <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
