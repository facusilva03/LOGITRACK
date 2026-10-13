<?php
// views/clientes/index.php — Listado de clientes con filtros
$tituloPagina = 'Clientes — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Clientes</h1>
        <p class="page-subtitulo">Base de datos de remitentes y destinatarios</p>
    </div>
</div>

<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Filtros -->
<form method="GET" action="/LOGITRACK/controllers/ClienteController.php" class="filtros-form">
    <input type="hidden" name="action" value="index">
    <div class="form-grupo">
        <label>Localidad</label>
        <select name="id_localidad">
            <option value="">Todas las localidades</option>
            <?php foreach ($localidades as $loc): ?>
                <option value="<?= $loc['id_localidad'] ?>"
                    <?= (int)($_GET['id_localidad'] ?? 0) === (int)$loc['id_localidad'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($loc['nombre'] . ' (' . $loc['provincia'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo">
        <label>Buscar</label>
        <input type="text" name="buscar" placeholder="Nombre, apellido o documento"
               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Filtrar</button>
    </div>
    <div class="spacer"></div>
    <a href="/LOGITRACK/controllers/ClienteController.php?action=crear" class="btn-primario">+ Nuevo cliente</a>
</form>

<div class="tabla-contenedor">
    <?php if (empty($clientes)): ?>
        <div class="empty-state"><p>No se encontraron clientes con los filtros aplicados.</p></div>
    <?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th>Documento</th>
                <th>Apellido y nombre</th>
                <th>Localidad</th>
                <th>Provincia</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($clientes as $c): ?>
            <tr>
                <td>
                    <span class="text-suave"><?= htmlspecialchars($c['tipo_documento']) ?></span>
                    <?= htmlspecialchars($c['documento']) ?>
                </td>
                <td><strong><?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($c['localidad']) ?></td>
                <td><?= htmlspecialchars($c['provincia']) ?></td>
                <td><?= htmlspecialchars($c['direccion'] ?? '—') ?></td>
                <td>
                    <div class="tabla-acciones">
                        <a href="/LOGITRACK/controllers/ClienteController.php?action=editar&id=<?= $c['id_cliente'] ?>"
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
