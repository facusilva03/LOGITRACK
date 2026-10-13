<?php
// controllers/IncidenteController.php — Registro y listado de incidentes en viajes

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Incidente.php';
require_once __DIR__ . '/../models/Usuario.php';

// Clientes no tienen acceso
if (esCliente()) {
    header("Location: /LOGITRACK/dashboard.php");
    exit;
}

$modelo = new Incidente($pdo);
$accion = trim($_GET['action'] ?? 'index');

// Resuelve el id_chofer del usuario logueado (0 si no es chofer)
$idChofer = 0;
if (esChofer()) {
    $usuarioModel = new Usuario($pdo);
    $infoRol  = $usuarioModel->getInfoRol((int)$_SESSION['usuario_id']);
    $idChofer = (int)($infoRol['id_chofer'] ?? 0);
}

// ── INDEX ─────────────────────────────────────────────────────────
if ($accion === 'index') {
    requiereRol(['Administrador', 'Empleado Sucursal', 'Chofer']);

    $tipoFiltro = trim($_GET['tipo'] ?? '');
    $filtros    = $tipoFiltro !== '' ? ['tipo' => $tipoFiltro] : [];
    if (esChofer()) {
        $filtros['id_chofer'] = $idChofer;
    }

    $incidentes = $modelo->getAll($filtros);
    $success    = $_SESSION['success'] ?? '';
    unset($_SESSION['success']);

    require_once __DIR__ . '/../views/incidentes/index.php';
    exit;
}

// ── CREAR — formulario ────────────────────────────────────────────
if ($accion === 'crear') {
    requiereRol(['Administrador', 'Empleado Sucursal', 'Chofer']);

    $viajesEnCurso = $modelo->getViajesEnCurso($idChofer);
    $error         = '';

    require_once __DIR__ . '/../views/incidentes/crear.php';
    exit;
}

// ── GUARDAR — POST desde crear.php ────────────────────────────────
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal', 'Chofer']);

    $id_viaje       = (int)trim($_POST['id_viaje']       ?? 0);
    $tipo_incidente = trim($_POST['tipo_incidente'] ?? '');
    $descripcion    = trim($_POST['descripcion']    ?? '');

    if ($id_viaje <= 0 || $tipo_incidente === '' || $descripcion === '') {
        $error         = 'Todos los campos son obligatorios.';
        $viajesEnCurso = $modelo->getViajesEnCurso($idChofer);
        require_once __DIR__ . '/../views/incidentes/crear.php';
        exit;
    }

    try {
        $modelo->registrar($id_viaje, $tipo_incidente, $descripcion);
        $_SESSION['success'] = 'Incidente registrado correctamente en el Viaje #' . $id_viaje . '.';
        header("Location: /LOGITRACK/controllers/IncidenteController.php");
        exit;
    } catch (RuntimeException $e) {
        error_log("IncidenteController guardar: " . $e->getMessage());
        $error         = 'No se pudo registrar el incidente. Intentá nuevamente.';
        $viajesEnCurso = $modelo->getViajesEnCurso($idChofer);
        require_once __DIR__ . '/../views/incidentes/crear.php';
        exit;
    }
}

// ── REGISTRAR — POST desde vista detalle de viaje (flujo existente) ──
if ($accion === 'registrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_viaje       = (int)trim($_POST['id_viaje']       ?? 0);
    $tipo_incidente = trim($_POST['tipo_incidente'] ?? '');
    $descripcion    = trim($_POST['descripcion']    ?? '');

    if ($id_viaje <= 0 || $tipo_incidente === '' || $descripcion === '') {
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&error=incidente_campos");
        exit;
    }

    try {
        $modelo->registrar($id_viaje, $tipo_incidente, $descripcion);
        // El trigger trg_incidente_demora propaga "Demorado" a los envíos del viaje
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&exito=incidente");
        exit;
    } catch (RuntimeException $e) {
        error_log("IncidenteController registrar: " . $e->getMessage());
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&error=incidente_fallo");
        exit;
    }
}

header("Location: /LOGITRACK/controllers/IncidenteController.php");
exit;
