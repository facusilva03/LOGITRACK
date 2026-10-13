-- LogiTrack · datos de demostración
-- Todo lo que hay acá es FICTICIO: nombres, documentos, patentes y teléfonos
-- están inventados. Los catálogos (roles, estados, tipos) son los de la aplicación.
--
-- Usuarios de prueba (contraseña de todos: LogiTrack-demo-2026)
--   admin.demo     Administrador
--   empleado.demo  Empleado de sucursal
--   chofer.demo    Chofer
--   cliente.demo   Cliente
-- NO usar estas credenciales fuera de un entorno de pruebas local.

SET NAMES utf8mb4;

-- ── Catálogos ───────────────────────────────────────────────────────────────
INSERT INTO Rol (id_rol, nombre) VALUES
  (1,'Administrador'),(2,'Chofer'),(3,'Cliente'),(4,'Empleado Sucursal');

INSERT INTO Estado_Catalogo (id_estado, nombre_estado, descripcion) VALUES
  (1,'Enviado','El paquete fue despachado desde la sucursal origen'),
  (2,'Recibido','El paquete fue recibido en una sucursal intermedia o destino'),
  (3,'Demorado','El paquete presenta una demora en su trayecto'),
  (4,'En camino','El paquete está en tránsito hacia su destino'),
  (5,'Entregado','El paquete fue entregado exitosamente al destinatario'),
  (6,'Devuelto','El paquete fue devuelto a la sucursal origen'),
  (7,'No entregado','Se intentó la entrega pero no fue posible concretarla');

INSERT INTO Tipo_Contenido (id_tipo_cont, nombre, requiere_seguro, descripcion) VALUES
  (1,'Estándar',0,'Mercadería general sin requerimientos especiales'),
  (2,'Frágil',1,'Artículos que requieren manipulación cuidadosa'),
  (3,'Peligroso',1,'Materiales peligrosos con regulación especial'),
  (4,'Refrigerado',1,'Productos que requieren cadena de frío'),
  (5,'Sobredimensionado',0,'Carga que excede medidas estándar');

INSERT INTO Tipo_Vehiculo (id_tipo_veh, nombre, capacidad_kg_max, requiere_licencia_especial) VALUES
  (1,'Camión mediano',5000.00,0),(2,'Camión pesado',15000.00,1),(3,'Semirremolque',28000.00,1),
  (4,'Furgón',1500.00,0),(5,'Camión cisterna',20000.00,1);

INSERT INTO Localidad (id_localidad, nombre, provincia) VALUES
  (1,'Mendoza','Mendoza'),(2,'San Rafael','Mendoza'),(3,'Godoy Cruz','Mendoza'),
  (4,'Maipú','Mendoza'),(5,'Luján de Cuyo','Mendoza'),(6,'Las Heras','Mendoza'),
  (7,'La Plata','Buenos Aires'),(8,'Mar del Plata','Buenos Aires'),
  (9,'Bahía Blanca','Buenos Aires'),(10,'Quilmes','Buenos Aires');

INSERT INTO Aseguradora (id_aseguradora, nombre, telefono_emergencia, web) VALUES
  (1,'Aseguradora Demo S.A.','0800-000-0000','www.ejemplo.com');

-- ── Estructura operativa (ficticia) ─────────────────────────────────────────
INSERT INTO Sucursal (id_sucursal, nombre, id_localidad, responsable) VALUES
  (1,'Sucursal Mendoza Centro',1,'Responsable Demo 1'),
  (2,'Sucursal San Rafael',2,'Responsable Demo 2'),
  (3,'Sucursal La Plata',7,'Responsable Demo 3');

INSERT INTO Empleado (id_empleado, nombre, apellido, id_sucursal) VALUES
  (1,'Empleado','Demo Uno',1),(2,'Empleado','Demo Dos',2);

INSERT INTO Chofer (id_chofer, legajo, dni, nombre, apellido, tipo_licencia, id_sucursal) VALUES
  (1,'CHO-0001','90000001','Chofer','Demo Uno','C',1),
  (2,'CHO-0002','90000002','Chofer','Demo Dos','B',2);

INSERT INTO Cliente (id_cliente, tipo_documento, documento, nombre, apellido, id_localidad, direccion) VALUES
  (1,'DNI','90000011','Cliente','Demo Uno',1,'Calle Ficticia 100'),
  (2,'DNI','90000012','Cliente','Demo Dos',2,'Calle Ficticia 200'),
  (3,'DNI','90000013','Cliente','Demo Tres',7,'Calle Ficticia 300'),
  (4,'DNI','90000014','Cliente','Demo Cuatro',3,'Calle Ficticia 400');

INSERT INTO Vehiculo (patente, id_tipo_veh, marca, modelo, id_sucursal) VALUES
  ('DEMO001',4,'Marca Demo','Modelo A',1),('DEMO002',1,'Marca Demo','Modelo B',2);

INSERT INTO Usuario (id_usuario, username, password_hash, id_rol, id_chofer, id_cliente, id_empleado, activo) VALUES
  (1,'admin.demo','$2y$12$2KO2kx70WjThT2fGAlPOG.F2uHTCklRs4SUHsgm9fBPC/R8vC2Zk2',1,NULL,NULL,NULL,1),
  (2,'empleado.demo','$2y$12$2KO2kx70WjThT2fGAlPOG.F2uHTCklRs4SUHsgm9fBPC/R8vC2Zk2',4,NULL,NULL,1,1),
  (3,'chofer.demo','$2y$12$2KO2kx70WjThT2fGAlPOG.F2uHTCklRs4SUHsgm9fBPC/R8vC2Zk2',2,1,NULL,NULL,1),
  (4,'cliente.demo','$2y$12$2KO2kx70WjThT2fGAlPOG.F2uHTCklRs4SUHsgm9fBPC/R8vC2Zk2',3,NULL,1,NULL,1);

-- ── Envíos y un viaje en curso ──────────────────────────────────────────────
INSERT INTO Envio (id_envio, nro_tracking, id_remitente, id_destinatario, id_suc_origen, id_suc_destino, id_tipo_cont, peso_kg) VALUES
  (1,'LT-DEMO0001',1,2,1,2,1,12.50),
  (2,'LT-DEMO0002',2,3,2,3,2,3.20),
  (3,'LT-DEMO0003',3,4,3,1,1,45.00);

INSERT INTO Seguro_Envio (id_envio, id_aseguradora, nro_poliza, monto_asegurado) VALUES
  (2,1,'POL-DEMO-0001',50000.00);

INSERT INTO Historial_Estado (id_envio, id_estado, fecha_hora, id_sucursal_actual) VALUES
  (1,1,'2026-01-10 09:00:00',1),(1,4,'2026-01-10 10:30:00',1),
  (2,1,'2026-01-10 09:15:00',2),
  (3,1,'2026-01-11 08:00:00',3),(3,5,'2026-01-12 16:45:00',1);

INSERT INTO Viaje (id_viaje, patente, id_chofer, fecha_salida, estado_viaje, id_suc_origen, id_suc_destino) VALUES
  (1,'DEMO001',1,'2026-01-10 10:00:00','En curso',1,2);

INSERT INTO Viaje_Envio (id_viaje, id_envio) VALUES (1,1);

INSERT INTO Trazabilidad_GPS (id_viaje, latitud, longitud, velocidad_kmh, fecha_hora) VALUES
  (1,-32.88900000,-68.84500000,62.0,'2026-01-10 10:30:00'),
  (1,-33.05000000,-68.70000000,70.5,'2026-01-10 11:15:00');
