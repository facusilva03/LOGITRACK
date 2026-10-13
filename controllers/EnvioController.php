<?php
// controllers/EnvioController.php — CRUD de envíos + historial de estados

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Envio.php';

$accion = trim($_GET['action'] ?? 'index');
$error  = '';
$exito  = '';

$modelo = new Envio($pdo);

// El cliente puede ver sus envíos y generar nuevos, pero no editar ni gestionar estados
if (esCliente() && !in_array($accion, ['index', 'detalle', 'crear', 'guardar'])) {
    header("Location: /LOGITRACK/controllers/EnvioController.php");
    exit;
}

// Chofer no tiene acceso a envíos
if (esChofer()) {
    header("Location: /LOGITRACK/dashboard.php");
    exit;
}

// ---- GUARDAR NUEVO ----
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_orig = trim($_POST['id_suc_origen'] ?? '');
    $id_dest = trim($_POST['id_suc_destino'] ?? '');
    $id_tipo = trim($_POST['id_tipo_cont'] ?? '');
    $peso_kg = trim($_POST['peso_kg'] ?? '');

    if (esCliente()) {
        // ── Flujo cliente: transacción PDO completa con upsert de destinatario ──
        $dniDest      = trim($_POST['dni_destinatario'] ?? '');
        $nombreDest   = trim($_POST['nombre_destinatario'] ?? '');
        $apellidoDest = trim($_POST['apellido_destinatario'] ?? '');
        $localDest    = trim($_POST['id_localidad_destinatario'] ?? '');
        $dirDest      = trim($_POST['direccion_destinatario'] ?? '');
        $cpDest       = trim($_POST['codigo_postal_destinatario'] ?? '');

        if ($dniDest === '' || $nombreDest === '' || $apellidoDest === '' || $localDest === ''
            || $dirDest === ''
            || $id_orig === '' || $id_dest === '' || $id_tipo === '' || $peso_kg === '') {
            $error  = 'Todos los campos son obligatorios.';
            $accion = 'crear';
        } elseif (!ctype_digit($dniDest)) {
            $error  = 'El DNI del destinatario debe contener solo números.';
            $accion = 'crear';
        } elseif ((float)$peso_kg <= 0) {
            $error  = 'El peso debe ser mayor a cero.';
            $accion = 'crear';
        } elseif ((float)$peso_kg > 100) {
            $error  = 'El peso máximo por paquete es 100 kg. Para cargas mayores, consultá con la empresa.';
            $accion = 'crear';
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Remitente: forzado desde la sesión, no aceptado del POST
                $stmtRem = $pdo->prepare("
                    SELECT c.id_cliente FROM Cliente c
                    INNER JOIN Usuario u ON u.id_cliente = c.id_cliente
                    WHERE u.id_usuario = ?
                ");
                $stmtRem->execute([$_SESSION['usuario_id']]);
                $rowRem = $stmtRem->fetch();
                if (!$rowRem) {
                    throw new RuntimeException('No se encontró el perfil del cliente en sesión.');
                }
                $id_rem = (int)$rowRem['id_cliente'];

                // 2. Destinatario: upsert — buscar por DNI, crear o actualizar dirección
                $datosDir = [
                    'id_localidad'  => $localDest,
                    'direccion'     => $dirDest,
                    'codigo_postal' => $cpDest,
                ];
                $id_des = $modelo->getIdClientePorDocumento($dniDest);
                if ($id_des === false) {
                    // No existe: INSERT cliente pasivo con todos sus datos
                    $id_des = $modelo->crearClientePasivo(array_merge([
                        'documento' => $dniDest,
                        'nombre'    => $nombreDest,
                        'apellido'  => $apellidoDest,
                    ], $datosDir));
                } else {
                    // Ya existe: actualizar localidad y dirección con los datos más recientes
                    $modelo->actualizarDireccionCliente($id_des, $datosDir);
                }

                if ($id_rem === $id_des) {
                    throw new RuntimeException('El remitente y el destinatario no pueden ser el mismo cliente.');
                }

                // 3. INSERT Envio + INSERT Historial_Estado (Pre-Admisión = 8)
                $tracking = 'LT-' . strtoupper(bin2hex(random_bytes(4)));
                $modelo->insertarEnvio(
                    [
                        'id_remitente'    => $id_rem,
                        'id_destinatario' => $id_des,
                        'id_suc_origen'   => $id_orig,
                        'id_suc_destino'  => $id_dest,
                        'id_tipo_cont'    => $id_tipo,
                        'peso_kg'         => $peso_kg,
                    ],
                    $tracking,
                    8,          // Pre-Admisión
                    (int)$id_orig
                );

                $pdo->commit();
                header("Location: /LOGITRACK/controllers/EnvioController.php?exito=creado");
                exit;

            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log("EnvioController guardar cliente: " . $e->getMessage());
                $error  = $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'No se pudo registrar el envío. Intentá de nuevo.';
                $accion = 'crear';
            }
        }

    } else {
        // ── Flujo empleado / administrador: campos detallados + upsert cliente ──
        $dniRem      = trim($_POST['dni_remitente'] ?? '');
        $nombreRem   = trim($_POST['nombre_remitente'] ?? '');
        $apellidoRem = trim($_POST['apellido_remitente'] ?? '');
        $localRem    = trim($_POST['id_localidad_remitente'] ?? '');
        $dirRem      = trim($_POST['direccion_remitente'] ?? '');

        $dniDes      = trim($_POST['dni_destinatario'] ?? '');
        $nombreDes   = trim($_POST['nombre_destinatario'] ?? '');
        $apellidoDes = trim($_POST['apellido_destinatario'] ?? '');
        $localDes    = trim($_POST['id_localidad_destinatario'] ?? '');
        $dirDes      = trim($_POST['direccion_destinatario'] ?? '');

        if ($dniRem === '' || $nombreRem === '' || $apellidoRem === '' || $localRem === '' || $dirRem === ''
            || $dniDes === '' || $nombreDes === '' || $apellidoDes === '' || $localDes === '' || $dirDes === ''
            || $id_orig === '' || $id_dest === '' || $id_tipo === '' || $peso_kg === '') {
            $error  = 'Todos los campos son obligatorios.';
            $accion = 'crear';
        } elseif (!ctype_digit($dniRem) || strlen($dniRem) < 7 || strlen($dniRem) > 8) {
            $error  = 'El DNI del remitente debe tener 7 u 8 dígitos numéricos sin puntos.';
            $accion = 'crear';
        } elseif (!ctype_digit($dniDes) || strlen($dniDes) < 7 || strlen($dniDes) > 8) {
            $error  = 'El DNI del destinatario debe tener 7 u 8 dígitos numéricos sin puntos.';
            $accion = 'crear';
        } elseif ($dniRem === $dniDes) {
            $error  = 'El remitente y el destinatario no pueden tener el mismo DNI.';
            $accion = 'crear';
        } elseif ((float)$peso_kg <= 0) {
            $error  = 'El peso debe ser mayor a cero.';
            $accion = 'crear';
        } elseif ((float)$peso_kg > 100) {
            $error  = 'El peso máximo por paquete es 100 kg. Para cargas mayores, consultá con la empresa.';
            $accion = 'crear';
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Remitente: buscar por DNI o crear cliente pasivo
                $id_rem = $modelo->getIdClientePorDocumento($dniRem);
                if ($id_rem === false) {
                    $id_rem = $modelo->crearClientePasivo([
                        'documento'     => $dniRem,
                        'nombre'        => $nombreRem,
                        'apellido'      => $apellidoRem,
                        'id_localidad'  => $localRem,
                        'direccion'     => $dirRem,
                        'codigo_postal' => '',
                    ]);
                } else {
                    $modelo->actualizarDireccionCliente($id_rem, [
                        'id_localidad'  => $localRem,
                        'direccion'     => $dirRem,
                        'codigo_postal' => '',
                    ]);
                }

                // 2. Destinatario: buscar por DNI o crear cliente pasivo
                $id_des = $modelo->getIdClientePorDocumento($dniDes);
                if ($id_des === false) {
                    $id_des = $modelo->crearClientePasivo([
                        'documento'     => $dniDes,
                        'nombre'        => $nombreDes,
                        'apellido'      => $apellidoDes,
                        'id_localidad'  => $localDes,
                        'direccion'     => $dirDes,
                        'codigo_postal' => '',
                    ]);
                } else {
                    $modelo->actualizarDireccionCliente($id_des, [
                        'id_localidad'  => $localDes,
                        'direccion'     => $dirDes,
                        'codigo_postal' => '',
                    ]);
                }

                if ($id_rem === $id_des) {
                    throw new RuntimeException('El remitente y el destinatario no pueden ser el mismo cliente.');
                }

                // 3. INSERT Envio + INSERT Historial_Estado (Recibido = 2)
                $tracking = 'LT-' . strtoupper(bin2hex(random_bytes(4)));
                $modelo->insertarEnvio(
                    [
                        'id_remitente'    => $id_rem,
                        'id_destinatario' => $id_des,
                        'id_suc_origen'   => $id_orig,
                        'id_suc_destino'  => $id_dest,
                        'id_tipo_cont'    => $id_tipo,
                        'peso_kg'         => $peso_kg,
                    ],
                    $tracking,
                    2,          // Recibido
                    (int)$id_orig
                );

                $pdo->commit();
                header("Location: /LOGITRACK/controllers/EnvioController.php?exito=creado");
                exit;

            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log("EnvioController guardar empleado: " . $e->getMessage());
                $error  = $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'No se pudo registrar el envío. Intentá de nuevo.';
                $accion = 'crear';
            }
        }
    }
}

// ---- ACTUALIZAR ----
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $id      = (int)($_POST['id_envio'] ?? 0);
    $id_rem  = trim($_POST['id_remitente'] ?? '');
    $id_des  = trim($_POST['id_destinatario'] ?? '');
    $id_orig = trim($_POST['id_suc_origen'] ?? '');
    $id_dest = trim($_POST['id_suc_destino'] ?? '');
    $id_tipo = trim($_POST['id_tipo_cont'] ?? '');

    if ($id <= 0 || $id_rem === '' || $id_des === '' || $id_orig === '' || $id_dest === '' || $id_tipo === '') {
        $error = 'Todos los campos son obligatorios.';
        $accion = 'editar';
    } else {
        $ok = $modelo->editar($id, [
            'id_remitente'    => $id_rem,
            'id_destinatario' => $id_des,
            'id_suc_origen'   => $id_orig,
            'id_suc_destino'  => $id_dest,
            'id_tipo_cont'    => $id_tipo,
        ]);
        if ($ok) {
            header("Location: /LOGITRACK/controllers/EnvioController.php?exito=editado");
            exit;
        }
        $error = 'No se pudo actualizar el envío.';
        $accion = 'editar';
    }
}

// ---- AGREGAR ESTADO ----
if ($accion === 'estado' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $idEnvio    = (int)($_POST['id_envio']          ?? 0);
    $idEstado   = (int)($_POST['id_estado']          ?? 0);
    $idSucursal = (int)($_POST['id_sucursal_actual'] ?? 0) ?: null;
    $pesoReal   = trim($_POST['peso_real'] ?? '');

    if ($idEnvio > 0 && $idEstado > 0) {
        $modelo->agregarEstado($idEnvio, $idEstado, $idSucursal);
        if ($pesoReal !== '' && (float)$pesoReal > 0) {
            $modelo->confirmarPeso($idEnvio, (float)$pesoReal);
        }
    }
    header("Location: /LOGITRACK/controllers/EnvioController.php?action=detalle&id={$idEnvio}&exito=estado");
    exit;
}

// ---- DATOS PARA VISTAS ----
// $clientes solo se carga para roles internos — los clientes no deben ver la lista completa
$clientes   = esCliente() ? [] : $modelo->getClientes();
$sucursales = $modelo->getSucursales();
$tipos      = $modelo->getTiposContenido();
$estados    = $modelo->getEstados();
$localidades = $modelo->getLocalidades();

if ($accion === 'index') {
    // El cliente ve solo sus envíos
    if (esCliente()) {
        try {
            $stmt = $pdo->prepare("SELECT id_cliente FROM Usuario WHERE id_usuario = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $row = $stmt->fetch();
            $envios = $row ? $modelo->getByCliente((int)$row['id_cliente']) : [];
        } catch (PDOException $e) {
            error_log("EnvioController index cliente: " . $e->getMessage());
            $envios = [];
        }
    } else {
        $filtros = [
            'id_estado'    => (int)($_GET['id_estado'] ?? 0),
            'id_suc_origen'=> (int)($_GET['id_suc_origen'] ?? 0),
            'tracking'     => trim($_GET['tracking'] ?? ''),
            'orden'        => in_array($_GET['orden'] ?? '', ['asc', 'desc']) ? $_GET['orden'] : 'desc',
        ];
        $envios = $modelo->getAll($filtros);
    }
    $exito = match($_GET['exito'] ?? '') {
        'creado'  => 'Envío registrado correctamente.',
        'editado' => 'Envío actualizado correctamente.',
        default   => ''
    };
    require_once __DIR__ . '/../views/envios/index.php';

} elseif ($accion === 'crear') {
    requiereRol(['Administrador', 'Empleado Sucursal', 'Cliente']);
    // Para clientes: obtener su propio perfil (se muestra como remitente fijo)
    $perfilRemitente = null;
    if (esCliente()) {
        try {
            $stmtP = $pdo->prepare("
                SELECT c.id_cliente, c.nombre, c.apellido, c.documento
                FROM Cliente c
                INNER JOIN Usuario u ON u.id_cliente = c.id_cliente
                WHERE u.id_usuario = ?
            ");
            $stmtP->execute([$_SESSION['usuario_id']]);
            $perfilRemitente = $stmtP->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("EnvioController crear perfil: " . $e->getMessage());
        }
    }
    require_once __DIR__ . '/../views/envios/crear.php';

} elseif ($accion === 'editar' || $accion === 'actualizar') {
    requiereRol(['Administrador', 'Empleado Sucursal']);
    $id    = (int)($_GET['id'] ?? $_POST['id_envio'] ?? 0);
    $envio = $modelo->getById($id);
    if (!$envio) {
        header("Location: /LOGITRACK/controllers/EnvioController.php");
        exit;
    }
    require_once __DIR__ . '/../views/envios/editar.php';

} elseif ($accion === 'detalle') {
    $id       = (int)($_GET['id'] ?? 0);
    $envio    = $modelo->getById($id);
    $historial = $envio ? $modelo->getHistorial($id) : [];
    $exito = ($_GET['exito'] ?? '') === 'estado' ? 'Estado agregado correctamente.' : '';
    if (!$envio) {
        header("Location: /LOGITRACK/controllers/EnvioController.php");
        exit;
    }
    require_once __DIR__ . '/../views/envios/detalle.php';

} else {
    header("Location: /LOGITRACK/controllers/EnvioController.php");
    exit;
}
