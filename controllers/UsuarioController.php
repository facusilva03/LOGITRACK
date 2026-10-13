<?php
// controllers/UsuarioController.php — CRUD de usuarios (solo Administrador)

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Usuario.php';

// Solo el administrador puede gestionar usuarios
requiereRol(['Administrador']);

$modelo = new Usuario($pdo);
$accion = trim($_GET['action'] ?? 'index');
$error  = '';
$exito  = '';

// ---- GUARDAR NUEVO (Alta unificada de personal interno) ----
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rolInterno  = trim($_POST['rol_interno']  ?? '');
    $nombre      = trim($_POST['nombre']       ?? '');
    $apellido    = trim($_POST['apellido']     ?? '');
    $id_sucursal = (int)trim($_POST['id_sucursal'] ?? 0);
    $documento   = trim($_POST['documento']    ?? '');
    $tipo_lic    = trim($_POST['tipo_licencia']?? '');
    $localidad   = trim($_POST['localidad']    ?? '');
    $direccion   = trim($_POST['direccion']    ?? '');
    $telefono    = trim($_POST['telefono']     ?? '');

    // DNI como credenciales iniciales
    $username = $documento;
    $password = $documento;

    $rolesValidos = ['Empleado Sucursal', 'Chofer'];

    if (!in_array($rolInterno, $rolesValidos) || $nombre === '' || $apellido === ''
        || $id_sucursal <= 0 || $documento === '') {
        $error  = 'Completá todos los campos obligatorios.';
        $accion = 'crear';
    } elseif (!ctype_digit($documento) || strlen($documento) < 7 || strlen($documento) > 8) {
        $error  = 'El DNI debe tener 7 u 8 dígitos numéricos sin puntos ni espacios.';
        $accion = 'crear';
    } elseif ($modelo->existeUsername($username)) {
        $error  = 'El DNI ingresado ya está registrado como usuario.';
        $accion = 'crear';
    } elseif ($rolInterno === 'Chofer' && $tipo_lic === '') {
        $error  = 'Para Chofer es obligatorio el Tipo de licencia.';
        $accion = 'crear';
    } else {
        try {
            $pdo->beginTransaction();
            $legajoGenerado = null;

            if ($rolInterno === 'Empleado Sucursal') {
                $tempLegajo = 'INIT-' . uniqid();
                $stmt = $pdo->prepare(
                    "INSERT INTO Empleado (legajo, dni, nombre, apellido, id_sucursal, localidad, direccion, telefono)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$tempLegajo, $documento, $nombre, $apellido, $id_sucursal, $localidad, $direccion, $telefono]);
                $idEmpleado = (int)$pdo->lastInsertId();

                $legajoGenerado = 'EMP-' . str_pad($idEmpleado, 4, '0', STR_PAD_LEFT);
                $pdo->prepare("UPDATE Empleado SET legajo = ? WHERE id_empleado = ?")
                    ->execute([$legajoGenerado, $idEmpleado]);

                $stmt = $pdo->prepare(
                    "INSERT INTO Usuario (username, password_hash, id_rol, id_empleado) VALUES (?, ?, 4, ?)"
                );
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $idEmpleado]);

            } else {
                // Insertar con legajo temporal para obtener el ID real
                $tempLegajo = 'INIT-' . uniqid();
                $stmt = $pdo->prepare(
                    "INSERT INTO Chofer (legajo, dni, nombre, apellido, tipo_licencia, id_sucursal, localidad, direccion, telefono)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$tempLegajo, $documento, $nombre, $apellido, $tipo_lic, $id_sucursal, $localidad, $direccion, $telefono]);
                $idChofer = (int)$pdo->lastInsertId();

                // Generar legajo definitivo basado en el ID asignado
                $legajoGenerado = 'CHF-' . str_pad($idChofer, 4, '0', STR_PAD_LEFT);
                $pdo->prepare("UPDATE Chofer SET legajo = ? WHERE id_chofer = ?")
                    ->execute([$legajoGenerado, $idChofer]);

                $stmt = $pdo->prepare(
                    "INSERT INTO Usuario (username, password_hash, id_rol, id_chofer) VALUES (?, ?, 2, ?)"
                );
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $idChofer]);
            }

            $pdo->commit();
            $qs = $legajoGenerado ? '&legajo=' . urlencode($legajoGenerado) : '';
            header("Location: /LOGITRACK/controllers/UsuarioController.php?exito=creado$qs");
            exit;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("UsuarioController guardar: " . $e->getMessage());
            $error = str_contains($e->getMessage(), 'Duplicate entry')
                ? 'El DNI ingresado ya está registrado.'
                : 'No se pudo crear el usuario. Intentá de nuevo.';
            $accion = 'crear';
        }
    }
}

// ---- ACTUALIZAR ----
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id_usuario'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $id_rol   = trim($_POST['id_rol'] ?? '');

    if ($id <= 0 || $username === '' || $id_rol === '') {
        $error = 'Los campos usuario y rol son obligatorios.';
        $accion = 'editar';
    } elseif ($modelo->existeUsername($username, $id)) {
        $error = 'El nombre de usuario ya está en uso.';
        $accion = 'editar';
    } else {
        $ok = $modelo->editar($id, [
            'username' => $username,
            'password' => $password,
            'id_rol'   => $id_rol,
        ]);
        if ($ok) {
            header("Location: /LOGITRACK/controllers/UsuarioController.php?exito=editado");
            exit;
        }
        $error = 'No se pudo actualizar el usuario. Intentá de nuevo.';
        $accion = 'editar';
    }
}

// ---- DESACTIVAR ----
if ($accion === 'desactivar') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id === (int)$_SESSION['usuario_id']) {
        header("Location: /LOGITRACK/controllers/UsuarioController.php?err=self");
        exit;
    }
    if ($id > 0) {
        $modelo->desactivar($id);
    }
    header("Location: /LOGITRACK/controllers/UsuarioController.php?exito=desactivado");
    exit;
}

// ---- REACTIVAR ----
if ($accion === 'reactivar') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $modelo->reactivar($id);
    }
    header("Location: /LOGITRACK/controllers/UsuarioController.php?exito=reactivado");
    exit;
}

// ---- ELIMINAR (solo borra el acceso — no cascadea a Empleado ni Chofer) ----
if ($accion === 'eliminar') {
    $idUsuario = (int)($_GET['id'] ?? 0);
    if ($idUsuario > 0 && $idUsuario !== (int)$_SESSION['usuario_id']) {
        try {
            $pdo->prepare("DELETE FROM Usuario WHERE id_usuario = ?")->execute([$idUsuario]);
        } catch (Throwable $e) {
            error_log("UsuarioController eliminar: " . $e->getMessage());
        }
    }
    header("Location: /LOGITRACK/controllers/UsuarioController.php?exito=eliminado");
    exit;
}

// ---- DATOS PARA VISTAS ----
$roles = $modelo->getRoles();
$stmtSuc  = $pdo->query("SELECT id_sucursal, nombre FROM Sucursal ORDER BY nombre");
$sucursales = $stmtSuc->fetchAll();

if ($accion === 'index') {
    $rolFiltro = trim($_GET['rol'] ?? '');
    $filtros   = [
        'rol'    => $rolFiltro,
        'activo' => $_GET['activo'] ?? ($rolFiltro === 'Empleado Sucursal' ? '1' : ''),
        'buscar' => trim($_GET['buscar'] ?? ''),
    ];
    $usuarios  = $modelo->getAll($filtros);
    $legajoMsg = !empty($_GET['legajo']) ? ' Legajo asignado: ' . htmlspecialchars($_GET['legajo']) . '.' : '';
    $exito     = match($_GET['exito'] ?? '') {
        'creado'      => 'Personal registrado correctamente.' . $legajoMsg,
        'editado'     => 'Usuario actualizado correctamente.',
        'desactivado' => 'Usuario desactivado correctamente.',
        'eliminado'   => 'Usuario eliminado del sistema.',
        default       => ''
    };
    $error = ($_GET['err'] ?? '') === 'self' ? 'No podés realizar esa acción sobre tu propio usuario.' : '';
    require_once __DIR__ . '/../views/usuarios/index.php';

} elseif ($accion === 'crear') {
    require_once __DIR__ . '/../views/usuarios/crear.php';

} elseif ($accion === 'editar' || $accion === 'actualizar') {
    $id      = (int)($_GET['id'] ?? $_POST['id_usuario'] ?? 0);
    $usuario = $modelo->getById($id);
    if (!$usuario) {
        header("Location: /LOGITRACK/controllers/UsuarioController.php");
        exit;
    }
    require_once __DIR__ . '/../views/usuarios/editar.php';

} else {
    header("Location: /LOGITRACK/controllers/UsuarioController.php");
    exit;
}
