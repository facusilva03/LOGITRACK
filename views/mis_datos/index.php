<?php
// views/mis_datos/index.php — Formulario para editar el perfil del usuario en sesión
$tituloPagina = 'Mis Datos — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Mis Datos</h1>
        <p class="page-subtitulo">Modificá tu información personal y de acceso</p>
    </div>
    <a href="/LOGITRACK/dashboard.php" class="btn btn-gris">Volver al inicio</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>

<form method="POST" action="/LOGITRACK/controllers/MisDatosController.php">

    <?php if (esCliente() && $cliente): ?>
    <!-- ── Datos personales (solo clientes) ───────────────────────────────── -->
    <div class="form-card" style="margin-bottom: 24px;">
        <h2 class="form-seccion-titulo">Datos personales</h2>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" required maxlength="100"
                       value="<?= htmlspecialchars($_POST['nombre'] ?? $cliente['nombre']) ?>">
            </div>
            <div class="form-grupo">
                <label for="apellido">Apellido *</label>
                <input type="text" id="apellido" name="apellido" required maxlength="100"
                       value="<?= htmlspecialchars($_POST['apellido'] ?? $cliente['apellido']) ?>">
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="tipo_documento">Tipo de documento *</label>
                <select id="tipo_documento" name="tipo_documento" required>
                    <?php foreach (['DNI', 'CUIL'] as $td): ?>
                        <option value="<?= $td ?>"
                            <?= ($_POST['tipo_documento'] ?? $cliente['tipo_documento']) === $td ? 'selected' : '' ?>>
                            <?= $td ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="documento">Número de documento *</label>
                <input type="text" id="documento" name="documento" required maxlength="11"
                       inputmode="numeric"
                       value="<?= htmlspecialchars($_POST['documento'] ?? $cliente['documento']) ?>">
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_localidad">Localidad *</label>
                <select id="id_localidad" name="id_localidad" required>
                    <option value="">— Seleccioná —</option>
                    <?php foreach ($localidades as $loc): ?>
                        <option value="<?= (int)$loc['id_localidad'] ?>"
                            <?= ($_POST['id_localidad'] ?? $cliente['id_localidad']) == $loc['id_localidad'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['nombre'] . ' (' . $loc['provincia'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="direccion">Dirección <span class="text-suave">(opcional)</span></label>
                <input type="text" id="direccion" name="direccion" maxlength="150"
                       value="<?= htmlspecialchars($_POST['direccion'] ?? $cliente['direccion'] ?? '') ?>"
                       placeholder="Calle, número, piso">
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Datos de acceso (todos los roles) ──────────────────────────────── -->
    <div class="form-card">
        <h2 class="form-seccion-titulo">Datos de acceso</h2>

        <div class="form-grupo">
            <label for="username">Nombre de usuario *</label>
            <input type="text" id="username" name="username" required maxlength="100"
                   autocomplete="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? $usuario['username']) ?>">
        </div>

        <p class="text-suave" style="font-size: 13px; margin: 0 0 16px;">
            Dejá los campos de contraseña vacíos si no querés cambiarla.
        </p>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="password">Nueva contraseña <span class="text-suave">(mín. 8 caracteres)</span></label>
                <input type="password" id="password" name="password"
                       minlength="8" autocomplete="new-password">
            </div>
            <div class="form-grupo">
                <label for="password_conf">Confirmar nueva contraseña</label>
                <input type="password" id="password_conf" name="password_conf"
                       minlength="8" autocomplete="new-password">
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Guardar cambios</button>
            <a href="/LOGITRACK/dashboard.php" class="btn btn-gris">Cancelar</a>
        </div>
    </div>

</form>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
