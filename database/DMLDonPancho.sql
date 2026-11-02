
USE don_pancho;

-- ============================================================
-- DML - AUTOPARTES Y REPUESTOS DON PANCHO
-- Datos iniciales y datos de prueba del sistema
-- ============================================================


-- ============================================================
-- 1. USUARIOS
-- ============================================================

INSERT INTO usuarios
(id_usuario, nombre, usuario, `contraseña`, rol)
VALUES
(1, 'Administrador Principal', 'admin',
'$2y$10$TTA/qCJSCCIL86.6AHAqB.5aFcgO2bbY57.0uIKHCJoJ.Ll/tl8Ny', 'admin'),

(2, 'Carlos Mendoza', 'carlos',
'$2y$10$8EoNTGAowIWYV/TBA1VPbef5vPu7aN0hxA/59Ui2wsTPLG6eNC5D2', 'bodeguero'),

(3, 'Ana Lopez', 'ana',
'$2y$10$PNkwKbf8ij1fTeu42YrzCePBmaxFXF2hRAllgYCAUhDU.MQtziIFG', 'bodeguero'),

(4, 'Jose Martinez', 'jose',
'$2y$10$GhjtRiTw/HEer0AmfCCpBuY/5mpHA3cLCZupI0acR7pVCWe6wdSAq', 'bodeguero'),

(5, 'Maria Flores', 'maria',
'$2y$10$6eMUMLE0h0UUS3SEupUlaumHhJBCXSBhgLU5z2RtKvBlEJsDQi8LK', 'bodeguero');


-- ============================================================
-- 2. CATEGORÍAS
-- ============================================================

INSERT INTO categorias
(id_categoria, nombre, descripcion, activo)
VALUES
(1, 'Frenos y Suspensión',
 'Pastillas, discos y amortiguadores', 1),

(2, 'Motor y Transmisión',
 'Repuestos relacionados con el motor', 1),

(3, 'Sistema Eléctrico',
 'Alternadores, baterias y componentes eléctricos', 1),

(4, 'Fluidos y Lubricantes',
 'Aceites, refrigerantes y líquidos automotrices', 1),

(5, 'Kits de Mantenimiento',
 'Kits de afinamiento y mantenimiento preventivo', 1);


-- ============================================================
-- 3. SUCURSALES
-- ============================================================

INSERT INTO sucursales
(id_sucursal, nombre, ciudad, direccion, activo)
VALUES
(1, 'Sucursal Toncontin', 'Tegucigalpa',
 'Boulevard Comunidad Economica Europea', 1),

(2, 'Sucursal Suyapa', 'Tegucigalpa',
 'Avenida Suyapa', 1),

(3, 'Sucursal Medina', 'San Pedro Sula',
 'Barrio Medina', 1),

(4, 'Sucursal CA-13', 'La Ceiba',
 'Carretera CA-13', 1),

(5, 'Bodega Central', 'Tegucigalpa',
 'Centro de almacenamiento de prueba', 1);


-- ============================================================
-- 4. PRODUCTOS INICIALES
-- ============================================================

INSERT INTO productos
(id_producto, id_categoria, nombre, descripcion,
 especificaciones, precio, fotografia, marca_vehiculo,
 tipo_vehiculo, viscosidad, activo)
VALUES

(1, 1, 'Pastillas de freno delanteras',
 'Juego de pastillas para frenos delanteros',
 'Material semimetalico',
 850.00, 'pastillas_toyota.jpg',
 'Toyota', 'sedan', NULL, 1),

(2, 1, 'Amortiguador delantero',
 'Amortiguador para suspension delantera',
 'Amortiguador hidraulico',
 2100.00, 'amortiguador_nissan.jpg',
 'Nissan', 'suv', NULL, 1),

(3, 2, 'Filtro de aceite',
 'Filtro para sistema de lubricacion',
 'Filtro de alto rendimiento',
 180.00, 'filtro_honda.jpg',
 'Honda', 'sedan', NULL, 1),

(4, 3, 'Alternador',
 'Alternador para sistema electrico',
 'Alternador de 12 voltios',
 2600.00, 'alternador_hyundai.jpg',
 'Hyundai', 'sedan', NULL, 1),

(5, 5, 'Kit de afinamiento',
 'Kit para mantenimiento preventivo',
 'Incluye componentes basicos de afinamiento',
 1450.00, 'kit_ford.jpg',
 'Ford', 'pickup_4x4', NULL, 1);


-- ============================================================
-- 5. PRODUCTOS DE PRUEBA PARA EL COTIZADOR ARMA TU KIT
-- ============================================================
-- Se registran 20 productos adicionales.
-- Cada grupo contiene aceite, filtro de aceite,
-- filtro de aire y bujías.
-- Los precios son de prueba y están expresados en lempiras.
-- La fotografía se deja NULL porque no se proporcionaron
-- archivos de imagen para estos productos.


-- ============================================================
-- 5.1. TOYOTA COROLLA - SEDÁN
-- ============================================================

INSERT INTO productos
(id_producto, id_categoria, nombre, descripcion,
 especificaciones, precio, fotografia, marca_vehiculo,
 tipo_vehiculo, viscosidad, activo)
VALUES

(6, 4, 'Aceite sintético Toyota 5W-30',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 5W-30, presentación 4 litros.',
 950.00, NULL, 'Toyota', 'sedan', '5W-30', 1),

(7, 2, 'Filtro de aceite Toyota Corolla',
 'Filtro de aceite para motor.',
 'Filtro compatible con Toyota Corolla.',
 220.00, NULL, 'Toyota', 'sedan', NULL, 1),

(8, 2, 'Filtro de aire Toyota Corolla',
 'Filtro de aire para motor.',
 'Filtro de aire compatible con Toyota Corolla.',
 350.00, NULL, 'Toyota', 'sedan', NULL, 1),

(9, 2, 'Bujías Toyota Corolla',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 600.00, NULL, 'Toyota', 'sedan', NULL, 1);


-- ============================================================
-- 5.2. HONDA CIVIC - SEDÁN
-- ============================================================

INSERT INTO productos
(id_producto, id_categoria, nombre, descripcion,
 especificaciones, precio, fotografia, marca_vehiculo,
 tipo_vehiculo, viscosidad, activo)
VALUES

(10, 4, 'Aceite sintético Honda 0W-20',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 0W-20, presentación 4 litros.',
 1100.00, NULL, 'Honda', 'sedan', '0W-20', 1),

(11, 2, 'Filtro de aceite Honda Civic',
 'Filtro de aceite para motor.',
 'Filtro compatible con Honda Civic.',
 240.00, NULL, 'Honda', 'sedan', NULL, 1),

(12, 2, 'Filtro de aire Honda Civic',
 'Filtro de aire para motor.',
 'Filtro compatible con Honda Civic.',
 380.00, NULL, 'Honda', 'sedan', NULL, 1),

(13, 2, 'Bujías Honda Civic',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 680.00, NULL, 'Honda', 'sedan', NULL, 1);


-- ============================================================
-- 5.3. HYUNDAI TUCSON - SUV
-- ============================================================

INSERT INTO productos
(id_producto, id_categoria, nombre, descripcion,
 especificaciones, precio, fotografia, marca_vehiculo,
 tipo_vehiculo, viscosidad, activo)
VALUES

(14, 4, 'Aceite sintético Hyundai 5W-30',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 5W-30, presentación 4 litros.',
 980.00, NULL, 'Hyundai', 'suv', '5W-30', 1),

(15, 2, 'Filtro de aceite Hyundai Tucson',
 'Filtro de aceite para motor.',
 'Filtro compatible con Hyundai Tucson.',
 260.00, NULL, 'Hyundai', 'suv', NULL, 1),

(16, 2, 'Filtro de aire Hyundai Tucson',
 'Filtro de aire para motor.',
 'Filtro compatible con Hyundai Tucson.',
 420.00, NULL, 'Hyundai', 'suv', NULL, 1),

(17, 2, 'Bujías Hyundai Tucson',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 720.00, NULL, 'Hyundai', 'suv', NULL, 1);


-- ============================================================
-- 5.4. NISSAN X-TRAIL - SUV
-- ============================================================

INSERT INTO productos
(id_producto, id_categoria, nombre, descripcion,
 especificaciones, precio, fotografia, marca_vehiculo,
 tipo_vehiculo, viscosidad, activo)
VALUES

(18, 4, 'Aceite sintético Nissan 5W-30',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 5W-30, presentación 4 litros.',
 1050.00, NULL, 'Nissan', 'suv', '5W-30', 1),

(19, 2, 'Filtro de aceite Nissan X-Trail',
 'Filtro de aceite para motor.',
 'Filtro compatible con Nissan X-Trail.',
 250.00, NULL, 'Nissan', 'suv', NULL, 1),

(20, 2, 'Filtro de aire Nissan X-Trail',
 'Filtro de aire para motor.',
 'Filtro compatible con Nissan X-Trail.',
 390.00, NULL, 'Nissan', 'suv', NULL, 1),

(21, 2, 'Bujías Nissan X-Trail',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 650.00, NULL, 'Nissan', 'suv', NULL, 1);


-- ============================================================
-- 5.5. TOYOTA HILUX - PICKUP 4X4
-- ============================================================

INSERT INTO productos
(id_producto, id_categoria, nombre, descripcion,
 especificaciones, precio, fotografia, marca_vehiculo,
 tipo_vehiculo, viscosidad, activo)
VALUES

(22, 4, 'Aceite diesel Toyota Hilux 15W-40',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 15W-40, presentación 4 litros.',
 1200.00, NULL, 'Toyota', 'pickup_4x4', '15W-40', 1),

(23, 2, 'Filtro de aceite Toyota Hilux',
 'Filtro de aceite para motor.',
 'Filtro compatible con Toyota Hilux.',
 280.00, NULL, 'Toyota', 'pickup_4x4', NULL, 1),

(24, 2, 'Filtro de aire Toyota Hilux',
 'Filtro de aire para motor.',
 'Filtro compatible con Toyota Hilux.',
 480.00, NULL, 'Toyota', 'pickup_4x4', NULL, 1),

(25, 2, 'Bujías Toyota Hilux',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 750.00, NULL, 'Toyota', 'pickup_4x4', NULL, 1);


-- ============================================================
-- 6. INVENTARIO INICIAL
-- ============================================================
-- Cada producto inicial se registra en una sucursal diferente.

INSERT INTO inventario
(id_inventario, id_producto, id_sucursal, cantidad, activo)
VALUES
(1, 1, 1, 15, 1),
(2, 2, 2, 8, 1),
(3, 3, 3, 35, 1),
(4, 4, 4, 4, 1),
(5, 5, 5, 10, 1);


-- ============================================================
-- 7. INVENTARIO DE PRODUCTOS DEL COTIZADOR
-- ============================================================
-- Se registran 20 unidades de cada producto adicional
-- en la Sucursal Toncontin, cuyo ID es 1.
-- Los productos del cotizador tienen IDs del 6 al 25.

INSERT INTO inventario
(id_producto, id_sucursal, cantidad, activo)
VALUES
(6, 1, 20, 1),
(7, 1, 20, 1),
(8, 1, 20, 1),
(9, 1, 20, 1),

(10, 1, 20, 1),
(11, 1, 20, 1),
(12, 1, 20, 1),
(13, 1, 20, 1),

(14, 1, 20, 1),
(15, 1, 20, 1),
(16, 1, 20, 1),
(17, 1, 20, 1),

(18, 1, 20, 1),
(19, 1, 20, 1),
(20, 1, 20, 1),
(21, 1, 20, 1),

(22, 1, 20, 1),
(23, 1, 20, 1),
(24, 1, 20, 1),
(25, 1, 20, 1);


-- ============================================================
-- 8. CLIENTES
-- ============================================================
-- RTN ficticios para pruebas.
-- No utilizarlos en facturas reales.

INSERT INTO clientes
(id_cliente, nombre, rtn, telefono, correo, direccion, activo)
VALUES

(1, 'Pedro Hernandez', '00000000000001',
 '99990001', 'pedro@gmail.com', 'Tegucigalpa', 1),

(2, 'Laura Garcia', '00000000000002',
 '99990002', 'laura@gmail.com', 'Comayaguela', 1),

(3, 'Roberto Castillo', '00000000000003',
 '99990003', 'roberto@gmail.com', 'San Pedro Sula', 1),

(4, 'Sofia Martinez', '00000000000004',
 '99990004', 'sofia@gmail.com', 'La Ceiba', 1),

(5, 'Daniel Rivera', '00000000000005',
 '99990005', 'daniel@gmail.com', 'Tegucigalpa', 1);


-- ============================================================
-- 9. FACTURAS
-- ============================================================
-- Los valores de ISV, flete y total son ejemplos de prueba.
-- No se aplicaron descuentos en estas facturas.

INSERT INTO facturas
(id_factura, numero_factura, id_cliente, id_sucursal,
 subtotal, descuento, tasa_isv, isv, flete, total,
 modalidad_flete, anulada)
VALUES

(1, 'DP-DEMO-0001', 1, 1,
 1700.00, 0.00, 15.00, 255.00, 50.00, 2005.00,
 'moto_express', 0),

(2, 'DP-DEMO-0002', 2, 2,
 2100.00, 0.00, 15.00, 315.00, 180.00, 2595.00,
 'camioncito', 0),

(3, 'DP-DEMO-0003', 3, 3,
 540.00, 0.00, 15.00, 81.00, 0.00, 621.00,
 'mostrador', 0),

(4, 'DP-DEMO-0004', 4, 4,
 2600.00, 0.00, 15.00, 390.00, 50.00, 3040.00,
 'moto_express', 0),

(5, 'DP-DEMO-0005', 5, 5,
 2900.00, 0.00, 15.00, 435.00, 180.00, 3515.00,
 'camioncito', 0);


-- ============================================================
-- 10. DETALLE DE FACTURAS
-- ============================================================
-- Cada detalle corresponde a una factura y un producto.

INSERT INTO detalle_factura
(id_detalle, id_factura, id_producto,
 cantidad, precio_unitario, subtotal)
VALUES

(1, 1, 1, 2, 850.00, 1700.00),
(2, 2, 2, 1, 2100.00, 2100.00),
(3, 3, 3, 3, 180.00, 540.00),
(4, 4, 4, 1, 2600.00, 2600.00),
(5, 5, 5, 2, 1450.00, 2900.00);


-- ============================================================
-- FIN DEL DML
-- ============================================================
