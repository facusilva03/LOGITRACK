<?php
// models/Cliente.php — Modelo de cliente con PDO (inyección de dependencia)

class Cliente {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT c.id_cliente, c.tipo_documento, c.documento,
                       c.nombre, c.apellido, c.direccion,
                       l.nombre AS localidad, l.provincia
                FROM Cliente c
                JOIN Localidad l ON l.id_localidad = c.id_localidad
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['id_localidad'])) {
            $sql .= " AND c.id_localidad = ?";
            $params[] = (int)$filtros['id_localidad'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.documento LIKE ?)";
            $term = '%' . $filtros['buscar'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY c.apellido, c.nombre";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Cliente::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, l.nombre AS localidad, l.provincia
                FROM Cliente c
                JOIN Localidad l ON l.id_localidad = c.id_localidad
                WHERE c.id_cliente = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Cliente::getById: " . $e->getMessage());
            return false;
        }
    }

    public function crear(array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Cliente (tipo_documento, documento, nombre, apellido, id_localidad, direccion)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['tipo_documento'],
                $datos['documento'],
                $datos['nombre'],
                $datos['apellido'],
                (int)$datos['id_localidad'],
                $datos['direccion'] ?? null,
            ]);
        } catch (PDOException $e) {
            error_log("Error en Cliente::crear: " . $e->getMessage());
            return false;
        }
    }

    public function editar(int $id, array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Cliente
                SET tipo_documento = ?, documento = ?, nombre = ?,
                    apellido = ?, id_localidad = ?, direccion = ?
                WHERE id_cliente = ?
            ");
            return $stmt->execute([
                $datos['tipo_documento'],
                $datos['documento'],
                $datos['nombre'],
                $datos['apellido'],
                (int)$datos['id_localidad'],
                $datos['direccion'] ?? null,
                $id,
            ]);
        } catch (PDOException $e) {
            error_log("Error en Cliente::editar: " . $e->getMessage());
            return false;
        }
    }

    // Sin baja lógica disponible (la tabla Cliente no tiene columna activo)
    // Se omite el método desactivar para respetar el esquema de BD

    public function contarTotal(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM Cliente");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Cliente::contarTotal: " . $e->getMessage());
            return 0;
        }
    }

    public function getLocalidades(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_localidad, nombre, provincia FROM Localidad ORDER BY provincia, nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Cliente::getLocalidades: " . $e->getMessage());
            return [];
        }
    }

    public function existeDocumento(string $documento, int $excludeId = 0): bool {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM Cliente WHERE documento = ? AND id_cliente != ?
            ");
            $stmt->execute([$documento, $excludeId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en Cliente::existeDocumento: " . $e->getMessage());
            return false;
        }
    }
}
