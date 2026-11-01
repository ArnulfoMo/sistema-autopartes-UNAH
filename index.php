
<?php
$tituloPagina = "Inicio | Autopartes Don Pancho";

$productosDestacados = [];

require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO BANNER -->
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

<!-- REPUESTOS DESTACADOS -->
<section class="fondo-blanco py-5">
    <div class="container">

        <h2 class="text-center fw-bold mb-3">
            Repuestos destacados
        </h2>

        <p class="text-center text-muted mb-5">
            Encuentra los mejores repuestos para tu vehículo.
        </p>

        <div class="row g-4">

            <?php if (!empty($productosDestacados)): ?>

                <?php foreach ($productosDestacados as $producto): ?>

                    <div class="col-md-4 col-lg-3">

                        <div class="card h-100 shadow-sm">

                            <img
                                src="<?= $baseUrl ?>uploads/<?= rawurlencode($producto['fotografia']) ?>"
                                class="card-img-top"
                                alt="<?= htmlspecialchars($producto['nombre']) ?>"
                            >

                            <div class="card-body">

                                <h5 class="card-title">
                                    <?= htmlspecialchars($producto['nombre']) ?>
                                </h5>

                                <p class="card-text text-muted">
                                    <?= htmlspecialchars($producto['descripcion']) ?>
                                </p>

                                <h5 class="text-success">
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

<!-- MARCAS VEHICULARES -->
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
                                <?= htmlspecialchars($marca) ?>
                            </h5>

                        </div>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>
</section>

<!-- SUCURSALES -->
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

<?php
require_once __DIR__ . '/includes/footer.php';
?>
