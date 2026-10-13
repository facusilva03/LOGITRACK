<?php
// views/choferes/crear.php — Formulario para registrar un nuevo chofer
$tituloPagina = 'Nuevo chofer — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Nuevo chofer</h1>
        <p class="page-subtitulo">Registrá los datos del conductor</p>
    </div>
    <a href="/LOGITRACK/controllers/ChoferController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/ChoferController.php?action=guardar">

        <div class="form-fila">
            <div class="form-grupo">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" required
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label for="apellido">Apellido *</label>
                <input type="text" id="apellido" name="apellido" required
                       value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="legajo">Legajo *</label>
                <input type="text" id="legajo" name="legajo" required
                       value="<?= htmlspecialchars($_POST['legajo'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label for="dni">DNI *</label>
                <input type="text" id="dni" name="dni" required
                       value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="tipo_licencia">Tipo de licencia *</label>
                <select id="tipo_licencia" name="tipo_licencia" required>
                    <option value="">-- Seleccioná --</option>
                    <option value="A" <?= ($_POST['tipo_licencia'] ?? '') === 'A' ? 'selected' : '' ?>>A — Motos</option>
                    <option value="B" <?= ($_POST['tipo_licencia'] ?? '') === 'B' ? 'selected' : '' ?>>B — Autos</option>
                    <option value="C" <?= ($_POST['tipo_licencia'] ?? '') === 'C' ? 'selected' : '' ?>>C — Camiones</option>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_sucursal">Sucursal *</label>
                <select id="id_sucursal" name="id_sucursal" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>"
                            <?= ($_POST['id_sucursal'] ?? '') == $s['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Registrar chofer</button>
            <a href="/LOGITRACK/controllers/ChoferController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
