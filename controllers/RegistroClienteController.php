<?php
// controllers/RegistroClienteController.php — Registro público de nuevos clientes web
// No requiere sesión activa.

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/ClienteWeb.php';

$modelo      = new ClienteWeb($pdo);
$localidades = $modelo->getLocalidades();
$error       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Sanitizar todos los inputs ───────────────────────────────────────────
    $tipo_documento = trim($_POST['tipo_documento'] ?? '');
    $documento      = trim($_POST['documento']      ?? '');
    $nombre         = trim($_POST['nombre']         ?? '');
    $apellido       = trim($_POST['apellido']       ?? '');
    $id_localidad   = trim($_POST['id_localidad']   ?? '');
    $direccion      = trim($_POST['direccion']      ?? '');
    $username       = trim($_POST['username']       ?? '');
    // No hacer trim() a las contraseñas — los espacios pueden ser intencionales
    $password       = $_POST['password']      ?? '';
    $password_conf  = $_POST['password_conf'] ?? '';

    // ── Validaciones de campos obligatorios ─────────────────────────────────
    if ($tipo_documento === '' || $documento === '' || $nombre === '' ||
        $apellido === '' || $id_localidad === '' || $username === '' || $password === '') {
        $error = 'Todos los campos obligatorios deben completarse.';

    } elseif ($tipo_documento === 'DNI' && (!ctype_digit($documento) || strlen($documento) < 7 || strlen($documento) > 8)) {
        $error = 'El DNI debe tener 7 u 8 dígitos numéricos sin puntos ni espacios.';

    } elseif ($tipo_documento === 'CUIL' && (!ctype_digit($documento) || strlen($documento) !== 11)) {
        $error = 'El CUIL debe tener exactamente 11 dígitos numéricos sin guiones.';

    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';

    } elseif ($password !== $password_conf) {
        $error = 'Las contraseñas no coinciden.';

    } elseif ($modelo->existeUsername($username)) {
        $error = 'Ese nombre de usuario ya está en uso. Elegí otro.';

    } elseif ($modelo->existeDocumento($documento)) {
        $error = 'Ya existe un cliente registrado con ese número de documento.';

    } else {
        try {
            $modelo->registrarNuevo(
                [
                    'tipo_documento' => $tipo_documento,
                    'documento'      => $documento,
                    'nombre'         => $nombre,
                    'apellido'       => $apellido,
                    'id_localidad'   => $id_localidad,
                    'direccion'      => $direccion !== '' ? $direccion : null,
                ],
                $username,
                $password
            );

            // ── Patrón PRG: redirigir para evitar reenvío del formulario ────
            header("Location: /LOGITRACK/login.php?msg=registro_exitoso");
            exit;

        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../views/registro/index.php';
