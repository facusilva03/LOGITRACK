<?php
// auth.php — Verificación de sesión y permisos
// Se incluye al inicio de cada página protegida

session_start();

$TIMEOUT = 30 * 60; // 30 minutos de inactividad

// Verificar si hay sesión activa
if (isset($_SESSION['usuario_id'])) {
    
    // Verificar timeout de inactividad
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    
    if ($inactivo > $TIMEOUT) {
        // Sesión expirada — destruimos todo correctamente
        session_unset();
        session_destroy();
        header("Location: /LOGITRACK/login.php?exp=1");
        exit;
    }

    // Actualizamos el timestamp en cada request
    $_SESSION['ultimo_acceso'] = time();

} else {
    // No hay sesión — mandamos al login
    header("Location: /LOGITRACK/login.php");
    exit;
}

// ── Funciones de control de roles ─────────────────────────────

function esAdmin(): bool {
    return $_SESSION['rol'] === 'Administrador';
}

function esEmpleado(): bool {
    return $_SESSION['rol'] === 'Empleado Sucursal';
}

function esChofer(): bool {
    return $_SESSION['rol'] === 'Chofer';
}

function esCliente(): bool {
    return $_SESSION['rol'] === 'Cliente';
}

// Corta la ejecución si el rol no tiene permiso
function requiereRol(array $rolesPermitidos): void {
    if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
        header("Location: /LOGITRACK/dashboard.php?error=sin_permiso");
        exit;
    }
}