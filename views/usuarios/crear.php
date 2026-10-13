<?php
$tituloPagina = 'Nuevo usuario — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$rol  = $_POST['rol_interno'] ?? '';
$post = fn(string $k) => htmlspecialchars($_POST[$k] ?? '');
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Alta de personal interno</h1>
        <p class="page-subtitulo">Registrá un Empleado de Sucursal o un Chofer junto con su acceso al sistema</p>
    </div>
    <a href="/LOGITRACK/controllers/UsuarioController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card form-card-wide">
    <form method="POST" action="/LOGITRACK/controllers/UsuarioController.php?action=guardar">

        <div class="form-grid-2col">

            <!-- ══ COLUMNA IZQUIERDA: Datos personales ══ -->
            <div class="form-col">
                <h2 class="seccion-titulo">Datos personales</h2>

                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" required
                               maxlength="100" placeholder="Ej: Juan"
                               value="<?= $post('nombre') ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="apellido">Apellido *</label>
                        <input type="text" id="apellido" name="apellido" required
                               maxlength="100" placeholder="Ej: Pérez"
                               value="<?= $post('apellido') ?>">
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="documento">DNI / Documento *</label>
                    <input type="text" id="documento" name="documento" required
                           inputmode="numeric" pattern="\d{7,8}" maxlength="8"
                           title="Ingresá el DNI sin puntos: 7 u 8 dígitos numéricos"
                           placeholder="Ej: 38271456"
                           value="<?= $post('documento') ?>">
                    <small class="form-hint">Se usará como usuario y contraseña inicial.</small>
                </div>

                <div class="form-grupo">
                    <label for="localidad">Localidad</label>
                    <input type="text" id="localidad" name="localidad"
                           maxlength="100" placeholder="Ej: Mendoza"
                           value="<?= $post('localidad') ?>">
                </div>

                <div class="form-grupo">
                    <label for="direccion">Dirección exacta</label>
                    <input type="text" id="direccion" name="direccion"
                           maxlength="150" placeholder="Ej: Av. San Martín 1234"
                           value="<?= $post('direccion') ?>">
                </div>

                <div class="form-grupo">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono"
                           maxlength="30" placeholder="Ej: +54 9 261 123-4567"
                           value="<?= $post('telefono') ?>">
                </div>
            </div><!-- /col izquierda -->

            <!-- ══ COLUMNA DERECHA: Puesto y credenciales ══ -->
            <div class="form-col">
                <h2 class="seccion-titulo">Puesto y credenciales</h2>

                <div class="form-grupo">
                    <label for="rol_interno">Rol *</label>
                    <select id="rol_interno" name="rol_interno" required>
                        <option value="">-- Seleccioná --</option>
                        <option value="Empleado Sucursal" <?= $rol === 'Empleado Sucursal' ? 'selected' : '' ?>>
                            Empleado de Sucursal
                        </option>
                        <option value="Chofer" <?= $rol === 'Chofer' ? 'selected' : '' ?>>
                            Chofer
                        </option>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="id_sucursal">Sucursal base *</label>
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

                <div class="form-grupo">
                    <label for="tipo_licencia">Tipo de licencia</label>
                    <input type="text" id="tipo_licencia" name="tipo_licencia"
                           maxlength="10" placeholder="Ej: A, B o C"
                           value="<?= $post('tipo_licencia') ?>">
                    <small class="form-hint" style="color: #c85a00; font-weight: 600;">
                        Obligatorio si el rol es Chofer.
                    </small>
                </div>

                <div class="form-col-sep">
                    <h2 class="seccion-titulo">Acceso al sistema</h2>
                    <p class="form-hint" style="font-size:13px; color: var(--texto-suave); margin-bottom:0;">
                        El usuario y la contraseña inicial se generan automáticamente a partir del DNI ingresado.
                        El titular podrá cambiarlos luego desde su perfil.
                    </p>
                </div>

            </div><!-- /col derecha -->

        </div><!-- /form-grid-2col -->

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Registrar personal</button>
            <a href="/LOGITRACK/controllers/UsuarioController.php" class="btn btn-gris">Cancelar</a>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
