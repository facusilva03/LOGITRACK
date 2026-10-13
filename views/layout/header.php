<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'LogiTrack') ?></title>
    <link rel="stylesheet" href="/LOGITRACK/assets/css/style.css">
    <?php echo $extraHead ?? ''; ?>
</head>
<body>

<!-- Barra superior: marca + usuario + cerrar sesión -->
<header class="topbar">
    <div class="topbar-brand">
        <!-- Truck SVG icon -->
        <svg class="topbar-brand-truck" width="28" height="20" viewBox="0 0 28 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="0.5" y="1.5" width="15" height="11" rx="2" fill="#52b788"/>
            <path d="M15.5 4 H21.5 L25.5 9 V14 H15.5 V4Z" fill="#52b788"/>
            <rect x="17" y="5.5" width="5" height="3.5" rx="0.7" fill="rgba(26,58,42,0.45)"/>
            <circle cx="5" cy="17" r="3" fill="#52b788" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
            <circle cx="5" cy="17" r="1.2" fill="rgba(26,58,42,0.4)"/>
            <circle cx="21.5" cy="17" r="3" fill="#52b788" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
            <circle cx="21.5" cy="17" r="1.2" fill="rgba(26,58,42,0.4)"/>
        </svg>
        <div class="topbar-brand-texto">
            <span class="topbar-brand-nombre">LogiTrack</span>
            <span class="topbar-brand-sub">Gestión Logística</span>
        </div>
    </div>
    <div class="topbar-acciones">
        <?php
            $displayNombre = $_SESSION['nombre_real'] ?? $_SESSION['username'] ?? '?';
            $inicialAvatar = strtoupper(mb_substr($displayNombre, 0, 1));
        ?>
        <a href="/LOGITRACK/logout.php" class="topbar-logout">Cerrar sesión</a>
        <div class="topbar-sep"></div>
        <div class="topbar-usuario">
            <div class="topbar-info">
                <span class="topbar-nombre"><?= htmlspecialchars($displayNombre) ?></span>
                <span class="topbar-rol"><?= htmlspecialchars($_SESSION['rol']) ?></span>
            </div>
            <div class="topbar-avatar"><?= $inicialAvatar ?></div>
        </div>
    </div>
</header>

<div class="layout">
