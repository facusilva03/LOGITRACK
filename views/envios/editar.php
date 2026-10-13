<?php
// views/envios/editar.php — Formulario para editar un envío existente
$tituloPagina = 'Editar envío — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Editar envío</h1>
        <p class="page-subtitulo">Tracking: <?= htmlspecialchars($envio['nro_tracking']) ?></p>
    </div>
    <a href="/LOGITRACK/controllers/EnvioController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="/LOGITRACK/controllers/EnvioController.php?action=actualizar">
        <input type="hidden" name="id_envio" value="<?= $envio['id_envio'] ?>">

        <h2 class="seccion-titulo">Partes del envío</h2>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_remitente">Remitente *</label>
                <select id="id_remitente" name="id_remitente" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>"
                            <?= ($_POST['id_remitente'] ?? $envio['id_remitente']) == $c['id_cliente'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre'] . ' — ' . $c['documento']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_destinatario">Destinatario *</label>
                <select id="id_destinatario" name="id_destinatario" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>"
                            <?= ($_POST['id_destinatario'] ?? $envio['id_destinatario']) == $c['id_cliente'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre'] . ' — ' . $c['documento']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h2 class="seccion-titulo mt-2">Ruta</h2>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="id_suc_origen">Sucursal de origen *</label>
                <select id="id_suc_origen" name="id_suc_origen" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>"
                            <?= ($_POST['id_suc_origen'] ?? $envio['id_suc_origen']) == $s['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="id_suc_destino">Sucursal de destino *</label>
                <select id="id_suc_destino" name="id_suc_destino" required>
                    <option value="">-- Seleccioná --</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>"
                            <?= ($_POST['id_suc_destino'] ?? $envio['id_suc_destino']) == $s['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-grupo">
            <label for="id_tipo_cont">Tipo de contenido *</label>
            <select id="id_tipo_cont" name="id_tipo_cont" required>
                <option value="">-- Seleccioná --</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= $t['id_tipo_cont'] ?>"
                        <?= ($_POST['id_tipo_cont'] ?? $envio['id_tipo_cont']) == $t['id_tipo_cont'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Guardar cambios</button>
            <a href="/LOGITRACK/controllers/EnvioController.php?action=detalle&id=<?= $envio['id_envio'] ?>"
               class="btn btn-outline">Ver detalle</a>
            <a href="/LOGITRACK/controllers/EnvioController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
