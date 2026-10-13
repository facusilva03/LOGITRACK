<?php
// views/layout/public_navbar.php — Barra de navegación para páginas públicas
// Incluir en: login.php, TrackingController, RegistroClienteController

$_urlNav = $_SERVER['REQUEST_URI'];

$_esTracking = strpos($_urlNav, 'TrackingController') !== false;
$_esRegistro = strpos($_urlNav, 'RegistroClienteController') !== false;
$_esLogin    = !$_esTracking && !$_esRegistro;
?>
<nav class="public-navbar">

    <a href="/LOGITRACK/login.php" class="nav-brand">
        <!-- Ícono mini: camión simplificado -->
        <span class="nav-brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 24" width="36" height="24" aria-hidden="true">
                <rect x="1"  y="7"  width="22" height="13" rx="2"   fill="#52b788"/>
                <rect x="23" y="11" width="12" height="9"  rx="1.5" fill="#2d6a4f"/>
                <rect x="25" y="13" width="7"  height="5"  rx="1"   fill="rgba(255,255,255,0.18)"/>
                <circle cx="7"  cy="22" r="3.5" fill="#1a3a2a"/>
                <circle cx="7"  cy="22" r="1.4" fill="#52b788"/>
                <circle cx="18" cy="22" r="3.5" fill="#1a3a2a"/>
                <circle cx="18" cy="22" r="1.4" fill="#52b788"/>
                <circle cx="29" cy="22" r="3.5" fill="#1a3a2a"/>
                <circle cx="29" cy="22" r="1.4" fill="#52b788"/>
            </svg>
        </span>
        <span class="nav-brand-texts">
            <span class="nav-brand-name">LogiTrack</span>
            <span class="nav-brand-sub">Gestión Logística</span>
        </span>
    </a>

    <div class="public-nav-links">
        <a href="/LOGITRACK/controllers/TrackingController.php"
           class="public-nav-link <?= $_esTracking ? 'nav-activo' : '' ?>">
            Rastrear envío
        </a>

        <?php if (!$_esLogin): ?>
        <a href="/LOGITRACK/login.php"
           class="public-nav-link <?= $_esLogin ? 'nav-activo' : '' ?>">
            Iniciar sesión
        </a>
        <?php endif; ?>

        <a href="/LOGITRACK/controllers/RegistroClienteController.php"
           class="public-nav-link nav-cta <?= $_esRegistro ? 'nav-activo' : '' ?>">
            Crear cuenta
        </a>
    </div>

</nav>
