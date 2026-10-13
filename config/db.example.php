<?php
// config/db.php — Copiar este archivo a config/db.php y completar con tus credenciales

define('DB_HOST', 'localhost');
define('DB_NAME', 'LOGITRACK');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
