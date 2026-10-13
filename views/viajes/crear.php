<?php
// views/viajes/crear.php — Paso 1: Cabecera del viaje
$tituloPagina = 'Nuevo viaje — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$filtroSucActual = (int)($_GET['filtro_suc'] ?? 0);
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Nuevo viaje</h1>
        <p class="page-subtitulo">Paso 1 de 2 — Definí la ruta, el vehículo y el chofer</p>
    </div>
    <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-gris">Volver al listado</a>
</div>

<!-- Indicador de progreso -->
<div style="display:flex; gap:0; margin-bottom:20px; border-radius:6px; overflow:hidden; border:1px solid #d4e0da;">
    <div style="flex:1; background:#2d6a4f; color:#fff; padding:10px 16px; font-size:12px; font-weight:600;">
        Paso 1 — Cabecera del viaje
    </div>
    <div style="flex:1; background:#f4f4f4; color:#607a6d; padding:10px 16px; font-size:12px;">
        Paso 2 — Asignación de carga
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Filtro de sucursal para cargar choferes disponibles -->
<div class="form-card" style="margin-bottom:16px; padding:14px 20px; background:#f4f9f6; border:1px solid #d4e0da;">
    <form method="GET" action="/LOGITRACK/controllers/ViajeController.php"
          style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <input type="hidden" name="action" value="crear">
        <div class="form-grupo" style="flex:1; min-width:200px; margin:0;">
            <label style="font-size:12px; font-weight:600; color:#607a6d; margin-bottom:4px; display:block;">
                Sucursal de origen (para filtrar choferes disponibles)
            </label>
            <select name="filtro_suc" style="width:100%;">
                <option value="0">— Mostrar todos —</option>
                <?php foreach ($sucursales as $suc): ?>
                    <option value="<?= (int)$suc['id_sucursal'] ?>"
                        <?= $filtroSucActual === (int)$suc['id_sucursal'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($suc['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-outline" style="white-space:nowrap; flex-shrink:0;">
            Cargar choferes disponibles
        </button>
    </form>
    <?php if ($filtroSucActual > 0): ?>
        <p style="margin-top:8px; font-size:12px; color:#607a6d;">
            Mostrando choferes actualmente en
            <strong>
                <?php
                    foreach ($sucursales as $s) {
                        if ((int)$s['id_sucursal'] === $filtroSucActual) {
                            echo htmlspecialchars($s['nombre']);
                            break;
                        }
                    }
                ?>
            </strong>
            (su sucursal base o destino de su último viaje finalizado).
        </p>
    <?php endif; ?>
</div>

<?php if (empty($choferes)): ?>
    <div class="alerta alerta-aviso">
        <?= $filtroSucActual > 0
            ? 'No hay choferes disponibles en esa sucursal en este momento.'
            : 'No hay choferes disponibles en este momento. Todos están asignados a viajes activos.' ?>
    </div>
<?php endif; ?>

<?php if (empty($vehiculos)): ?>
    <div class="alerta alerta-aviso">
        <?= $filtroSucActual > 0
            ? 'No hay vehículos disponibles en esa sucursal en este momento.'
            : 'No hay vehículos disponibles en este momento. Todos están asignados a viajes activos.' ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/ViajeController.php?action=guardar">

        <!-- Ruta -->
        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_suc_origen">Sucursal origen *</label>
                <select id="id_suc_origen" name="id_suc_origen" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($sucursales as $suc): ?>
                        <option value="<?= (int)$suc['id_sucursal'] ?>"
                            <?= (($_POST['id_suc_origen'] ?? '') == $suc['id_sucursal']
                                || ($filtroSucActual > 0 && empty($_POST) && $filtroSucActual === (int)$suc['id_sucursal']))
                                ? 'selected' : '' ?>>
                            <?= htmlspecialchars($suc['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_suc_destino">Sucursal destino *</label>
                <select id="id_suc_destino" name="id_suc_destino" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($sucursales as $suc): ?>
                        <option value="<?= (int)$suc['id_sucursal'] ?>"
                            <?= ($_POST['id_suc_destino'] ?? '') == $suc['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($suc['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Vehículo y Chofer -->
        <div class="form-fila">
            <div class="form-grupo">
                <label for="patente">Vehículo *</label>
                <select id="patente" name="patente" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($vehiculos as $veh): ?>
                        <option value="<?= htmlspecialchars($veh['patente']) ?>"
                            <?= ($_POST['patente'] ?? '') === $veh['patente'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(
                                $veh['patente'] . ' — ' . $veh['tipo'] . ' ' . $veh['marca'] . ' ' . $veh['modelo']
                                . ' — ' . number_format((float)$veh['capacidad_kg_max'], 0, ',', '.') . ' kg'
                                . ' — Ubicación: ' . ($veh['ubicacion_actual'] ?? 'Sucursal base')
                            ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_chofer">Chofer *</label>
                <select id="id_chofer" name="id_chofer" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($choferes as $ch): ?>
                        <option value="<?= (int)$ch['id_chofer'] ?>"
                            <?= ($_POST['id_chofer'] ?? '') == $ch['id_chofer'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(
                                $ch['apellido'] . ', ' . $ch['nombre']
                                . ' (Leg. ' . $ch['legajo'] . ')'
                                . ' — Ubicación actual: ' . ($ch['ubicacion_actual'] ?? 'Sucursal base')
                            ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Fecha -->
        <div class="form-grupo">
            <label for="fecha_salida">Fecha y hora de salida *</label>
            <input type="datetime-local" id="fecha_salida" name="fecha_salida" required
                   min="<?= date('Y-m-d\TH:i') ?>"
                   value="<?= htmlspecialchars($_POST['fecha_salida'] ?? '') ?>">
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Siguiente — Asignar carga &rarr;</button>
            <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
