<?php
$tituloPagina = 'Ficha de Empleado — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$apellidoNombre = htmlspecialchars(($empleado['apellido'] ?? '') . ', ' . ($empleado['nombre'] ?? ''));
$legajo         = $empleado['legajo']         ?? '—';
$dni            = $empleado['dni']            ?? '—';
$sucursal       = $empleado['sucursal_nombre']?? '—';
$telefono       = $empleado['telefono']       ?? null;
$direccion      = $empleado['direccion']      ?? null;
$localidad      = $empleado['localidad']      ?? null;
$username       = $empleado['username']       ?? '—';
$activo         = $empleado['activo']         ?? 0;
$fechaAlta      = !empty($empleado['created_at'])
                    ? date('d/m/Y', strtotime($empleado['created_at']))
                    : '—';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Ficha de empleado</h1>
        <p class="page-subtitulo"><?= $apellidoNombre ?></p>
    </div>
    <div class="flex-gap">
        <a href="/LOGITRACK/controllers/EmpleadoController.php?action=editar&id=<?= (int)$empleado['id_empleado'] ?>"
           class="btn btn-outline">Editar</a>
        <a href="/LOGITRACK/controllers/EmpleadoController.php" class="btn btn-gris">← Volver al listado</a>
    </div>
</div>

<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>

<div class="perfil-card">

    <!-- Encabezado de perfil -->
    <div class="perfil-header">
        <div class="perfil-avatar">
            <?= strtoupper(substr($empleado['nombre'] ?? 'E', 0, 1) . substr($empleado['apellido'] ?? '', 0, 1)) ?>
        </div>
        <div class="perfil-header-info">
            <h2 class="perfil-nombre"><?= $apellidoNombre ?></h2>
            <div class="perfil-header-meta">
                <span class="badge badge-azul"><?= htmlspecialchars($legajo) ?></span>
                <?php if ($activo): ?>
                    <span class="badge badge-verde">Activo</span>
                <?php else: ?>
                    <span class="badge badge-gris">Inactivo</span>
                <?php endif; ?>
                <span class="perfil-sucursal"><?= htmlspecialchars($sucursal) ?></span>
            </div>
        </div>
    </div>

    <!-- Cuerpo de la ficha: dos columnas -->
    <div class="perfil-body">

        <div class="perfil-seccion">
            <h3 class="perfil-seccion-titulo">Datos personales</h3>
            <dl class="perfil-dl">
                <div class="perfil-dl-fila">
                    <dt>DNI</dt>
                    <dd><?= htmlspecialchars($dni) ?></dd>
                </div>
                <div class="perfil-dl-fila">
                    <dt>Teléfono</dt>
                    <dd><?= $telefono ? htmlspecialchars($telefono) : '<span class="text-suave">No registrado</span>' ?></dd>
                </div>
                <div class="perfil-dl-fila">
                    <dt>Dirección</dt>
                    <dd><?= $direccion ? htmlspecialchars($direccion) : '<span class="text-suave">No registrada</span>' ?></dd>
                </div>
                <div class="perfil-dl-fila">
                    <dt>Localidad</dt>
                    <dd><?= $localidad ? htmlspecialchars($localidad) : '<span class="text-suave">No registrada</span>' ?></dd>
                </div>
            </dl>
        </div>

        <div class="perfil-seccion">
            <h3 class="perfil-seccion-titulo">Acceso al sistema</h3>
            <dl class="perfil-dl">
                <div class="perfil-dl-fila">
                    <dt>Usuario</dt>
                    <dd><?= htmlspecialchars($username) ?></dd>
                </div>
                <div class="perfil-dl-fila">
                    <dt>Sucursal base</dt>
                    <dd><?= htmlspecialchars($sucursal) ?></dd>
                </div>
                <div class="perfil-dl-fila">
                    <dt>Fecha de alta</dt>
                    <dd><?= $fechaAlta ?></dd>
                </div>
                <div class="perfil-dl-fila">
                    <dt>Estado</dt>
                    <dd>
                        <?php if ($activo): ?>
                            <span class="badge badge-verde">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-gris">Inactivo</span>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </div>

    </div><!-- /perfil-body -->

    <div class="perfil-footer">
        <a href="/LOGITRACK/controllers/EmpleadoController.php" class="btn btn-gris">← Volver al listado</a>
        <a href="/LOGITRACK/controllers/EmpleadoController.php?action=editar&id=<?= (int)$empleado['id_empleado'] ?>"
           class="btn btn-outline">Editar datos</a>
        <a href="/LOGITRACK/controllers/EmpleadoController.php?action=eliminar&id=<?= (int)$empleado['id_empleado'] ?>"
           class="btn btn-rojo"
           onclick="return confirm('¿Eliminar permanentemente a <?= htmlspecialchars(addslashes(($empleado['nombre'] ?? '') . ' ' . ($empleado['apellido'] ?? ''))) ?>?\nEsta acción no se puede deshacer.')">
           Eliminar empleado
        </a>
    </div>

</div><!-- /perfil-card -->

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
