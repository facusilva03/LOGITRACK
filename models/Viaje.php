<?php
// models/Viaje.php — Modelo de viaje con PDO (inyección de dependencia)

class Viaje {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT v.id_viaje, v.patente, v.fecha_salida, v.estado_viaje,
                       ch.nombre AS chofer_nombre, ch.apellido AS chofer_ap,
                       so.nombre AS suc_origen_nombre, sd.nombre AS suc_destino_nombre,
                       COUNT(ve.id_envio) AS cant_envios
                FROM Viaje v
                JOIN Chofer ch ON ch.id_chofer = v.id_chofer
                LEFT JOIN Sucursal so ON so.id_sucursal = v.id_suc_origen
                LEFT JOIN Sucursal sd ON sd.id_sucursal = v.id_suc_destino
                LEFT JOIN Viaje_Envio ve ON ve.id_viaje = v.id_viaje
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['estado_viaje'])) {
            $sql .= " AND v.estado_viaje = ?";
            $params[] = $filtros['estado_viaje'];
        }
        if (!empty($filtros['id_chofer'])) {
            $sql .= " AND v.id_chofer = ?";
            $params[] = (int)$filtros['id_chofer'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(v.fecha_salida) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(v.fecha_salida) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['patente'])) {
            $sql .= " AND v.patente LIKE ?";
            $params[] = '%' . $filtros['patente'] . '%';
        }

        $sql .= " GROUP BY v.id_viaje ORDER BY v.id_viaje DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getAll: " . $e->getMessage());
            return [];
        }
    }

    // Viajes de un chofer específico
    public function getByChofer(int $idChofer, array $filtros = []): array {
        $filtros['id_chofer'] = $idChofer;
        return $this->getAll($filtros);
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT v.*, ch.nombre AS chofer_nombre, ch.apellido AS chofer_ap,
                       tv.nombre AS tipo_vehiculo, tv.capacidad_kg_max,
                       so.nombre AS suc_origen_nombre, sd.nombre AS suc_destino_nombre
                FROM Viaje v
                JOIN Chofer ch ON ch.id_chofer = v.id_chofer
                JOIN Vehiculo veh ON veh.patente = v.patente
                JOIN Tipo_Vehiculo tv ON tv.id_tipo_veh = veh.id_tipo_veh
                LEFT JOIN Sucursal so ON so.id_sucursal = v.id_suc_origen
                LEFT JOIN Sucursal sd ON sd.id_sucursal = v.id_suc_destino
                WHERE v.id_viaje = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getById: " . $e->getMessage());
            return false;
        }
    }

    // Crea un viaje con ruta (suc. origen/destino) y devuelve el id generado
    public function crearConRuta(array $datos): int|false {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Viaje (patente, id_chofer, fecha_salida, estado_viaje, id_suc_origen, id_suc_destino)
                VALUES (?, ?, ?, 'Planificado', ?, ?)
            ");
            $ok = $stmt->execute([
                $datos['patente'],
                (int)$datos['id_chofer'],
                $datos['fecha_salida'],
                (int)$datos['id_suc_origen'],
                (int)$datos['id_suc_destino'],
            ]);
            return $ok ? (int)$this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Error en Viaje::crearConRuta: " . $e->getMessage());
            return false;
        }
    }

    // Choferes disponibles, opcionalmente filtrados por ubicación actual.
    // Ubicación actual = destino del último viaje Finalizado; si nunca viajó = su sucursal base.
    public function getChoferesDisponibles(int $idSucOrigen = 0): array {
        try {
            $ubicacionActual = "
                COALESCE(
                    (SELECT v2.id_suc_destino FROM Viaje v2
                     WHERE v2.id_chofer = ch.id_chofer AND v2.estado_viaje = 'Finalizado'
                     ORDER BY v2.id_viaje DESC LIMIT 1),
                    ch.id_sucursal
                )
            ";

            $sql = "
                SELECT ch.id_chofer, ch.legajo, ch.nombre, ch.apellido,
                       (SELECT s.nombre FROM Sucursal s
                        WHERE s.id_sucursal = ({$ubicacionActual})) AS ubicacion_actual
                FROM Chofer ch
                WHERE ch.id_chofer NOT IN (
                    SELECT v.id_chofer FROM Viaje v
                    WHERE v.estado_viaje IN ('Planificado', 'En curso', 'Demorado')
                )
            ";
            $params = [];

            if ($idSucOrigen > 0) {
                $sql .= " AND ({$ubicacionActual}) = ?";
                $params[] = $idSucOrigen;
            }

            $sql .= " ORDER BY ch.apellido, ch.nombre";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getChoferesDisponibles: " . $e->getMessage());
            return [];
        }
    }

    // Vehículos disponibles, opcionalmente filtrados por ubicación actual.
    // Ubicación actual = destino del último viaje Finalizado; si nunca viajó = su sucursal base.
    public function getVehiculosDisponibles(int $idSucOrigen = 0): array {
        try {
            $ubicacionActual = "
                COALESCE(
                    (SELECT v2.id_suc_destino FROM Viaje v2
                     WHERE v2.patente = veh.patente AND v2.estado_viaje = 'Finalizado'
                     ORDER BY v2.id_viaje DESC LIMIT 1),
                    veh.id_sucursal
                )
            ";

            $sql = "
                SELECT veh.patente, tv.nombre AS tipo, veh.marca, veh.modelo,
                       tv.capacidad_kg_max,
                       (SELECT s.nombre FROM Sucursal s
                        WHERE s.id_sucursal = ({$ubicacionActual})) AS ubicacion_actual
                FROM Vehiculo veh
                JOIN Tipo_Vehiculo tv ON tv.id_tipo_veh = veh.id_tipo_veh
                WHERE veh.estado_vehiculo = 'Activo'
                  AND veh.patente NOT IN (
                    SELECT v.patente FROM Viaje v
                    WHERE v.estado_viaje IN ('Planificado', 'En curso', 'Demorado')
                )
            ";
            $params = [];

            if ($idSucOrigen > 0) {
                $sql .= " AND ({$ubicacionActual}) = ?";
                $params[] = $idSucOrigen;
            }

            $sql .= " ORDER BY veh.patente";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getVehiculosDisponibles: " . $e->getMessage());
            return [];
        }
    }

    public function getSucursales(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_sucursal, nombre FROM Sucursal ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getSucursales: " . $e->getMessage());
            return [];
        }
    }

    public function crear(array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Viaje (patente, id_chofer, fecha_salida, estado_viaje)
                VALUES (?, ?, ?, 'Planificado')
            ");
            return $stmt->execute([
                $datos['patente'],
                (int)$datos['id_chofer'],
                $datos['fecha_salida'],
            ]);
        } catch (PDOException $e) {
            error_log("Error en Viaje::crear: " . $e->getMessage());
            return false;
        }
    }

    public function editar(int $id, array $datos): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Viaje
                SET patente = ?, id_chofer = ?, fecha_salida = ?, estado_viaje = ?,
                    id_suc_origen = ?, id_suc_destino = ?
                WHERE id_viaje = ?
            ");
            return $stmt->execute([
                $datos['patente'],
                (int)$datos['id_chofer'],
                $datos['fecha_salida'],
                $datos['estado_viaje'],
                (int)$datos['id_suc_origen'],
                (int)$datos['id_suc_destino'],
                $id,
            ]);
        } catch (PDOException $e) {
            error_log("Error en Viaje::editar: " . $e->getMessage());
            return false;
        }
    }

    // Baja lógica: marcar como Cancelado en lugar de DELETE
    public function desactivar(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE Viaje SET estado_viaje = 'Cancelado' WHERE id_viaje = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en Viaje::desactivar: " . $e->getMessage());
            return false;
        }
    }

    public function getChoferes(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_chofer, nombre, apellido, legajo FROM Chofer ORDER BY apellido, nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getChoferes: " . $e->getMessage());
            return [];
        }
    }

    public function getVehiculos(): array {
        try {
            $stmt = $this->pdo->query("
                SELECT v.patente, tv.nombre AS tipo, v.marca, v.modelo
                FROM Vehiculo v
                JOIN Tipo_Vehiculo tv ON tv.id_tipo_veh = v.id_tipo_veh
                ORDER BY v.patente
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getVehiculos: " . $e->getMessage());
            return [];
        }
    }

    public function contarEnCurso(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM Viaje WHERE estado_viaje = 'En curso'");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Viaje::contarEnCurso: " . $e->getMessage());
            return 0;
        }
    }

    public function contarFinalizados(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM Viaje WHERE estado_viaje = 'Finalizado'");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Viaje::contarFinalizados: " . $e->getMessage());
            return 0;
        }
    }

    public function getEnviosAsignados(int $idViaje): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.id_envio, e.nro_tracking,
                       cr.nombre AS remitente, cr.apellido AS remitente_ap,
                       so.nombre AS origen, sd.nombre AS destino
                FROM Viaje_Envio ve
                JOIN Envio e ON e.id_envio = ve.id_envio
                JOIN Cliente cr ON cr.id_cliente = e.id_remitente
                JOIN Sucursal so ON so.id_sucursal = e.id_suc_origen
                JOIN Sucursal sd ON sd.id_sucursal = e.id_suc_destino
                WHERE ve.id_viaje = ?
            ");
            $stmt->execute([$idViaje]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getEnviosAsignados: " . $e->getMessage());
            return [];
        }
    }

    public function contarIncidentes(int $idChofer): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(i.id_incidente) FROM Incidente i
                JOIN Viaje v ON v.id_viaje = i.id_viaje
                WHERE v.id_chofer = ?
            ");
            $stmt->execute([$idChofer]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Viaje::contarIncidentes: " . $e->getMessage());
            return 0;
        }
    }

    public function getUltimoViaje(int $idChofer): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id_viaje, patente, fecha_salida, estado_viaje
                FROM Viaje WHERE id_chofer = ?
                ORDER BY fecha_salida DESC LIMIT 1
            ");
            $stmt->execute([$idChofer]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getUltimoViaje: " . $e->getMessage());
            return false;
        }
    }

    // Último viaje en curso del chofer con datos GPS del último registro
    public function getViajeEnCurso(int $idChofer): array|false {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    v.id_viaje,
                    v.patente,
                    v.fecha_salida,
                    g_last.velocidad_kmh  AS vel_actual,
                    g_last.fecha_hora     AS ultimo_gps_fecha,
                    COALESCE((
                        SELECT SUM(
                            6371 * 2 * ASIN(SQRT(
                                POWER(SIN(RADIANS((t2.latitud  - t1.latitud)  / 2)), 2) +
                                COS(RADIANS(t1.latitud)) * COS(RADIANS(t2.latitud)) *
                                POWER(SIN(RADIANS((t2.longitud - t1.longitud) / 2)), 2)
                            ))
                        )
                        FROM Trazabilidad_GPS t1
                        JOIN Trazabilidad_GPS t2
                            ON  t1.id_viaje = t2.id_viaje
                            AND t2.id_gps   = (
                                SELECT MIN(g2.id_gps)
                                FROM   Trazabilidad_GPS g2
                                WHERE  g2.id_viaje = t1.id_viaje
                                  AND  g2.id_gps   > t1.id_gps
                            )
                        WHERE t1.id_viaje = v.id_viaje
                    ), 0) AS km_recorridos
                FROM Viaje v
                LEFT JOIN Trazabilidad_GPS g_last
                    ON g_last.id_gps = (
                        SELECT MAX(id_gps) FROM Trazabilidad_GPS WHERE id_viaje = v.id_viaje
                    )
                WHERE v.id_chofer = ? AND v.estado_viaje = 'En curso'
                ORDER BY v.fecha_salida DESC
                LIMIT 1
            ");
            $stmt->execute([$idChofer]);
            return $stmt->fetch() ?: false;
        } catch (PDOException $e) {
            error_log("Error en Viaje::getViajeEnCurso: " . $e->getMessage());
            return false;
        }
    }

    // Últimos N viajes del chofer con velocidad promedio y km recorridos (Haversine)
    public function getHistorialChofer(int $idChofer, int $limite = 5): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    v.id_viaje,
                    v.patente,
                    v.fecha_salida,
                    v.estado_viaje,
                    ROUND(AVG(g.velocidad_kmh), 1) AS vel_promedio,
                    COALESCE((
                        SELECT SUM(
                            6371 * 2 * ASIN(SQRT(
                                POWER(SIN(RADIANS((t2.latitud  - t1.latitud)  / 2)), 2) +
                                COS(RADIANS(t1.latitud)) * COS(RADIANS(t2.latitud)) *
                                POWER(SIN(RADIANS((t2.longitud - t1.longitud) / 2)), 2)
                            ))
                        )
                        FROM Trazabilidad_GPS t1
                        JOIN Trazabilidad_GPS t2
                            ON  t1.id_viaje = t2.id_viaje
                            AND t2.id_gps   = (
                                SELECT MIN(g2.id_gps)
                                FROM   Trazabilidad_GPS g2
                                WHERE  g2.id_viaje = t1.id_viaje
                                  AND  g2.id_gps   > t1.id_gps
                            )
                        WHERE t1.id_viaje = v.id_viaje
                    ), 0) AS km_recorridos
                FROM Viaje v
                LEFT JOIN Trazabilidad_GPS g ON g.id_viaje = v.id_viaje
                WHERE v.id_chofer = ?
                GROUP BY v.id_viaje
                ORDER BY v.fecha_salida DESC
                LIMIT ?
            ");
            $stmt->execute([$idChofer, $limite]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getHistorialChofer: " . $e->getMessage());
            return [];
        }
    }

    // Envíos del viaje con su estado actual, para el control de descarga
    public function getEnviosParaVerificar(int $idViaje): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.id_envio, e.nro_tracking,
                       cr.nombre AS remitente, cr.apellido AS remitente_ap,
                       tc.nombre AS tipo_contenido,
                       h.id_estado AS estado_actual
                FROM Viaje_Envio ve
                JOIN Envio e ON e.id_envio = ve.id_envio
                JOIN Cliente cr ON cr.id_cliente = e.id_remitente
                JOIN Tipo_Contenido tc ON tc.id_tipo_cont = e.id_tipo_cont
                JOIN Historial_Estado h ON h.id_envio = e.id_envio
                    AND h.id_hist = (SELECT MAX(h2.id_hist) FROM Historial_Estado h2 WHERE h2.id_envio = e.id_envio)
                WHERE ve.id_viaje = ?
                ORDER BY e.nro_tracking
            ");
            $stmt->execute([$idViaje]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Viaje::getEnviosParaVerificar: " . $e->getMessage());
            return [];
        }
    }

    //PROCESO DE ALAMCENADO 
    public function finalizarViaje(int $idViaje): bool {
        try {
            $stmt = $this->pdo->prepare("CALL SP_Finalizar_Operatoria_Viaje(:id_viaje)");
            $stmt->bindParam(':id_viaje', $idViaje, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error en Viaje::finalizarViaje SP id={$idViaje}: " . $e->getMessage());
            return false;
        }
    }

    //LLAMADA A LA VISTA
    public function getViajesActivos(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM VW_Viajes_Activos");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Viaje::getViajesActivos: " . $e->getMessage());
            return [];
        }
    }

    public function contarEnCursoPorSucursal(int $idSucursal): int {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(v.id_viaje)
                FROM Viaje v
                JOIN Chofer ch ON ch.id_chofer = v.id_chofer
                WHERE v.estado_viaje = 'En curso'
                  AND ch.id_sucursal = ?
            ");
            $stmt->execute([$idSucursal]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Viaje::contarEnCursoPorSucursal: " . $e->getMessage());
            return 0;
        }
    }
}
