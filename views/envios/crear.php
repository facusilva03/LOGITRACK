<?php
// views/envios/crear.php — Formulario de nuevo envío (cliente: remitente fijo + DNI; empleado/admin: selects)
$tituloPagina = 'Nuevo envío — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Nuevo envío</h1>
        <p class="page-subtitulo">
            <?php if (esCliente()): ?>
                Completá los datos y presentate en la sucursal para confirmar el ingreso
            <?php else: ?>
                El N° de tracking se genera automáticamente al guardar
            <?php endif; ?>
        </p>
    </div>
    <a href="/LOGITRACK/controllers/EnvioController.php" class="btn btn-gris">Volver al listado</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>


<?php
// Agrupar localidades por provincia para los <optgroup>
$localesPorProv = [];
foreach ($localidades as $loc) {
    $localesPorProv[$loc['provincia']][] = $loc;
}
ksort($localesPorProv);
?>

<?php if (esCliente()): ?>
<!-- ═══════════════════════════════════════════════════════════
     FORMULARIO PARA CLIENTES — layout 2 columnas CSS Grid
     ═══════════════════════════════════════════════════════════ -->
<div class="form-card form-card-wide">
    <form method="POST" action="/LOGITRACK/controllers/EnvioController.php?action=guardar">

        <div class="form-grid-2col">

            <!-- ══ COLUMNA IZQUIERDA: Remitente + Ruta y Paquete ══ -->
            <div class="form-col">

                <!-- REMITENTE (solo lectura) -->
                <div>
                    <h2 class="seccion-titulo">Remitente</h2>
                    <?php if ($perfilRemitente): ?>
                        <div class="remitente-fijo">
                            <div>
                                <div class="remitente-fijo-nombre">
                                    <?= htmlspecialchars($perfilRemitente['apellido'] . ', ' . $perfilRemitente['nombre']) ?>
                                </div>
                                <div class="remitente-fijo-doc">
                                    DNI <?= htmlspecialchars($perfilRemitente['documento']) ?>
                                </div>
                            </div>
                            <span class="remitente-fijo-badge">Tu cuenta</span>
                        </div>
                    <?php else: ?>
                        <div class="alerta alerta-error">No se pudo cargar tu perfil. Contactá a soporte.</div>
                    <?php endif; ?>
                </div>

                <!-- RUTA Y PAQUETE -->
                <div class="form-col-sep">
                    <h2 class="seccion-titulo">Ruta y paquete</h2>

                    <div class="form-grupo">
                        <label for="id_suc_origen">Sucursal de origen *</label>
                        <select id="id_suc_origen" name="id_suc_origen" required>
                            <option value="">-- Seleccioná --</option>
                            <?php foreach ($sucursales as $s): ?>
                                <option value="<?= $s['id_sucursal'] ?>"
                                    <?= ($_POST['id_suc_origen'] ?? '') == $s['id_sucursal'] ? 'selected' : '' ?>>
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
                                    <?= ($_POST['id_suc_destino'] ?? '') == $s['id_sucursal'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-grupo">
                        <label for="id_tipo_cont">Tipo de contenido *</label>
                        <select id="id_tipo_cont" name="id_tipo_cont" required>
                            <option value="">-- Seleccioná --</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id_tipo_cont'] ?>"
                                    <?= ($_POST['id_tipo_cont'] ?? '') == $t['id_tipo_cont'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-grupo">
                        <label for="peso_kg">Peso estimado (kg) *</label>
                        <input
                            type="number"
                            id="peso_kg"
                            name="peso_kg"
                            required
                            min="0.1"
                            step="0.01"
                            placeholder="Ej: 2.50"
                            value="<?= htmlspecialchars($_POST['peso_kg'] ?? '') ?>">
                        <small class="form-hint">El empleado verificará el peso real en la sucursal</small>
                    </div>
                </div>

            </div><!-- /form-col izquierda -->

            <!-- ══ COLUMNA DERECHA: Destinatario ══ -->
            <div class="form-col">
                <h2 class="seccion-titulo">Destinatario</h2>

                <div class="form-grupo">
                    <label for="dni_destinatario">DNI *</label>
                    <input
                        type="number"
                        id="dni_destinatario"
                        name="dni_destinatario"
                        required
                        placeholder="Ej: 38271456"
                        min="1000000"
                        max="99999999"
                        value="<?= htmlspecialchars($_POST['dni_destinatario'] ?? '') ?>">
                </div>

                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="nombre_destinatario">Nombre *</label>
                        <input
                            type="text"
                            id="nombre_destinatario"
                            name="nombre_destinatario"
                            required
                            maxlength="100"
                            placeholder="Ej: María"
                            value="<?= htmlspecialchars($_POST['nombre_destinatario'] ?? '') ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="apellido_destinatario">Apellido *</label>
                        <input
                            type="text"
                            id="apellido_destinatario"
                            name="apellido_destinatario"
                            required
                            maxlength="100"
                            placeholder="Ej: González"
                            value="<?= htmlspecialchars($_POST['apellido_destinatario'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="id_localidad_destinatario">Localidad *</label>
                    <select id="id_localidad_destinatario" name="id_localidad_destinatario" required>
                        <option value="">-- Seleccioná --</option>
                        <?php foreach ($localesPorProv as $provincia => $locs): ?>
                            <optgroup label="<?= htmlspecialchars($provincia) ?>">
                                <?php foreach ($locs as $l): ?>
                                    <option value="<?= $l['id_localidad'] ?>"
                                        <?= ($_POST['id_localidad_destinatario'] ?? '') == $l['id_localidad'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($l['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="direccion_destinatario">Dirección exacta *</label>
                    <input
                        type="text"
                        id="direccion_destinatario"
                        name="direccion_destinatario"
                        required
                        maxlength="150"
                        placeholder="Ej: San Martín 123, Piso 2 Dpto B"
                        value="<?= htmlspecialchars($_POST['direccion_destinatario'] ?? '') ?>">
                </div>

                <div class="form-grupo">
                    <label for="codigo_postal_destinatario">Código postal</label>
                    <input
                        type="text"
                        id="codigo_postal_destinatario"
                        name="codigo_postal_destinatario"
                        maxlength="10"
                        placeholder="Ej: 5500"
                        value="<?= htmlspecialchars($_POST['codigo_postal_destinatario'] ?? '') ?>">
                </div>

            </div><!-- /form-col derecha -->

        </div><!-- /form-grid-2col -->

        <!-- AVISO Pre-Admisión (ancho completo) -->
        <div class="alerta alerta-aviso">
            <strong>Pre-Admisión:</strong> Tu solicitud quedará pendiente hasta que la presentes en la sucursal de origen. El empleado confirmará el peso real y pasará el estado a <em>Recibido</em>.
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Solicitar envío</button>
            <a href="/LOGITRACK/controllers/EnvioController.php" class="btn btn-gris">Cancelar</a>
        </div>

    </form>
</div>

<?php else: ?>
<!-- ═══════════════════════════════════════════════════════════
     FORMULARIO PARA EMPLEADOS Y ADMINISTRADORES — layout 2 columnas
     ═══════════════════════════════════════════════════════════ -->
<div class="form-card form-card-wide">
    <form method="POST" action="/LOGITRACK/controllers/EnvioController.php?action=guardar">

        <div class="form-grid-2col">

            <!-- ══ COLUMNA IZQUIERDA: Remitente + Ruta y Paquete ══ -->
            <div class="form-col">

                <h2 class="seccion-titulo">Remitente</h2>

                <div class="form-grupo">
                    <label for="dni_remitente">DNI *</label>
                    <input type="text" id="dni_remitente" name="dni_remitente" required
                           inputmode="numeric" pattern="\d{7,8}" maxlength="8"
                           title="7 u 8 dígitos numéricos sin puntos"
                           placeholder="Ej: 38271456"
                           value="<?= htmlspecialchars($_POST['dni_remitente'] ?? '') ?>">
                </div>

                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="nombre_remitente">Nombre *</label>
                        <input type="text" id="nombre_remitente" name="nombre_remitente" required
                               maxlength="100" placeholder="Ej: Juan"
                               value="<?= htmlspecialchars($_POST['nombre_remitente'] ?? '') ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="apellido_remitente">Apellido *</label>
                        <input type="text" id="apellido_remitente" name="apellido_remitente" required
                               maxlength="100" placeholder="Ej: Pérez"
                               value="<?= htmlspecialchars($_POST['apellido_remitente'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="id_localidad_remitente">Localidad *</label>
                    <select id="id_localidad_remitente" name="id_localidad_remitente" required>
                        <option value="">-- Seleccioná --</option>
                        <?php foreach ($localesPorProv as $provincia => $locs): ?>
                            <optgroup label="<?= htmlspecialchars($provincia) ?>">
                                <?php foreach ($locs as $l): ?>
                                    <option value="<?= $l['id_localidad'] ?>"
                                        <?= ($_POST['id_localidad_remitente'] ?? '') == $l['id_localidad'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($l['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="direccion_remitente">Dirección exacta *</label>
                    <input type="text" id="direccion_remitente" name="direccion_remitente" required
                           maxlength="150" placeholder="Ej: San Martín 123, Piso 2"
                           value="<?= htmlspecialchars($_POST['direccion_remitente'] ?? '') ?>">
                </div>

                <div class="form-col-sep">
                    <h2 class="seccion-titulo">Ruta y paquete</h2>

                    <div class="form-grupo">
                        <label for="id_suc_origen">Sucursal de origen *</label>
                        <select id="id_suc_origen" name="id_suc_origen" required>
                            <option value="">-- Seleccioná --</option>
                            <?php foreach ($sucursales as $s): ?>
                                <option value="<?= $s['id_sucursal'] ?>"
                                    <?= ($_POST['id_suc_origen'] ?? '') == $s['id_sucursal'] ? 'selected' : '' ?>>
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
                                    <?= ($_POST['id_suc_destino'] ?? '') == $s['id_sucursal'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-grupo">
                        <label for="id_tipo_cont">Tipo de contenido *</label>
                        <select id="id_tipo_cont" name="id_tipo_cont" required>
                            <option value="">-- Seleccioná --</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id_tipo_cont'] ?>"
                                    <?= ($_POST['id_tipo_cont'] ?? '') == $t['id_tipo_cont'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-grupo">
                        <label for="peso_kg">Peso (kg) *</label>
                        <input type="number" id="peso_kg" name="peso_kg" required
                               min="0.1" step="0.01" placeholder="Ej: 2.50"
                               value="<?= htmlspecialchars($_POST['peso_kg'] ?? '') ?>">
                    </div>
                </div>

            </div><!-- /form-col izquierda -->

            <!-- ══ COLUMNA DERECHA: Destinatario ══ -->
            <div class="form-col">
                <h2 class="seccion-titulo">Destinatario</h2>

                <div class="form-grupo">
                    <label for="dni_destinatario">DNI *</label>
                    <input type="text" id="dni_destinatario" name="dni_destinatario" required
                           inputmode="numeric" pattern="\d{7,8}" maxlength="8"
                           title="7 u 8 dígitos numéricos sin puntos"
                           placeholder="Ej: 29384756"
                           value="<?= htmlspecialchars($_POST['dni_destinatario'] ?? '') ?>">
                </div>

                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="nombre_destinatario">Nombre *</label>
                        <input type="text" id="nombre_destinatario" name="nombre_destinatario" required
                               maxlength="100" placeholder="Ej: María"
                               value="<?= htmlspecialchars($_POST['nombre_destinatario'] ?? '') ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="apellido_destinatario">Apellido *</label>
                        <input type="text" id="apellido_destinatario" name="apellido_destinatario" required
                               maxlength="100" placeholder="Ej: González"
                               value="<?= htmlspecialchars($_POST['apellido_destinatario'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="id_localidad_destinatario">Localidad *</label>
                    <select id="id_localidad_destinatario" name="id_localidad_destinatario" required>
                        <option value="">-- Seleccioná --</option>
                        <?php foreach ($localesPorProv as $provincia => $locs): ?>
                            <optgroup label="<?= htmlspecialchars($provincia) ?>">
                                <?php foreach ($locs as $l): ?>
                                    <option value="<?= $l['id_localidad'] ?>"
                                        <?= ($_POST['id_localidad_destinatario'] ?? '') == $l['id_localidad'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($l['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="direccion_destinatario">Dirección exacta *</label>
                    <input type="text" id="direccion_destinatario" name="direccion_destinatario" required
                           maxlength="150" placeholder="Ej: San Martín 123, Piso 2 Dpto B"
                           value="<?= htmlspecialchars($_POST['direccion_destinatario'] ?? '') ?>">
                </div>

            </div><!-- /form-col derecha -->

        </div><!-- /form-grid-2col -->

        <p class="form-hint mt-1">
            El número de tracking (<strong>LT-XXXXXXXX</strong>) se genera automáticamente al guardar.
        </p>

        <div class="form-acciones">
            <button type="submit" class="btn btn-verde">Registrar envío</button>
            <a href="/LOGITRACK/controllers/EnvioController.php" class="btn btn-gris">Cancelar</a>
        </div>

    </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
