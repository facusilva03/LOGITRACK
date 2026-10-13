<?php
// sidebar.php — Menú lateral dinámico según el rol del usuario en sesión

// Detecta la URL actual para marcar el ítem activo
$urlActual = $_SERVER['REQUEST_URI'];
$esActivo = function(string $ruta) use ($urlActual): string {
    return (strpos($urlActual, $ruta) !== false) ? 'activo' : '';
};
?>
<aside class="sidebar">
    <nav class="sidebar-nav">
        <span class="sidebar-seccion">Principal</span>
        <a href="/LOGITRACK/dashboard.php"
           class="nav-item <?= (strpos($urlActual, 'dashboard') !== false) ? 'activo' : '' ?>">
            Dashboard
        </a>

        <?php if (esAdmin()): ?>
            <span class="sidebar-seccion">Administración</span>
            <a href="/LOGITRACK/controllers/UsuarioController.php"
               class="nav-item <?= (strpos($urlActual, 'UsuarioController') !== false && empty($_GET['rol'])) ? 'activo' : '' ?>">Usuarios</a>
            <a href="/LOGITRACK/controllers/EmpleadoController.php"
               class="nav-item <?= $esActivo('EmpleadoController') ?>">Empleados</a>
            <a href="/LOGITRACK/controllers/ChoferController.php"
               class="nav-item <?= $esActivo('ChoferController') ?>">Choferes</a>
            <a href="/LOGITRACK/controllers/VehiculoController.php"
               class="nav-item <?= $esActivo('VehiculoController') ?>">Vehículos</a>
            <a href="/LOGITRACK/controllers/AseguradoraController.php"
               class="nav-item <?= $esActivo('AseguradoraController') ?>">Aseguradoras</a>

            <span class="sidebar-seccion">Operaciones</span>
            <a href="/LOGITRACK/controllers/ClienteController.php"
               class="nav-item <?= $esActivo('ClienteController') ?>">Clientes</a>
            <a href="/LOGITRACK/controllers/EnvioController.php"
               class="nav-item <?= $esActivo('EnvioController') ?>">Envíos</a>
            <a href="/LOGITRACK/controllers/ViajeController.php"
               class="nav-item <?= $esActivo('ViajeController') ?>">Viajes</a>
            <a href="/LOGITRACK/controllers/ViajeController.php?action=enCamino"
               class="nav-item <?= (strpos($urlActual, 'action=enCamino') !== false) ? 'activo' : '' ?>">Viajes en Curso</a>
            <a href="/LOGITRACK/controllers/IncidenteController.php"
               class="nav-item <?= $esActivo('IncidenteController') ?>">Incidentes</a>

        <?php elseif (esEmpleado()): ?>
            <span class="sidebar-seccion">Operaciones</span>
            <a href="/LOGITRACK/controllers/ClienteController.php"
               class="nav-item <?= $esActivo('ClienteController') ?>">Clientes</a>
            <a href="/LOGITRACK/controllers/EnvioController.php"
               class="nav-item <?= $esActivo('EnvioController') ?>">Envíos</a>
            <a href="/LOGITRACK/controllers/ViajeController.php"
               class="nav-item <?= $esActivo('ViajeController') ?>">Viajes</a>
            <a href="/LOGITRACK/controllers/ViajeController.php?action=enCamino"
               class="nav-item <?= (strpos($urlActual, 'action=enCamino') !== false) ? 'activo' : '' ?>">Viajes en Curso</a>
            <a href="/LOGITRACK/controllers/IncidenteController.php"
               class="nav-item <?= $esActivo('IncidenteController') ?>">Incidentes</a>

            <span class="sidebar-seccion">Mi cuenta</span>
            <a href="/LOGITRACK/controllers/MisDatosController.php"
               class="nav-item <?= $esActivo('MisDatosController') ?>">Mis Datos</a>

        <?php elseif (esChofer()): ?>
            <span class="sidebar-seccion">Mi actividad</span>
            <a href="/LOGITRACK/controllers/ViajeController.php"
               class="nav-item <?= $esActivo('ViajeController') ?>">Mis Viajes</a>
            <a href="/LOGITRACK/controllers/IncidenteController.php"
               class="nav-item <?= $esActivo('IncidenteController') ?>">Mis Incidentes</a>

            <span class="sidebar-seccion">Mi cuenta</span>
            <a href="/LOGITRACK/controllers/MisDatosController.php"
               class="nav-item <?= $esActivo('MisDatosController') ?>">Mis Datos</a>

        <?php elseif (esCliente()): ?>
            <span class="sidebar-seccion">Mi actividad</span>
            <a href="/LOGITRACK/controllers/EnvioController.php"
               class="nav-item <?= $esActivo('EnvioController') ?>">Mis Envíos</a>

            <span class="sidebar-seccion">Mi cuenta</span>
            <a href="/LOGITRACK/controllers/MisDatosController.php"
               class="nav-item <?= $esActivo('MisDatosController') ?>">Mis Datos</a>
            <a href="/LOGITRACK/controllers/CuentaController.php?action=confirmar"
               class="nav-item nav-item-peligro <?= $esActivo('CuentaController') ?>">Eliminar cuenta</a>
        <?php endif; ?>
    </nav>
</aside>

<main class="main-content">
