<?php
// models/Empleado.php

class Empleado {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT e.id_empleado, e.legajo, e.nombre, e.apellido,
                       e.dni, e.localidad, e.direccion, e.telefono,
                       s.nombre AS sucursal_nombre,
                       u.id_usuario, u.activo, u.created_at
                FROM Empleado e
                LEFT JOIN Sucursal s ON s.id_sucursal = e.id_sucursal
                LEFT JOIN Usuario  u ON u.id_empleado = e.id_empleado
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['id_sucursal'])) {
            $sql .= " AND e.id_sucursal = ?";
            $params[] = (int)$filtros['id_sucursal'];
        }
        if (!empty($filtros['buscar'])) {
            $like = '%' . $filtros['buscar'] . '%';
            $sql .= " AND (e.nombre LIKE ? OR e.apellido LIKE ?
                        OR e.legajo LIKE ? OR e.dni LIKE ?)";
            array_push($params, $like, $like, $like, $like);
        }

        $sql .= " ORDER BY e.legajo ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Empleado::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.id_empleado, e.legajo, e.nombre, e.apellido,
                       e.dni, e.localidad, e.direccion, e.telefono,
                       s.nombre AS sucursal_nombre,
                       u.id_usuario, u.username, u.activo, u.created_at
                FROM Empleado e
                LEFT JOIN Sucursal s ON s.id_sucursal = e.id_sucursal
                LEFT JOIN Usuario  u ON u.id_empleado = e.id_empleado
                WHERE e.id_empleado = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Empleado::getById: " . $e->getMessage());
            return false;
        }
    }

    public function editar(int $idEmpleado, array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Empleado
                SET nombre      = ?,
                    apellido    = ?,
                    telefono    = ?,
                    direccion   = ?,
                    localidad   = ?,
                    id_sucursal = ?
                WHERE id_empleado = ?
            ");
            return $stmt->execute([
                trim($datos['nombre']),
                trim($datos['apellido']),
                trim($datos['telefono'])   ?: null,
                trim($datos['direccion'])  ?: null,
                trim($datos['localidad'])  ?: null,
                (int)$datos['id_sucursal'],
                $idEmpleado,
            ]);
        } catch (PDOException $e) {
            error_log("Empleado::editar id={$idEmpleado}: " . $e->getMessage());
            return false;
        }
    }

    public function getSucursales(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_sucursal, nombre FROM Sucursal ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Empleado::getSucursales: " . $e->getMessage());
            return [];
        }
    }
}
