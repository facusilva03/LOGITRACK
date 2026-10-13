<?php
// controllers/AseguradoraController.php — CRUD de aseguradoras (solo Administrador)

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Aseguradora.php';

requiereRol(['Administrador']);

$modelo = new Aseguradora($pdo);
$accion = trim($_GET['action'] ?? 'index');
$error  = '';

// ── GUARDAR NUEVA ────────────────────────────────────────────────
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim($_POST['nombre'] ?? '');
    if ($nombre === '') {
        $error  = 'El nombre de la aseguradora es obligatorio.';
        $accion = 'crear';
    } else {
        $ok = $modelo->crear([
            'nombre'              => $nombre,
            'telefono_emergencia' => $_POST['telefono_emergencia'] ?? '',
            'web'                 => $_POST['web'] ?? '',
        ]);
        if ($ok) {
            $_SESSION['success'] = 'Aseguradora "' . htmlspecialchars($nombre) . '" registrada correctamente.';
            header("Location: /LOGITRACK/controllers/AseguradoraController.php");
            exit;
        }
        $error  = 'No se pudo registrar la aseguradora.';
        $accion = 'crear';
    }
}

// ── ACTUALIZAR ───────────────────────────────────────────────────
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id_aseguradora'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');

    if ($id <= 0 || $nombre === '') {
        $error  = 'El nombre de la aseguradora es obligatorio.';
        $accion = 'editar';
    } else {
        $ok = $modelo->editar($id, [
            'nombre'              => $nombre,
            'telefono_emergencia' => $_POST['telefono_emergencia'] ?? '',
            'web'                 => $_POST['web'] ?? '',
        ]);
        if ($ok) {
            $_SESSION['success'] = 'Aseguradora actualizada correctamente.';
            header("Location: /LOGITRACK/controllers/AseguradoraController.php");
            exit;
        }
        $error  = 'No se pudo actualizar la aseguradora.';
        $accion = 'editar';
    }
}

// ── ELIMINAR ─────────────────────────────────────────────────────
if ($accion === 'eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_aseguradora'] ?? 0);
    if ($id > 0) {
        if ($modelo->eliminar($id)) {
            $_SESSION['success'] = 'Aseguradora eliminada. Los vehículos asociados quedaron sin aseguradora.';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar la aseguradora.';
        }
    }
    header("Location: /LOGITRACK/controllers/AseguradoraController.php");
    exit;
}

// ── VISTAS ───────────────────────────────────────────────────────

if ($accion === 'index') {
    $buscar       = trim($_GET['buscar'] ?? '');
    $aseguradoras = $modelo->getAll($buscar);
    $success      = $_SESSION['success'] ?? '';
    $error        = $_SESSION['error']   ?? $error;
    unset($_SESSION['success'], $_SESSION['error']);
    require_once __DIR__ . '/../views/aseguradoras/index.php';

} elseif ($accion === 'crear') {
    require_once __DIR__ . '/../views/aseguradoras/crear.php';

} elseif ($accion === 'editar' || $accion === 'actualizar') {
    $id          = (int)($_GET['id'] ?? $_POST['id_aseguradora'] ?? 0);
    $aseguradora = $id ? $modelo->getById($id) : false;
    if (!$aseguradora) {
        header("Location: /LOGITRACK/controllers/AseguradoraController.php");
        exit;
    }
    $cantVehiculos = $modelo->cantidadVehiculos($id);
    require_once __DIR__ . '/../views/aseguradoras/editar.php';

} else {
    header("Location: /LOGITRACK/controllers/AseguradoraController.php");
    exit;
}
