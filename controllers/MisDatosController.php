<?php
// controllers/MisDatosController.php — Edición del perfil propio del usuario en sesión

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Cliente.php';

// Administrador no puede usar esta pantalla
if (esAdmin()) {
    header("Location: /LOGITRACK/dashboard.php");
    exit;
}

$modeloUsuario = new Usuario($pdo);
$modeloCliente = new Cliente($pdo);

$idUsuario = (int)$_SESSION['usuario_id'];
$error     = '';
$exito     = '';

// Cargar datos actuales del usuario
$usuario = $modeloUsuario->getById($idUsuario);
if (!$usuario) {
    header("Location: /LOGITRACK/dashboard.php");
    exit;
}

// Si es cliente, cargar sus datos personales y las localidades para el <select>
$cliente    = null;
$localidades = [];
if (esCliente() && !empty($usuario['id_cliente'])) {
    $cliente     = $modeloCliente->getById((int)$usuario['id_cliente']);
    $localidades = $modeloCliente->getLocalidades();
}

// ── Procesar formulario ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username     = trim($_POST['username']     ?? '');
    $password     = $_POST['password']          ?? '';
    $password_conf = $_POST['password_conf']    ?? '';

    $valido = true;

    // Validar credenciales
    if ($username === '') {
        $error  = 'El nombre de usuario no puede estar vacío.';
        $valido = false;
    } elseif (strlen($username) > 100) {
        $error  = 'El nombre de usuario no puede superar los 100 caracteres.';
        $valido = false;
    } elseif (!empty($password) && strlen($password) < 8) {
        $error  = 'La nueva contraseña debe tener al menos 8 caracteres.';
        $valido = false;
    } elseif (!empty($password) && $password !== $password_conf) {
        $error  = 'Las contraseñas no coinciden.';
        $valido = false;
    } elseif ($modeloUsuario->existeUsername($username, $idUsuario)) {
        $error  = 'Ese nombre de usuario ya está en uso. Elegí otro.';
        $valido = false;
    }

    // Validar datos personales del cliente (solo si aplica y las credenciales pasaron)
    $datosCliente = [];
    if ($valido && esCliente() && $cliente) {
        $tipo_documento = trim($_POST['tipo_documento'] ?? '');
        $documento      = trim($_POST['documento']      ?? '');
        $nombre         = trim($_POST['nombre']         ?? '');
        $apellido       = trim($_POST['apellido']       ?? '');
        $id_localidad   = trim($_POST['id_localidad']   ?? '');
        $direccion      = trim($_POST['direccion']      ?? '');

        if ($tipo_documento === '' || $documento === '' || $nombre === '' || $apellido === '' || $id_localidad === '') {
            $error  = 'Los campos de datos personales son obligatorios.';
            $valido = false;
        } elseif ($tipo_documento === 'DNI' && (!ctype_digit($documento) || strlen($documento) < 7 || strlen($documento) > 8)) {
            $error  = 'El DNI debe tener 7 u 8 dígitos numéricos sin puntos ni espacios.';
            $valido = false;
        } elseif ($tipo_documento === 'CUIL' && (!ctype_digit($documento) || strlen($documento) !== 11)) {
            $error  = 'El CUIL debe tener exactamente 11 dígitos numéricos sin guiones.';
            $valido = false;
        } elseif ($modeloCliente->existeDocumento($documento, (int)$usuario['id_cliente'])) {
            $error  = 'Ese número de documento ya pertenece a otro cliente registrado.';
            $valido = false;
        } else {
            $datosCliente = [
                'tipo_documento' => $tipo_documento,
                'documento'      => $documento,
                'nombre'         => $nombre,
                'apellido'       => $apellido,
                'id_localidad'   => $id_localidad,
                'direccion'      => $direccion !== '' ? $direccion : null,
            ];
        }
    }

    // Guardar cambios si todo es válido
    if ($valido) {
        $nuevaPassword = !empty($password) ? $password : null;
        $okUsuario     = $modeloUsuario->actualizarCredenciales($idUsuario, $username, $nuevaPassword);

        $okCliente = true;
        if (esCliente() && $cliente && !empty($datosCliente)) {
            $okCliente = $modeloCliente->editar((int)$usuario['id_cliente'], $datosCliente);
        }

        if ($okUsuario && $okCliente) {
            $_SESSION['username'] = $username;
            $exito   = 'Tus datos fueron actualizados correctamente.';
            $usuario = $modeloUsuario->getById($idUsuario);
            if (esCliente() && !empty($usuario['id_cliente'])) {
                $cliente = $modeloCliente->getById((int)$usuario['id_cliente']);
            }
        } else {
            $error = 'Ocurrió un error al guardar los cambios. Intentá nuevamente.';
        }
    }
}

require_once __DIR__ . '/../views/mis_datos/index.php';
