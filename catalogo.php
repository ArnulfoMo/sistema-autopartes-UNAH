<?php
declare(strict_types=1);

$tituloPagina = "Catálogo | Autopartes Don Pancho";

require_once __DIR__ . '/clases/Producto.php';

$categoriaSeleccionada = trim(
    (string) ($_GET['categoria'] ?? '')
);

$categorias = [
    'Frenos y Suspensión',
    'Motor y Transmisión',
    'Sistema Eléctrico',
    'Fluidos y Lubricantes'
];

$productos = [];
$productosFiltrados = [];
$errorCatalogo = '';

try {
    $conexionBD = new ConexionBD();
    $conexion = $conexionBD->conectar();

    if (!$conexion instanceof PDO) {
        throw new RuntimeException("No se pudo conectar a la base de datos.");
    }

    $productoBD = new Producto($conexion);
    $inventario = $productoBD->obtenerInventario();

    foreach ($inventario as $producto) {
        $id = (int) $producto['id_producto'];

        if (!isset($productos[$id])) {
            $productos[$id] = $producto;
        }
    }

    $productos = array_values($productos);

    $productosFiltrados = array_filter(
        $productos,
        function ($producto) use ($categoriaSeleccionada) {
            return $categoriaSeleccionada === ''
                || $producto['categoria'] === $categoriaSeleccionada;
        }
    );

} catch (Throwable $e) {
    error_log("Error en catálogo: " . $e->getMessage());
    $errorCatalogo = "No se pudo cargar el catálogo.";
}

require_once __DIR__ . '/includes/header.php';
?>

<main>

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

    <section class="fondo-blanco py-5">
        <div class="container">

            <?php if ($errorCatalogo !== ''): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorCatalogo, ENT_QUOTES, 'UTF-8') ?>
                </div>

            <?php else: ?>

                <div class="row g-4">

                    <div class="col-12 col-lg-3">

                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">

                                <h2 class="h5 fw-bold mb-4">
                                    <i class="bi bi-funnel me-2"></i>
                                    Categorías
                                </h2>

                                <form method="GET" action="catalogo.php">

                                    <div class="mb-4">

                                        <label for="categoria"
                                               class="form-label fw-bold">
                                            Seleccionar categoría
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
                                                    <?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>

                                    <button
                                        type="submit"
                                        class="btn btn-warning fw-bold w-100"
                                    >
                                        <i class="bi bi-search me-2"></i>
                                        Ver productos
                                    </button>

                                    <a
                                        href="<?= $baseUrl ?>catalogo.php"
                                        class="btn btn-outline-secondary w-100 mt-2"
                                    >
                                        Todas las categorías
                                    </a>

                                </form>

                            </div>
                        </div>

                    </div>

                    <div class="col-12 col-lg-9">

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

                            <h2 class="h4 fw-bold mb-0">
                                <?= $categoriaSeleccionada !== ''
                                    ? htmlspecialchars($categoriaSeleccionada, ENT_QUOTES, 'UTF-8')
                                    : 'Todos los repuestos' ?>
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
                                                    src="<?= $baseUrl ?>uploads/productos/<?= rawurlencode($fotografia) ?>"
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
                                                    <?= htmlspecialchars($producto['categoria'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                </span>

                                                <h3 class="card-title h5 fw-bold">
                                                    <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                                </h3>

                                                <p class="text-muted small mb-2">
                                                    <?= htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                </p>

                                                <?php if (!empty($producto['especificaciones'])): ?>

                                                    <p class="small mb-2">
                                                        <strong>Especificaciones:</strong>
                                                        <?= nl2br(htmlspecialchars(
                                                            $producto['especificaciones'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        )) ?>
                                                    </p>

                                                <?php endif; ?>

                                                <?php if (!empty($producto['marca_vehiculo'])): ?>

                                                    <p class="small mb-3">
                                                        <strong>Marca:</strong>
                                                        <?= htmlspecialchars($producto['marca_vehiculo'], ENT_QUOTES, 'UTF-8') ?>
                                                    </p>

                                                <?php endif; ?>

                                                <div class="mt-auto">

                                                    <h4 class="h5 fw-bold text-success mb-3">
                                                        L. <?= number_format(
                                                            (float) $producto['precio'],
                                                            2
                                                        ) ?>
                                                    </h4>


                                                    <form method="POST" action="<?= $baseUrl ?>carrito.php">

                                                        <input
                                                            type="hidden"
                                                            name="accion"
                                                            value="agregar"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id_producto"
                                                            value="<?= (int) $producto['id_producto'] ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="cantidad"
                                                            value="1"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-warning fw-bold w-100"
                                                        >
                                                            <i class="bi bi-cart-plus me-2"></i>
                                                            Agregar al carrito
                                                        </button>

                                                    </form>


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
                                        No hay repuestos disponibles
                                        en esta categoría.
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

            <?php endif; ?>

        </div>
    </section>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
