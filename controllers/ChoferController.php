<?php
// controllers/ChoferController.php — CRUD de choferes (solo Administrador)

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Chofer.php';

requiereRol(['Administrador']);

$modelo = new Chofer($pdo);
$accion = trim($_GET['action'] ?? 'index');
$error  = '';
$exito  = '';

// ---- GUARDAR NUEVO ----
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $legajo       = trim($_POST['legajo'] ?? '');
    $dni          = trim($_POST['dni'] ?? '');
    $nombre       = trim($_POST['nombre'] ?? '');
    $apellido     = trim($_POST['apellido'] ?? '');
    $tipo_lic     = trim($_POST['tipo_licencia'] ?? '');
    $id_sucursal  = trim($_POST['id_sucursal'] ?? '');

    if ($legajo === '' || $dni === '' || $nombre === '' || $apellido === '' || $tipo_lic === '' || $id_sucursal === '') {
        $error = 'Todos los campos son obligatorios.';
        $accion = 'crear';
    } else {
        $ok = $modelo->crear([
            'legajo'        => $legajo,
            'dni'           => $dni,
            'nombre'        => $nombre,
            'apellido'      => $apellido,
            'tipo_licencia' => $tipo_lic,
            'id_sucursal'   => $id_sucursal,
        ]);
        if ($ok) {
            header("Location: /LOGITRACK/controllers/ChoferController.php?exito=creado");
            exit;
        }
        $error = 'No se pudo registrar el chofer. Verificá que el legajo y DNI no estén duplicados.';
        $accion = 'crear';
    }
}

// ---- ACTUALIZAR ----
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id_chofer'] ?? 0);
    $legajo      = trim($_POST['legajo'] ?? '');
    $dni         = trim($_POST['dni'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido    = trim($_POST['apellido'] ?? '');
    $tipo_lic    = trim($_POST['tipo_licencia'] ?? '');
    $id_sucursal = trim($_POST['id_sucursal'] ?? '');

    if ($id <= 0 || $legajo === '' || $dni === '' || $nombre === '' || $apellido === '' || $tipo_lic === '' || $id_sucursal === '') {
        $error = 'Todos los campos son obligatorios.';
        $accion = 'editar';
    } else {
        $ok = $modelo->editar($id, [
            'legajo'        => $legajo,
            'dni'           => $dni,
            'nombre'        => $nombre,
            'apellido'      => $apellido,
            'tipo_licencia' => $tipo_lic,
            'id_sucursal'   => $id_sucursal,
        ]);
        if ($ok) {
            header("Location: /LOGITRACK/controllers/ChoferController.php?exito=editado");
            exit;
        }
        $error = 'No se pudo actualizar el chofer. Verificá que el legajo y DNI no estén duplicados.';
        $accion = 'editar';
    }
}

// ---- ELIMINAR (hard delete — choferes, cascada unidireccional) ----
if ($accion === 'eliminar') {
    $idChofer = (int)($_GET['id'] ?? 0);
    if ($idChofer > 0) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM Usuario WHERE id_chofer = ?")->execute([$idChofer]);
            $pdo->prepare("DELETE FROM Chofer  WHERE id_chofer = ?")->execute([$idChofer]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("ChoferController eliminar: " . $e->getMessage());
        }
    }
    header("Location: /LOGITRACK/controllers/ChoferController.php?exito=eliminado");
    exit;
}

// ---- DATOS PARA VISTAS ----
$sucursales = $modelo->getSucursales();

if ($accion === 'index') {
    $filtros = [
        'id_sucursal'   => (int)($_GET['id_sucursal'] ?? 0),
        'tipo_licencia' => trim($_GET['tipo_licencia'] ?? ''),
        'buscar'        => trim($_GET['buscar'] ?? ''),
    ];
    $choferes = $modelo->getAll($filtros);
    $exito = match($_GET['exito'] ?? '') {
        'creado'    => 'Chofer registrado correctamente.',
        'editado'   => 'Chofer actualizado correctamente.',
        'eliminado' => 'Chofer eliminado del sistema.',
        default     => ''
    };
    require_once __DIR__ . '/../views/choferes/index.php';

} elseif ($accion === 'crear') {
    require_once __DIR__ . '/../views/choferes/crear.php';

} elseif ($accion === 'editar' || $accion === 'actualizar') {
    $id     = (int)($_GET['id'] ?? $_POST['id_chofer'] ?? 0);
    $chofer = $modelo->getById($id);
    if (!$chofer) {
        header("Location: /LOGITRACK/controllers/ChoferController.php");
        exit;
    }
    require_once __DIR__ . '/../views/choferes/editar.php';

} else {
    header("Location: /LOGITRACK/controllers/ChoferController.php");
    exit;
}
