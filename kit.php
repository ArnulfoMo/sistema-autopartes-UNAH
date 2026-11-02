<?php

declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/clases/kit.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

/*
|--------------------------------------------------------------------------
| Catálogo de vehículos
|--------------------------------------------------------------------------
*/

$vehiculos = KitMantenimiento::catalogoVehiculos();

/*
|--------------------------------------------------------------------------
| Petición AJAX para consultar productos
|--------------------------------------------------------------------------
*/

if (isset($_GET['accion']) && $_GET['accion'] === 'productos') {

    header('Content-Type: application/json; charset=utf-8');

    if ($pdo === null) {
        http_response_code(500);

        echo json_encode([
            'error' => 'No se pudo conectar con la base de datos.'
        ]);

        exit;
    }

    $tipo = $_GET['tipo'] ?? '';
    $marca = $_GET['marca'] ?? '';

    $tiposPermitidos = ['sedan', 'suv', 'pickup_4x4'];

    if (!in_array($tipo, $tiposPermitidos, true)) {
        http_response_code(400);

        echo json_encode([
            'error' => 'Tipo de vehículo inválido.'
        ]);

        exit;
    }

    if (!isset($vehiculos[$tipo][$marca])) {
        http_response_code(400);

        echo json_encode([
            'error' => 'Marca de vehículo inválida.'
        ]);

        exit;
    }

    try {

        $kit = new KitMantenimiento($pdo);

        $respuesta = [
            'aceites' => $kit->obtenerProductos(
                $tipo,
                $marca,
                'aceites'
            ),

            'filtros' => $kit->obtenerProductos(
                $tipo,
                $marca,
                'filtros'
            ),

            'repuestos' => $kit->obtenerProductos(
                $tipo,
                $marca,
                'repuestos'
            )
        ];

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

    } catch (PDOException $e) {

        error_log('Error en cotizador: ' . $e->getMessage());

        http_response_code(500);

        echo json_encode([
            'error' => 'Error al consultar los productos.'
        ]);
    }

    exit;
}

require_once __DIR__ . '/includes/header.php';

?>

<link rel="stylesheet" href="assets/css/kit.css">

<section class="container py-5">

    <div class="kit-header text-center mb-5">

        <h1>Cotizador de mantenimiento Tune-Up</h1>

        <p>
            Selecciona los productos necesarios para realizar
            el mantenimiento de tu vehículo.
        </p>

    </div>

    <!-- VEHÍCULO -->

    <div class="kit-section">

        <h3>1. Selecciona tu vehículo</h3>

        <div class="row g-3">

            <div class="col-md-4">

                <label for="tipoVehiculo" class="form-label">
                    Tipo de vehículo
                </label>

                <select id="tipoVehiculo" class="form-select">

                    <option value="">Seleccionar...</option>

                    <option value="sedan">Sedán</option>

                    <option value="suv">SUV</option>

                    <option value="pickup_4x4">Pick Up 4x4</option>

                </select>

            </div>

            <div class="col-md-4">

                <label for="marcaVehiculo" class="form-label">
                    Marca
                </label>

                <select id="marcaVehiculo" class="form-select" disabled>

                    <option value="">Selecciona una marca</option>

                </select>

            </div>

            <div class="col-md-4">

                <label for="modeloVehiculo" class="form-label">
                    Modelo
                </label>

                <select id="modeloVehiculo" class="form-select" disabled>

                    <option value="">Selecciona un modelo</option>

                </select>

            </div>

        </div>

    </div>

    <!-- ACEITES -->

    <div class="kit-section">

        <h3>2. Selecciona el aceite</h3>

        <div class="mb-3">

            <label for="viscosidad" class="form-label">
                Viscosidad
            </label>

            <select id="viscosidad" class="form-select" disabled>

                <option value="">Primero selecciona tu vehículo</option>

            </select>

        </div>

        <div id="contenedorAceites" class="row g-3">

            <p class="text-muted">
                Selecciona un vehículo para consultar los aceites disponibles.
            </p>

        </div>

    </div>

    <!-- FILTROS -->

    <div class="kit-section">

        <h3>3. Selecciona los filtros</h3>

        <p class="text-muted">
            Puedes seleccionar uno o varios filtros.
        </p>

        <div id="contenedorFiltros" class="row g-3">

            <p class="text-muted">
                Primero selecciona tu vehículo.
            </p>

        </div>

    </div>

    <!-- REPUESTOS -->

    <div class="kit-section">

        <h3>4. Repuestos adicionales</h3>

        <p class="text-muted">
            Agrega los repuestos adicionales que necesites.
        </p>

        <div id="contenedorRepuestos" class="row g-3">

            <p class="text-muted">
                Primero selecciona tu vehículo.
            </p>

        </div>

    </div>

    <!-- RESUMEN -->

    <div class="kit-section kit-resumen">

        <h3>5. Resumen del mantenimiento</h3>

        <div id="resumenKit">

            <p class="text-muted">
                Todavía no has seleccionado productos.
            </p>

        </div>

        <div class="kit-total">

            <span>Total del kit:</span>

            <span id="totalKit">L 0.00</span>

        </div>

        <form action="carrito.php" method="POST" id="formKit">

            <input
                type="hidden"
                name="accion"
                value="agregar"
            >

            <div id="productosCarrito"></div>

            <button
                type="submit"
                id="btnAgregarCarrito"
                class="btn btn-primary w-100 mt-4"
                disabled
            >
                Agregar kit al carrito
            </button>

        </form>

    </div>

</section>

<script>
    window.KIT_VEHICULOS = <?= json_encode(
        $vehiculos,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS |
        JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;
</script>

<script src="assets/js/kit.js" defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- /**DATOS DE PRUEBA UTILIZADOS

USE don_pancho;

-- ====================
-- KIT TOYOTA SEDAN
-- ====================

INSERT INTO productos
(id_categoria, nombre, descripcion, especificaciones, precio,
 marca_vehiculo, tipo_vehiculo, viscosidad, activo)
VALUES
(4, 'Aceite sintético Toyota 5W-30',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 5W-30, presentación 4 litros.',
 950.00, 'Toyota', 'sedan', '5W-30', 1),

(2, 'Filtro de aceite Toyota Corolla',
 'Filtro de aceite para motor.',
 'Filtro compatible con Toyota Corolla.',
 220.00, 'Toyota', 'sedan', NULL, 1),

(2, 'Filtro de aire Toyota Corolla',
 'Filtro de aire para motor.',
 'Filtro de aire compatible con Toyota Corolla.',
 350.00, 'Toyota', 'sedan', NULL, 1),

(2, 'Bujías Toyota Corolla',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 600.00, 'Toyota', 'sedan', NULL, 1);


-- =========================================
-- KIT 2: HONDA CIVIC - SEDAN
-- ===========

INSERT INTO productos
(id_categoria, nombre, descripcion, especificaciones, precio,
 marca_vehiculo, tipo_vehiculo, viscosidad, activo)
VALUES
(4, 'Aceite sintético Honda 0W-20',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 0W-20, presentación 4 litros.',
 1100.00, 'Honda', 'sedan', '0W-20', 1),

(2, 'Filtro de aceite Honda Civic',
 'Filtro de aceite para motor.',
 'Filtro compatible con Honda Civic.',
 240.00, 'Honda', 'sedan', NULL, 1),

(2, 'Filtro de aire Honda Civic',
 'Filtro de aire para motor.',
 'Filtro compatible con Honda Civic.',
 380.00, 'Honda', 'sedan', NULL, 1),

(2, 'Bujías Honda Civic',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 680.00, 'Honda', 'sedan', NULL, 1);


-- ===========
-- HYUNDAI
-- ===========

INSERT INTO productos
(id_categoria, nombre, descripcion, especificaciones, precio,
 marca_vehiculo, tipo_vehiculo, viscosidad, activo)
VALUES
(4, 'Aceite sintético Hyundai 5W-30',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 5W-30, presentación 4 litros.',
 980.00, 'Hyundai', 'suv', '5W-30', 1),

(2, 'Filtro de aceite Hyundai Tucson',
 'Filtro de aceite para motor.',
 'Filtro compatible con Hyundai Tucson.',
 260.00, 'Hyundai', 'suv', NULL, 1),

(2, 'Filtro de aire Hyundai Tucson',
 'Filtro de aire para motor.',
 'Filtro compatible con Hyundai Tucson.',
 420.00, 'Hyundai', 'suv', NULL, 1),

(2, 'Bujías Hyundai Tucson',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 720.00, 'Hyundai', 'suv', NULL, 1);


-- ===============================
-- NISSAN X-TRAIL - SUV
-- ===============================

INSERT INTO productos
(id_categoria, nombre, descripcion, especificaciones, precio,
 marca_vehiculo, tipo_vehiculo, viscosidad, activo)
VALUES
(4, 'Aceite sintético Nissan 5W-30',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 5W-30, presentación 4 litros.',
 1050.00, 'Nissan', 'suv', '5W-30', 1),

(2, 'Filtro de aceite Nissan X-Trail',
 'Filtro de aceite para motor.',
 'Filtro compatible con Nissan X-Trail.',
 250.00, 'Nissan', 'suv', NULL, 1),

(2, 'Filtro de aire Nissan X-Trail',
 'Filtro de aire para motor.',
 'Filtro compatible con Nissan X-Trail.',
 390.00, 'Nissan', 'suv', NULL, 1),

(2, 'Bujías Nissan X-Trail',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 650.00, 'Nissan', 'suv', NULL, 1);


-- ==============================
-- TOYOTA HILUX - PICKUP 4X4
-- ==============================

INSERT INTO productos
(id_categoria, nombre, descripcion, especificaciones, precio,
 marca_vehiculo, tipo_vehiculo, viscosidad, activo)
VALUES
(4, 'Aceite diesel Toyota Hilux 15W-40',
 'Aceite de motor para mantenimiento preventivo.',
 'Viscosidad 15W-40, presentación 4 litros.',
 1200.00, 'Toyota', 'pickup_4x4', '15W-40', 1),

(2, 'Filtro de aceite Toyota Hilux',
 'Filtro de aceite para motor.',
 'Filtro compatible con Toyota Hilux.',
 280.00, 'Toyota', 'pickup_4x4', NULL, 1),

(2, 'Filtro de aire Toyota Hilux',
 'Filtro de aire para motor.',
 'Filtro compatible con Toyota Hilux.',
 480.00, 'Toyota', 'pickup_4x4', NULL, 1),

(2, 'Bujías Toyota Hilux',
 'Juego de bujías para mantenimiento.',
 'Juego de 4 bujías.',
 750.00, 'Toyota', 'pickup_4x4', NULL, 1);


-- ====================
-- REGISTRAR INVENTARIO
-- ====================

SET @sucursal = (
    SELECT MIN(id_sucursal)
    FROM sucursales
);

INSERT INTO inventario
(id_producto, id_sucursal, cantidad, activo)

SELECT
    p.id_producto,
    @sucursal,
    20,
    1

FROM productos p

WHERE @sucursal IS NOT NULL

AND p.nombre IN (
    'Aceite sintético Toyota 5W-30',
    'Filtro de aceite Toyota Corolla',
    'Filtro de aire Toyota Corolla',
    'Bujías Toyota Corolla',

    'Aceite sintético Honda 0W-20',
    'Filtro de aceite Honda Civic',
    'Filtro de aire Honda Civic',
    'Bujías Honda Civic',

    'Aceite sintético Hyundai 5W-30',
    'Filtro de aceite Hyundai Tucson',
    'Filtro de aire Hyundai Tucson',
    'Bujías Hyundai Tucson',

    'Aceite sintético Nissan 5W-30',
    'Filtro de aceite Nissan X-Trail',
    'Filtro de aire Nissan X-Trail',
    'Bujías Nissan X-Trail',

    'Aceite diesel Toyota Hilux 15W-40',
    'Filtro de aceite Toyota Hilux',
    'Filtro de aire Toyota Hilux',
    'Bujías Toyota Hilux'
)

ON DUPLICATE KEY UPDATE
    cantidad = 20,
    activo = 1;


*/ -->