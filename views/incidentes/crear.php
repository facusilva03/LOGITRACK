<?php
$tituloPagina = 'Registrar incidente — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Registrar incidente</h1>
        <p class="page-subtitulo">Completá los datos del incidente ocurrido durante el viaje</p>
    </div>
    <a href="/LOGITRACK/controllers/IncidenteController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">

    <?php if (empty($viajesEnCurso)): ?>
        <div class="alerta alerta-error">
            No hay viajes en curso
            <?= esChofer() ? 'asignados a tu usuario' : '' ?>.
            Solo se pueden registrar incidentes sobre viajes activos.
        </div>
        <div class="form-acciones">
            <a href="/LOGITRACK/controllers/IncidenteController.php" class="btn btn-gris">Volver al listado</a>
        </div>
    <?php else: ?>

    <form method="POST" action="/LOGITRACK/controllers/IncidenteController.php?action=guardar">

        <!-- Viaje -->
        <div class="form-grupo">
            <label for="id_viaje">Viaje *</label>
            <select id="id_viaje" name="id_viaje" required>
                <option value="">— Seleccioná un viaje en curso —</option>
                <?php foreach ($viajesEnCurso as $v): ?>
                    <option value="<?= (int)$v['id_viaje'] ?>"
                        <?= (int)($_POST['id_viaje'] ?? 0) === (int)$v['id_viaje'] ? 'selected' : '' ?>>
                        Viaje #<?= (int)$v['id_viaje'] ?>
                        — <?= htmlspecialchars($v['patente']) ?>
                        <?php if (!empty($v['chofer_nombre'])): ?>
                            — <?= htmlspecialchars($v['chofer_ap'] . ', ' . $v['chofer_nombre']) ?>
                        <?php endif; ?>
                        (salida: <?= date('d/m/Y', strtotime($v['fecha_salida'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Descripción del incidente -->
        <div class="form-grupo">
            <label for="tipo_incidente">¿Qué ocurrió? *</label>
            <input type="text" id="tipo_incidente" name="tipo_incidente" required
                   maxlength="50"
                   placeholder="Ej: Rueda pinchada desvío por inundación, frenada brusca..."
                   value="<?= htmlspecialchars($_POST['tipo_incidente'] ?? '') ?>">
            <small class="form-hint">Describí el incidente en pocas palabras (máx. 50 caracteres).</small>
        </div>

        <div class="form-grupo">
            <label for="descripcion">Detalle adicional *</label>
            <textarea id="descripcion" name="descripcion" rows="5" required
                      placeholder="Describí con más detalle qué pasó, el lugar, las circunstancias y si hubo consecuencias..."
                      style="resize:vertical;"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Registrar incidente</button>
            <a href="/LOGITRACK/controllers/IncidenteController.php" class="btn btn-gris">Cancelar</a>
        </div>
    </form>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
