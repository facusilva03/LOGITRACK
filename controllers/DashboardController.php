<?php
// controllers/DashboardController.php — Lógica del panel principal por rol

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Envio.php';
require_once __DIR__ . '/../models/Viaje.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Chofer.php';
require_once __DIR__ . '/../models/Incidente.php';

$tituloPagina = 'Dashboard — LogiTrack';
$kpis  = [];
$datos = [];

$usuarioModel   = new Usuario($pdo);
$envioModel     = new Envio($pdo);
$viajeModel     = new Viaje($pdo);
$clienteModel   = new Cliente($pdo);
$choferModel    = new Chofer($pdo);
$incidenteModel = new Incidente($pdo);

$usuarioInfo = null;
if (esChofer() || esCliente()) {
    $usuarioInfo = $usuarioModel->getInfoRol((int)$_SESSION['usuario_id']);
}

if (esAdmin()) {

    // RRHH (id_rol: 4 = Empleado Sucursal, 2 = Chofer)
    $kpis['usuarios_total'] = $usuarioModel->contarActivos();
    $kpis['empleados']      = $usuarioModel->contarPorRol(4);
    $kpis['choferes']       = $usuarioModel->contarPorRol(2);

    // Operaciones — ciclo de envíos
    $kpis['envios_activos']    = $envioModel->contarActivos();
    $kpis['envios_en_curso']   = $envioModel->contarEnCurso();
    $kpis['envios_completados']= $envioModel->contarCompletados();
    $kpis['envios_total']      = $envioModel->contarTotal();

    // Flota y alertas
    $kpis['viajes_en_curso']  = $viajeModel->contarEnCurso();
    $kpis['devueltos']        = $envioModel->contarDevueltos();
    $kpis['demorados']        = $envioModel->contarDemorados();
    $kpis['incidentes_total']    = $incidenteModel->contarTotal();
    $kpis['viajes_finalizados_admin'] = $viajeModel->contarFinalizados();

    $datos['ultimos_incidentes'] = $incidenteModel->getUltimos(4);

    require_once __DIR__ . '/../views/dashboard/admin.php';
    exit;

} elseif (esEmpleado()) {

    $idSucursal = $usuarioModel->getSucursalEmpleado((int)$_SESSION['usuario_id']) ?: 0;

    $kpis['envios_hoy']      = $envioModel->contarEnviosHoyEnSucursal($idSucursal);
    $kpis['pendientes']      = $envioModel->contarPendientesEnSucursal($idSucursal);
    $kpis['pre_admisiones']  = $envioModel->contarPreAdmisionesPendientes($idSucursal);
    $kpis['viajes_activos']  = $viajeModel->contarEnCursoPorSucursal($idSucursal);
    $datos['ultimos_envios'] = $envioModel->getUltimosEnviosPorSucursal($idSucursal, 5);

    require_once __DIR__ . '/../views/dashboard/empleado.php';
    exit;

} elseif (esChofer() && $usuarioInfo && $usuarioInfo['id_chofer']) {

    $idChofer = (int)$usuarioInfo['id_chofer'];
    $conteos  = $choferModel->contarViajes($idChofer);
    $statsGPS = $choferModel->getStatsGPS($idChofer);
    $kmTotal  = $choferModel->getKmTotal($idChofer);

    $kpis['total_viajes']       = $conteos['total'];
    $kpis['viajes_finalizados'] = $conteos['finalizados'];
    $kpis['incidentes']         = $incidenteModel->contarPorChofer($idChofer);
    $kpis['km_total']           = $kmTotal;
    $kpis['vel_promedio']       = $statsGPS['vel_promedio'];
    $kpis['vel_maxima']         = $statsGPS['vel_maxima'];
    $kpis['viaje_vel_maxima']   = $statsGPS['viaje_maxima'];
    $kpis['km_promedio_viaje']  = $conteos['total'] > 0
                                  ? round($kmTotal / $conteos['total'], 1)
                                  : 0.0;

    $datos['perfil_chofer']    = $choferModel->getById($idChofer);
    $datos['viaje_en_curso']   = $viajeModel->getViajeEnCurso($idChofer);
    $datos['historial_viajes'] = $viajeModel->getHistorialChofer($idChofer, 5);

    require_once __DIR__ . '/../views/dashboard/chofer.php';
    exit;

} elseif (esCliente() && $usuarioInfo && $usuarioInfo['id_cliente']) {

    $idCliente = (int)$usuarioInfo['id_cliente'];

    // Perfil con JOIN a Localidad (devuelve 'localidad' y 'provincia')
    $datos['perfil_cliente'] = $clienteModel->getById($idCliente);

    // KPIs calculados directamente en BD
    $kpis['total_envios'] = $envioModel->contarEnviosComoRemitente($idCliente);
    $kpis['en_camino']    = $envioModel->contarEnCaminoPorCliente($idCliente);
    $kpis['entregados']   = $envioModel->contarEntregadosPorCliente($idCliente);
    $kpis['recibidos']    = $envioModel->contarRecibidosPorCliente($idCliente);

    // Últimos 3 movimientos de estado vinculados a este cliente
    $datos['actividad'] = $envioModel->getActividadReciente($idCliente);

}

require_once __DIR__ . '/../views/dashboard/index.php';
