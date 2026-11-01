<?php
declare(strict_types=1);

$tituloPagina = "Catálogo | Autopartes Don Pancho";

require_once __DIR__ . '/clases/Producto.php';


require_once __DIR__ . '/includes/header.php';
?>

<main>

    <!-- ENCABEZADO -->
    <section class="fondo-gris py-5 text-center">
        <div class="container">

            <h1 class="fw-bold mb-3">
                Catálogo de repuestos
            </h1>

            <p class="text-muted lead mb-0">
                Encuentra repuestos para vehículos
                japoneses, coreanos y americanos.
            </p>

        </div>
    </section>

    <!-- CATÁLOGO -->
    <section class="fondo-blanco py-5">
        <div class="container">

            <div class="row g-4">

                <!-- FILTROS -->
                <div class="col-12 col-lg-3">

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">

                            <h2 class="h5 fw-bold mb-4">
                                <i class="bi bi-funnel me-2"></i>
                                Filtrar repuestos
                            </h2>

                            <form method="GET" action="catalogo.php">

                                <!-- BUSCAR -->
                                <div class="mb-3">

                                    <label for="buscar"
                                           class="form-label fw-bold">
                                        Buscar producto
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="buscar"
                                        name="buscar"
                                        placeholder="Nombre del repuesto"
                                        value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                </div>

                                <!-- CATEGORÍA -->
                                <div class="mb-3">

                                    <label for="categoria"
                                           class="form-label fw-bold">
                                        Categoría
                                    </label>

                                    <select
                                        class="form-select"
                                        id="categoria"
                                        name="categoria"
                                    >

                                        <option value="">
                                            Todas las categorías
                                        </option>

                                        <?php foreach ($categorias as $categoria): ?>

                                            <option
                                                value="<?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $categoriaSeleccionada === $categoria ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($categoria) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                                <!-- MARCA -->
                                <div class="mb-4">

                                    <label for="marca"
                                           class="form-label fw-bold">
                                        Marca vehicular
                                    </label>

                                    <select
                                        class="form-select"
                                        id="marca"
                                        name="marca"
                                    >

                                        <option value="">
                                            Todas las marcas
                                        </option>

                                        <?php foreach ($marcas as $marca): ?>

                                            <option
                                                value="<?= htmlspecialchars($marca, ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $marcaSeleccionada === $marca ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($marca) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-warning fw-bold w-100"
                                >
                                    <i class="bi bi-search me-2"></i>
                                    Aplicar filtros
                                </button>

                                <a
                                    href="<?= $baseUrl ?>catalogo.php"
                                    class="btn btn-outline-secondary w-100 mt-2"
                                >
                                    Limpiar filtros
                                </a>

                            </form>

                        </div>
                    </div>

                </div>

                <!-- PRODUCTOS -->
                <div class="col-12 col-lg-9">

                    <div class="d-flex justify-content-between
                                align-items-center mb-4 flex-wrap gap-2">

                        <h2 class="h4 fw-bold mb-0">
                            Repuestos disponibles
                        </h2>

                        <span class="text-muted">
                            <?= count($productosFiltrados) ?>
                            producto(s) encontrado(s)
                        </span>

                    </div>

                    <div class="row g-4">

                        <?php if (!empty($productosFiltrados)): ?>

                            <?php foreach ($productosFiltrados as $producto): ?>

                                <div class="col-12 col-sm-6 col-xl-4">

                                    <div class="card producto-card h-100 shadow-sm">

                                        <?php
                                        $fotografia = basename(
                                            (string) ($producto['fotografia'] ?? '')
                                        );
                                        ?>

                                        <?php if ($fotografia !== ''): ?>

                                            <img
                                                src="<?= $baseUrl ?>uploads/<?= rawurlencode($fotografia) ?>"
                                                class="card-img-top producto-imagen"
                                                alt="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                            >

                                        <?php else: ?>

                                            <div class="producto-sin-imagen">
                                                <i class="bi bi-image fs-1 text-muted"></i>
                                                <p class="text-muted small">
                                                    Sin fotografía
                                                </p>
                                            </div>

                                        <?php endif; ?>

                                        <div class="card-body d-flex flex-column">

                                            <span class="badge bg-secondary align-self-start mb-2">
                                                <?= htmlspecialchars($producto['categoria'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>

                                            <h3 class="card-title h5 fw-bold">
                                                <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </h3>

                                            <p class="text-muted small mb-2">
                                                <?= htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </p>

                                            <p class="small mb-3">
                                                <strong>Marca:</strong>
                                                <?= htmlspecialchars($producto['marca'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>

                                            <div class="mt-auto">

                                                <h4 class="h5 fw-bold text-success mb-3">
                                                    L. <?= number_format(
                                                        (float) $producto['precio'],
                                                        2
                                                    ) ?>
                                                </h4>

                                                <!-- falta para carrito -->
                                                <button
                                                    type="button"
                                                    class="btn btn-warning fw-bold w-100"
                                                    disabled
                                                >
                                                    <i class="bi bi-cart-plus me-2"></i>
                                                    Agregar al carrito
                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="col-12 text-center py-5">

                                <i class="bi bi-search fs-1 text-muted"></i>

                                <h3 class="h5 mt-3">
                                    No se encontraron productos
                                </h3>

                                <p class="text-muted">
                                    Intenta cambiar los filtros de búsqueda.
                                </p>

                                <a
                                    href="<?= $baseUrl ?>catalogo.php"
                                    class="btn btn-warning fw-bold"
                                >
                                    Ver todos los repuestos
                                </a>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>
    </section>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
