<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — LogiTrack</title>
    <link rel="stylesheet" href="/LOGITRACK/assets/css/style.css">
</head>
<body class="public-body">

<?php require_once __DIR__ . '/../layout/public_navbar.php'; ?>

<main class="public-page">

    <h1 class="public-page-titulo">Crea tu cuenta</h1>
    <p class="public-page-subtitulo">
        Completá el formulario para registrarte y hacer seguimiento de tus envíos.
    </p>

    <div class="registro-card">

        <h2 class="registro-titulo">Datos de registro</h2>
        <p class="registro-subtitulo">
            Todos los campos marcados son obligatorios. La dirección es opcional.
        </p>

        <?php if (!empty($error)): ?>
            <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/LOGITRACK/controllers/RegistroClienteController.php">

            <!-- SECCIÓN 1: Datos personales -->
            <p class="registro-seccion-label">Datos personales</p>

            <div class="form-fila">
                <div class="form-grupo">
                    <label for="tipo_documento">Tipo de documento *</label>
                    <select id="tipo_documento" name="tipo_documento" required>
                        <option value="">— Seleccioná —</option>
                        <?php
                        $tiposDoc = ['DNI', 'CUIL'];
                        foreach ($tiposDoc as $tipo):
                            $sel = (($_POST['tipo_documento'] ?? '') === $tipo) ? 'selected' : '';
                        ?>
                        <option value="<?= $tipo ?>" <?= $sel ?>><?= $tipo ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grupo">
                    <label for="documento">Número de documento *</label>
                    <input
                        type="text"
                        id="documento"
                        name="documento"
                        required
                        inputmode="numeric"
                        maxlength="11"
                        value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>"
                        placeholder="Ej: 38123456"
                    >
                </div>
            </div>

            <div class="form-fila">
                <div class="form-grupo">
                    <label for="nombre">Nombre *</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        required
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                        placeholder="Ej: Juan"
                    >
                </div>
                <div class="form-grupo">
                    <label for="apellido">Apellido *</label>
                    <input
                        type="text"
                        id="apellido"
                        name="apellido"
                        required
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>"
                        placeholder="Ej: García"
                    >
                </div>
            </div>

            <div class="form-fila">
                <div class="form-grupo">
                    <label for="id_localidad">Localidad *</label>
                    <select id="id_localidad" name="id_localidad" required>
                        <option value="">— Seleccioná —</option>
                        <?php foreach ($localidades as $loc):
                            $sel = (($_POST['id_localidad'] ?? '') == $loc['id_localidad']) ? 'selected' : '';
                        ?>
                        <option value="<?= (int)$loc['id_localidad'] ?>" <?= $sel ?>>
                            <?= htmlspecialchars($loc['nombre']) ?> (<?= htmlspecialchars($loc['provincia']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grupo">
                    <label for="direccion">Dirección</label>
                    <input
                        type="text"
                        id="direccion"
                        name="direccion"
                        maxlength="150"
                        value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>"
                        placeholder="Calle, número, piso (opcional)"
                    >
                </div>
            </div>

            <!-- SECCIÓN 2: Datos de acceso -->
            <p class="registro-seccion-label">Datos de acceso</p>

            <div class="form-grupo">
                <label for="username">Nombre de usuario *</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    maxlength="100"
                    autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    placeholder="Ej: jgarcia92"
                >
            </div>

            <div class="form-fila">
                <div class="form-grupo">
                    <label for="password">Contraseña * <span class="text-suave">(mín. 8 caracteres)</span></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>
                <div class="form-grupo">
                    <label for="password_conf">Confirmar contraseña *</label>
                    <input
                        type="password"
                        id="password_conf"
                        name="password_conf"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn btn-verde">Crear cuenta</button>
                <a href="/LOGITRACK/login.php" class="btn btn-gris">Cancelar</a>
            </div>

        </form>

        <div class="registro-pie">
            ¿Ya tenés cuenta? <a href="/LOGITRACK/login.php">Iniciá sesión</a>
        </div>

    </div>

</main>

</body>
</html>
