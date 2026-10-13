<?php
// views/viajes/en_camino.php — Viajes activos según VW_Viajes_Activos
$tituloPagina = 'Viajes en Curso — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Viajes en Curso</h1>
        <p class="page-subtitulo">Viajes actualmente en tránsito según VW_Viajes_Activos</p>
    </div>
    <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-gris">Ver todos los viajes</a>
</div>

<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th>ID Viaje</th>
                <th>Estado</th>
                <th>Patente</th>
                <th>Chofer</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($viajesActivos)): ?>
            <tr>
                <td colspan="4" style="text-align:center; color:var(--texto-suave); padding:24px;">
                    No hay viajes en tránsito en este momento.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($viajesActivos as $v): ?>
            <tr>
                <td>
                    <a href="/LOGITRACK/controllers/ViajeController.php?action=detalle&id=<?= (int)$v['id_viaje'] ?>"
                       class="btn btn-outline btn-sm">#<?= (int)$v['id_viaje'] ?></a>
                </td>
                <td><span class="badge badge-azul"><?= htmlspecialchars($v['estado_viaje']) ?></span></td>
                <td><?= htmlspecialchars($v['patente_vehiculo']) ?></td>
                <td><?= htmlspecialchars($v['apellido_chofer'] . ', ' . $v['nombre_chofer']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
