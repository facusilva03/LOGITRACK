<?php
// controllers/EmpleadoController.php — Gestión de Empleados de Sucursal

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Empleado.php';

requiereRol(['Administrador']);

$modelo = new Empleado($pdo);
$accion = trim($_GET['action'] ?? 'index');
$exito  = '';
$error  = '';

// ---- ELIMINAR (cascada: primero Usuario, luego Empleado) ----
if ($accion === 'eliminar') {
    $idEmpleado = (int)($_GET['id'] ?? 0);
    if ($idEmpleado > 0) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM Usuario  WHERE id_empleado = ?")->execute([$idEmpleado]);
            $pdo->prepare("DELETE FROM Empleado WHERE id_empleado = ?")->execute([$idEmpleado]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("EmpleadoController eliminar: " . $e->getMessage());
        }
    }
    header("Location: /LOGITRACK/controllers/EmpleadoController.php?exito=eliminado");
    exit;
}

// ---- ACTUALIZAR EMPLEADO ----
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
    $nombre     = trim($_POST['nombre']     ?? '');
    $apellido   = trim($_POST['apellido']   ?? '');
    $id_suc     = (int)($_POST['id_sucursal'] ?? 0);

    if ($idEmpleado <= 0 || $nombre === '' || $apellido === '' || $id_suc <= 0) {
        $error  = 'Nombre, apellido y sucursal son obligatorios.';
        $accion = 'editar';
    } else {
        $ok = $modelo->editar($idEmpleado, [
            'nombre'      => $nombre,
            'apellido'    => $apellido,
            'telefono'    => $_POST['telefono']  ?? '',
            'direccion'   => $_POST['direccion'] ?? '',
            'localidad'   => $_POST['localidad'] ?? '',
            'id_sucursal' => $id_suc,
        ]);
        if ($ok) {
            header("Location: /LOGITRACK/controllers/EmpleadoController.php?action=ver&id={$idEmpleado}&exito=editado");
            exit;
        }
        $error  = 'No se pudo actualizar el empleado.';
        $accion = 'editar';
    }
}

// ---- DATOS PARA VISTAS ----
$sucursales = $modelo->getSucursales();

if ($accion === 'index') {
    $filtros = [
        'id_sucursal' => (int)($_GET['id_sucursal'] ?? 0),
        'buscar'      => trim($_GET['buscar'] ?? ''),
    ];
    $empleados = $modelo->getAll($filtros);
    $exito = match($_GET['exito'] ?? '') {
        'eliminado' => 'Empleado eliminado del sistema.',
        'creado'    => 'Empleado registrado correctamente.',
        default     => ''
    };
    require_once __DIR__ . '/../views/empleados/index.php';

} elseif ($accion === 'ver') {
    $id       = (int)($_GET['id'] ?? 0);
    $empleado = $modelo->getById($id);
    if (!$empleado) {
        header("Location: /LOGITRACK/controllers/EmpleadoController.php");
        exit;
    }
    $exito = match($_GET['exito'] ?? '') {
        'editado' => 'Datos del empleado actualizados correctamente.',
        default   => $exito
    };
    require_once __DIR__ . '/../views/empleados/detalle.php';

} elseif ($accion === 'editar' || $accion === 'actualizar') {
    $id       = (int)($_GET['id'] ?? $_POST['id_empleado'] ?? 0);
    $empleado = $modelo->getById($id);
    if (!$empleado) {
        header("Location: /LOGITRACK/controllers/EmpleadoController.php");
        exit;
    }
    require_once __DIR__ . '/../views/empleados/editar.php';

} else {
    header("Location: /LOGITRACK/controllers/EmpleadoController.php");
    exit;
}
