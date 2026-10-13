<?php
$tituloPagina = 'Editar aseguradora — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Editar aseguradora</h1>
        <p class="page-subtitulo"><?= htmlspecialchars($aseguradora['nombre']) ?></p>
    </div>
    <a href="/LOGITRACK/controllers/AseguradoraController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($cantVehiculos > 0): ?>
    <div class="alerta alerta-aviso">
        Esta aseguradora tiene <strong><?= $cantVehiculos ?></strong> vehículo<?= $cantVehiculos > 1 ? 's' : '' ?> asignado<?= $cantVehiculos > 1 ? 's' : '' ?>.
        Si la eliminás, quedarán sin aseguradora.
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/AseguradoraController.php?action=actualizar">
        <input type="hidden" name="id_aseguradora" value="<?= (int)$aseguradora['id_aseguradora'] ?>">

        <div class="form-grupo">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" required maxlength="100"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? $aseguradora['nombre']) ?>">
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="telefono_emergencia">Teléfono de emergencia</label>
                <input type="text" id="telefono_emergencia" name="telefono_emergencia" maxlength="20"
                       placeholder="Ej: 0800-555-1234"
                       value="<?= htmlspecialchars($_POST['telefono_emergencia'] ?? $aseguradora['telefono_emergencia'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label for="web">Sitio web</label>
                <input type="text" id="web" name="web" maxlength="100"
                       value="<?= htmlspecialchars($_POST['web'] ?? $aseguradora['web'] ?? '') ?>">
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Guardar cambios</button>
            <a href="/LOGITRACK/controllers/AseguradoraController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
