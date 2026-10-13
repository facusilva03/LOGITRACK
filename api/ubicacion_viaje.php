<?php
// api/ubicacion_viaje.php
// ob_start() captura cualquier notice/warning de PHP antes de que corrompa el JSON
ob_start();

session_start();

$rolPermitido = $_SESSION['rol'] ?? '';
if (
    empty($_SESSION['usuario_id']) ||
    !in_array($rolPermitido, ['Administrador', 'Empleado Sucursal'], true)
) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Descartar cualquier output espurio antes de enviar JSON
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$idViaje = (int)($_GET['id_viaje'] ?? 0);
if ($idViaje <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'id_viaje inválido']);
    exit;
}

// ── 1. GPS REAL ───────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        SELECT latitud, longitud, velocidad_kmh, fecha_hora
        FROM Trazabilidad_GPS
        WHERE id_viaje = ?
        ORDER BY fecha_hora DESC
        LIMIT 1
    ");
    $stmt->execute([$idViaje]);
    $gps = $stmt->fetch();

    if ($gps && $gps['latitud'] !== null) {
        echo json_encode([
            'lat'        => (float)$gps['latitud'],
            'lng'        => (float)$gps['longitud'],
            'velocidad'  => $gps['velocidad_kmh'] !== null ? (int)$gps['velocidad_kmh'] : null,
            'fecha_hora' => $gps['fecha_hora'],
            'simulado'   => false,
        ]);
        exit;
    }
} catch (PDOException $e) {
    error_log("api/ubicacion_viaje GPS: " . $e->getMessage());
}

// ── 2. FALLBACK: simulación desde sucursal de origen ─────────────
try {
    $stmt = $pdo->prepare("
        SELECT s.latitud, s.longitud, v.fecha_salida
        FROM Viaje v
        JOIN Sucursal s ON s.id_sucursal = v.id_suc_origen
        WHERE v.id_viaje = ?
    ");
    $stmt->execute([$idViaje]);
    $origen = $stmt->fetch();

    if (!$origen || $origen['latitud'] === null) {
        echo json_encode(['error' => 'Sin coordenadas de origen disponibles']);
        exit;
    }

    $latOrigen = (float)$origen['latitud'];
    $lngOrigen = (float)$origen['longitud'];
    $segundos  = max(0, time() - strtotime($origen['fecha_salida'] ?? 'now'));

    $offset_lat = sin($segundos / 300) * 0.08 + ($segundos / 86400) * 0.15;
    $offset_lng = cos($segundos / 400) * 0.06 + ($segundos / 86400) * 0.10;

    echo json_encode([
        'lat'        => round($latOrigen + $offset_lat, 8),
        'lng'        => round($lngOrigen + $offset_lng, 8),
        'velocidad'  => null,
        'fecha_hora' => null,
        'simulado'   => true,
    ]);
} catch (PDOException $e) {
    error_log("api/ubicacion_viaje simulacion: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
