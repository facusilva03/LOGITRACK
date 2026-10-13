<?php
// views/choferes/index.php — Listado de choferes
$tituloPagina = 'Choferes — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Choferes</h1>
        <p class="page-subtitulo">Personal de conducción registrado</p>
    </div>
</div>

<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Filtros -->
<form method="GET" action="/LOGITRACK/controllers/ChoferController.php" class="filtros-form">
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
    <div class="form-grupo">
        <label>Tipo de licencia</label>
        <select name="tipo_licencia">
            <option value="">Todas</option>
            <option value="A" <?= ($_GET['tipo_licencia'] ?? '') === 'A' ? 'selected' : '' ?>>A — Motos</option>
            <option value="B" <?= ($_GET['tipo_licencia'] ?? '') === 'B' ? 'selected' : '' ?>>B — Autos</option>
            <option value="C" <?= ($_GET['tipo_licencia'] ?? '') === 'C' ? 'selected' : '' ?>>C — Camiones</option>
        </select>
    </div>
    <div class="form-grupo">
        <label>Buscar</label>
        <input type="text" name="buscar" placeholder="Nombre, apellido o legajo"
               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Filtrar</button>
    </div>
</form>

<div class="tabla-contenedor">
    <?php if (empty($choferes)): ?>
        <div class="empty-state"><p>No se encontraron choferes con los filtros aplicados.</p></div>
    <?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th>Legajo</th>
                <th>Apellido y nombre</th>
                <th>DNI</th>
                <th>Licencia</th>
                <th>Sucursal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($choferes as $ch): ?>
            <tr>
                <td><?= htmlspecialchars($ch['legajo']) ?></td>
                <td><strong><?= htmlspecialchars($ch['apellido'] . ', ' . $ch['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($ch['dni']) ?></td>
                <td>
                    <span class="badge <?= $ch['tipo_licencia'] === 'C' ? 'badge-azul' : 'badge-gris' ?>">
                        <?= htmlspecialchars($ch['tipo_licencia']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($ch['sucursal']) ?></td>
                <td>
                    <div class="tabla-acciones">
                        <a href="/LOGITRACK/controllers/ChoferController.php?action=editar&id=<?= $ch['id_chofer'] ?>"
                           class="btn btn-outline btn-sm">Editar</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
