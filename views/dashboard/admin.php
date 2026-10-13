<?php
$tituloPagina = 'Panel de Administración — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

?>

<!-- ══════════════════════════════════════════════════════
     ENCABEZADO
══════════════════════════════════════════════════════ -->
<div class="page-header">
    <div>
        <h1 class="page-titulo">Panel de Administración</h1>
        <p class="page-subtitulo">Resumen operativo en tiempo real · <?= date('d/m/Y') ?></p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 1 — RECURSOS HUMANOS
══════════════════════════════════════════════════════ -->
<div class="dash-seccion-titulo">RECURSOS HUMANOS</div>
<div class="kpi-grid kpi-grid-3">

    <a href="/LOGITRACK/controllers/UsuarioController.php" class="kpi-card kpi-card-link oscuro">
        <div class="kpi-valor"><?= $kpis['usuarios_total'] ?></div>
        <div class="kpi-etiqueta">Usuarios registrados</div>
    </a>

    <a href="/LOGITRACK/controllers/UsuarioController.php?rol=Empleado+Sucursal" class="kpi-card kpi-card-link">
        <div class="kpi-valor"><?= $kpis['empleados'] ?></div>
        <div class="kpi-etiqueta">Empleados de Sucursal</div>
    </a>

    <a href="/LOGITRACK/controllers/ChoferController.php" class="kpi-card kpi-card-link medio">
        <div class="kpi-valor"><?= $kpis['choferes'] ?></div>
        <div class="kpi-etiqueta">Choferes activos</div>
    </a>

</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 2 — OPERACIONES: CICLO DE ENVÍOS
══════════════════════════════════════════════════════ -->
<div class="dash-seccion-titulo">OPERACIONES — CICLO DE ENVÍOS</div>
<div class="kpi-grid kpi-grid-4">

    <a href="/LOGITRACK/controllers/EnvioController.php" class="kpi-card kpi-card-link naranja">
        <div class="kpi-valor"><?= $kpis['envios_activos'] ?></div>
        <div class="kpi-etiqueta">Envíos activos</div>
    </a>

    <a href="/LOGITRACK/controllers/EnvioController.php?id_estado=4" class="kpi-card kpi-card-link">
        <div class="kpi-valor"><?= $kpis['envios_en_curso'] ?></div>
        <div class="kpi-etiqueta">En camino (arriba del camión)</div>
    </a>

    <a href="/LOGITRACK/controllers/EnvioController.php?id_estado=5" class="kpi-card kpi-card-link medio">
        <div class="kpi-valor"><?= $kpis['envios_completados'] ?></div>
        <div class="kpi-etiqueta">Completados / Entregados</div>
    </a>

    <a href="/LOGITRACK/controllers/EnvioController.php" class="kpi-card kpi-card-link oscuro">
        <div class="kpi-valor"><?= $kpis['envios_total'] ?></div>
        <div class="kpi-etiqueta">Paquetes totales registrados</div>
    </a>

</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 3 — FLOTA Y ALERTAS
══════════════════════════════════════════════════════ -->
<div class="dash-seccion-titulo">FLOTA Y ALERTAS</div>
<div class="dash-flota-grid">

    <!-- Viajes en curso — tarjeta grande izquierda -->
    <a href="/LOGITRACK/controllers/ViajeController.php?estado_viaje=En+curso" class="kpi-card kpi-card-link kpi-card-grande oscuro">
        <div class="kpi-grande-icono">
            <svg width="48" height="34" viewBox="0 0 48 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="0.5" y="2" width="28" height="20" rx="3" fill="#52b788"/>
                <path d="M28.5 7 H38 L46 19 V28 H28.5 V7Z" fill="#52b788"/>
                <rect x="30" y="10" width="9" height="6" rx="1" fill="rgba(26,58,42,0.4)"/>
                <circle cx="9"  cy="30" r="4" fill="#52b788" stroke="#1a3a2a" stroke-width="2"/>
                <circle cx="37" cy="30" r="4" fill="#52b788" stroke="#1a3a2a" stroke-width="2"/>
            </svg>
        </div>
        <div class="kpi-valor kpi-valor-grande"><?= $kpis['viajes_en_curso'] ?></div>
        <div class="kpi-etiqueta">Viajes en curso</div>
    </a>

    <!-- Grid de 4 alertas a la derecha -->
    <div class="dash-alertas-grid">

        <a href="/LOGITRACK/controllers/EnvioController.php?id_estado=6" class="kpi-card kpi-card-link kpi-card-alerta alerta-rojo">
            <div class="kpi-valor kpi-valor-alerta"><?= $kpis['devueltos'] ?></div>
            <div class="kpi-etiqueta">Devueltos</div>
        </a>

        <a href="/LOGITRACK/controllers/EnvioController.php?id_estado=3" class="kpi-card kpi-card-link kpi-card-alerta alerta-amarillo">
            <div class="kpi-valor kpi-valor-alerta"><?= $kpis['demorados'] ?></div>
            <div class="kpi-etiqueta">Demorados</div>
        </a>

        <a href="/LOGITRACK/controllers/IncidenteController.php" class="kpi-card kpi-card-link kpi-card-alerta alerta-rojo-fuerte">
            <div class="kpi-valor kpi-valor-alerta"><?= $kpis['incidentes_total'] ?></div>
            <div class="kpi-etiqueta">Incidentes</div>
        </a>

        <a href="/LOGITRACK/controllers/ViajeController.php?estado_viaje=Finalizado" class="kpi-card kpi-card-link kpi-card-alerta alerta-verde">
            <div class="kpi-valor kpi-valor-alerta"><?= $kpis['viajes_finalizados_admin'] ?></div>
            <div class="kpi-etiqueta">Viajes finalizados</div>
        </a>

    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     TABLA INFERIOR — ÚLTIMOS SINIESTROS E INCIDENTES
══════════════════════════════════════════════════════ -->
<div class="dash-seccion-titulo" style="margin-top: 12px;">ÚLTIMOS SINIESTROS E INCIDENTES</div>
<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Viaje / Patente</th>
                <th>Chofer</th>
                <th>Tipo de incidente</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($datos['ultimos_incidentes'])): ?>
            <tr>
                <td colspan="6" style="text-align:center; color:var(--texto-suave); padding: 24px;">
                    Sin incidentes registrados.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($datos['ultimos_incidentes'] as $inc): ?>
            <tr>
                <td><?= date('d/m/Y H:i', strtotime($inc['fecha_hora'])) ?></td>
                <td>
                    <a href="/LOGITRACK/controllers/ViajeController.php?action=detalle&id=<?= (int)$inc['id_viaje'] ?>"
                       style="color:var(--verde-medio); font-weight:600;">
                        Viaje #<?= $inc['id_viaje'] ?>
                    </a>
                    <span style="color:var(--texto-suave); font-size:12px;"> · <?= htmlspecialchars($inc['patente']) ?></span>
                </td>
                <td><?= htmlspecialchars($inc['chofer_ap'] . ', ' . $inc['chofer_nombre']) ?></td>
                <td><?= htmlspecialchars($inc['tipo_incidente']) ?></td>
                <td style="max-width:300px; white-space:normal; font-size:13px; color:var(--texto-suave);">
                    <?= htmlspecialchars(mb_strimwidth($inc['descripcion'], 0, 80, '…')) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
