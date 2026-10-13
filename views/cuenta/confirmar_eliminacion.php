<?php
// views/cuenta/confirmar_eliminacion.php — Doble confirmación para eliminar cuenta
$tituloPagina = 'Eliminar cuenta — LogiTrack';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-titulo">Eliminar cuenta</h1>
        <p class="page-subtitulo">Esta acción no se puede deshacer</p>
    </div>
    <a href="/LOGITRACK/dashboard.php" class="btn btn-gris">Cancelar</a>
</div>

<!-- Advertencia visual -->
<div class="alerta alerta-error" style="display:flex; gap:12px; align-items:flex-start; font-size:14px;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
         stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top:1px;">
        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    <div>
        <strong>¿Estás seguro/a de que querés eliminar tu cuenta?</strong><br>
        Vas a perder el acceso al sistema y no podrás iniciar sesión nuevamente con este usuario.<br>
        Tus datos personales y el historial de tus envíos quedan guardados en nuestro sistema
        para el seguimiento logístico de tus paquetes.
    </div>
</div>

<div class="form-card" style="max-width:560px; margin-top:24px;">
    <h2 style="font-size:16px; font-weight:600; color:#283d34; margin-bottom:8px;">
        Confirmación final
    </h2>
    <p style="font-size:13px; color:#607a6d; margin-bottom:24px;">
        Al confirmar, tu sesión se cerrará de inmediato y tu cuenta quedará desactivada.
        Si en el futuro querés crear una nueva cuenta, podés hacerlo con el mismo número de documento.
    </p>

    <form method="POST" action="/LOGITRACK/controllers/CuentaController.php?action=ejecutar">
        <div class="form-acciones">
            <button type="submit" class="btn btn-rojo">
                Sí, eliminar mi cuenta definitivamente
            </button>
            <a href="/LOGITRACK/dashboard.php" class="btn btn-gris">No, volver al inicio</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
