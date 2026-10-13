<?php
// models/Incidente.php — Modelo de incidentes de viaje

class Incidente {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Inserta un incidente en la BD.
     * El trigger trg_incidente_demora propaga el estado "Demorado"
     * a todos los envíos del viaje automáticamente — no hay lógica aquí.
     */
    public function registrar(int $idViaje, string $tipoIncidente, string $descripcion): bool {
        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare("
                INSERT INTO Incidente (id_viaje, tipo_incidente, descripcion)
                VALUES (?, ?, ?)
            ")->execute([$idViaje, $tipoIncidente, $descripcion]);

            // El viaje pasa a Demorado; el trigger trg_incidente_demora ya maneja los envíos
            $this->pdo->prepare("
                UPDATE Viaje SET estado_viaje = 'Demorado'
                WHERE id_viaje = ? AND estado_viaje = 'En curso'
            ")->execute([$idViaje]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Error en Incidente::registrar: " . $e->getMessage());
            throw new RuntimeException("No se pudo registrar el incidente.");
        }
    }

    public function getByViaje(int $idViaje): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id_incidente, tipo_incidente, descripcion, fecha_hora
                FROM Incidente
                WHERE id_viaje = ?
                ORDER BY fecha_hora DESC
            ");
            $stmt->execute([$idViaje]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Incidente::getByViaje: " . $e->getMessage());
            return [];
        }
    }

    public function getViajesEnCurso(int $idChofer = 0): array {
        try {
            if ($idChofer > 0) {
                $stmt = $this->pdo->prepare("
                    SELECT v.id_viaje, v.patente, v.fecha_salida
                    FROM Viaje v
                    WHERE v.estado_viaje = 'En curso'
                      AND v.id_chofer = ?
                    ORDER BY v.fecha_salida DESC
                ");
                $stmt->execute([$idChofer]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT v.id_viaje, v.patente, v.fecha_salida,
                           ch.nombre AS chofer_nombre, ch.apellido AS chofer_ap
                    FROM Viaje v
                    JOIN Chofer ch ON ch.id_chofer = v.id_chofer
                    WHERE v.estado_viaje = 'En curso'
                    ORDER BY v.fecha_salida DESC
                ");
                $stmt->execute();
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Incidente::getViajesEnCurso: " . $e->getMessage());
            return [];
        }
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT i.id_incidente, i.tipo_incidente, i.descripcion, i.fecha_hora,
                       v.id_viaje, v.patente,
                       ch.nombre AS chofer_nombre, ch.apellido AS chofer_ap
                FROM Incidente i
                JOIN Viaje v  ON v.id_viaje   = i.id_viaje
                JOIN Chofer ch ON ch.id_chofer = v.id_chofer
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['tipo'])) {
            $sql .= " AND i.tipo_incidente LIKE ?";
            $params[] = '%' . $filtros['tipo'] . '%';
        }

        if (!empty($filtros['id_chofer'])) {
            $sql .= " AND v.id_chofer = ?";
            $params[] = (int)$filtros['id_chofer'];
        }

        $sql .= " ORDER BY i.fecha_hora DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Incidente::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function contarTotal(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM Incidente");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Incidente::contarTotal: " . $e->getMessage());
            return 0;
        }
    }

    public function contarPorTipo(string $tipo): int {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Incidente WHERE tipo_incidente = ?");
            $stmt->execute([$tipo]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Incidente::contarPorTipo: " . $e->getMessage());
            return 0;
        }
    }

    public function getUltimos(int $limite = 4): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT i.id_incidente, i.tipo_incidente, i.descripcion, i.fecha_hora,
                       v.id_viaje, v.patente,
                       ch.nombre AS chofer_nombre, ch.apellido AS chofer_ap
                FROM Incidente i
                JOIN Viaje v  ON v.id_viaje   = i.id_viaje
                JOIN Chofer ch ON ch.id_chofer = v.id_chofer
                ORDER BY i.fecha_hora DESC
                LIMIT ?
            ");
            $stmt->execute([$limite]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Incidente::getUltimos: " . $e->getMessage());
            return [];
        }
    }

    public function contarPorChofer(int $idChofer): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(i.id_incidente)
                FROM Incidente i
                JOIN Viaje v ON v.id_viaje = i.id_viaje
                WHERE v.id_chofer = ?
            ");
            $stmt->execute([$idChofer]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Incidente::contarPorChofer: " . $e->getMessage());
            return 0;
        }
    }
}
