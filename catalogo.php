<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


require_once __DIR__ . '/config/conexion.php';

$rol = strtolower($_SESSION['rol'] ?? '');
$esAdmin = in_array($rol, ['admin', 'administrador'], true);

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

$productos = [];
$error = null;

$busqueda = trim($_GET['buscar'] ?? '');

if ($pdo !== null) {
    try {
        $sql = "SELECT
                    p.id_producto,
                    p.nombre,
                    p.descripcion,
                    p.especificaciones,
                    p.precio,
                    p.fotografia,
                    p.marca_vehiculo,
                    p.tipo_vehiculo,
                    p.viscosidad,
                    c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c
                    ON p.id_categoria = c.id_categoria
                WHERE p.activo = 1";

        $parametros = [];

        if ($busqueda !== '') {
            $sql .= " AND (
                p.nombre LIKE :buscar1
                OR p.descripcion LIKE :buscar2
                OR p.marca_vehiculo LIKE :buscar3
                OR c.nombre LIKE :buscar4
            )";

            $termino = "%{$busqueda}%";

            $parametros = [
                ':buscar1' => $termino,
                ':buscar2' => $termino,
                ':buscar3' => $termino,
                ':buscar4' => $termino
            ];
        }

        $sql .= " ORDER BY p.nombre ASC";

        $consulta = $pdo->prepare($sql);
        $consulta->execute($parametros);

        $productos = $consulta->fetchAll();

    } catch (PDOException $e) {
        error_log("Error al cargar catálogo: " . $e->getMessage());
        $error = "No se pudo cargar el catálogo de productos.";
    }
} else {
    $error = "No se pudo conectar con la base de datos.";
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="container py-5">
    <section class="mb-5">
        <div class="d-flex flex-column flex-md-row
                    justify-content-between align-items-md-center gap-3">

            <div>
                <h1 class="fw-bold">Catálogo de productos</h1>
                <p class="text-secondary mb-0">
                    Encuentra los repuestos que necesitas para tu vehículo.
                </p>
            </div>

            <?php if ($esAdmin): ?>
                <a href="admin/inventario.php"
                   class="btn btn-dark">
                    Gestionar inventario
                </a>
            <?php endif; ?>

        </div>
    </section>

    <section class="mb-4">
        <form action="catalogo.php" method="GET">
            <div class="input-group">
                <input
                    type="search"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar repuestos, marcas o categorías..."
                    value="<?= htmlspecialchars($busqueda) ?>"
                >

                <button class="btn btn-dark" type="submit">
                    Buscar
                </button>

                <?php if ($busqueda !== ''): ?>
                    <a href="catalogo.php" class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- resutlados -->
    <section>

        <?php if ($error !== null): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php elseif (empty($productos)): ?>

            <div class="alert alert-info text-center py-5">
                <h5>No se encontraron productos</h5>
                <p class="mb-0">
                    Intenta buscar con otro nombre o categoría.
                </p>
            </div>

        <?php else: ?>

            <div class="d-flex justify-content-between mb-3">
                <span class="text-secondary">
                    <?= count($productos) ?> productos encontrados
                </span>
            </div>

            <div class="row row-cols-1 row-cols-sm-2
                        row-cols-lg-3 row-cols-xl-4 g-4">

                <?php foreach ($productos as $producto): ?>

                    <?php
                    $nombre = htmlspecialchars($producto['nombre'] ?? '');
                    $fotografia = basename($producto['fotografia'] ?? '');
                    $rutaImagen = __DIR__ . '/uploads/' . $fotografia;

                    $imagenDisponible = (
                        $fotografia !== '' &&
                        is_file($rutaImagen)
                    );
                    ?>

                    <div class="col">
                        <article class="card h-100 border-0 shadow-sm">

                            <!-- imagenes -->
                            <?php if ($imagenDisponible): ?>

                                <img
                                    src="uploads/<?= rawurlencode($fotografia) ?>"
                                    class="card-img-top"
                                    alt="<?= $nombre ?>"
                                    style="height: 220px; object-fit: contain;"
                                >

                            <?php else: ?>

                                <div class="bg-light d-flex
                                            align-items-center
                                            justify-content-center"
                                     style="height: 220px;">
                                    <span class="text-secondary">
                                        Sin fotografía
                                    </span>
                                </div>

                            <?php endif; ?>

                            <!-- descripcion -->
                            <div class="card-body d-flex flex-column">

                                <span class="badge text-bg-secondary
                                             align-self-start mb-2">
                                    <?= htmlspecialchars(
                                        $producto['categoria'] ?? 'Sin categoría'
                                    ) ?>
                                </span>

                                <h5 class="card-title fw-bold">
                                    <?= $nombre ?>
                                </h5>

                                <p class="card-text text-secondary small">
                                    <?= htmlspecialchars(
                                        $producto['descripcion'] ?? ''
                                    ) ?>
                                </p>

                                <ul class="list-unstyled small text-secondary">

                                    <?php if (!empty($producto['marca_vehiculo'])): ?>
                                        <li>
                                            <strong>Marca:</strong>
                                            <?= htmlspecialchars($producto['marca_vehiculo']) ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($producto['tipo_vehiculo'])): ?>
                                        <li>
                                            <strong>Vehículo:</strong>
                                            <?= htmlspecialchars($producto['tipo_vehiculo']) ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($producto['viscosidad'])): ?>
                                        <li>
                                            <strong>Viscosidad:</strong>
                                            <?= htmlspecialchars($producto['viscosidad']) ?>
                                        </li>
                                    <?php endif; ?>

                                </ul>

                                <?php if (!empty($producto['especificaciones'])): ?>
                                    <p class="small text-secondary">
                                        <?= htmlspecialchars($producto['especificaciones']) ?>
                                    </p>
                                <?php endif; ?>

                                <div class="mt-auto pt-3
                                            d-flex justify-content-between
                                            align-items-center">

                                    <span class="fs-5 fw-bold text-success">
                                        L <?= number_format(
                                            (float)$producto['precio'],
                                            2
                                        ) ?>
                                    </span>

                                    <?php if ($esAdmin): ?>
                                        <a href="admin/inventario.php"
                                        class="btn btn-outline-dark btn-sm">
                                            Administrar
                                        </a>

                                    <?php else: ?>

                                        <form action="carrito.php" method="POST"
                                            class="d-flex align-items-center gap-2">

                                            <input type="hidden" name="accion" value="agregar">

                                            <input type="hidden"
                                                name="id_producto"
                                                value="<?= (int)$producto['id_producto'] ?>">

                                            <input type="number"
                                                name="cantidad"
                                                class="form-control form-control-sm"
                                                value="1"
                                                min="1"
                                                max="99"
                                                style="width: 65px;"
                                                required>

                                            <button type="submit"
                                                    class="btn btn-warning btn-sm">
                                                <i class="bi bi-cart-plus"></i>
                                                Agregar
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </div>

                            </div>
                        </article>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
