
INSERT INTO usuarios
(id_usuario, nombre, usuario, `password`, rol)
VALUES
(1, 'Administrador Principal', 'admin',
'DonPanchoadmin123@', 'admin'),

(2, 'Carlos Mendoza', 'carlos',
'DonPanchobodega123@', 'bodeguero'),

(3, 'Ana Lopez', 'ana',
'DonPanchobodega123@', 'bodeguero'),

(4, 'Jose Martinez', 'jose',
'DonPanchobodega123@', 'bodeguero'),

(5, 'Maria Flores', 'maria',
'DonPanchobodega123@', 'bodeguero');


-- 2. CATEGORIAS

INSERT INTO categorias
(id_categoria, nombre, descripcion, activo)
VALUES
(1, 'Frenos y Suspensión', 'Pastillas, discos y amortiguadores', 1),

(2, 'Motor y Transmisión', 'Repuestos relacionados con el motor', 1),

(3, 'Sistema Eléctrico', 'Alternadores, baterias y componentes eléctricos', 1),

(4, 'Fluidos y Lubricantes', 'Aceites, refrigerantes y líquidos automotrices', 1),

(5, 'Kits de Mantenimiento', 'Kits de afinamiento y mantenimiento preventivo', 1);


-- 3. SUCURSALES

INSERT INTO sucursales
(id_sucursal, nombre, ciudad, direccion, activo)
VALUES
(1, 'Sucursal Toncontin', 'Tegucigalpa', 'Boulevard Comunidad Economica Europea', 1),

(2, 'Sucursal Suyapa', 'Tegucigalpa', 'Avenida Suyapa', 1),

(3, 'Sucursal Medina', 'San Pedro Sula', 'Barrio Medina', 1),

(4, 'Sucursal CA-13', 'La Ceiba', 'Carretera CA-13', 1),

(5, 'Bodega Central', 'Tegucigalpa', 'Centro de almacenamiento de prueba', 1);


-- 4. PRODUCTOS

INSERT INTO productos
(id_producto, id_categoria, nombre, descripcion, especificaciones,
 precio, fotografia, marca_vehiculo, tipo_vehiculo, viscosidad, activo)
VALUES

(1, 1, 'Pastillas de freno delanteras',
 'Juego de pastillas para frenos delanteros',
 'Material semimetalico', 850.00,
 'pastillas_toyota.jpg', 'Toyota', 'sedan', NULL, 1),

(2, 1, 'Amortiguador delantero',
 'Amortiguador para suspension delantera',
 'Amortiguador hidraulico', 2100.00,
 'amortiguador_nissan.jpg', 'Nissan', 'suv', NULL, 1),

(3, 2, 'Filtro de aceite',
 'Filtro para sistema de lubricacion',
 'Filtro de alto rendimiento', 180.00,
 'filtro_honda.jpg', 'Honda', 'sedan', NULL, 1),

(4, 3, 'Alternador',
 'Alternador para sistema electrico',
 'Alternador de 12 voltios', 2600.00,
 'alternador_hyundai.jpg', 'Hyundai', 'sedan', NULL, 1),

(5, 5, 'Kit de afinamiento',
 'Kit para mantenimiento preventivo',
 'Incluye componentes basicos de afinamiento', 1450.00,
 'kit_ford.jpg', 'Ford', 'pickup_4x4', NULL, 1);


-- 5. INVENTARIO
-- Cada producto se registra en una sucursal diferente.

INSERT INTO inventario
(id_inventario, id_producto, id_sucursal, cantidad, activo)
VALUES
(1, 1, 1, 15, 1),
(2, 2, 2, 8, 1),
(3, 3, 3, 35, 1),
(4, 4, 4, 4, 1),
(5, 5, 5, 10, 1);


-- 6. CLIENTES
-- RTN ficticios para pruebas, no utilizarlos en facturas reales.

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


-- 7. FACTURAS
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


-- 8. DETALLE_FACTURA
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

