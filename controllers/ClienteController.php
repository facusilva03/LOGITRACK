<?php
// controllers/ClienteController.php — CRUD de clientes (Admin + Empleado Sucursal)

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Cliente.php';

requiereRol(['Administrador', 'Empleado Sucursal']);

$modelo = new Cliente($pdo);
$accion = trim($_GET['action'] ?? 'index');
$error  = '';
$exito  = '';

// ---- GUARDAR NUEVO ----
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_doc    = trim($_POST['tipo_documento'] ?? '');
    $documento   = trim($_POST['documento'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido    = trim($_POST['apellido'] ?? '');
    $id_loc      = trim($_POST['id_localidad'] ?? '');
    $direccion   = trim($_POST['direccion'] ?? '');

    if ($tipo_doc === '' || $documento === '' || $nombre === '' || $apellido === '' || $id_loc === '') {
        $error = 'Los campos marcados son obligatorios.';
        $accion = 'crear';
    } elseif ($modelo->existeDocumento($documento)) {
        $error = 'Ya existe un cliente con ese número de documento.';
        $accion = 'crear';
    } else {
        $ok = $modelo->crear([
            'tipo_documento' => $tipo_doc,
            'documento'      => $documento,
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'id_localidad'   => $id_loc,
            'direccion'      => $direccion,
        ]);
        if ($ok) {
            header("Location: /LOGITRACK/controllers/ClienteController.php?exito=creado");
            exit;
        }
        $error = 'No se pudo registrar el cliente. Intentá de nuevo.';
        $accion = 'crear';
    }
}

// ---- ACTUALIZAR ----
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = (int)($_POST['id_cliente'] ?? 0);
    $tipo_doc  = trim($_POST['tipo_documento'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $id_loc    = trim($_POST['id_localidad'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($id <= 0 || $tipo_doc === '' || $documento === '' || $nombre === '' || $apellido === '' || $id_loc === '') {
        $error = 'Los campos marcados son obligatorios.';
        $accion = 'editar';
    } elseif ($modelo->existeDocumento($documento, $id)) {
        $error = 'Ya existe otro cliente con ese número de documento.';
        $accion = 'editar';
    } else {
        $ok = $modelo->editar($id, [
            'tipo_documento' => $tipo_doc,
            'documento'      => $documento,
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'id_localidad'   => $id_loc,
            'direccion'      => $direccion,
        ]);
        if ($ok) {
            header("Location: /LOGITRACK/controllers/ClienteController.php?exito=editado");
            exit;
        }
        $error = 'No se pudo actualizar el cliente. Intentá de nuevo.';
        $accion = 'editar';
    }
}

// ---- DATOS PARA VISTAS ----
$localidades = $modelo->getLocalidades();

if ($accion === 'index') {
    $filtros = [
        'id_localidad' => (int)($_GET['id_localidad'] ?? 0),
        'buscar'       => trim($_GET['buscar'] ?? ''),
    ];
    $clientes = $modelo->getAll($filtros);
    $exito = match($_GET['exito'] ?? '') {
        'creado'  => 'Cliente registrado correctamente.',
        'editado' => 'Cliente actualizado correctamente.',
        default   => ''
    };
    require_once __DIR__ . '/../views/clientes/index.php';

} elseif ($accion === 'crear') {
    require_once __DIR__ . '/../views/clientes/crear.php';

} elseif ($accion === 'editar' || $accion === 'actualizar') {
    $id      = (int)($_GET['id'] ?? $_POST['id_cliente'] ?? 0);
    $cliente = $modelo->getById($id);
    if (!$cliente) {
        header("Location: /LOGITRACK/controllers/ClienteController.php");
        exit;
    }
    require_once __DIR__ . '/../views/clientes/editar.php';

} else {
    header("Location: /LOGITRACK/controllers/ClienteController.php");
    exit;
}
