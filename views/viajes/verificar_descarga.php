<?php
// views/viajes/verificar_descarga.php — Control de descarga al finalizar el viaje
$tituloPagina = 'Control de Descarga — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$idSucursalActual = (int)($viaje['id_suc_destino'] ?? 0);
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Control de Descarga — Viaje #<?= (int)$viaje['id_viaje'] ?></h1>
        <p class="page-subtitulo">Verificá el estado de cada paquete al momento de la descarga</p>
    </div>
    <a href="/LOGITRACK/controllers/ViajeController.php?action=detalle&id=<?= (int)$viaje['id_viaje'] ?>"
       class="btn btn-gris">Volver al detalle</a>
</div>

<?php if (empty($envios)): ?>
    <div class="alerta alerta-aviso">Este viaje no tiene envíos asignados.</div>
<?php else: ?>
<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th>N° Tracking</th>
                <th>Remitente</th>
                <th>Tipo de contenido</th>
                <th style="width:380px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($envios as $e): ?>
            <tr>
                <td><strong><?= htmlspecialchars($e['nro_tracking']) ?></strong></td>
                <td><?= htmlspecialchars($e['remitente'] . ' ' . $e['remitente_ap']) ?></td>
                <td><?= htmlspecialchars($e['tipo_contenido']) ?></td>
                <td>
                    <?php if ((int)$e['estado_actual'] === 9): ?>
                        <span class="badge badge-verde">Verificado</span>
                    <?php elseif ((int)$e['estado_actual'] === 6): ?>
                        <span class="badge badge-rojo">Siniestrado</span>
                    <?php else: ?>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <form method="POST" action="/LOGITRACK/controllers/ViajeController.php?action=marcarCorrecto">
                                <input type="hidden" name="id_viaje" value="<?= (int)$viaje['id_viaje'] ?>">
                                <input type="hidden" name="id_envio" value="<?= (int)$e['id_envio'] ?>">
                                <input type="hidden" name="id_sucursal_actual" value="<?= $idSucursalActual ?>">
                                <button type="submit" class="btn btn-verde btn-sm" title="Marcar como correcto">
                                    &#10003; Correcto
                                </button>
                            </form>
                            <form method="POST" action="/LOGITRACK/controllers/ViajeController.php?action=marcarRoto"
                                  style="display:flex; gap:6px; align-items:center;">
                                <input type="hidden" name="id_viaje" value="<?= (int)$viaje['id_viaje'] ?>">
                                <input type="hidden" name="id_envio" value="<?= (int)$e['id_envio'] ?>">
                                <input type="hidden" name="id_sucursal_actual" value="<?= $idSucursalActual ?>">
                                <input type="text" name="observaciones" placeholder="Detalle del daño..." required
                                       style="width:160px; font-size:12px; padding:6px 8px;">
                                <button type="submit" class="btn btn-rojo btn-sm" title="Marcar como roto/devuelto">
                                    &#10007; Roto
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
