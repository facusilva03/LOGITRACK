<?php
// login.php — Punto de entrada al sistema
// Verifica identidad del usuario contra la BD

session_start();

// Si ya hay sesión activa, mandamos directo al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

require 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Buscamos el usuario en la BD por su username
    $stmt = $pdo->prepare("
        SELECT u.id_usuario, u.username, u.password_hash, u.activo,
               u.id_rol, u.id_chofer, u.id_cliente, u.id_empleado,
               r.nombre AS rol
        FROM Usuario u
        JOIN Rol r ON r.id_rol = u.id_rol
        WHERE u.username = ?
    ");
    $stmt->execute([$username]);
    $usuario = $stmt->fetch();

    // Verificamos que exista, esté activo y la contraseña sea correcta
    if ($usuario && $usuario['activo'] == 1 && password_verify($password, $usuario['password_hash'])) {

        // Regeneramos el ID de sesión para prevenir Session Fixation
        session_regenerate_id(true);

        // Buscar nombre real según la entidad vinculada al rol
        $nombreReal = $usuario['username'];
        $idRol = (int)$usuario['id_rol'];

        if ($idRol === 2 && !empty($usuario['id_chofer'])) {
            $sn = $pdo->prepare("SELECT nombre, apellido FROM Chofer WHERE id_chofer = ?");
            $sn->execute([(int)$usuario['id_chofer']]);
            $p = $sn->fetch();
            if ($p) $nombreReal = $p['nombre'] . ' ' . $p['apellido'];
        } elseif ($idRol === 3 && !empty($usuario['id_cliente'])) {
            $sn = $pdo->prepare("SELECT nombre, apellido FROM Cliente WHERE id_cliente = ?");
            $sn->execute([(int)$usuario['id_cliente']]);
            $p = $sn->fetch();
            if ($p) $nombreReal = $p['nombre'] . ' ' . $p['apellido'];
        } elseif ($idRol === 4 && !empty($usuario['id_empleado'])) {
            $sn = $pdo->prepare("SELECT nombre, apellido FROM Empleado WHERE id_empleado = ?");
            $sn->execute([(int)$usuario['id_empleado']]);
            $p = $sn->fetch();
            if ($p) $nombreReal = $p['nombre'] . ' ' . $p['apellido'];
        }

        // Guardamos los datos del usuario en la sesión
        $_SESSION['usuario_id']    = $usuario['id_usuario'];
        $_SESSION['username']      = $usuario['username'];
        $_SESSION['rol']           = $usuario['rol'];
        $_SESSION['nombre_real']   = $nombreReal;
        $_SESSION['ultimo_acceso'] = time();

        header("Location: dashboard.php");
        exit;

    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiTrack — Iniciar sesión</title>
    <link rel="stylesheet" href="/LOGITRACK/assets/css/style.css">
</head>
<body class="public-body">

<?php require_once __DIR__ . '/views/layout/public_navbar.php'; ?>

<div class="login-split">

    <!-- Panel izquierdo: marca + SVG logístico -->
    <div class="login-hero-panel">

        <!-- SVG: camión con ruta de seguimiento — reemplaza al triángulo genérico -->
        <div class="login-hero-svg">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 78" width="180" height="117" aria-hidden="true">
                <!-- Línea de ruta con waypoints -->
                <circle cx="6"   cy="14" r="4.5" fill="#52b788"/>
                <line x1="10.5" y1="14" x2="35" y2="14" stroke="#52b788" stroke-width="2" stroke-dasharray="5,4" opacity="0.6"/>
                <circle cx="39"  cy="14" r="3.5" fill="#52b788" opacity="0.75"/>
                <line x1="42.5" y1="14" x2="67" y2="14" stroke="#52b788" stroke-width="2" stroke-dasharray="5,4" opacity="0.6"/>
                <circle cx="71"  cy="14" r="3.5" fill="#52b788" opacity="0.75"/>
                <line x1="74.5" y1="14" x2="99" y2="14" stroke="#52b788" stroke-width="2" stroke-dasharray="5,4" opacity="0.6"/>
                <!-- Marcador destino (pin) -->
                <circle cx="106" cy="14" r="6"   fill="#52b788"/>
                <circle cx="106" cy="14" r="2.5" fill="white"/>

                <!-- Cuerpo del camión (área de carga) -->
                <rect x="2"  y="28" width="70" height="32" rx="3.5" fill="#52b788"/>
                <!-- Detalles de carga -->
                <line x1="12" y1="39" x2="60" y2="39" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
                <line x1="12" y1="48" x2="48" y2="48" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>

                <!-- Cabina del camión -->
                <rect x="72" y="39" width="40" height="21" rx="3" fill="#2d6a4f"/>
                <!-- Parabrisas -->
                <rect x="77" y="43" width="22" height="13" rx="2" fill="rgba(255,255,255,0.18)"/>
                <!-- Caño de escape -->
                <rect x="110" y="29" width="5" height="12" rx="2.5" fill="#2d6a4f"/>
                <!-- Humo -->
                <circle cx="112" cy="25" r="2.5" fill="rgba(255,255,255,0.2)"/>
                <circle cx="115" cy="20" r="1.8" fill="rgba(255,255,255,0.12)"/>

                <!-- Ruedas -->
                <circle cx="22"  cy="64" r="10" fill="#1a3a2a"/>
                <circle cx="22"  cy="64" r="4"  fill="#52b788"/>
                <circle cx="58"  cy="64" r="10" fill="#1a3a2a"/>
                <circle cx="58"  cy="64" r="4"  fill="#52b788"/>
                <circle cx="92"  cy="64" r="10" fill="#1a3a2a"/>
                <circle cx="92"  cy="64" r="4"  fill="#52b788"/>

                <!-- Línea de suelo -->
                <line x1="0" y1="74" x2="120" y2="74" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
            </svg>
        </div>

        <h1 class="login-hero-titulo">Gestión logística<br>en un solo lugar</h1>
        <p class="login-hero-desc">
            Administrá envíos, viajes y choferes con trazabilidad
            en tiempo real desde cualquier sucursal.
        </p>
        <ul class="login-hero-features">
            <li><span class="feature-dot"></span>Seguimiento de paquetes en tiempo real</li>
            <li><span class="feature-dot"></span>Gestión de rutas y vehículos</li>
            <li><span class="feature-dot"></span>Control de clientes y sucursales</li>
            <li><span class="feature-dot"></span>Historial completo de estados</li>
        </ul>
    </div>

    <!-- Panel derecho: formulario de login -->
    <div class="login-form-panel">
        <div class="login-card-nueva">

            <h2 class="login-card-titulo">Iniciar sesión</h2>
            <p class="login-card-subtitulo">Ingresá tus credenciales para acceder al sistema</p>

            <?php if (!empty($error)): ?>
                <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['exp'])): ?>
                <div class="alerta alerta-aviso">Tu sesión expiró por inactividad. Ingresá nuevamente.</div>
            <?php endif; ?>

            <?php if (($_GET['msg'] ?? '') === 'registro_exitoso'): ?>
                <div class="alerta alerta-exito">¡Cuenta creada! Ya podés iniciar sesión.</div>
            <?php endif; ?>

            <?php if (($_GET['msg'] ?? '') === 'cuenta_eliminada'): ?>
                <div class="alerta alerta-aviso">Tu cuenta fue eliminada correctamente. Tus datos de cliente permanecen en el sistema.</div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form">
                <div class="form-grupo">
                    <label for="username">Usuario</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    >
                </div>
                <div class="form-grupo">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>
                <button type="submit" class="btn btn-verde btn-bloque">Iniciar sesión</button>
            </form>

            <div class="login-card-pie">
                ¿Sos nuevo cliente?
                <a href="/LOGITRACK/controllers/RegistroClienteController.php">Creá tu cuenta</a>
                &nbsp;&middot;&nbsp;
                <a href="/LOGITRACK/controllers/TrackingController.php">Rastrear envío</a>
            </div>
            <div class="login-card-pie" style="margin-top:8px;">
                ¿Olvidaste tu contraseña? Comunicate al<br>
                <a href="tel:+541112131415" style="white-space:nowrap;">+54 1112131415</a>
            </div>

            <p class="login-pie" style="margin-top:16px;">Universidad Champagnat &mdash; Base de Datos II</p>
        </div>
    </div>

</div>

</body>
</html>
