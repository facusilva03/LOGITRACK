<?php
// views/clientes/crear.php — Formulario para registrar un nuevo cliente
$tituloPagina = 'Nuevo cliente — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Nuevo cliente</h1>
        <p class="page-subtitulo">Registrá los datos del remitente o destinatario</p>
    </div>
    <a href="/LOGITRACK/controllers/ClienteController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/ClienteController.php?action=guardar">

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
                <label for="tipo_documento">Tipo de documento *</label>
                <select id="tipo_documento" name="tipo_documento" required>
                    <option value="">-- Seleccioná --</option>
                    <option value="DNI"  <?= ($_POST['tipo_documento'] ?? '') === 'DNI'  ? 'selected' : '' ?>>DNI</option>
                    <option value="CUIT" <?= ($_POST['tipo_documento'] ?? '') === 'CUIT' ? 'selected' : '' ?>>CUIT</option>
                    <option value="CUIL" <?= ($_POST['tipo_documento'] ?? '') === 'CUIL' ? 'selected' : '' ?>>CUIL</option>
                    <option value="Pasaporte" <?= ($_POST['tipo_documento'] ?? '') === 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                </select>
            </div>
            <div class="form-grupo">
                <label for="documento">Número de documento *</label>
                <input type="text" id="documento" name="documento" required
                       value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>">
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_localidad">Localidad *</label>
                <select id="id_localidad" name="id_localidad" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($localidades as $loc): ?>
                        <option value="<?= $loc['id_localidad'] ?>"
                            <?= ($_POST['id_localidad'] ?? '') == $loc['id_localidad'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['nombre'] . ' (' . $loc['provincia'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion"
                       value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>">
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Registrar cliente</button>
            <a href="/LOGITRACK/controllers/ClienteController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
