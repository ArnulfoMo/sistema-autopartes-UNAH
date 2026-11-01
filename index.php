<?php
declare(strict_types=1);

$tituloPagina = "Inicio | Autopartes Don Pancho";

require_once __DIR__ . '/clases/Producto.php';

$productosDestacados = [];

try {
    $conexionBD = new ConexionBD();
    $conexion = $conexionBD->conectar();

    if (!$conexion instanceof PDO) {
        throw new RuntimeException("No se pudo conectar a la base de datos.");
    }

    $productoBD = new Producto($conexion);
    $inventario = $productoBD->obtenerInventario();

    $productosUnicos = [];

    foreach ($inventario as $producto) {
        $id = (int) $producto['id_producto'];

        if (!isset($productosUnicos[$id])) {
            $productosUnicos[$id] = $producto;
        }
    }

    $productosDestacados = array_slice(
        array_values($productosUnicos),
        0,
        5
    );

} catch (Throwable $e) {
    error_log("Error en inicio: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<main>

    <section class="hero-inicio text-white text-center">
        <div class="container py-5">

            <h1 class="display-4 fw-bold">
                Expertos en Repuestos Genuinos
                y Compatibles
            </h1>

            <p class="lead mt-3">
                Repuestos originales y de reemplazo para
                vehículos japoneses, coreanos y americanos.
            </p>

            <a href="<?= $baseUrl ?>catalogo.php"
               class="btn btn-warning btn-lg mt-3 fw-bold">
                <i class="bi bi-search"></i>
                Explorar catálogo
            </a>

            <a href="<?= $baseUrl ?>kit.php"
               class="btn btn-outline-light btn-lg mt-3 ms-2">
                <i class="bi bi-tools"></i>
                Arma tu Kit
            </a>

        </div>
    </section>

    <section class="fondo-blanco py-5">
        <div class="container">

            <h2 class="text-center fw-bold mb-3">
                Repuestos destacados
            </h2>

            <p class="text-center text-muted mb-5">
                Encuentra los mejores repuestos para tu vehículo.
            </p>

            <div class="row g-4 justify-content-center">

                <?php if (!empty($productosDestacados)): ?>

                    <?php foreach ($productosDestacados as $producto): ?>

                        <div class="col-12 col-sm-6 col-md-4 col-lg">

                            <div class="card h-100 shadow-sm">

                                <?php
                                $fotografia = basename(
                                    (string) ($producto['fotografia'] ?? '')
                                );
                                ?>

                                <?php if ($fotografia !== ''): ?>

                                    <img
                                        src="<?= $baseUrl ?>uploads/productos/<?= rawurlencode($fotografia) ?>"
                                        class="card-img-top"
                                        style="height: 190px; object-fit: contain; padding: 15px;"
                                        alt="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                <?php else: ?>

                                    <div class="d-flex align-items-center justify-content-center bg-light"
                                         style="height: 190px;">
                                        <i class="bi bi-image fs-1 text-muted"></i>
                                    </div>

                                <?php endif; ?>

                                <div class="card-body d-flex flex-column">

                                    <h5 class="card-title">
                                        <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </h5>

                                    <p class="card-text text-muted small">
                                        <?= htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <h5 class="text-success mt-auto">
                                        L. <?= number_format(
                                            (float) $producto['precio'],
                                            2
                                        ) ?>
                                    </h5>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p class="text-center text-muted">
                        Próximamente tendremos repuestos destacados.
                    </p>

                <?php endif; ?>

            </div>

            <div class="text-center mt-4">

                <a href="<?= $baseUrl ?>catalogo.php"
                   class="btn btn-dark">
                    Ver catálogo completo
                </a>

            </div>

        </div>
    </section>

    <section class="fondo-gris py-5">
        <div class="container">

            <h2 class="text-center fw-bold mb-4">
                Marcas vehiculares
            </h2>

            <?php
            $marcas = [
                'Toyota',
                'Nissan',
                'Honda',
                'Hyundai',
                'Isuzu',
                'Ford'
            ];
            ?>

            <div class="row g-3 justify-content-center">

                <?php foreach ($marcas as $marca): ?>

                    <div class="col-6 col-md-4 col-lg-2">

                        <div class="card text-center shadow-sm">
                            <div class="card-body">

                                <h5 class="fw-bold mb-0">
                                    <?= htmlspecialchars($marca, ENT_QUOTES, 'UTF-8') ?>
                                </h5>

                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>
    </section>

    <section class="fondo-blanco py-5 text-center">
        <div class="container">

            <h2 class="fw-bold">
                Visítanos en nuestras sucursales
            </h2>

            <p class="text-muted mt-3">
                Estamos presentes en Tegucigalpa,
                San Pedro Sula y La Ceiba.
            </p>

            <a href="<?= $baseUrl ?>sucursales.php"
               class="btn btn-warning fw-bold">
                Ver sucursales
            </a>

        </div>
    </section>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
