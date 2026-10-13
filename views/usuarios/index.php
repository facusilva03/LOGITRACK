<?php
// views/usuarios/index.php — Listado de usuarios con filtros
$tituloPagina = 'Usuarios — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<?php
$rolFiltro = trim($_GET['rol'] ?? '');
$tituloRol = [
    'Empleado Sucursal' => 'Empleados de Sucursal',
    'Chofer'            => 'Choferes',
    'Cliente'           => 'Clientes',
    'Administrador'     => 'Administradores',
];
?>
<div class="page-header">
    <div>
        <h1 class="page-titulo">
            <?= $rolFiltro !== '' ? htmlspecialchars($tituloRol[$rolFiltro] ?? $rolFiltro) : 'Usuarios' ?>
        </h1>
        <p class="page-subtitulo">
            <?php if ($rolFiltro !== ''): ?>
                <?= count($usuarios) ?> registro<?= count($usuarios) !== 1 ? 's' : '' ?> encontrado<?= count($usuarios) !== 1 ? 's' : '' ?>
                &nbsp;·&nbsp;
                <a href="/LOGITRACK/controllers/UsuarioController.php" style="color:var(--verde-medio);">Ver todos</a>
            <?php else: ?>
                Gestión de accesos al sistema
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($rolFiltro === 'Empleado Sucursal'): ?>
<!-- Filtro específico para empleados: búsqueda libre, siempre activos -->
<form method="GET" action="/LOGITRACK/controllers/UsuarioController.php" class="filtros-form">
    <input type="hidden" name="action" value="index">
    <input type="hidden" name="rol"    value="Empleado Sucursal">
    <input type="hidden" name="activo" value="1">
    <div class="form-grupo" style="min-width:220px; max-width:420px;">
        <label>Buscar empleado</label>
        <input type="text" name="buscar"
               placeholder="Legajo, nombre, apellido o DNI..."
               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Buscar</button>
    </div>
    <div class="spacer"></div>
    <a href="/LOGITRACK/controllers/UsuarioController.php?action=crear" class="btn-primario">+ Nuevo empleado</a>
</form>
<?php else: ?>
<!-- Filtro genérico: rol, estado, búsqueda libre -->
<form method="GET" action="/LOGITRACK/controllers/UsuarioController.php" class="filtros-form">
    <input type="hidden" name="action" value="index">
    <div class="form-grupo">
        <label>Rol</label>
        <select name="rol">
            <option value="">Todos los roles</option>
            <?php foreach ($roles as $r): ?>
                <option value="<?= htmlspecialchars($r['nombre']) ?>"
                    <?= ($_GET['rol'] ?? '') === $r['nombre'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo">
        <label>Estado</label>
        <select name="activo">
            <option value="">Todos</option>
            <option value="1" <?= ($_GET['activo'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
            <option value="0" <?= ($_GET['activo'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
        </select>
    </div>
    <div class="form-grupo" style="min-width:200px;">
        <label>Buscar</label>
        <input type="text" name="buscar"
               placeholder="Usuario, nombre, apellido, DNI..."
               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    </div>
    <div class="form-grupo">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-outline">Filtrar</button>
    </div>
    <div class="spacer"></div>
    <a href="/LOGITRACK/controllers/UsuarioController.php?action=crear" class="btn-primario">+ Nuevo usuario</a>
</form>
<?php endif; ?>

<div class="tabla-contenedor">
    <?php if (empty($usuarios)): ?>
        <div class="empty-state"><p>No se encontraron usuarios con los filtros aplicados.</p></div>
    <?php elseif ($rolFiltro === 'Empleado Sucursal'): ?>
    <!-- Vista enriquecida de RRHH — Empleados de Sucursal -->
    <table class="tabla tabla-amplia">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Contacto y Residencia</th>
                <th>Sucursal</th>
                <th>Estado / Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <?php
                $nombreCompleto  = trim(($u['nombre_personal'] ?? '') . ' ' . ($u['apellido_personal'] ?? ''));
                $apellidoNombre  = trim(($u['apellido_personal'] ?? '') . ', ' . ($u['nombre_personal'] ?? ''));
                $legajoDisplay   = $u['legajo_personal'] ?? null;
                $dniDisplay      = $u['dni_personal']    ?? null;
                $telDisplay      = $u['telefono_personal']   ?? null;
                $dirDisplay      = $u['direccion_personal']  ?? null;
                $locDisplay      = $u['localidad_personal']  ?? null;
            ?>
            <tr>
                <td>
                    <div class="cell-stack">
                        <span class="cell-primary"><?= htmlspecialchars($apellidoNombre) ?></span>
                        <span class="cell-secondary">
                            <?php if ($legajoDisplay): ?>
                                <strong><?= htmlspecialchars($legajoDisplay) ?></strong>
                            <?php endif; ?>
                            <?php if ($dniDisplay): ?>
                                &nbsp;·&nbsp;DNI <?= htmlspecialchars($dniDisplay) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="cell-stack">
                        <?php if ($telDisplay): ?>
                            <span class="cell-primary"><?= htmlspecialchars($telDisplay) ?></span>
                        <?php else: ?>
                            <span class="cell-secondary">Sin teléfono</span>
                        <?php endif; ?>
                        <?php if ($dirDisplay || $locDisplay): ?>
                            <span class="cell-secondary">
                                <?= htmlspecialchars(implode(' — ', array_filter([$dirDisplay, $locDisplay]))) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['sucursal_nombre'] ?? '—') ?></td>
                <td>
                    <span class="badge badge-verde">Activo</span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <!-- Vista genérica: credenciales para Admins, Clientes, etc. -->
    <table class="tabla">
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u['id_usuario'] ?></td>
                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                <td><?= htmlspecialchars($u['rol']) ?></td>
                <td>
                    <?php if ($u['activo']): ?>
                        <span class="badge badge-verde">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-gris">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="text-suave"><?= htmlspecialchars(substr($u['created_at'] ?? '', 0, 10)) ?></td>
                <td>
                    <div class="tabla-acciones">
                        <a href="/LOGITRACK/controllers/UsuarioController.php?action=editar&id=<?= $u['id_usuario'] ?>"
                           class="btn btn-outline btn-sm">Editar</a>
                        <?php if ($u['id_usuario'] != $_SESSION['usuario_id'] && $u['rol'] !== 'Administrador'): ?>
                            <?php if ($u['activo']): ?>
                                <a href="/LOGITRACK/controllers/UsuarioController.php?action=desactivar&id=<?= $u['id_usuario'] ?>"
                                   class="btn btn-outline btn-sm"
                                   onclick="return confirm('¿Desactivar a <?= htmlspecialchars(addslashes($u['username'])) ?>?')">
                                   Desactivar
                                </a>
                            <?php else: ?>
                                <a href="/LOGITRACK/controllers/UsuarioController.php?action=reactivar&id=<?= $u['id_usuario'] ?>"
                                   class="btn btn-verde btn-sm"
                                   onclick="return confirm('¿Reactivar a <?= htmlspecialchars(addslashes($u['username'])) ?>?')">
                                   Reactivar
                                </a>
                            <?php endif; ?>
                            <a href="/LOGITRACK/controllers/UsuarioController.php?action=eliminar&id=<?= $u['id_usuario'] ?>"
                               class="btn btn-rojo btn-sm"
                               onclick="return confirm('¿Eliminar el acceso del usuario <?= htmlspecialchars(addslashes($u['username'])) ?>?\nEl empleado o chofer asociado NO será eliminado.')">
                               Eliminar acceso
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
