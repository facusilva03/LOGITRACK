<?php
// controllers/ViajeController.php — CRUD de viajes (Admin + Empleado + Chofer solo lectura)

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Viaje.php';
require_once __DIR__ . '/../models/Vehiculo.php';
require_once __DIR__ . '/../models/Envio.php';

// Cliente no tiene acceso a viajes
if (esCliente()) {
    header("Location: /LOGITRACK/dashboard.php");
    exit;
}

$modelo = new Viaje($pdo);
$accion = trim($_GET['action'] ?? 'index');
$error  = '';
$exito  = '';

// Chofer solo puede ver su propio listado
if (esChofer() && !in_array($accion, ['index', 'detalle'])) {
    header("Location: /LOGITRACK/controllers/ViajeController.php");
    exit;
}

// ── PASO 1: GUARDAR NUEVO VIAJE ──────────────────────────────────
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);

    $patente        = trim($_POST['patente']        ?? '');
    $id_chofer      = trim($_POST['id_chofer']      ?? '');
    $fecha_salida   = trim($_POST['fecha_salida']   ?? '');
    $id_suc_origen  = trim($_POST['id_suc_origen']  ?? '');
    $id_suc_destino = trim($_POST['id_suc_destino'] ?? '');

    if ($patente === '' || $id_chofer === '' || $fecha_salida === ''
        || $id_suc_origen === '' || $id_suc_destino === '') {
        $error  = 'Todos los campos son obligatorios.';
        $accion = 'crear';
    } elseif (strtotime($fecha_salida) < strtotime('now')) {
        $error  = 'La fecha y hora de salida no puede ser en el pasado.';
        $accion = 'crear';
    } elseif ($id_suc_origen === $id_suc_destino) {
        $error  = 'La sucursal de origen y destino no pueden ser iguales.';
        $accion = 'crear';
    } else {
        $idViaje = $modelo->crearConRuta([
            'patente'        => $patente,
            'id_chofer'      => $id_chofer,
            'fecha_salida'   => $fecha_salida,
            'id_suc_origen'  => $id_suc_origen,
            'id_suc_destino' => $id_suc_destino,
        ]);
        if ($idViaje) {
            header("Location: /LOGITRACK/controllers/ViajeController.php?action=gestionarCarga&id_viaje={$idViaje}");
            exit;
        }
        $error  = 'No se pudo registrar el viaje. Verificá que el vehículo y el chofer estén disponibles.';
        $accion = 'crear';
    }
}

// ── ACTUALIZAR ───────────────────────────────────────────────────
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $id             = (int)($_POST['id_viaje'] ?? 0);
    $patente        = trim($_POST['patente']        ?? '');
    $id_chofer      = trim($_POST['id_chofer']      ?? '');
    $fecha_salida   = trim($_POST['fecha_salida']   ?? '');
    $estado         = trim($_POST['estado_viaje']   ?? '');
    $id_suc_origen  = trim($_POST['id_suc_origen']  ?? '');
    $id_suc_destino = trim($_POST['id_suc_destino'] ?? '');

    if ($id <= 0 || $patente === '' || $id_chofer === '' || $fecha_salida === '' || $estado === ''
        || $id_suc_origen === '' || $id_suc_destino === '') {
        $error  = 'Todos los campos son obligatorios.';
        $accion = 'editar';
    } elseif ($id_suc_origen === $id_suc_destino) {
        $error  = 'La sucursal de origen y destino no pueden ser iguales.';
        $accion = 'editar';
    } else {
        $ok = $modelo->editar($id, [
            'patente'        => $patente,
            'id_chofer'      => $id_chofer,
            'fecha_salida'   => $fecha_salida,
            'estado_viaje'   => $estado,
            'id_suc_origen'  => $id_suc_origen,
            'id_suc_destino' => $id_suc_destino,
        ]);
        if ($ok) {
            header("Location: /LOGITRACK/controllers/ViajeController.php?exito=editado");
            exit;
        }
        $error  = 'No se pudo actualizar el viaje.';
        $accion = 'editar';
    }
}

// ── CANCELAR (baja lógica) ───────────────────────────────────────
if ($accion === 'cancelar') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $modelo->desactivar($id);
    }
    header("Location: /LOGITRACK/controllers/ViajeController.php?exito=cancelado");
    exit;
}

// ── ASIGNAR ENVÍOS DESDE DETALLE (acción existente) ─────────────
if ($accion === 'asignar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);

    $id_viaje   = (int)trim($_POST['id_viaje'] ?? 0);
    $envios_ids = $_POST['envios_ids'] ?? [];

    if ($id_viaje <= 0 || empty($envios_ids) || !is_array($envios_ids)) {
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&error=asignar_vacio");
        exit;
    }

    $envios_ids = array_filter(array_map('intval', $envios_ids), fn($id) => $id > 0);

    if (empty($envios_ids)) {
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&error=asignar_vacio");
        exit;
    }

    $modeloVehiculo = new Vehiculo($pdo);
    $modeloEnvio    = new Envio($pdo);

    $capacidad = $modeloVehiculo->getCapacidadPorViaje($id_viaje);
    $pesoTotal = $modeloEnvio->getPesoTotal($envios_ids);

    if ($pesoTotal > $capacidad) {
        $max   = number_format($capacidad, 2);
        $total = number_format($pesoTotal, 2);
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&error=capacidad&max={$max}&total={$total}");
        exit;
    }

    try {
        $modeloEnvio->asignarAViaje($id_viaje, $envios_ids);
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&exito=asignado");
        exit;
    } catch (RuntimeException $e) {
        error_log("ViajeController asignar: " . $e->getMessage());
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&error=asignar_fallo");
        exit;
    }
}

// ── PASO 2: ASIGNAR CARGA DESDE WIZARD ──────────────────────────
if ($accion === 'asignarCarga' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);

    $id_viaje = (int)trim($_POST['id_viaje'] ?? 0);
    $paquetes = $_POST['paquetes'] ?? [];

    if ($id_viaje <= 0) {
        header("Location: /LOGITRACK/controllers/ViajeController.php");
        exit;
    }

    // Sin selección: viaje creado sin carga inicial, ir al detalle
    if (empty($paquetes)) {
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&exito=asignado");
        exit;
    }

    $paquetes = array_filter(array_map('intval', $paquetes), fn($id) => $id > 0);

    $viaje = $modelo->getById($id_viaje);
    if (!$viaje) {
        header("Location: /LOGITRACK/controllers/ViajeController.php");
        exit;
    }

    $modeloEnvio = new Envio($pdo);
    $pesoTotal   = $modeloEnvio->getPesoTotal($paquetes);
    $capacidad   = (float)($viaje['capacidad_kg_max'] ?? 0);

    if ($pesoTotal > $capacidad) {
        $max   = number_format($capacidad, 2);
        $total = number_format($pesoTotal, 2);
        $_SESSION['error_carga'] = "El peso seleccionado ({$total} kg) supera la capacidad del vehículo ({$max} kg). Reducí la selección.";
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=gestionarCarga&id_viaje={$id_viaje}");
        exit;
    }

    try {
        $idSucOrigen = isset($viaje['id_suc_origen']) ? (int)$viaje['id_suc_origen'] : null;
        $modeloEnvio->asignarAViaje($id_viaje, $paquetes, $idSucOrigen);
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$id_viaje}&exito=asignado");
        exit;
    } catch (RuntimeException $e) {
        error_log("ViajeController asignarCarga: " . $e->getMessage());
        $_SESSION['error_carga'] = 'No se pudo completar la asignación. Intentá de nuevo.';
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=gestionarCarga&id_viaje={$id_viaje}");
        exit;
    }
}

// ── CONTROL DE DESCARGA: MARCAR ENVÍO COMO CORRECTO ─────────────
if ($accion === 'marcarCorrecto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);

    $idViaje    = (int)($_POST['id_viaje'] ?? 0);
    $idEnvio    = (int)($_POST['id_envio'] ?? 0);
    $idSucursal = (int)($_POST['id_sucursal_actual'] ?? 0);

    if ($idEnvio > 0 && $idSucursal > 0) {
        try {
            $pdo->prepare("
                INSERT INTO Historial_Estado (id_envio, id_estado, fecha_hora, id_sucursal_actual)
                VALUES (?, 9, NOW(), ?)
            ")->execute([$idEnvio, $idSucursal]);
        } catch (PDOException $e) {
            error_log("ViajeController marcarCorrecto: " . $e->getMessage());
        }
    }
    header("Location: /LOGITRACK/controllers/ViajeController.php?action=verificarDescarga&id_viaje={$idViaje}");
    exit;
}

// ── CONTROL DE DESCARGA: MARCAR ENVÍO COMO ROTO/DEVUELTO ────────
if ($accion === 'marcarRoto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);

    $idViaje       = (int)($_POST['id_viaje'] ?? 0);
    $idEnvio       = (int)($_POST['id_envio'] ?? 0);
    $idSucursal    = (int)($_POST['id_sucursal_actual'] ?? 0);
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($idEnvio > 0 && $idSucursal > 0 && $observaciones !== '') {
        try {
            $pdo->prepare("
                INSERT INTO Historial_Estado (id_envio, id_estado, fecha_hora, id_sucursal_actual, observaciones)
                VALUES (?, 6, NOW(), ?, ?)
            ")->execute([$idEnvio, $idSucursal, $observaciones]);
        } catch (PDOException $e) {
            error_log("ViajeController marcarRoto: " . $e->getMessage());
        }
    }
    header("Location: /LOGITRACK/controllers/ViajeController.php?action=verificarDescarga&id_viaje={$idViaje}");
    exit;
}

// ── FINALIZAR VIAJE — llama al SP ───────────────────────────────
if ($accion === 'finalizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);

    $idViaje = (int)($_POST['id_viaje'] ?? 0);

    if ($idViaje <= 0) {
        header("Location: /LOGITRACK/controllers/ViajeController.php");
        exit;
    }

    if ($modelo->finalizarViaje($idViaje)) {
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$idViaje}&exito=finalizado");
    } else {
        header("Location: /LOGITRACK/controllers/ViajeController.php?action=detalle&id={$idViaje}&error=finalizar_fallo");
    }
    exit;
}

// ── VISTAS ───────────────────────────────────────────────────────

if ($accion === 'index') {
    if (esChofer()) {
        try {
            $stmt = $pdo->prepare("SELECT id_chofer FROM Usuario WHERE id_usuario = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $row      = $stmt->fetch();
            $idChofer = $row ? (int)$row['id_chofer'] : 0;
        } catch (PDOException $e) {
            error_log("ViajeController chofer lookup: " . $e->getMessage());
            $idChofer = 0;
        }
        $filtros = [
            'estado_viaje' => trim($_GET['estado_viaje'] ?? ''),
            'fecha_desde'  => trim($_GET['fecha_desde']  ?? ''),
            'fecha_hasta'  => trim($_GET['fecha_hasta']  ?? ''),
            'patente'      => trim($_GET['patente']      ?? ''),
        ];
        $viajes = $idChofer ? $modelo->getByChofer($idChofer, $filtros) : [];
    } else {
        $filtros = [
            'estado_viaje' => trim($_GET['estado_viaje'] ?? ''),
            'id_chofer'    => (int)($_GET['id_chofer']   ?? 0),
            'fecha_desde'  => trim($_GET['fecha_desde']  ?? ''),
            'fecha_hasta'  => trim($_GET['fecha_hasta']  ?? ''),
            'patente'      => trim($_GET['patente']      ?? ''),
        ];
        $viajes = $modelo->getAll($filtros);
    }
    $choferes = $modelo->getChoferes();
    $exito = match($_GET['exito'] ?? '') {
        'creado'    => 'Viaje registrado correctamente.',
        'editado'   => 'Viaje actualizado correctamente.',
        'cancelado' => 'Viaje cancelado correctamente.',
        default     => ''
    };
    require_once __DIR__ . '/../views/viajes/index.php';

} elseif ($accion === 'crear') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $idSucOrigenFiltro = (int)($_GET['filtro_suc'] ?? 0);
    $choferes   = $modelo->getChoferesDisponibles($idSucOrigenFiltro);
    $vehiculos  = $modelo->getVehiculosDisponibles($idSucOrigenFiltro);
    $sucursales = $modelo->getSucursales();
    require_once __DIR__ . '/../views/viajes/crear.php';

} elseif ($accion === 'editar' || $accion === 'actualizar') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $id        = (int)($_GET['id'] ?? $_POST['id_viaje'] ?? 0);
    $viaje     = $modelo->getById($id);
    $choferes  = $modelo->getChoferes();
    $vehiculos = $modelo->getVehiculos();
    $sucursales = $modelo->getSucursales();
    if (!$viaje) {
        header("Location: /LOGITRACK/controllers/ViajeController.php");
        exit;
    }
    require_once __DIR__ . '/../views/viajes/editar.php';

} elseif ($accion === 'detalle') {
    $id     = (int)($_GET['id'] ?? 0);
    $viaje  = $modelo->getById($id);
    $envios = $viaje ? $modelo->getEnviosAsignados($id) : [];
    if (!$viaje) {
        header("Location: /LOGITRACK/controllers/ViajeController.php");
        exit;
    }

    $exito = match($_GET['exito'] ?? '') {
        'finalizado' => 'Viaje finalizado correctamente. Los envíos fueron actualizados.',
        'incidente'  => 'Incidente registrado. Los paquetes del viaje han sido marcados como Demorados automáticamente.',
        'asignado'   => 'Envíos asignados correctamente. El estado de cada paquete fue actualizado a "En camino".',
        default      => ''
    };
    $error = match($_GET['error'] ?? '') {
        'finalizar_fallo'  => 'No se pudo finalizar el viaje. Verificá que el SP esté disponible e intentá de nuevo.',
        'incidente_campos' => 'Todos los campos del incidente son obligatorios.',
        'incidente_fallo'  => 'No se pudo registrar el incidente. Intentá de nuevo.',
        'asignar_vacio'    => 'Debés seleccionar al menos un envío para asignar.',
        'asignar_fallo'    => 'No se pudo completar la asignación. Intentá de nuevo.',
        'capacidad'        => sprintf(
            'El peso total excede la capacidad del vehículo. Máximo: %s kg. Seleccionado: %s kg.',
            htmlspecialchars($_GET['max'] ?? '0'),
            htmlspecialchars($_GET['total'] ?? '0')
        ),
        default => ''
    };
    require_once __DIR__ . '/../views/viajes/detalle.php';

} elseif ($accion === 'verificarDescarga') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $id_viaje = (int)($_GET['id_viaje'] ?? 0);
    $viaje    = $modelo->getById($id_viaje);
    if (!$viaje || $viaje['estado_viaje'] !== 'Finalizado') {
        header("Location: /LOGITRACK/controllers/ViajeController.php");
        exit;
    }
    $envios = $modelo->getEnviosParaVerificar($id_viaje);
    require_once __DIR__ . '/../views/viajes/verificar_descarga.php';

} elseif ($accion === 'gestionarCarga') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $id_viaje = (int)($_GET['id_viaje'] ?? 0);
    $viaje    = $modelo->getById($id_viaje);
    if (!$viaje) {
        header("Location: /LOGITRACK/controllers/ViajeController.php");
        exit;
    }
    $modeloEnvio = new Envio($pdo);
    $paquetes    = $modeloEnvio->getDisponiblesParaViaje(
        (int)($viaje['id_suc_origen']  ?? 0),
        (int)($viaje['id_suc_destino'] ?? 0)
    );
    // Leer y limpiar el mensaje de error de la sesión (flash)
    $errorCarga = $_SESSION['error_carga'] ?? '';
    unset($_SESSION['error_carga']);
    require_once __DIR__ . '/../views/viajes/gestionar_carga.php';

} elseif ($accion === 'enCamino') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $viajesActivos = $modelo->getViajesActivos();
    require_once __DIR__ . '/../views/viajes/en_camino.php';

} else {
    header("Location: /LOGITRACK/controllers/ViajeController.php");
    exit;
}
