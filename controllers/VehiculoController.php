<?php
// controllers/VehiculoController.php — Gestión de flota (solo Administrador)

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Vehiculo.php';

requiereRol(['Administrador']);

$modelo = new Vehiculo($pdo);
$accion = trim($_GET['action'] ?? 'index');
$error  = '';

// ── GUARDAR NUEVO ────────────────────────────────────────────────
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $patente         = trim($_POST['patente']         ?? '');
    $id_tipo_veh     = trim($_POST['id_tipo_veh']     ?? '');
    $marca           = trim($_POST['marca']           ?? '');
    $modelo_veh      = trim($_POST['modelo']          ?? '');
    $id_sucursal     = trim($_POST['id_sucursal']     ?? '');
    $id_aseguradora  = trim($_POST['id_aseguradora']  ?? '');
    $estado_vehiculo = trim($_POST['estado_vehiculo'] ?? '');

    if ($patente === '' || $id_tipo_veh === '' || $marca === '' || $id_sucursal === '' || $estado_vehiculo === '') {
        $error  = 'Los campos Patente, Tipo, Marca, Sucursal y Estado son obligatorios.';
        $accion = 'crear';
    } else {
        $ok = $modelo->crear([
            'patente'         => $patente,
            'id_tipo_veh'     => $id_tipo_veh,
            'marca'           => $marca,
            'modelo'          => $modelo_veh,
            'id_sucursal'     => $id_sucursal,
            'id_aseguradora'  => $id_aseguradora,
            'estado_vehiculo' => $estado_vehiculo,
        ]);
        if ($ok) {
            $_SESSION['success'] = 'Vehículo ' . strtoupper($patente) . ' registrado correctamente.';
            header("Location: /LOGITRACK/controllers/VehiculoController.php");
            exit;
        }
        $error  = 'No se pudo registrar el vehículo. Verificá que la patente no esté duplicada.';
        $accion = 'crear';
    }
}

// ── ACTUALIZAR ───────────────────────────────────────────────────
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $patente         = trim($_POST['patente']         ?? '');
    $id_tipo_veh     = trim($_POST['id_tipo_veh']     ?? '');
    $marca           = trim($_POST['marca']           ?? '');
    $modelo_veh      = trim($_POST['modelo']          ?? '');
    $id_sucursal     = trim($_POST['id_sucursal']     ?? '');
    $id_aseguradora  = trim($_POST['id_aseguradora']  ?? '');
    $estado_vehiculo = trim($_POST['estado_vehiculo'] ?? '');

    if ($patente === '' || $id_tipo_veh === '' || $marca === '' || $id_sucursal === '' || $estado_vehiculo === '') {
        $error  = 'Los campos Tipo, Marca, Sucursal y Estado son obligatorios.';
        $accion = 'editar';
    } else {
        $ok = $modelo->editar($patente, [
            'id_tipo_veh'     => $id_tipo_veh,
            'marca'           => $marca,
            'modelo'          => $modelo_veh,
            'id_sucursal'     => $id_sucursal,
            'id_aseguradora'  => $id_aseguradora,
            'estado_vehiculo' => $estado_vehiculo,
        ]);
        if ($ok) {
            $_SESSION['success'] = 'Vehículo ' . strtoupper($patente) . ' actualizado correctamente.';
            header("Location: /LOGITRACK/controllers/VehiculoController.php");
            exit;
        }
        $error  = 'No se pudo actualizar el vehículo.';
        $accion = 'editar';
    }
}

// ── DESACTIVAR (baja lógica) ─────────────────────────────────────
if ($accion === 'desactivar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $patente = trim($_POST['patente'] ?? '');
    if ($patente !== '') {
        if ($modelo->tieneViajesActivos($patente)) {
            $_SESSION['error'] = 'No se puede dar de baja ' . strtoupper($patente) . ': tiene viajes activos asignados.';
        } elseif ($modelo->desactivar($patente)) {
            $_SESSION['success'] = 'Vehículo ' . strtoupper($patente) . ' dado de baja correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo dar de baja el vehículo.';
        }
    }
    header("Location: /LOGITRACK/controllers/VehiculoController.php");
    exit;
}

// ── DATOS AUXILIARES (selects) ───────────────────────────────────
$tiposVehiculo = $modelo->getTiposVehiculo();
$sucursales    = $modelo->getSucursales();
$aseguradoras  = $modelo->getAseguradoras();

// ── VISTAS ───────────────────────────────────────────────────────

if ($accion === 'index') {
    $filtros = [
        'id_sucursal'    => (int)($_GET['id_sucursal']    ?? 0),
        'estado_vehiculo'=> trim($_GET['estado_vehiculo'] ?? ''),
        'buscar'         => trim($_GET['buscar']          ?? ''),
    ];
    $vehiculos = $modelo->getAll($filtros);

    // Leer y limpiar mensajes flash de sesión
    $success = $_SESSION['success'] ?? '';
    $error   = $_SESSION['error']   ?? $error;
    unset($_SESSION['success'], $_SESSION['error']);

    require_once __DIR__ . '/../views/vehiculos/index.php';

} elseif ($accion === 'crear') {
    require_once __DIR__ . '/../views/vehiculos/crear.php';

} elseif ($accion === 'editar' || $accion === 'actualizar') {
    $patente = trim($_GET['patente'] ?? $_POST['patente'] ?? '');
    $vehiculo = $patente ? $modelo->getById($patente) : false;
    if (!$vehiculo) {
        header("Location: /LOGITRACK/controllers/VehiculoController.php");
        exit;
    }
    require_once __DIR__ . '/../views/vehiculos/editar.php';

} else {
    header("Location: /LOGITRACK/controllers/VehiculoController.php");
    exit;
}
