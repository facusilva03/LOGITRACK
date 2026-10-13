<?php
// models/Envio.php — Modelo de envío con PDO (inyección de dependencia)

class Envio {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT e.id_envio, e.nro_tracking,
                       cr.nombre AS remitente, cr.apellido AS remitente_ap,
                       cd.nombre AS destinatario, cd.apellido AS destinatario_ap,
                       so.nombre AS suc_origen, sd.nombre AS suc_destino,
                       tc.nombre AS tipo_contenido,
                       ec.nombre_estado AS estado_actual
                FROM Envio e
                JOIN Cliente cr ON cr.id_cliente = e.id_remitente
                JOIN Cliente cd ON cd.id_cliente = e.id_destinatario
                JOIN Sucursal so ON so.id_sucursal = e.id_suc_origen
                JOIN Sucursal sd ON sd.id_sucursal = e.id_suc_destino
                JOIN Tipo_Contenido tc ON tc.id_tipo_cont = e.id_tipo_cont
                LEFT JOIN (
                    SELECT h1.id_envio, ec1.nombre_estado
                    FROM Historial_Estado h1
                    JOIN Estado_Catalogo ec1 ON ec1.id_estado = h1.id_estado
                    WHERE h1.id_hist = (
                        SELECT MAX(h2.id_hist) FROM Historial_Estado h2
                        WHERE h2.id_envio = h1.id_envio
                    )
                ) ult ON ult.id_envio = e.id_envio
                LEFT JOIN Estado_Catalogo ec ON ec.nombre_estado = ult.nombre_estado
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['id_estado'])) {
            $sql .= " AND ec.id_estado = ?";
            $params[] = (int)$filtros['id_estado'];
        }
        if (!empty($filtros['id_suc_origen'])) {
            $sql .= " AND e.id_suc_origen = ?";
            $params[] = (int)$filtros['id_suc_origen'];
        }
        if (!empty($filtros['tracking'])) {
            $sql .= " AND e.nro_tracking LIKE ?";
            $params[] = '%' . $filtros['tracking'] . '%';
        }

        $dir = ($filtros['orden'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY e.id_envio $dir";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getAll: " . $e->getMessage());
            return [];
        }
    }

    // ── Métricas del dashboard de cliente ──────────────────────────

    public function contarEnviosComoRemitente(int $idCliente): int {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Envio WHERE id_remitente = ?");
            $stmt->execute([$idCliente]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarEnviosComoRemitente: " . $e->getMessage());
            return 0;
        }
    }

    public function contarEnCaminoPorCliente(int $idCliente): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE (e.id_remitente = ? OR e.id_destinatario = ?)
                  AND h.id_estado != 5
            ");
            $stmt->execute([$idCliente, $idCliente]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarEnCaminoPorCliente: " . $e->getMessage());
            return 0;
        }
    }

    public function contarEntregadosPorCliente(int $idCliente): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE e.id_remitente = ? AND h.id_estado = 5
            ");
            $stmt->execute([$idCliente]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarEntregadosPorCliente: " . $e->getMessage());
            return 0;
        }
    }

    public function contarRecibidosPorCliente(int $idCliente): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE e.id_destinatario = ? AND h.id_estado = 5
            ");
            $stmt->execute([$idCliente]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarRecibidosPorCliente: " . $e->getMessage());
            return 0;
        }
    }

    public function getActividadReciente(int $idCliente, int $limite = 3): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.nro_tracking,
                       ec.nombre_estado,
                       h.fecha_hora,
                       s.nombre AS sucursal_actual
                FROM Historial_Estado h
                JOIN Envio e         ON e.id_envio   = h.id_envio
                JOIN Estado_Catalogo ec ON ec.id_estado = h.id_estado
                LEFT JOIN Sucursal s ON s.id_sucursal = h.id_sucursal_actual
                WHERE e.id_remitente = ? OR e.id_destinatario = ?
                ORDER BY h.fecha_hora DESC
                LIMIT ?
            ");
            $stmt->execute([$idCliente, $idCliente, $limite]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getActividadReciente: " . $e->getMessage());
            return [];
        }
    }

    // Envíos de un cliente específico (remitente o destinatario)
    public function getByCliente(int $idCliente): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.id_envio, e.nro_tracking,
                       cr.nombre AS remitente, cr.apellido AS remitente_ap,
                       cd.nombre AS destinatario, cd.apellido AS destinatario_ap,
                       so.nombre AS suc_origen, sd.nombre AS suc_destino,
                       COALESCE(ec.nombre_estado, 'Sin estado') AS estado_actual
                FROM Envio e
                JOIN Cliente cr ON cr.id_cliente = e.id_remitente
                JOIN Cliente cd ON cd.id_cliente = e.id_destinatario
                JOIN Sucursal so ON so.id_sucursal = e.id_suc_origen
                JOIN Sucursal sd ON sd.id_sucursal = e.id_suc_destino
                LEFT JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                LEFT JOIN Estado_Catalogo ec ON ec.id_estado = h.id_estado
                WHERE e.id_remitente = ? OR e.id_destinatario = ?
                ORDER BY e.id_envio DESC
            ");
            $stmt->execute([$idCliente, $idCliente]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getByCliente: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.*,
                       cr.nombre AS remitente, cr.apellido AS remitente_ap,
                       cd.nombre AS destinatario, cd.apellido AS destinatario_ap,
                       so.nombre AS suc_origen, sd.nombre AS suc_destino,
                       tc.nombre AS tipo_contenido
                FROM Envio e
                JOIN Cliente cr ON cr.id_cliente = e.id_remitente
                JOIN Cliente cd ON cd.id_cliente = e.id_destinatario
                JOIN Sucursal so ON so.id_sucursal = e.id_suc_origen
                JOIN Sucursal sd ON sd.id_sucursal = e.id_suc_destino
                JOIN Tipo_Contenido tc ON tc.id_tipo_cont = e.id_tipo_cont
                WHERE e.id_envio = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Envio::getById: " . $e->getMessage());
            return false;
        }
    }

    public function crear(array $datos): int|false {
        try {
            $tracking = 'LT-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $this->pdo->prepare("
                INSERT INTO Envio (nro_tracking, id_remitente, id_destinatario,
                                   id_suc_origen, id_suc_destino, id_tipo_cont, peso_kg)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $ok = $stmt->execute([
                $tracking,
                (int)$datos['id_remitente'],
                (int)$datos['id_destinatario'],
                (int)$datos['id_suc_origen'],
                (int)$datos['id_suc_destino'],
                (int)$datos['id_tipo_cont'],
                (float)$datos['peso_kg'],
            ]);

            if (!$ok) return false;
            $idEnvio = (int)$this->pdo->lastInsertId();

            // Registrar estado inicial "Enviado" (id=1)
            $stmt2 = $this->pdo->prepare("
                INSERT INTO Historial_Estado (id_envio, id_estado, id_sucursal_actual)
                VALUES (?, 1, ?)
            ");
            $stmt2->execute([$idEnvio, (int)$datos['id_suc_origen']]);

            return $idEnvio;
        } catch (PDOException $e) {
            error_log("Error en Envio::crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea el Envio y su primer Historial_Estado en una transacción atómica.
     * Retorna el id_envio generado o lanza RuntimeException si falla.
     */
    public function crearEnvioConHistorial(array $datos, string $tracking, int $idEstadoInicial, int $idSucursal): int {
        try {
            $this->pdo->beginTransaction();

            // Paso A: insertar el envío
            $stmt = $this->pdo->prepare("
                INSERT INTO Envio (nro_tracking, id_remitente, id_destinatario,
                                   id_suc_origen, id_suc_destino, id_tipo_cont, peso_kg)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tracking,
                (int)$datos['id_remitente'],
                (int)$datos['id_destinatario'],
                (int)$datos['id_suc_origen'],
                (int)$datos['id_suc_destino'],
                (int)$datos['id_tipo_cont'],
                (float)$datos['peso_kg'],
            ]);

            // Paso B: ID del registro recién insertado
            $idEnvio = (int)$this->pdo->lastInsertId();

            // Paso C: registrar el estado inicial en el historial
            $stmt2 = $this->pdo->prepare("
                INSERT INTO Historial_Estado (id_envio, id_estado, id_sucursal_actual)
                VALUES (?, ?, ?)
            ");
            $stmt2->execute([$idEnvio, $idEstadoInicial, $idSucursal]);

            $this->pdo->commit();
            return $idEnvio;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en Envio::crearEnvioConHistorial: " . $e->getMessage());
            throw new RuntimeException("No se pudo registrar el envío con su estado inicial.");
        }
    }

    // Paquetes con estado 'Recibido' que coinciden exactamente con la ruta del viaje
    public function getDisponiblesParaViaje(int $idSucOrigen, int $idSucDestino): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.id_envio, e.nro_tracking, e.peso_kg, e.peso_real,
                       cr.nombre AS remitente, cr.apellido AS remitente_ap,
                       cd.nombre AS destinatario, cd.apellido AS destinatario_ap,
                       tc.nombre AS tipo_contenido
                FROM Envio e
                JOIN Cliente cr        ON cr.id_cliente   = e.id_remitente
                JOIN Cliente cd        ON cd.id_cliente   = e.id_destinatario
                JOIN Tipo_Contenido tc ON tc.id_tipo_cont = e.id_tipo_cont
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (
                        SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio
                    )
                JOIN Estado_Catalogo ec ON ec.id_estado = h.id_estado
                WHERE e.id_suc_origen  = ?
                  AND e.id_suc_destino = ?
                  AND ec.nombre_estado = 'Recibido'
                ORDER BY e.id_envio ASC
            ");
            $stmt->execute([$idSucOrigen, $idSucDestino]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getDisponiblesParaViaje: " . $e->getMessage());
            return [];
        }
    }

    // Suma el peso de un conjunto arbitrario de envíos recibidos como array de IDs.
    // Construye los placeholders dinámicamente para evitar concatenación directa.
    public function getPesoTotal(array $idsEnvios): float {
        if (empty($idsEnvios)) {
            return 0.0;
        }
        try {
            $placeholders = implode(',', array_fill(0, count($idsEnvios), '?'));
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(COALESCE(peso_real, peso_kg)), 0)
                FROM Envio
                WHERE id_envio IN ({$placeholders})
            ");
            $params = array_map('intval', $idsEnvios);
            $stmt->execute($params);
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::getPesoTotal: " . $e->getMessage());
            return 0.0;
        }
    }

    // Asigna un lote de envíos a un viaje y registra el estado "En camino"
    // en una única transacción atómica.
    // ID 4 = "En camino" en Estado_Catalogo — verificar con: SELECT * FROM Estado_Catalogo;
    public function asignarAViaje(int $idViaje, array $idsEnvios, ?int $idSucursal = null): bool {
        if (empty($idsEnvios)) {
            return false;
        }
        try {
            $this->pdo->beginTransaction();

            $stmtViaje = $this->pdo->prepare("
                INSERT INTO Viaje_Envio (id_viaje, id_envio) VALUES (?, ?)
            ");
            $stmtEstado = $this->pdo->prepare("
                INSERT INTO Historial_Estado (id_envio, id_estado, id_sucursal_actual)
                VALUES (?, 4, ?)
            ");

            foreach ($idsEnvios as $idEnvio) {
                $idEnvio = (int)$idEnvio;
                $stmtViaje->execute([$idViaje, $idEnvio]);
                $stmtEstado->execute([$idEnvio, $idSucursal]);
            }

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en Envio::asignarAViaje: " . $e->getMessage());
            throw new RuntimeException("No se pudo completar la asignación de envíos al viaje.");
        }
    }

    public function editar(int $id, array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Envio
                SET id_remitente = ?, id_destinatario = ?,
                    id_suc_origen = ?, id_suc_destino = ?, id_tipo_cont = ?
                WHERE id_envio = ?
            ");
            return $stmt->execute([
                (int)$datos['id_remitente'],
                (int)$datos['id_destinatario'],
                (int)$datos['id_suc_origen'],
                (int)$datos['id_suc_destino'],
                (int)$datos['id_tipo_cont'],
                $id,
            ]);
        } catch (PDOException $e) {
            error_log("Error en Envio::editar: " . $e->getMessage());
            return false;
        }
    }

    public function agregarEstado(int $idEnvio, int $idEstado, ?int $idSucursal): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Historial_Estado (id_envio, id_estado, id_sucursal_actual)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$idEnvio, $idEstado, $idSucursal]);
        } catch (PDOException $e) {
            error_log("Error en Envio::agregarEstado: " . $e->getMessage());
            return false;
        }
    }

    public function confirmarPeso(int $idEnvio, float $pesoReal): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE Envio SET peso_real = ? WHERE id_envio = ?");
            return $stmt->execute([$pesoReal, $idEnvio]);
        } catch (PDOException $e) {
            error_log("Error en Envio::confirmarPeso: " . $e->getMessage());
            return false;
        }
    }

    public function getHistorial(int $idEnvio): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT h.id_hist, ec.nombre_estado, h.fecha_hora,
                       s.nombre AS sucursal
                FROM Historial_Estado h
                JOIN Estado_Catalogo ec ON ec.id_estado = h.id_estado
                LEFT JOIN Sucursal s ON s.id_sucursal = h.id_sucursal_actual
                WHERE h.id_envio = ?
                ORDER BY h.fecha_hora DESC
            ");
            $stmt->execute([$idEnvio]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getHistorial: " . $e->getMessage());
            return [];
        }
    }

    public function contarTotal(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM Envio");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarTotal: " . $e->getMessage());
            return 0;
        }
    }

    // ── Métricas del dashboard de administrador ────────────────────

    public function contarActivos(): int {
        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE h.id_estado NOT IN (5, 6, 7)
            ");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarActivos: " . $e->getMessage());
            return 0;
        }
    }

    public function contarEnCurso(): int {
        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE h.id_estado = 4
            ");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarEnCurso: " . $e->getMessage());
            return 0;
        }
    }

    public function contarCompletados(): int {
        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE h.id_estado = 5
            ");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarCompletados: " . $e->getMessage());
            return 0;
        }
    }

    public function contarDevueltos(): int {
        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE h.id_estado = 6
            ");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarDevueltos: " . $e->getMessage());
            return 0;
        }
    }

    public function contarDemorados(): int {
        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE h.id_estado = 3
            ");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarDemorados: " . $e->getMessage());
            return 0;
        }
    }

    public function contarEnviosHoy(): int {
        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(DISTINCT h.id_envio)
                FROM Historial_Estado h
                WHERE DATE(h.fecha_hora) = CURDATE()
                  AND h.id_hist = (
                      SELECT MIN(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = h.id_envio
                  )
            ");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarEnviosHoy: " . $e->getMessage());
            return 0;
        }
    }

    public function contarPendientes(): int {
        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(DISTINCT h.id_envio)
                FROM Historial_Estado h
                JOIN Estado_Catalogo ec ON ec.id_estado = h.id_estado
                WHERE ec.nombre_estado IN ('Enviado', 'En camino')
                  AND h.id_hist = (
                      SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = h.id_envio
                  )
            ");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarPendientes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Devuelve la línea de tiempo completa de un envío para el seguimiento público.
     * No requiere sesión activa — es acceso anónimo.
     * Retorna un array con una fila por cada evento del historial (ASC),
     * o array vacío si el código no existe.
     */
    public function getTrackingPublico(string $codigoTracking): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.nro_tracking,
                       cr.nombre      AS remitente,
                       cr.apellido    AS remitente_ap,
                       cd.nombre      AS destinatario,
                       cd.apellido    AS destinatario_ap,
                       so.nombre      AS suc_origen,
                       sd.nombre      AS suc_destino,
                       ec.nombre_estado,
                       h.fecha_hora,
                       sa.nombre      AS sucursal_actual
                FROM Envio e
                JOIN Cliente            cr ON cr.id_cliente  = e.id_remitente
                JOIN Cliente            cd ON cd.id_cliente  = e.id_destinatario
                JOIN Sucursal           so ON so.id_sucursal = e.id_suc_origen
                JOIN Sucursal           sd ON sd.id_sucursal = e.id_suc_destino
                JOIN Historial_Estado   h  ON h.id_envio     = e.id_envio
                JOIN Estado_Catalogo    ec ON ec.id_estado   = h.id_estado
                LEFT JOIN Sucursal      sa ON sa.id_sucursal = h.id_sucursal_actual
                WHERE e.nro_tracking = ?
                ORDER BY h.fecha_hora ASC
            ");
            $stmt->execute([strtoupper(trim($codigoTracking))]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getTrackingPublico: " . $e->getMessage());
            return [];
        }
    }

    public function getEstados(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_estado, nombre_estado FROM Estado_Catalogo ORDER BY id_estado");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getEstados: " . $e->getMessage());
            return [];
        }
    }

    public function getSucursales(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_sucursal, nombre FROM Sucursal ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getSucursales: " . $e->getMessage());
            return [];
        }
    }

    public function getTiposContenido(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_tipo_cont, nombre FROM Tipo_Contenido ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getTiposContenido: " . $e->getMessage());
            return [];
        }
    }

    public function getClientes(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_cliente, nombre, apellido, documento FROM Cliente ORDER BY apellido, nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getClientes: " . $e->getMessage());
            return [];
        }
    }

    public function getClientePorDocumento(string $documento): array|false {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id_cliente, nombre, apellido, documento FROM Cliente WHERE documento = ? LIMIT 1"
            );
            $stmt->execute([trim($documento)]);
            return $stmt->fetch() ?: false;
        } catch (PDOException $e) {
            error_log("Error en Envio::getClientePorDocumento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Devuelve el id_cliente cuyo documento coincide, o false si no existe.
     * No gestiona transacción — diseñado para llamarse dentro de una transacción externa.
     */
    public function getIdClientePorDocumento(string $documento): int|false {
        $stmt = $this->pdo->prepare(
            "SELECT id_cliente FROM Cliente WHERE documento = ? LIMIT 1"
        );
        $stmt->execute([trim($documento)]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id_cliente'] : false;
    }

    /**
     * Inserta un cliente pasivo (sin acceso al sistema).
     * No gestiona transacción — debe llamarse dentro de una transacción externa.
     * Lanza PDOException si falla, para que la transacción externa haga rollBack.
     */
    public function crearClientePasivo(array $datos): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Cliente (tipo_documento, documento, nombre, apellido, id_localidad, direccion, codigo_postal)
            VALUES ('DNI', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($datos['documento']),
            trim($datos['nombre']),
            trim($datos['apellido']),
            (int)$datos['id_localidad'],
            $datos['direccion'] !== '' ? trim($datos['direccion']) : null,
            $datos['codigo_postal'] !== '' ? trim($datos['codigo_postal']) : null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Actualiza localidad, dirección y código postal de un cliente existente.
     * No gestiona transacción — debe llamarse dentro de una transacción externa.
     */
    public function actualizarDireccionCliente(int $idCliente, array $datos): void {
        $stmt = $this->pdo->prepare("
            UPDATE Cliente
            SET id_localidad  = ?,
                direccion     = ?,
                codigo_postal = ?
            WHERE id_cliente = ?
        ");
        $stmt->execute([
            (int)$datos['id_localidad'],
            $datos['direccion'] !== '' ? trim($datos['direccion']) : null,
            $datos['codigo_postal'] !== '' ? trim($datos['codigo_postal']) : null,
            $idCliente,
        ]);
    }

    /**
     * Inserta el Envio y su entrada inicial en Historial_Estado.
     * No gestiona transacción — debe llamarse dentro de una transacción externa.
     * Lanza PDOException si falla.
     */
    public function insertarEnvio(array $datos, string $tracking, int $idEstado, int $idSucursal): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Envio (nro_tracking, id_remitente, id_destinatario,
                               id_suc_origen, id_suc_destino, id_tipo_cont, peso_kg)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tracking,
            (int)$datos['id_remitente'],
            (int)$datos['id_destinatario'],
            (int)$datos['id_suc_origen'],
            (int)$datos['id_suc_destino'],
            (int)$datos['id_tipo_cont'],
            (float)$datos['peso_kg'],
        ]);
        $idEnvio = (int)$this->pdo->lastInsertId();

        $stmt2 = $this->pdo->prepare("
            INSERT INTO Historial_Estado (id_envio, id_estado, id_sucursal_actual)
            VALUES (?, ?, ?)
        ");
        $stmt2->execute([$idEnvio, $idEstado, $idSucursal]);

        return $idEnvio;
    }

    public function getLocalidades(): array {
        try {
            $stmt = $this->pdo->query(
                "SELECT id_localidad, nombre, provincia FROM Localidad ORDER BY provincia, nombre"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getLocalidades: " . $e->getMessage());
            return [];
        }
    }

    // ── Métricas del dashboard de empleado de sucursal ────────────────

    public function contarEnviosHoyEnSucursal(int $idSucursal): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                WHERE e.id_suc_origen = ?
                  AND DATE((
                      SELECT MIN(h.fecha_hora) FROM Historial_Estado h WHERE h.id_envio = e.id_envio
                  )) = CURDATE()
            ");
            $stmt->execute([$idSucursal]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarEnviosHoyEnSucursal: " . $e->getMessage());
            return 0;
        }
    }

    public function contarPendientesEnSucursal(int $idSucursal): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE e.id_suc_origen = ?
                  AND h.id_estado = 2
            ");
            $stmt->execute([$idSucursal]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarPendientesEnSucursal: " . $e->getMessage());
            return 0;
        }
    }

    public function contarPreAdmisionesPendientes(int $idSucursal): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT e.id_envio)
                FROM Envio e
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE e.id_suc_origen = ?
                  AND h.id_estado = 8
            ");
            $stmt->execute([$idSucursal]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Envio::contarPreAdmisionesPendientes: " . $e->getMessage());
            return 0;
        }
    }

    public function getUltimosEnviosPorSucursal(int $idSucursal, int $limite = 5): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.id_envio,
                       e.nro_tracking,
                       cr.nombre    AS cliente_nombre,
                       cr.apellido  AS cliente_ap,
                       sd.nombre    AS destino,
                       COALESCE(ec.nombre_estado, 'Sin estado') AS estado_actual,
                       (SELECT MIN(h2.fecha_hora)
                        FROM Historial_Estado h2
                        WHERE h2.id_envio = e.id_envio) AS fecha_registro
                FROM Envio e
                JOIN Cliente    cr ON cr.id_cliente   = e.id_remitente
                JOIN Sucursal   sd ON sd.id_sucursal  = e.id_suc_destino
                LEFT JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h3.id_hist) FROM Historial_Estado h3 WHERE h3.id_envio = e.id_envio)
                LEFT JOIN Estado_Catalogo ec ON ec.id_estado = h.id_estado
                WHERE e.id_suc_origen = ?
                ORDER BY fecha_registro DESC
                LIMIT ?
            ");
            $stmt->execute([$idSucursal, $limite]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Envio::getUltimosEnviosPorSucursal: " . $e->getMessage());
            return [];
        }
    }
}
