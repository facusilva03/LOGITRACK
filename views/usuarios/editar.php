<?php
// views/usuarios/editar.php — Formulario para editar un usuario existente
$tituloPagina = 'Editar usuario — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Editar usuario</h1>
        <p class="page-subtitulo"><?= htmlspecialchars($usuario['username']) ?></p>
    </div>
    <a href="/LOGITRACK/controllers/UsuarioController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/UsuarioController.php?action=actualizar">
        <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">

        <div class="form-grupo">
            <label for="username">Nombre de usuario *</label>
            <input type="text" id="username" name="username" required
                   value="<?= htmlspecialchars($_POST['username'] ?? $usuario['username']) ?>">
        </div>

        <div class="form-grupo">
            <label for="password">Nueva contraseña <span class="text-suave">(dejá vacío para no cambiar)</span></label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-grupo">
            <label for="id_rol">Rol *</label>
            <select id="id_rol" name="id_rol" required>
                <option value="">-- Seleccioná un rol --</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id_rol'] ?>"
                        <?= ($_POST['id_rol'] ?? $usuario['id_rol']) == $r['id_rol'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Guardar cambios</button>
            <a href="/LOGITRACK/controllers/UsuarioController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
