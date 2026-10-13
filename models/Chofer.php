<?php
// models/Chofer.php — Modelo de chofer con PDO (inyección de dependencia)

class Chofer {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT ch.id_chofer, ch.legajo, ch.dni, ch.nombre, ch.apellido,
                       ch.tipo_licencia, s.nombre AS sucursal
                FROM Chofer ch
                JOIN Sucursal s ON s.id_sucursal = ch.id_sucursal
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['id_sucursal'])) {
            $sql .= " AND ch.id_sucursal = ?";
            $params[] = (int)$filtros['id_sucursal'];
        }
        if (!empty($filtros['tipo_licencia'])) {
            $sql .= " AND ch.tipo_licencia = ?";
            $params[] = $filtros['tipo_licencia'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (ch.nombre LIKE ? OR ch.apellido LIKE ? OR ch.legajo LIKE ?)";
            $term = '%' . $filtros['buscar'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY ch.legajo ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Chofer::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT ch.*, s.nombre AS sucursal
                FROM Chofer ch
                JOIN Sucursal s ON s.id_sucursal = ch.id_sucursal
                WHERE ch.id_chofer = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Chofer::getById: " . $e->getMessage());
            return false;
        }
    }

    public function crear(array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Chofer (legajo, dni, nombre, apellido, tipo_licencia, id_sucursal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['legajo'],
                $datos['dni'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['tipo_licencia'],
                (int)$datos['id_sucursal'],
            ]);
        } catch (PDOException $e) {
            error_log("Error en Chofer::crear: " . $e->getMessage());
            return false;
        }
    }

    public function editar(int $id, array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Chofer
                SET legajo = ?, dni = ?, nombre = ?, apellido = ?,
                    tipo_licencia = ?, id_sucursal = ?
                WHERE id_chofer = ?
            ");
            return $stmt->execute([
                $datos['legajo'],
                $datos['dni'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['tipo_licencia'],
                (int)$datos['id_sucursal'],
                $id,
            ]);
        } catch (PDOException $e) {
            error_log("Error en Chofer::editar: " . $e->getMessage());
            return false;
        }
    }

    public function getSucursales(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_sucursal, nombre FROM Sucursal ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Chofer::getSucursales: " . $e->getMessage());
            return [];
        }
    }

    // Conteo de viajes de un chofer (para métricas del dashboard)
    public function contarViajes(int $idChofer): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    COUNT(*) AS total,
                    SUM(estado_viaje = 'Finalizado') AS finalizados
                FROM Viaje WHERE id_chofer = ?
            ");
            $stmt->execute([$idChofer]);
            return $stmt->fetch() ?: ['total' => 0, 'finalizados' => 0];
        } catch (PDOException $e) {
            error_log("Error en Chofer::contarViajes: " . $e->getMessage());
            return ['total' => 0, 'finalizados' => 0];
        }
    }

    // Kilómetros totales recorridos calculados con Haversine entre puntos GPS consecutivos
    public function getKmTotal(int $idChofer): float {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(
                    6371 * 2 * ASIN(SQRT(
                        POWER(SIN(RADIANS((t2.latitud  - t1.latitud)  / 2)), 2) +
                        COS(RADIANS(t1.latitud)) * COS(RADIANS(t2.latitud)) *
                        POWER(SIN(RADIANS((t2.longitud - t1.longitud) / 2)), 2)
                    ))
                ), 0) AS km_total
                FROM Trazabilidad_GPS t1
                JOIN Trazabilidad_GPS t2
                    ON  t1.id_viaje = t2.id_viaje
                    AND t2.id_gps   = (
                        SELECT MIN(g.id_gps)
                        FROM   Trazabilidad_GPS g
                        WHERE  g.id_viaje = t1.id_viaje
                          AND  g.id_gps   > t1.id_gps
                    )
                WHERE t1.id_viaje IN (
                    SELECT id_viaje FROM Viaje WHERE id_chofer = ?
                )
            ");
            $stmt->execute([$idChofer]);
            return round((float)$stmt->fetchColumn(), 1);
        } catch (PDOException $e) {
            error_log("Error en Chofer::getKmTotal: " . $e->getMessage());
            return 0.0;
        }
    }

    // Estadísticas GPS: velocidad promedio, máxima y viaje donde ocurrió la máxima
    public function getStatsGPS(int $idChofer): array {
        try {
            $stmtProm = $this->pdo->prepare("
                SELECT ROUND(AVG(g.velocidad_kmh), 1)
                FROM Trazabilidad_GPS g
                JOIN Viaje v ON v.id_viaje = g.id_viaje
                WHERE v.id_chofer = ?
            ");
            $stmtProm->execute([$idChofer]);
            $velPromedio = (float)($stmtProm->fetchColumn() ?? 0);

            $stmtMax = $this->pdo->prepare("
                SELECT g.velocidad_kmh, g.id_viaje
                FROM Trazabilidad_GPS g
                JOIN Viaje v ON v.id_viaje = g.id_viaje
                WHERE v.id_chofer = ?
                ORDER BY g.velocidad_kmh DESC
                LIMIT 1
            ");
            $stmtMax->execute([$idChofer]);
            $maxRow = $stmtMax->fetch();

            return [
                'vel_promedio' => $velPromedio,
                'vel_maxima'   => $maxRow ? round((float)$maxRow['velocidad_kmh'], 1) : 0.0,
                'viaje_maxima' => $maxRow ? (int)$maxRow['id_viaje'] : null,
            ];
        } catch (PDOException $e) {
            error_log("Error en Chofer::getStatsGPS: " . $e->getMessage());
            return ['vel_promedio' => 0.0, 'vel_maxima' => 0.0, 'viaje_maxima' => null];
        }
    }
}
