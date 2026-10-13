<?php
// views/envios/detalle.php — Detalle de un envío con historial de estados
$tituloPagina = 'Detalle de envío — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$claseBadge = [
    'Enviado'                => 'badge-azul',
    'Recibido'               => 'badge-gris',
    'Demorado'               => 'badge-naranja',
    'En camino'              => 'badge-amarillo',
    'En sucursal de entrega' => 'badge-celeste',
    'Entregado'              => 'badge-verde',
    'Devuelto'               => 'badge-rojo',
    'No entregado'           => 'badge-rojo',
];

$estadoActual = $historial[0]['nombre_estado'] ?? 'Sin estado';
$claseActual  = $claseBadge[$estadoActual] ?? 'badge-gris';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo"><?= htmlspecialchars($envio['nro_tracking']) ?></h1>
        <p class="page-subtitulo">Detalle del envío #<?= $envio['id_envio'] ?></p>
    </div>
    <div class="flex-gap">
        <?php if (!esCliente()): ?>
        <a href="/LOGITRACK/controllers/EnvioController.php?action=editar&id=<?= $envio['id_envio'] ?>"
           class="btn btn-outline">Editar</a>
        <?php endif; ?>
        <a href="/LOGITRACK/controllers/EnvioController.php" class="btn btn-gris">Volver</a>
    </div>
</div>

<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>

<!-- Datos del envío -->
<div class="form-card mb-3">
    <div class="flex-gap mb-2">
        <h2 class="seccion-titulo" style="border:none;margin:0">Estado actual:</h2>
        <span class="badge <?= $claseActual ?>" style="font-size:14px;padding:5px 14px">
            <?= htmlspecialchars($estadoActual) ?>
        </span>
    </div>

    <div class="form-fila">
        <div>
            <p class="text-suave">Remitente</p>
            <p><strong><?= htmlspecialchars($envio['remitente'] . ' ' . $envio['remitente_ap']) ?></strong></p>
        </div>
        <div>
            <p class="text-suave">Destinatario</p>
            <p><strong><?= htmlspecialchars($envio['destinatario'] . ' ' . $envio['destinatario_ap']) ?></strong></p>
        </div>
    </div>
    <div class="form-fila mt-2">
        <div>
            <p class="text-suave">Sucursal origen</p>
            <p><?= htmlspecialchars($envio['suc_origen']) ?></p>
        </div>
        <div>
            <p class="text-suave">Sucursal destino</p>
            <p><?= htmlspecialchars($envio['suc_destino']) ?></p>
        </div>
    </div>
    <div class="form-fila mt-2">
        <div>
            <p class="text-suave">Tipo de contenido</p>
            <p><?= htmlspecialchars($envio['tipo_contenido']) ?></p>
        </div>
        <div>
            <p class="text-suave">Peso</p>
            <?php
                $pesoDec  = (float)($envio['peso_kg']   ?? 0);
                $pesoReal = isset($envio['peso_real']) && $envio['peso_real'] !== null
                            ? (float)$envio['peso_real'] : null;
                $diff     = $pesoReal !== null ? round($pesoReal - $pesoDec, 2) : null;
            ?>
            <p>
                <span class="text-suave" style="font-size:12px;">Declarado:</span>
                <strong><?= number_format($pesoDec, 2, ',', '.') ?> kg</strong>
            </p>
            <?php if ($pesoReal !== null): ?>
                <p>
                    <span class="text-suave" style="font-size:12px;">Confirmado:</span>
                    <strong><?= number_format($pesoReal, 2, ',', '.') ?> kg</strong>
                    <?php if ($diff != 0): ?>
                        <span style="font-size:12px; color:<?= $diff > 0 ? '#d9534f' : '#e8a838' ?>;">
                            (<?= $diff > 0 ? '+' : '' ?><?= number_format($diff, 2, ',', '.') ?> kg)
                        </span>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p class="text-suave" style="font-size:12px;">Sin confirmar</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Historial de estados -->
<h2 class="seccion-titulo">Historial de estados</h2>

<?php if (empty($historial)): ?>
    <div class="alerta alerta-aviso">Este envío no tiene estados registrados.</div>
<?php else: ?>
<div class="tabla-contenedor mb-3">
    <table class="tabla">
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Estado</th>
                <th>Sucursal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($historial as $h): ?>
            <tr>
                <td><?= htmlspecialchars($h['fecha_hora']) ?></td>
                <td>
                    <span class="badge <?= $claseBadge[$h['nombre_estado']] ?? 'badge-gris' ?>">
                        <?= htmlspecialchars($h['nombre_estado']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($h['sucursal'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Agregar nuevo estado (admin/empleado) -->
<?php if (!esCliente()): ?>
<div class="form-card">
    <h2 class="seccion-titulo">Registrar nuevo estado</h2>
    <form method="POST" action="/LOGITRACK/controllers/EnvioController.php?action=estado">
        <input type="hidden" name="id_envio" value="<?= $envio['id_envio'] ?>">
        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_estado">Nuevo estado *</label>
                <select id="id_estado" name="id_estado" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($estados as $est): ?>
                        <option value="<?= $est['id_estado'] ?>"><?= htmlspecialchars($est['nombre_estado']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_sucursal_actual">Sucursal actual</label>
                <select id="id_sucursal_actual" name="id_sucursal_actual">
                    <option value="">-- Opcional --</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-fila">
            <div class="form-grupo">
                <label for="peso_real">Peso real confirmado (kg)</label>
                <input type="number" id="peso_real" name="peso_real"
                       min="0.01" step="0.01" placeholder="Ej: 3.50"
                       value="<?= htmlspecialchars($envio['peso_real'] ?? '') ?>">
                <small class="form-hint">Completar al recibir el paquete en sucursal. Reemplaza el valor anterior si ya tenía uno.</small>
            </div>
        </div>
        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Registrar estado</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
