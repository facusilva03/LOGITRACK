<?php
// views/dashboard/chofer.php — Dashboard del rol Chofer
$tituloPagina = 'Mi Panel — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$perfil = $datos['perfil_chofer'] ?? null;
$vc     = $datos['viaje_en_curso']   ?: false;
$hist   = $datos['historial_viajes'] ?? [];

$nombreMostrar = $perfil
    ? htmlspecialchars($perfil['apellido'] . ', ' . $perfil['nombre'])
    : htmlspecialchars($_SESSION['username']);

$badgeEstado = [
    'En curso'    => 'badge-azul',
    'Finalizado'  => 'badge-verde',
    'Planificado' => 'badge-gris',
    'Cancelado'   => 'badge-rojo',
];

// TODO: Para activar la sección económica crear tabla pagos_chofer con columnas:
// id, id_chofer, id_viaje, monto, fecha_pago, concepto
// Luego reemplazar los $0 con queries reales.
?>

<!-- ══════════════════════════════════════════════════════
     ENCABEZADO
══════════════════════════════════════════════════════ -->
<div class="page-header">
    <div>
        <h1 class="page-titulo">Mi Panel</h1>
        <p class="page-subtitulo">
            <?= $nombreMostrar ?>
            <?php if ($perfil): ?>
                &nbsp;&middot;&nbsp; Legajo <?= htmlspecialchars($perfil['legajo'] ?? '') ?>
                &nbsp;&middot;&nbsp; Licencia <?= htmlspecialchars($perfil['tipo_licencia'] ?? '') ?>
                &nbsp;&middot;&nbsp; <?= htmlspecialchars($perfil['sucursal'] ?? '') ?>
            <?php endif; ?>
            &nbsp;&middot;&nbsp; <?= date('d/m/Y') ?>
        </p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 1 — MIS VIAJES
══════════════════════════════════════════════════════ -->
<div class="ch-seccion-titulo">Mis viajes</div>
<div class="ch-grid-4">

    <!-- Viajes realizados -->
    <div class="ch-card">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero"><?= (int)$kpis['total_viajes'] ?></div>
                <div class="ch-card-label">Viajes realizados</div>
            </div>
            <div class="ch-card-icon ch-icon-verde">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13" rx="2"/>
                    <path d="M16 8h4l3 5v4h-7V8z"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Viajes finalizados -->
    <div class="ch-card">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero"><?= (int)$kpis['viajes_finalizados'] ?></div>
                <div class="ch-card-label">Viajes finalizados</div>
            </div>
            <div class="ch-card-icon ch-icon-verde">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </div>
        <?php if ($kpis['total_viajes'] > 0): ?>
            <div class="ch-card-sub">
                <?= round($kpis['viajes_finalizados'] / $kpis['total_viajes'] * 100) ?>% de completitud
            </div>
        <?php endif; ?>
    </div>

    <!-- Incidentes -->
    <div class="ch-card" style="border-top-color: #d9534f;">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero"><?= (int)$kpis['incidentes'] ?></div>
                <div class="ch-card-label">Incidentes registrados</div>
            </div>
            <div class="ch-card-icon ch-icon-rojo">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Km recorridos -->
    <div class="ch-card" style="border-top-color: #e8a838;">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero"><?= number_format($kpis['km_total'], 1, ',', '.') ?></div>
                <div class="ch-card-label">Kilómetros recorridos</div>
            </div>
            <div class="ch-card-icon ch-icon-naranja">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="ch-card-sub">km totales acumulados</div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 2 — ESTADÍSTICAS GPS
══════════════════════════════════════════════════════ -->
<div class="ch-seccion-titulo">Estadísticas GPS</div>
<div class="ch-grid-3">

    <!-- Velocidad promedio -->
    <div class="ch-card">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero"><?= number_format($kpis['vel_promedio'], 1, ',', '.') ?></div>
                <div class="ch-card-label">Velocidad promedio</div>
            </div>
            <div class="ch-card-icon ch-icon-verde">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2C6.48 2 2 6.48 2 12"/>
                    <path d="M12 2c5.52 0 10 4.48 10 10"/>
                    <line x1="12" y1="12" x2="16.5" y2="7.5"/>
                    <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                </svg>
            </div>
        </div>
        <div class="ch-card-sub">km/h promedio en todos los viajes</div>
    </div>

    <!-- Velocidad máxima -->
    <div class="ch-card" style="border-top-color: #e8a838;">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero"><?= number_format($kpis['vel_maxima'], 1, ',', '.') ?></div>
                <div class="ch-card-label">Velocidad máxima</div>
            </div>
            <div class="ch-card-icon ch-icon-naranja">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="13 17 18 12 13 7"/>
                    <polyline points="6 17 11 12 6 7"/>
                </svg>
            </div>
        </div>
        <?php if ($kpis['viaje_vel_maxima']): ?>
            <div class="ch-card-sub">km/h · Viaje #<?= (int)$kpis['viaje_vel_maxima'] ?></div>
        <?php else: ?>
            <div class="ch-card-sub">km/h registrados</div>
        <?php endif; ?>
    </div>

    <!-- Km promedio por viaje -->
    <div class="ch-card">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero"><?= number_format($kpis['km_promedio_viaje'], 1, ',', '.') ?></div>
                <div class="ch-card-label">Km promedio por viaje</div>
            </div>
            <div class="ch-card-icon ch-icon-verde">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6"  y1="20" x2="6"  y2="14"/>
                </svg>
            </div>
        </div>
        <div class="ch-card-sub">km totales ÷ cantidad de viajes</div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 3 — INFORMACIÓN ECONÓMICA (pendiente)
══════════════════════════════════════════════════════ -->
<div class="ch-seccion-titulo">Información económica</div>
<div class="ch-badge-info">
    &#8505;&#65039; Requiere tabla <code>pagos_chofer</code> &mdash; mostrando datos de ejemplo
</div>
<div class="ch-grid-3">

    <!-- Ganado este mes -->
    <div class="ch-card ch-card-eco" style="border-top-color: #7b9cda;">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero">$0</div>
                <div class="ch-card-label">Ganado este mes</div>
            </div>
            <div class="ch-card-icon ch-icon-azul">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Por viaje promedio -->
    <div class="ch-card ch-card-eco" style="border-top-color: #7b9cda;">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero">$0</div>
                <div class="ch-card-label">Por viaje promedio</div>
            </div>
            <div class="ch-card-icon ch-icon-azul">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Ganado histórico -->
    <div class="ch-card ch-card-eco" style="border-top-color: #7b9cda;">
        <div class="ch-card-header">
            <div>
                <div class="ch-card-numero">$0</div>
                <div class="ch-card-label">Ganado histórico</div>
            </div>
            <div class="ch-card-icon ch-icon-azul">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 4 — VIAJE ACTUAL
══════════════════════════════════════════════════════ -->
<div class="ch-seccion-titulo">Viaje actual</div>

<?php if ($vc): ?>

    <div class="ch-viaje-actual">
        <div class="ch-viaje-meta">
            <strong style="color:#283d34; font-size:14px;">Viaje #<?= (int)$vc['id_viaje'] ?></strong>
            <span style="color:#607a6d; font-size:13px;">&middot; <?= htmlspecialchars($vc['patente']) ?></span>
            <span class="badge badge-azul">En curso</span>
        </div>

        <div class="ch-viaje-fila">

            <!-- Fecha salida -->
            <div>
                <div class="ch-viaje-dato-label">Fecha salida</div>
                <div class="ch-viaje-dato-valor">
                    <?= $vc['fecha_salida'] ? date('d/m/Y', strtotime($vc['fecha_salida'])) : '—' ?>
                </div>
                <div class="ch-viaje-dato-sub">
                    <?= $vc['fecha_salida'] ? date('H:i', strtotime($vc['fecha_salida'])) : '' ?>
                </div>
            </div>

            <!-- Velocidad actual -->
            <div>
                <div class="ch-viaje-dato-label">Velocidad actual</div>
                <div class="ch-viaje-dato-valor">
                    <?= $vc['vel_actual'] !== null
                        ? number_format((float)$vc['vel_actual'], 1, ',', '.') . ' km/h'
                        : '—' ?>
                </div>
                <div class="ch-viaje-dato-sub">último registro GPS</div>
            </div>

            <!-- Km recorridos en este viaje -->
            <div>
                <div class="ch-viaje-dato-label">Km recorridos</div>
                <div class="ch-viaje-dato-valor">
                    <?= number_format((float)$vc['km_recorridos'], 1, ',', '.') ?> km
                </div>
                <div class="ch-viaje-dato-sub">en este viaje</div>
            </div>

            <!-- Último punto GPS -->
            <div>
                <div class="ch-viaje-dato-label">Último punto GPS</div>
                <?php if ($vc['ultimo_gps_fecha']): ?>
                    <?php
                        $segs = time() - strtotime($vc['ultimo_gps_fecha']);
                        if ($segs < 60) {
                            $tiempoStr = 'hace menos de 1 min';
                        } elseif ($segs < 3600) {
                            $tiempoStr = 'hace ' . (int)floor($segs / 60) . ' min';
                        } else {
                            $tiempoStr = 'hace ' . (int)floor($segs / 3600) . ' h';
                        }
                    ?>
                    <div class="ch-viaje-dato-valor" style="font-size:13px;"><?= $tiempoStr ?></div>
                    <div class="ch-viaje-dato-sub">
                        <?= date('H:i', strtotime($vc['ultimo_gps_fecha'])) ?>
                    </div>
                <?php else: ?>
                    <div class="ch-viaje-dato-valor">—</div>
                    <div class="ch-viaje-dato-sub">sin registros GPS</div>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php else: ?>

    <div class="ch-no-viaje">
        No tenés un viaje activo en este momento.
    </div>

<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 5 — HISTORIAL DE VIAJES
══════════════════════════════════════════════════════ -->
<div class="ch-seccion-titulo">Historial de viajes</div>

<div class="ch-tabla-wrap">
    <table class="ch-tabla">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>N° Viaje</th>
                <th>Patente</th>
                <th>Km recorridos</th>
                <th>Vel. promedio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($hist)): ?>
            <tr>
                <td colspan="6" style="text-align:center; color:#607a6d; padding:24px;">
                    Sin viajes registrados.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($hist as $v):
                $badgeClass = $badgeEstado[$v['estado_viaje']] ?? 'badge-gris';
            ?>
            <tr>
                <td><?= $v['fecha_salida'] ? date('d/m/Y', strtotime($v['fecha_salida'])) : '—' ?></td>
                <td style="font-weight:600;">#<?= (int)$v['id_viaje'] ?></td>
                <td><?= htmlspecialchars($v['patente']) ?></td>
                <td><?= number_format((float)$v['km_recorridos'], 1, ',', '.') ?> km</td>
                <td>
                    <?= $v['vel_promedio'] !== null
                        ? number_format((float)$v['vel_promedio'], 1, ',', '.') . ' km/h'
                        : '—' ?>
                </td>
                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($v['estado_viaje']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
