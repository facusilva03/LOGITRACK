<?php
// views/viajes/index.php — Listado de viajes con filtros
$tituloPagina = 'Viajes — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$claseBadgeViaje = [
    'Planificado' => 'badge-gris',
    'En curso'    => 'badge-azul',
    'Demorado'    => 'badge-naranja',
    'Finalizado'  => 'badge-verde',
    'Cancelado'   => 'badge-rojo',
];
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo"><?= esChofer() ? 'Mis Viajes' : 'Viajes' ?></h1>
        <p class="page-subtitulo">
            <?= esChofer() ? 'Tus viajes asignados' : 'Gestión de viajes logísticos' ?>
        </p>
    </div>
</div>

<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>

<!-- Filtros -->
<form method="GET" action="/LOGITRACK/controllers/ViajeController.php" class="filtros-form">
    <input type="hidden" name="action" value="index">
    <div class="form-grupo">
        <label>Estado</label>
        <select name="estado_viaje">
            <option value="">Todos</option>
            <?php foreach (['Planificado', 'En curso', 'Demorado', 'Finalizado', 'Cancelado'] as $est): ?>
                <option value="<?= $est ?>"
                    <?= ($_GET['estado_viaje'] ?? '') === $est ? 'selected' : '' ?>>
                    <?= $est ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if (!esChofer()): ?>
    <div class="form-grupo">
        <label>Chofer</label>
        <select name="id_chofer">
            <option value="">Todos</option>
            <?php foreach ($choferes as $ch): ?>
                <option value="<?= $ch['id_chofer'] ?>"
                    <?= (int)($_GET['id_chofer'] ?? 0) === (int)$ch['id_chofer'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ch['apellido'] . ', ' . $ch['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="form-grupo">
        <label>Patente</label>
        <input type="text" name="patente" placeholder="Ej: AA123BB"
               value="<?= htmlspecialchars($_GET['patente'] ?? '') ?>"
               style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()">
    </div>
    <div class="form-grupo">
        <label>Desde</label>
        <input type="date" name="fecha_desde" value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>Hasta</label>
        <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Filtrar</button>
    </div>
    <?php if (!esChofer()): ?>
    <div class="spacer"></div>
    <a href="/LOGITRACK/controllers/ViajeController.php?action=crear" class="btn-primario">+ Nuevo viaje</a>
    <?php endif; ?>
</form>

<div class="tabla-contenedor">
    <?php if (empty($viajes)): ?>
        <div class="empty-state"><p>No se encontraron viajes con los filtros aplicados.</p></div>
    <?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th>#</th>
                <th>Patente</th>
                <th>Ruta</th>
                <?php if (!esChofer()): ?><th>Chofer</th><?php endif; ?>
                <th>Fecha salida</th>
                <th>Envíos</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($viajes as $v): ?>
            <tr>
                <td><?= $v['id_viaje'] ?></td>
                <td><strong><?= htmlspecialchars($v['patente']) ?></strong></td>
                <td style="white-space:nowrap; font-size:13px;">
                    <?= htmlspecialchars($v['suc_origen_nombre'] ?? '—') ?>
                    <span style="color:var(--texto-suave);"> → </span>
                    <?= htmlspecialchars($v['suc_destino_nombre'] ?? '—') ?>
                </td>
                <?php if (!esChofer()): ?>
                    <td><?= htmlspecialchars($v['chofer_ap'] . ', ' . $v['chofer_nombre']) ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars(substr($v['fecha_salida'] ?? '—', 0, 16)) ?></td>
                <td><span class="badge badge-gris"><?= $v['cant_envios'] ?></span></td>
                <td>
                    <?php $clase = $claseBadgeViaje[$v['estado_viaje']] ?? 'badge-gris'; ?>
                    <span class="badge <?= $clase ?>"><?= htmlspecialchars($v['estado_viaje']) ?></span>
                </td>
                <td>
                    <div class="tabla-acciones">
                        <a href="/LOGITRACK/controllers/ViajeController.php?action=detalle&id=<?= $v['id_viaje'] ?>"
                           class="btn btn-outline btn-sm">Ver</a>
                        <?php if (!esChofer()): ?>
                        <?php if ($v['estado_viaje'] === 'Planificado'): ?>
                        <a href="/LOGITRACK/controllers/ViajeController.php?action=gestionarCarga&id_viaje=<?= $v['id_viaje'] ?>"
                           class="btn btn-outline btn-sm">Asignar envíos</a>
                        <?php endif; ?>
                        <a href="/LOGITRACK/controllers/ViajeController.php?action=editar&id=<?= $v['id_viaje'] ?>"
                           class="btn btn-outline btn-sm">Editar</a>
                        <?php if ($v['estado_viaje'] !== 'Cancelado' && $v['estado_viaje'] !== 'Finalizado'): ?>
                        <a href="/LOGITRACK/controllers/ViajeController.php?action=cancelar&id=<?= $v['id_viaje'] ?>"
                           class="btn btn-rojo btn-sm"
                           onclick="return confirm('¿Cancelar viaje #<?= $v['id_viaje'] ?>?')">Cancelar</a>
                        <?php endif; ?>
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
