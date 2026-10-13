<?php
// controllers/TrackingController.php — Seguimiento de paquetes (público y autenticado)

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Envio.php';

// Detectar sesión activa sin forzar redirect (la página es pública)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$esSesionActiva = false;
if (isset($_SESSION['usuario_id'])) {
    $inactivo = time() - ($_SESSION['ultimo_acceso'] ?? 0);
    if ($inactivo <= 30 * 60) {
        $esSesionActiva = true;
        $_SESSION['ultimo_acceso'] = time();
    } else {
        session_unset();
        session_destroy();
    }
}

// Helpers de rol disponibles para el layout interno (sin incluir auth.php que fuerza redirect)
if (!function_exists('esAdmin')) {
    function esAdmin(): bool    { return ($_SESSION['rol'] ?? '') === 'Administrador'; }
    function esEmpleado(): bool { return ($_SESSION['rol'] ?? '') === 'Empleado Sucursal'; }
    function esChofer(): bool   { return ($_SESSION['rol'] ?? '') === 'Chofer'; }
    function esCliente(): bool  { return ($_SESSION['rol'] ?? '') === 'Cliente'; }
}

$modelo = new Envio($pdo);
$accion = trim($_GET['action'] ?? 'buscar');

$codigo = '';
$linea  = [];   // filas del historial ordenadas cronológicamente
$error  = '';

if ($accion === 'buscar') {
    $codigo = strtoupper(trim($_GET['codigo'] ?? ''));

    if ($codigo !== '') {
        $linea = $modelo->getTrackingPublico($codigo);

        if (empty($linea)) {
            $error = 'No se encontró ningún envío con ese código de seguimiento.';
        }
    }
}

require_once __DIR__ . '/../views/tracking/index.php';
