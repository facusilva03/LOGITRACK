<?php
$tituloPagina = 'Incidentes — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Incidentes y Siniestros</h1>
        <p class="page-subtitulo">
            <?php if ($tipoFiltro !== ''): ?>
                Filtrado por: <strong><?= htmlspecialchars($tipoFiltro) ?></strong>
                &nbsp;·&nbsp; <?= count($incidentes) ?> registro<?= count($incidentes) !== 1 ? 's' : '' ?>
            <?php else: ?>
                Historial completo de incidentes registrados
            <?php endif; ?>
        </p>
    </div>
    <?php if ($tipoFiltro !== ''): ?>
        <a href="/LOGITRACK/controllers/IncidenteController.php" class="btn btn-gris">Ver todos</a>
    <?php endif; ?>
</div>

<?php if (!empty($success)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Filtro por texto libre -->
<form method="GET" action="/LOGITRACK/controllers/IncidenteController.php" class="filtros-form">
    <div class="form-grupo">
        <label>Buscar incidente</label>
        <input type="text" name="tipo" value="<?= htmlspecialchars($tipoFiltro) ?>"
               placeholder="Ej: reventón, inundación, falla...">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Buscar</button>
    </div>
    <div class="spacer"></div>
    <?php if (esAdmin() || esEmpleado() || esChofer()): ?>
        <a href="/LOGITRACK/controllers/IncidenteController.php?action=crear" class="btn-primario">+ Nuevo incidente</a>
    <?php endif; ?>
</form>

<div class="tabla-contenedor">
    <?php if (empty($incidentes)): ?>
        <div class="empty-state"><p>No se encontraron incidentes con ese filtro.</p></div>
    <?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Viaje / Patente</th>
                <th>Chofer</th>
                <th>Tipo de incidente</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($incidentes as $inc): ?>
            <tr>
                <td style="white-space:nowrap;">
                    <?= date('d/m/Y', strtotime($inc['fecha_hora'])) ?>
                    <span style="color:var(--texto-suave); font-size:12px;">
                        <?= date('H:i', strtotime($inc['fecha_hora'])) ?> hs
                    </span>
                </td>
                <td>
                    <a href="/LOGITRACK/controllers/ViajeController.php?action=detalle&id=<?= (int)$inc['id_viaje'] ?>"
                       style="color:var(--verde-medio); font-weight:600;">
                        Viaje #<?= $inc['id_viaje'] ?>
                    </a>
                    <span style="color:var(--texto-suave); font-size:12px;"> · <?= htmlspecialchars($inc['patente']) ?></span>
                </td>
                <td><?= htmlspecialchars($inc['chofer_ap'] . ', ' . $inc['chofer_nombre']) ?></td>
                <td><?= htmlspecialchars($inc['tipo_incidente']) ?></td>
                <td style="max-width:320px; white-space:normal; font-size:13px; color:var(--texto-suave);">
                    <?= htmlspecialchars($inc['descripcion']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
