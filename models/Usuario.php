<?php
// models/Usuario.php — Modelo de usuario con PDO (inyección de dependencia)

class Usuario {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT u.id_usuario, u.username, u.activo, r.nombre AS rol,
                       u.created_at, u.id_chofer, u.id_cliente, u.id_empleado,
                       COALESCE(e.nombre,   ch.nombre)   AS nombre_personal,
                       COALESCE(e.apellido, ch.apellido) AS apellido_personal,
                       COALESCE(e.legajo,   ch.legajo)   AS legajo_personal,
                       COALESCE(e.dni,       ch.dni)       AS dni_personal,
                       COALESCE(e.localidad, ch.localidad) AS localidad_personal,
                       COALESCE(e.direccion, ch.direccion) AS direccion_personal,
                       COALESCE(e.telefono,  ch.telefono)  AS telefono_personal,
                       COALESCE(se.nombre,   sc.nombre)    AS sucursal_nombre
                FROM Usuario u
                JOIN Rol r ON r.id_rol = u.id_rol
                LEFT JOIN Empleado e  ON e.id_empleado  = u.id_empleado
                LEFT JOIN Sucursal se ON se.id_sucursal = e.id_sucursal
                LEFT JOIN Chofer ch   ON ch.id_chofer   = u.id_chofer
                LEFT JOIN Sucursal sc ON sc.id_sucursal = ch.id_sucursal
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['rol'])) {
            $sql .= " AND r.nombre = ?";
            $params[] = $filtros['rol'];
        }
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $sql .= " AND u.activo = ?";
            $params[] = (int)$filtros['activo'];
        }
        if (!empty($filtros['buscar'])) {
            $like = '%' . $filtros['buscar'] . '%';
            $sql .= " AND (COALESCE(e.nombre, ch.nombre) LIKE ?
                        OR COALESCE(e.apellido, ch.apellido) LIKE ?
                        OR COALESCE(e.legajo, ch.legajo) LIKE ?
                        OR COALESCE(e.dni, ch.dni) LIKE ?
                        OR u.username LIKE ?)";
            array_push($params, $like, $like, $like, $like, $like);
        }

        $sql .= " ORDER BY u.id_usuario ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Usuario::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.id_usuario, u.username, u.activo, u.id_rol, u.created_at,
                       u.id_chofer, u.id_cliente, u.id_empleado, r.nombre AS rol
                FROM Usuario u
                JOIN Rol r ON r.id_rol = u.id_rol
                WHERE u.id_usuario = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Usuario::getById: " . $e->getMessage());
            return false;
        }
    }

    public function crear(array $datos): bool {
        try {
            $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                INSERT INTO Usuario (username, password_hash, id_rol, activo)
                VALUES (?, ?, ?, 1)
            ");
            return $stmt->execute([
                $datos['username'],
                $hash,
                (int)$datos['id_rol'],
            ]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::crear: " . $e->getMessage());
            return false;
        }
    }

    public function editar(int $id, array $datos): bool {
        try {
            if (!empty($datos['password'])) {
                $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("
                    UPDATE Usuario SET username = ?, password_hash = ?, id_rol = ?
                    WHERE id_usuario = ?
                ");
                return $stmt->execute([
                    $datos['username'],
                    $hash,
                    (int)$datos['id_rol'],
                    $id,
                ]);
            } else {
                $stmt = $this->pdo->prepare("
                    UPDATE Usuario SET username = ?, id_rol = ?
                    WHERE id_usuario = ?
                ");
                return $stmt->execute([
                    $datos['username'],
                    (int)$datos['id_rol'],
                    $id,
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::editar: " . $e->getMessage());
            return false;
        }
    }

    // Baja lógica — nunca DELETE físico
    public function desactivar(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE Usuario SET activo = 0 WHERE id_usuario = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::desactivar: " . $e->getMessage());
            return false;
        }
    }

    public function reactivar(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE Usuario SET activo = 1 WHERE id_usuario = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::reactivar: " . $e->getMessage());
            return false;
        }
    }

    public function getRoles(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_rol, nombre FROM Rol ORDER BY id_rol");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Usuario::getRoles: " . $e->getMessage());
            return [];
        }
    }

    public function getInfoRol(int $idUsuario): array|false {
        try {
            $stmt = $this->pdo->prepare("SELECT id_chofer, id_cliente FROM Usuario WHERE id_usuario = ?");
            $stmt->execute([$idUsuario]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Usuario::getInfoRol: " . $e->getMessage());
            return false;
        }
    }

    public function getSucursalEmpleado(int $idUsuario): int|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.id_sucursal
                FROM Usuario u
                JOIN Empleado e ON e.id_empleado = u.id_empleado
                WHERE u.id_usuario = ?
            ");
            $stmt->execute([$idUsuario]);
            $row = $stmt->fetch();
            return $row ? (int)$row['id_sucursal'] : false;
        } catch (PDOException $e) {
            error_log("Error en Usuario::getSucursalEmpleado: " . $e->getMessage());
            return false;
        }
    }

    public function contarActivos(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM Usuario WHERE activo = 1");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Usuario::contarActivos: " . $e->getMessage());
            return 0;
        }
    }

    public function contarPorRol(int $idRol): int {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE id_rol = ? AND activo = 1");
            $stmt->execute([$idRol]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Usuario::contarPorRol: " . $e->getMessage());
            return 0;
        }
    }

    public function actualizarCredenciales(int $id, string $username, ?string $password): bool {
        try {
            if ($password !== null) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("UPDATE Usuario SET username = ?, password_hash = ? WHERE id_usuario = ?");
                return $stmt->execute([$username, $hash, $id]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE Usuario SET username = ? WHERE id_usuario = ?");
                return $stmt->execute([$username, $id]);
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarCredenciales: " . $e->getMessage());
            return false;
        }
    }

    public function existeUsername(string $username, int $excludeId = 0): bool {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM Usuario WHERE username = ? AND id_usuario != ?
            ");
            $stmt->execute([$username, $excludeId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en Usuario::existeUsername: " . $e->getMessage());
            return false;
        }
    }
}
