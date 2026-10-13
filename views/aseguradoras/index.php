<?php
$tituloPagina = 'Aseguradoras — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Aseguradoras</h1>
        <p class="page-subtitulo">Compañías de seguro asignables a la flota</p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="GET" action="/LOGITRACK/controllers/AseguradoraController.php" class="filtros-form">
    <div class="form-grupo">
        <label>Buscar</label>
        <input type="text" name="buscar" placeholder="Nombre de aseguradora"
               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Filtrar</button>
    </div>
    <div class="spacer"></div>
    <a href="/LOGITRACK/controllers/AseguradoraController.php?action=crear" class="btn-primario">+ Nueva aseguradora</a>
</form>

<div class="tabla-contenedor">
<?php if (empty($aseguradoras)): ?>
    <div class="empty-state"><p>No se encontraron aseguradoras.</p></div>
<?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Teléfono emergencia</th>
                <th>Web</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($aseguradoras as $a): ?>
            <tr>
                <td><strong><?= htmlspecialchars($a['nombre']) ?></strong></td>
                <td><?= $a['telefono_emergencia'] ? htmlspecialchars($a['telefono_emergencia']) : '<span style="color:var(--texto-suave);">—</span>' ?></td>
                <td>
                    <?php if ($a['web']): ?>
                        <span style="font-size:13px; color:var(--texto-suave);"><?= htmlspecialchars($a['web']) ?></span>
                    <?php else: ?>
                        <span style="color:var(--texto-suave);">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="tabla-acciones">
                        <a href="/LOGITRACK/controllers/AseguradoraController.php?action=editar&id=<?= (int)$a['id_aseguradora'] ?>"
                           class="btn btn-outline btn-sm">Editar</a>
                        <form method="POST" action="/LOGITRACK/controllers/AseguradoraController.php?action=eliminar"
                              style="display:inline;"
                              onsubmit="return confirm('¿Eliminar la aseguradora «<?= htmlspecialchars(addslashes($a['nombre'])) ?>»?\nLos vehículos asociados quedarán sin aseguradora.')">
                            <input type="hidden" name="id_aseguradora" value="<?= (int)$a['id_aseguradora'] ?>">
                            <button type="submit" class="btn btn-rojo btn-sm">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
