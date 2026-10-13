<?php
// controllers/CuentaController.php — Gestión de cuenta propia del cliente

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

// Solo clientes pueden acceder aquí
if (!esCliente()) {
    header("Location: /LOGITRACK/dashboard.php");
    exit;
}

$accion = trim($_GET['action'] ?? 'confirmar');

// ── PASO 1: mostrar página de advertencia ────────────────────────
if ($accion === 'confirmar') {
    require_once __DIR__ . '/../views/cuenta/confirmar_eliminacion.php';
    exit;
}

// ── PASO 2: ejecutar la eliminación ─────────────────────────────
if ($accion === 'ejecutar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = (int)$_SESSION['usuario_id'];

    if ($idUsuario > 0) {
        try {
            // Elimina solo el Usuario; el registro de Cliente permanece
            $stmt = $pdo->prepare("DELETE FROM Usuario WHERE id_usuario = ? AND id_rol = (SELECT id_rol FROM Rol WHERE nombre_rol = 'Cliente' LIMIT 1)");
            $stmt->execute([$idUsuario]);
        } catch (PDOException $e) {
            error_log("CuentaController eliminar: " . $e->getMessage());
        }
    }

    // Destruir sesión y redirigir al login
    session_unset();
    session_destroy();
    header("Location: /LOGITRACK/login.php?msg=cuenta_eliminada");
    exit;
}

// Cualquier otra acción: volver al dashboard
header("Location: /LOGITRACK/dashboard.php");
exit;
