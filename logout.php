<?php
// logout.php — Cierra sesión en 3 pasos según el patrón de seguridad del proyecto

session_start();

// Paso 1: vaciar el array de sesión
$_SESSION = [];

// Paso 2: borrar la cookie de sesión del navegador
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'],
        $p['secure'], $p['httponly']);
}

// Paso 3: destruir la sesión en el servidor
session_destroy();

// Evitar que el navegador almacene la página en caché
header('Cache-Control: no-store, no-cache, must-revalidate');
header("Location: /LOGITRACK/login.php");
exit;
