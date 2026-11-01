
-- BASE DE DATOS: DON PANCHO
-- Sistema de Autopartes & Repuestos "Don Pancho"

-- POLITICA DE BORRADO:
--   * ON DELETE RESTRICT en FKs con historial fiscal
--     (productos, clientes, facturas) para proteger el SAR.
-- practicamente es para que si tengo una factura de algun producto y elimino el 
-- producto no se elimine ya que las facturas de ese producto serian como un invento
-- una factura fantasma por decirlo de alguna manera (factura sin producto)


--   * ON DELETE CASCADE solo en detalle_factura → facturas
--     (el detalle no existe sin la factura).

--   * Para "eliminar" productos/clientes/categorias se usa un soft delete que es un update:
--     UPDATE productos 
--     SET activo = 0  
--     WHERE id_producto =1; 
--     nunca DELETE fisico.
--     el soft delete solo desactiva mediante un 

--   * Para anular una factura se usa anulada = 1.





-- 1. TABLA USUARIOS
-- Login, roles y control de acceso

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50)  NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,           
    rol        ENUM('admin','bodeguero') NOT NULL
);



-- 2. TABLA CATEGORIAS
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    activo TINYINT(1) NOT NULL DEFAULT 1   
);



-- 3. TABLA SUCURSALES
CREATE TABLE sucursales (
    id_sucursal INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    activo TINYINT(1) NOT NULL DEFAULT 1    
);



-- 4. TABLA PRODUCTOS
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255),
    especificaciones VARCHAR(255),
    precio DECIMAL(10,2) NOT NULL,
    fotografia VARCHAR(255) DEFAULT NULL,
    marca_vehiculo VARCHAR(50)  DEFAULT NULL,
    tipo_vehiculo ENUM('sedan','suv','pickup_4x4') DEFAULT NULL,
    viscosidad VARCHAR(20)  DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,  

    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);



-- 5. TABLA INVENTARIO
CREATE TABLE inventario (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_sucursal INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
       
    CONSTRAINT fk_inventario_producto
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    
        
    CONSTRAINT fk_inventario_sucursal
        FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_producto_sucursal
        UNIQUE (id_producto, id_sucursal)

        
);



-- 6. TABLA CLIENTES
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    rtn VARCHAR(20)  NOT NULL UNIQUE,
    telefono VARCHAR(20),
    correo VARCHAR(150),
    direccion  VARCHAR(255),
    activo TINYINT(1) NOT NULL DEFAULT 1     
);



-- 7. TABLA FACTURAS
CREATE TABLE facturas (
    id_factura INT AUTO_INCREMENT PRIMARY KEY,
    numero_factura VARCHAR(30) NOT NULL UNIQUE,
    id_cliente INT NOT NULL,
    id_sucursal   INT NOT NULL,
    fecha_emision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tasa_isv DECIMAL(5,2)  NOT NULL DEFAULT 15.00,
    isv DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    flete DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,

    modalidad_flete ENUM('mostrador','moto_express','camioncito')
                    NOT NULL DEFAULT 'mostrador',

    anulada TINYINT(1) NOT NULL DEFAULT 0,   

    CONSTRAINT fk_factura_cliente
        FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    
    CONSTRAINT fk_factura_sucursal
        FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);



-- 8. TABLA DETALLE_FACTURA
-- Productos que contiene cada factura
CREATE TABLE detalle_factura (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_factura INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario  DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_detalle_factura
        FOREIGN KEY (id_factura) REFERENCES facturas(id_factura)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


