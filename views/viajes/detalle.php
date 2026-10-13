<?php
// views/viajes/detalle.php — Detalle de un viaje con sus envíos asignados
$tituloPagina = 'Detalle de viaje — LogiTrack';

// Leaflet CSS — solo para Admin y Empleado
if (!esChofer()) {
    $extraHead = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>';
}

require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$claseBadgeViaje = [
    'Planificado' => 'badge-gris',
    'En curso'    => 'badge-azul',
    'Demorado'    => 'badge-naranja',
    'Finalizado'  => 'badge-verde',
    'Cancelado'   => 'badge-rojo',
];
$claseViaje = $claseBadgeViaje[$viaje['estado_viaje']] ?? 'badge-gris';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Viaje #<?= $viaje['id_viaje'] ?></h1>
        <p class="page-subtitulo">Patente: <?= htmlspecialchars($viaje['patente']) ?></p>
    </div>
    <div class="flex-gap">
        <?php if (!esChofer()): ?>
        <?php if ($viaje['estado_viaje'] === 'Planificado'): ?>
        <a href="/LOGITRACK/controllers/ViajeController.php?action=gestionarCarga&id_viaje=<?= $viaje['id_viaje'] ?>"
           class="btn-primario">Asignar envíos</a>
        <?php endif; ?>
        <?php if ($viaje['estado_viaje'] === 'En curso'): ?>
        <form method="POST" action="/LOGITRACK/controllers/ViajeController.php?action=finalizar"
              onsubmit="return confirm('¿Confirmar la finalización del viaje? Esta acción no se puede deshacer.')">
            <input type="hidden" name="id_viaje" value="<?= $viaje['id_viaje'] ?>">
            <button type="submit" class="btn btn-verde">Finalizar viaje</button>
        </form>
        <?php endif; ?>
        <?php if ($viaje['estado_viaje'] === 'Finalizado'): ?>
        <a href="/LOGITRACK/controllers/ViajeController.php?action=verificarDescarga&id_viaje=<?= $viaje['id_viaje'] ?>"
           class="btn-primario">Verificar descarga</a>
        <?php endif; ?>
        <a href="/LOGITRACK/controllers/ViajeController.php?action=editar&id=<?= $viaje['id_viaje'] ?>"
           class="btn btn-outline">Editar</a>
        <?php endif; ?>
        <a href="/LOGITRACK/controllers/ViajeController.php" class="btn btn-gris">Volver</a>
    </div>
</div>

<!-- Datos del viaje -->
<div class="form-card mb-3">
    <div class="flex-gap mb-2">
        <h2 class="seccion-titulo" style="border:none;margin:0">Estado:</h2>
        <span class="badge <?= $claseViaje ?>" style="font-size:14px;padding:5px 14px">
            <?= htmlspecialchars($viaje['estado_viaje']) ?>
        </span>
    </div>

    <div class="form-fila">
        <div>
            <p class="text-suave">Origen</p>
            <p><strong><?= htmlspecialchars($viaje['suc_origen_nombre'] ?? '—') ?></strong></p>
        </div>
        <div>
            <p class="text-suave">Destino</p>
            <p><strong><?= htmlspecialchars($viaje['suc_destino_nombre'] ?? '—') ?></strong></p>
        </div>
    </div>

    <div class="form-fila">
        <div>
            <p class="text-suave">Chofer</p>
            <p><strong><?= htmlspecialchars($viaje['chofer_ap'] . ', ' . $viaje['chofer_nombre']) ?></strong></p>
        </div>
        <div>
            <p class="text-suave">Tipo de vehículo</p>
            <p><?= htmlspecialchars($viaje['tipo_vehiculo'] ?? '—') ?></p>
        </div>
    </div>
    <div class="mt-2">
        <p class="text-suave">Fecha de salida</p>
        <p><?= htmlspecialchars($viaje['fecha_salida'] ?? '—') ?></p>
    </div>
</div>

<!-- Envíos asignados -->
<h2 class="seccion-titulo">Envíos en este viaje</h2>

<?php if (empty($envios)): ?>
    <div class="alerta alerta-aviso">Este viaje no tiene envíos asignados.</div>
<?php else: ?>
<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th>N° Tracking</th>
                <th>Remitente</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($envios as $e): ?>
            <tr>
                <td><strong><?= htmlspecialchars($e['nro_tracking']) ?></strong></td>
                <td><?= htmlspecialchars($e['remitente'] . ' ' . $e['remitente_ap']) ?></td>
                <td><?= htmlspecialchars($e['origen']) ?></td>
                <td><?= htmlspecialchars($e['destino']) ?></td>
                <td>
                    <a href="/LOGITRACK/controllers/EnvioController.php?action=detalle&id=<?= $e['id_envio'] ?>"
                       class="btn btn-outline btn-sm">Ver envío</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (!esChofer()): ?>
<!-- Mapa GPS en tiempo real -->
<h2 class="seccion-titulo" style="margin-top:28px;">Ubicación GPS</h2>

<div style="position:relative;">
    <span id="badge-simulado"
          style="display:none; position:absolute; top:10px; right:10px; z-index:1000;
                 background:#c62828; color:#fff; padding:4px 12px; border-radius:20px;
                 font-size:12px; font-weight:600; letter-spacing:.3px;">
        🔴 Ubicación Simulada
    </span>
    <div id="mapaGPS" style="height:350px; border-radius:8px; border:1px solid #d4e0da; overflow:hidden;
                              background:#f4f4f4; display:flex; align-items:center; justify-content:center;">
        <span style="color:var(--texto-suave); font-size:13px;">Cargando mapa…</span>
    </div>
</div>
<p id="gps-meta" style="font-size:11px; color:var(--texto-suave); margin-top:6px; min-height:16px;"></p>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function () {
    var ID_VIAJE  = <?= json_encode((int)$viaje['id_viaje']) ?>;
    var ESTADO    = <?= json_encode($viaje['estado_viaje']) ?>;
    var mapa      = null;
    var marcador  = null;
    var intervalo = null;

    function initMapa(lat, lng) {
        var contenedor = document.getElementById('mapaGPS');

        // Leaflet marca el contenedor con _leaflet_id si ya fue inicializado (aunque sea parcialmente).
        // Si no limpiamos ese flag, el segundo intento tira "Map container is already initialized".
        if (contenedor._leaflet_id) {
            delete contenedor._leaflet_id;
        }

        contenedor.innerHTML = '';
        contenedor.style.cssText = 'height:350px; border-radius:8px; border:1px solid #d4e0da; overflow:hidden;';

        mapa = L.map(contenedor).setView([lat, lng], 10);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(mapa);

        marcador = L.marker([lat, lng])
            .addTo(mapa)
            .bindPopup('<strong>Viaje #' + ID_VIAJE + '</strong>')
            .openPopup();
    }

    function actualizarUI(data) {
        document.getElementById('badge-simulado').style.display =
            data.simulado ? 'block' : 'none';

        var meta = document.getElementById('gps-meta');
        if (data.simulado) {
            meta.textContent = 'Posición calculada por simulación matemática'
                + ' · lat: ' + data.lat.toFixed(5)
                + '  lng: ' + data.lng.toFixed(5);
        } else {
            var txt = 'Última actualización: ' + (data.fecha_hora || '—');
            if (data.velocidad !== null) txt += ' · Velocidad: ' + data.velocidad + ' km/h';
            meta.textContent = txt;
        }

        if (!mapa) {
            try {
                initMapa(data.lat, data.lng);
            } catch (e) {
                console.error('Leaflet initMapa error:', e);
                meta.textContent = 'No se pudo inicializar el mapa. Posición: '
                    + data.lat.toFixed(5) + ', ' + data.lng.toFixed(5);
            }
        } else {
            marcador.setLatLng([data.lat, data.lng]);
        }
    }

    function fetchUbicacion() {
        fetch('/LOGITRACK/api/ubicacion_viaje.php?id_viaje=' + ID_VIAJE)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.lat !== undefined) {
                    actualizarUI(data);
                } else if (!mapa) {
                    document.getElementById('gps-meta').textContent =
                        data.error || 'Sin datos de ubicación disponibles.';
                    if (intervalo) clearInterval(intervalo);
                }
            })
            .catch(function (err) {
                // Solo mostrar error de red si el mapa todavía no cargó
                if (!mapa) {
                    console.error('fetch error:', err);
                    document.getElementById('gps-meta').textContent =
                        'Error de red al obtener la ubicación.';
                }
            });
    }

    // Primera llamada inmediata, luego polling cada 5 s
    fetchUbicacion();
    // Solo hacer polling si el viaje está activo
    if (ESTADO === 'En curso' || ESTADO === 'Demorado') {
        intervalo = setInterval(fetchUbicacion, 5000);
    }
})();
</script>

<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
