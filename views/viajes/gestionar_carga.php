<?php
// views/viajes/gestionar_carga.php — Paso 2: Asignación de paquetes al viaje
$tituloPagina = 'Gestionar carga — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Viaje #<?= (int)$viaje['id_viaje'] ?> — Gestionar carga</h1>
        <p class="page-subtitulo">Paso 2 de 2 — Seleccioná los paquetes a transportar</p>
    </div>
    <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-gris">Volver al listado</a>
</div>

<!-- Indicador de progreso -->
<div style="display:flex; gap:0; margin-bottom:20px; border-radius:6px; overflow:hidden; border:1px solid #d4e0da;">
    <div style="flex:1; background:#f4f4f4; color:#607a6d; padding:10px 16px; font-size:12px;">
        Paso 1 — Cabecera del viaje &#10003;
    </div>
    <div style="flex:1; background:#2d6a4f; color:#fff; padding:10px 16px; font-size:12px; font-weight:600;">
        Paso 2 — Asignación de carga
    </div>
</div>

<!-- Resumen del viaje -->
<div class="form-card" style="margin-bottom:16px; padding:0.8rem 1rem;">
    <div style="display:grid; grid-template-columns: repeat(4,1fr); gap:16px; align-items:center;">
        <div>
            <div style="font-size:10px; color:#607a6d; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Ruta</div>
            <div style="font-size:13px; font-weight:600; color:#283d34;">
                <?= htmlspecialchars($viaje['suc_origen_nombre'] ?? '—') ?>
                &nbsp;&rarr;&nbsp;
                <?= htmlspecialchars($viaje['suc_destino_nombre'] ?? '—') ?>
            </div>
        </div>
        <div>
            <div style="font-size:10px; color:#607a6d; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Vehículo</div>
            <div style="font-size:13px; font-weight:600; color:#283d34;"><?= htmlspecialchars($viaje['patente']) ?></div>
        </div>
        <div>
            <div style="font-size:10px; color:#607a6d; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Tipo</div>
            <div style="font-size:13px; color:#283d34;"><?= htmlspecialchars($viaje['tipo_vehiculo'] ?? '—') ?></div>
        </div>
        <div>
            <div style="font-size:10px; color:#607a6d; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Capacidad máxima</div>
            <div style="font-size:15px; font-weight:700; color:#2d6a4f;">
                <?= number_format((float)($viaje['capacidad_kg_max'] ?? 0), 0, ',', '.') ?> kg
            </div>
        </div>
    </div>
</div>

<?php if (!empty($errorCarga)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($errorCarga) ?></div>
<?php endif; ?>

<form method="POST" action="/LOGITRACK/controllers/ViajeController.php?action=asignarCarga">
    <input type="hidden" name="id_viaje" value="<?= (int)$viaje['id_viaje'] ?>">

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <!-- Seleccionar todos — sin JS, el usuario hace click fila por fila -->
                    </th>
                    <th>Tracking</th>
                    <th>Remitente</th>
                    <th>Destinatario</th>
                    <th>Contenido</th>
                    <th style="text-align:right;">Peso (kg)</th>
                </tr>
            </thead>
            <?php
                $pesoTotalDisponible = array_sum(array_map(
                    fn($p) => (float)($p['peso_real'] ?? $p['peso_kg']),
                    $paquetes
                ));
                $capacidad           = (float)($viaje['capacidad_kg_max'] ?? 0);
                $porcentaje          = $capacidad > 0 ? min(100, round($pesoTotalDisponible / $capacidad * 100)) : 0;
                $colorBarra          = $porcentaje >= 90 ? '#d9534f' : ($porcentaje >= 70 ? '#e8a838' : '#2d6a4f');
            ?>
            <tbody>
            <?php if (empty($paquetes)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#607a6d; padding:32px;">
                        No hay paquetes con estado <strong>Recibido</strong> disponibles para esta ruta.<br>
                        <small style="font-size:11px;">
                            <?= htmlspecialchars($viaje['suc_origen_nombre'] ?? '—') ?>
                            &rarr;
                            <?= htmlspecialchars($viaje['suc_destino_nombre'] ?? '—') ?>
                        </small>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($paquetes as $paq): ?>
                <tr>
                    <td style="text-align:center;">
                        <input type="checkbox"
                               name="paquetes[]"
                               value="<?= (int)$paq['id_envio'] ?>"
                               style="width:15px; height:15px; cursor:pointer; accent-color:#2d6a4f;">
                    </td>
                    <td style="font-family:monospace; font-size:12px; font-weight:600;">
                        <?= htmlspecialchars($paq['nro_tracking']) ?>
                    </td>
                    <td><?= htmlspecialchars($paq['remitente_ap'] . ', ' . $paq['remitente']) ?></td>
                    <td><?= htmlspecialchars($paq['destinatario_ap'] . ', ' . $paq['destinatario']) ?></td>
                    <td>
                        <span class="badge badge-gris"><?= htmlspecialchars($paq['tipo_contenido']) ?></span>
                    </td>
                    <td style="text-align:right; font-weight:600;">
                        <?php
                            $pesoMostrar = isset($paq['peso_real']) && $paq['peso_real'] !== null
                                           ? (float)$paq['peso_real'] : (float)$paq['peso_kg'];
                            $esConfirmado = isset($paq['peso_real']) && $paq['peso_real'] !== null;
                        ?>
                        <?= number_format($pesoMostrar, 2, ',', '.') ?>
                        <?php if ($esConfirmado): ?>
                            <span style="font-size:10px; color:#607a6d; display:block;">confirmado</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($paquetes)): ?>
            <tfoot>
                <tr style="background:#f4f9f6; border-top:2px solid #d4e0da;">
                    <td colspan="5" style="padding:10px 12px; font-size:13px; color:#607a6d;">
                        Peso total si seleccionás todos los paquetes
                    </td>
                    <td style="text-align:right; padding:10px 12px; font-weight:700; font-size:14px; color:<?= $colorBarra ?>;">
                        <?= number_format($pesoTotalDisponible, 2, ',', '.') ?> kg
                        <span style="font-weight:400; font-size:11px; color:#607a6d; display:block;">
                            de <?= number_format($capacidad, 0, ',', '.') ?> kg (<?= $porcentaje ?>%)
                        </span>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <div class="form-acciones" style="margin-top:16px;">
        <button type="submit" class="btn btn-verde">
            Confirmar carga y finalizar
        </button>
        <a href="/LOGITRACK/controllers/ViajeController.php?action=detalle&id=<?= (int)$viaje['id_viaje'] ?>"
           class="btn btn-outline">
            Omitir — ir al detalle del viaje
        </a>
        <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-gris">
            Volver al listado
        </a>
    </div>
</form>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
