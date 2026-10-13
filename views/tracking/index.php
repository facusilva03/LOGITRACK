<?php
$tituloPagina = 'Rastrear envío — LogiTrack';
if ($esSesionActiva):
    require_once __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../layout/sidebar.php';
else: ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastrear envío — LogiTrack</title>
    <link rel="stylesheet" href="/LOGITRACK/assets/css/style.css">
</head>
<body class="public-body">
<?php require_once __DIR__ . '/../layout/public_navbar.php'; ?>
<main class="public-page">
<?php endif; ?>

    <!-- Buscador -->
    <div class="tracking-search-card <?= $esSesionActiva ? 'tracking-search-card-interna' : '' ?>">

        <!-- SVG ruta / waypoints -->
        <div class="tracking-search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 40" width="200" height="50" aria-hidden="true">
                <circle cx="12"  cy="20" r="7" fill="#1a3a2a"/>
                <circle cx="12"  cy="20" r="3" fill="#52b788"/>
                <line x1="19"  y1="20" x2="49"  y2="20" stroke="#dee2e6" stroke-width="3"/>
                <circle cx="55"  cy="20" r="6"  fill="#dee2e6"/>
                <circle cx="55"  cy="20" r="2.5" fill="white"/>
                <line x1="61"  y1="20" x2="91"  y2="20" stroke="#dee2e6" stroke-width="3"/>
                <circle cx="97"  cy="20" r="6"  fill="#dee2e6"/>
                <circle cx="97"  cy="20" r="2.5" fill="white"/>
                <line x1="103" y1="20" x2="133" y2="20" stroke="#dee2e6" stroke-width="3"/>
                <circle cx="140" cy="20" r="8"   fill="#52b788"/>
                <circle cx="140" cy="20" r="3.5" fill="white"/>
                <rect x="63" y="13" width="18" height="9" rx="1.5" fill="#2d6a4f"/>
                <rect x="81" y="16" width="8"  height="6" rx="1"   fill="#1a3a2a"/>
                <circle cx="67" cy="23" r="2.8" fill="#1a3a2a"/>
                <circle cx="77" cy="23" r="2.8" fill="#1a3a2a"/>
                <circle cx="86" cy="23" r="2.8" fill="#1a3a2a"/>
            </svg>
        </div>

        <h1 class="tracking-search-titulo">Rastrear mi envío</h1>
        <p class="tracking-search-desc">
            Ingresá el código de seguimiento para ver el recorrido completo de tu paquete
        </p>

        <form method="GET" action="/LOGITRACK/controllers/TrackingController.php">
            <div class="tracking-input-row">
                <input
                    type="text"
                    name="codigo"
                    placeholder="Ej: LT-A3F8B2C1"
                    value="<?= htmlspecialchars($codigo ?? '') ?>"
                    maxlength="12"
                    required
                >
                <button type="submit" class="btn btn-verde">Buscar</button>
            </div>
        </form>
    </div>

    <!-- Mensaje de error -->
    <?php if (!empty($error)): ?>
        <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Resultado del seguimiento -->
    <?php if (!empty($linea)):
        $primera = $linea[0];
        $ultima  = $linea[count($linea) - 1];
    ?>
    <div class="tracking-result-card">

        <div class="tracking-result-header">
            <div class="tracking-codigo">Código de seguimiento: <?= htmlspecialchars($primera['nro_tracking']) ?></div>
            <div class="tracking-ruta">
                <?= htmlspecialchars($primera['suc_origen']) ?>
                <span class="tracking-ruta-sep">&#8594;</span>
                <?= htmlspecialchars($primera['suc_destino']) ?>
            </div>
            <div class="tracking-partes">
                <?php if ($esSesionActiva): ?>
                <div class="tracking-parte">
                    <strong>Remitente</strong>
                    <?= htmlspecialchars($primera['remitente'] . ' ' . $primera['remitente_ap']) ?>
                </div>
                <div class="tracking-parte">
                    <strong>Destinatario</strong>
                    <?= htmlspecialchars($primera['destinatario'] . ' ' . $primera['destinatario_ap']) ?>
                </div>
                <?php endif; ?>
                <div class="tracking-parte">
                    <strong>Estado actual</strong>
                    <?= htmlspecialchars($ultima['nombre_estado']) ?>
                </div>
            </div>
        </div>

        <div class="timeline-wrap">
            <div class="timeline-encabezado">Historial de seguimiento — <?= count($linea) ?> evento<?= count($linea) !== 1 ? 's' : '' ?></div>
            <ul class="timeline-lista">
                <?php foreach ($linea as $i => $evento):
                    $esUltimo = ($i === count($linea) - 1);
                ?>
                <li class="timeline-item <?= $esUltimo ? 'estado-actual' : '' ?>">
                    <span class="timeline-punto"></span>
                    <div class="timeline-estado">
                        <?= htmlspecialchars($evento['nombre_estado']) ?>
                        <?php if ($esUltimo): ?>
                            <span class="badge badge-verde" style="margin-left:8px;vertical-align:middle;font-size:11px;">Estado actual</span>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-meta">
                        <?php if (!empty($evento['sucursal_actual'])): ?>
                            <?= htmlspecialchars($evento['sucursal_actual']) ?> &mdash;
                        <?php endif; ?>
                        <?= htmlspecialchars(date('d/m/Y H:i', strtotime($evento['fecha_hora']))) ?> hs
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
    <?php endif; ?>

<?php if ($esSesionActiva):
    require_once __DIR__ . '/../layout/footer.php';
else: ?>
</main>
</body>
</html>
<?php endif; ?>
