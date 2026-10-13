<?php
// views/vehiculos/crear.php — Formulario para registrar un nuevo vehículo
$tituloPagina = 'Nuevo vehículo — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$estadosVehiculo = ['Activo', 'Averiado', 'En Reparación'];
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Nuevo vehículo</h1>
        <p class="page-subtitulo">Registrá un vehículo en la flota</p>
    </div>
    <a href="/LOGITRACK/controllers/VehiculoController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/VehiculoController.php?action=guardar">

        <!-- Patente -->
        <div class="form-grupo">
            <label for="patente">Patente *</label>
            <input type="text" id="patente" name="patente" required maxlength="15"
                   placeholder="Ej: ABC123 o AB123CD"
                   style="text-transform:uppercase; font-family:monospace; letter-spacing:1px;"
                   value="<?= htmlspecialchars(strtoupper($_POST['patente'] ?? '')) ?>">
        </div>

        <!-- Marca y Modelo -->
        <div class="form-fila">
            <div class="form-grupo">
                <label for="marca">Marca *</label>
                <input type="text" id="marca" name="marca" required maxlength="50"
                       value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label for="modelo">Modelo</label>
                <input type="text" id="modelo" name="modelo" maxlength="50"
                       value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>">
            </div>
        </div>

        <!-- Tipo de vehículo y Estado -->
        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_tipo_veh">Tipo de vehículo *</label>
                <select id="id_tipo_veh" name="id_tipo_veh" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($tiposVehiculo as $tv): ?>
                        <option value="<?= (int)$tv['id_tipo_veh'] ?>"
                            <?= ($_POST['id_tipo_veh'] ?? '') == $tv['id_tipo_veh'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tv['nombre']) ?>
                            — <?= number_format((float)$tv['capacidad_kg_max'], 0, ',', '.') ?> kg
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="estado_vehiculo">Estado *</label>
                <select id="estado_vehiculo" name="estado_vehiculo" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($estadosVehiculo as $est): ?>
                        <option value="<?= $est ?>"
                            <?= ($_POST['estado_vehiculo'] ?? '') === $est ? 'selected' : '' ?>>
                            <?= $est ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Sucursal y Aseguradora -->
        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_sucursal">Sucursal base *</label>
                <select id="id_sucursal" name="id_sucursal" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= (int)$s['id_sucursal'] ?>"
                            <?= ($_POST['id_sucursal'] ?? '') == $s['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_aseguradora">Aseguradora</label>
                <select id="id_aseguradora" name="id_aseguradora">
                    <option value="">— Sin aseguradora —</option>
                    <?php foreach ($aseguradoras as $a): ?>
                        <option value="<?= (int)$a['id_aseguradora'] ?>"
                            <?= ($_POST['id_aseguradora'] ?? '') == $a['id_aseguradora'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Registrar vehículo</button>
            <a href="/LOGITRACK/controllers/VehiculoController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
