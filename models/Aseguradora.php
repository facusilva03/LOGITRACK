<?php
// models/Aseguradora.php

class Aseguradora {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(string $buscar = ''): array {
        try {
            $sql    = "SELECT id_aseguradora, nombre, telefono_emergencia, web FROM Aseguradora";
            $params = [];
            if ($buscar !== '') {
                $sql   .= " WHERE nombre LIKE ?";
                $params[] = '%' . $buscar . '%';
            }
            $sql .= " ORDER BY nombre ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Aseguradora::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id_aseguradora, nombre, telefono_emergencia, web
                FROM Aseguradora WHERE id_aseguradora = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: false;
        } catch (PDOException $e) {
            error_log("Aseguradora::getById: " . $e->getMessage());
            return false;
        }
    }

    public function crear(array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Aseguradora (nombre, telefono_emergencia, web)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([
                trim($datos['nombre']),
                trim($datos['telefono_emergencia']) ?: null,
                trim($datos['web'])                 ?: null,
            ]);
        } catch (PDOException $e) {
            error_log("Aseguradora::crear: " . $e->getMessage());
            return false;
        }
    }

    public function editar(int $id, array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Aseguradora
                SET nombre               = ?,
                    telefono_emergencia  = ?,
                    web                  = ?
                WHERE id_aseguradora = ?
            ");
            return $stmt->execute([
                trim($datos['nombre']),
                trim($datos['telefono_emergencia']) ?: null,
                trim($datos['web'])                 ?: null,
                $id,
            ]);
        } catch (PDOException $e) {
            error_log("Aseguradora::editar id={$id}: " . $e->getMessage());
            return false;
        }
    }

    // La FK en Vehiculo es ON DELETE SET NULL — eliminar es seguro.
    // Los vehículos quedan con id_aseguradora = NULL.
    public function eliminar(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM Aseguradora WHERE id_aseguradora = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Aseguradora::eliminar id={$id}: " . $e->getMessage());
            return false;
        }
    }

    public function cantidadVehiculos(int $id): int {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Vehiculo WHERE id_aseguradora = ?");
            $stmt->execute([$id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Aseguradora::cantidadVehiculos: " . $e->getMessage());
            return 0;
        }
    }
}
