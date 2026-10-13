<?php
// models/Vehiculo.php — Modelo de vehículos con PDO (inyección de dependencia)

class Vehiculo {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT v.patente, v.marca, v.modelo, v.estado_vehiculo,
                       v.id_tipo_veh, v.id_sucursal, v.id_aseguradora,
                       tv.nombre          AS tipo_vehiculo,
                       tv.capacidad_kg_max,
                       s.nombre           AS sucursal,
                       a.nombre           AS aseguradora
                FROM Vehiculo v
                JOIN  Tipo_Vehiculo tv  ON tv.id_tipo_veh     = v.id_tipo_veh
                JOIN  Sucursal      s   ON s.id_sucursal       = v.id_sucursal
                LEFT JOIN Aseguradora a ON a.id_aseguradora    = v.id_aseguradora
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['id_sucursal'])) {
            $sql .= " AND v.id_sucursal = ?";
            $params[] = (int)$filtros['id_sucursal'];
        }
        if (!empty($filtros['estado_vehiculo'])) {
            $sql .= " AND v.estado_vehiculo = ?";
            $params[] = $filtros['estado_vehiculo'];
        }
        if (!empty($filtros['buscar'])) {
            $like = '%' . $filtros['buscar'] . '%';
            $sql .= " AND (v.patente LIKE ? OR v.marca LIKE ? OR v.modelo LIKE ?)";
            array_push($params, $like, $like, $like);
        }

        $sql .= " ORDER BY v.patente ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getById(string $patente): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT v.*,
                       tv.nombre          AS tipo_vehiculo,
                       tv.capacidad_kg_max,
                       s.nombre           AS sucursal,
                       a.nombre           AS aseguradora
                FROM Vehiculo v
                JOIN  Tipo_Vehiculo tv  ON tv.id_tipo_veh     = v.id_tipo_veh
                JOIN  Sucursal      s   ON s.id_sucursal       = v.id_sucursal
                LEFT JOIN Aseguradora a ON a.id_aseguradora    = v.id_aseguradora
                WHERE v.patente = ?
            ");
            $stmt->execute([$patente]);
            return $stmt->fetch() ?: false;
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::getById: " . $e->getMessage());
            return false;
        }
    }

    public function crear(array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Vehiculo (patente, id_tipo_veh, marca, modelo, id_sucursal, id_aseguradora, estado_vehiculo)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                strtoupper(trim($datos['patente'])),
                (int)$datos['id_tipo_veh'],
                trim($datos['marca']),
                trim($datos['modelo']),
                (int)$datos['id_sucursal'],
                !empty($datos['id_aseguradora']) ? (int)$datos['id_aseguradora'] : null,
                trim($datos['estado_vehiculo']),
            ]);
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::crear: " . $e->getMessage());
            return false;
        }
    }

    public function editar(string $patente, array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Vehiculo
                SET id_tipo_veh     = ?,
                    marca           = ?,
                    modelo          = ?,
                    id_sucursal     = ?,
                    id_aseguradora  = ?,
                    estado_vehiculo = ?
                WHERE patente = ?
            ");
            return $stmt->execute([
                (int)$datos['id_tipo_veh'],
                trim($datos['marca']),
                trim($datos['modelo']),
                (int)$datos['id_sucursal'],
                !empty($datos['id_aseguradora']) ? (int)$datos['id_aseguradora'] : null,
                trim($datos['estado_vehiculo']),
                $patente,
            ]);
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::editar: " . $e->getMessage());
            return false;
        }
    }

    public function getTiposVehiculo(): array {
        try {
            $stmt = $this->pdo->query("
                SELECT id_tipo_veh, nombre, capacidad_kg_max
                FROM Tipo_Vehiculo ORDER BY nombre
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::getTiposVehiculo: " . $e->getMessage());
            return [];
        }
    }

    public function getSucursales(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_sucursal, nombre FROM Sucursal ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::getSucursales: " . $e->getMessage());
            return [];
        }
    }

    public function getAseguradoras(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_aseguradora, nombre FROM Aseguradora ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::getAseguradoras: " . $e->getMessage());
            return [];
        }
    }

    public function tieneViajesActivos(string $patente): bool {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM Viaje
                WHERE patente = ? AND estado_viaje IN ('Planificado', 'En curso', 'Demorado')
            ");
            $stmt->execute([$patente]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::tieneViajesActivos: " . $e->getMessage());
            return true; // fail-safe: bloquear si hay error
        }
    }

    public function desactivar(string $patente): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE Vehiculo SET estado_vehiculo = 'Inactivo' WHERE patente = ?");
            return $stmt->execute([$patente]);
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::desactivar: " . $e->getMessage());
            return false;
        }
    }

    // Devuelve la capacidad máxima en kg del vehículo asignado a un viaje.
    // Retorna 0.0 si el viaje no existe o no tiene vehículo asignado.
    public function getCapacidadPorViaje(int $idViaje): float {
        try {
            $stmt = $this->pdo->prepare("
                SELECT tv.capacidad_kg_max
                FROM Viaje vi
                JOIN Vehiculo      veh ON veh.patente     = vi.patente
                JOIN Tipo_Vehiculo tv  ON tv.id_tipo_veh  = veh.id_tipo_veh
                WHERE vi.id_viaje = ?
            ");
            $stmt->execute([$idViaje]);
            $resultado = $stmt->fetchColumn();
            return $resultado !== false ? (float)$resultado : 0.0;
        } catch (PDOException $e) {
            error_log("Error en Vehiculo::getCapacidadPorViaje: " . $e->getMessage());
            return 0.0;
        }
    }
}
