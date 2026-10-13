<?php
// models/ClienteWeb.php — Registro de nuevo cliente web (transacción ACID)

class ClienteWeb {
    private PDO $pdo;

    // ID del rol "Cliente" en la tabla Rol — confirmado: SELECT * FROM Rol;
    private const ROL_CLIENTE = 3;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Registra un nuevo cliente y su usuario en una única transacción atómica.
     *
     * Paso A: INSERT en Cliente — obtiene el id_cliente con lastInsertId().
     * Paso B: INSERT en Usuario vinculado al id_cliente recién creado.
     *
     * Si ambos INSERTs tienen éxito → commit().
     * Si cualquiera falla → rollBack() + lanza RuntimeException.
     *
     * $datosCliente esperado:
     *   tipo_documento (string), documento (string),
     *   nombre (string), apellido (string),
     *   id_localidad (int), direccion (string|null)
     */
    public function registrarNuevo(array $datosCliente, string $username, string $passwordPlana): void {
        try {
            $this->pdo->beginTransaction();

            // ── Paso A: insertar o reutilizar el cliente ─────────────────────
            // Si existe un Cliente huérfano (sin usuario activo) con el mismo
            // documento, se actualizan sus datos y se reutiliza el id.
            $stmtExiste = $this->pdo->prepare("SELECT id_cliente FROM Cliente WHERE documento = ?");
            $stmtExiste->execute([$datosCliente['documento']]);
            $clienteExistente = $stmtExiste->fetchColumn();

            if ($clienteExistente) {
                $idCliente = (int)$clienteExistente;
                $stmtUpdate = $this->pdo->prepare("
                    UPDATE Cliente
                    SET tipo_documento = ?, nombre = ?, apellido = ?,
                        id_localidad = ?, direccion = ?
                    WHERE id_cliente = ?
                ");
                $stmtUpdate->execute([
                    $datosCliente['tipo_documento'],
                    $datosCliente['nombre'],
                    $datosCliente['apellido'],
                    (int)$datosCliente['id_localidad'],
                    $datosCliente['direccion'] ?? null,
                    $idCliente,
                ]);
            } else {
                $stmtCliente = $this->pdo->prepare("
                    INSERT INTO Cliente (tipo_documento, documento, nombre, apellido, id_localidad, direccion)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmtCliente->execute([
                    $datosCliente['tipo_documento'],
                    $datosCliente['documento'],
                    $datosCliente['nombre'],
                    $datosCliente['apellido'],
                    (int)$datosCliente['id_localidad'],
                    $datosCliente['direccion'] ?? null,
                ]);
                $idCliente = (int)$this->pdo->lastInsertId();
            }

            // ── Paso B: insertar el usuario vinculado ────────────────────────
            // id_rol = 4 → "Cliente" — verificar con: SELECT * FROM Rol;
            $stmtUsuario = $this->pdo->prepare("
                INSERT INTO Usuario (username, password_hash, id_rol, id_cliente, activo)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmtUsuario->execute([
                $username,
                password_hash($passwordPlana, PASSWORD_DEFAULT),
                self::ROL_CLIENTE,
                $idCliente,
            ]);

            $this->pdo->commit();

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en ClienteWeb::registrarNuevo: " . $e->getMessage());
            throw new RuntimeException("No se pudo completar el registro. Intentá nuevamente.");
        }
    }

    // Verifica si el username ya está tomado antes de intentar el INSERT
    public function existeUsername(string $username): bool {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE username = ?");
            $stmt->execute([$username]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en ClienteWeb::existeUsername: " . $e->getMessage());
            return false;
        }
    }

    // Verifica si el documento ya está registrado con un usuario activo
    public function existeDocumento(string $documento): bool {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM Cliente c
                JOIN Usuario u ON u.id_cliente = c.id_cliente
                WHERE c.documento = ?
                  AND u.activo = 1
            ");
            $stmt->execute([$documento]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en ClienteWeb::existeDocumento: " . $e->getMessage());
            return false;
        }
    }

    // Localidades para el <select> del formulario de registro
    public function getLocalidades(): array {
        try {
            $stmt = $this->pdo->query("
                SELECT id_localidad, nombre, provincia
                FROM Localidad
                ORDER BY provincia, nombre
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en ClienteWeb::getLocalidades: " . $e->getMessage());
            return [];
        }
    }
}
