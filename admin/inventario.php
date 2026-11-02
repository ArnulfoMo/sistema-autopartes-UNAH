<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../clases/Producto.php';
require_once __DIR__ . '/../includes/header.php'; 
require_once __DIR__ . '/../includes/auth.php';
protegerPagina(['admin','bodeguero']);


$conexionBD = new ConexionBD();
$pdo = $conexionBD->conectar();

if (!$pdo) {
    die("No se pudo conectar con la base de datos.");
}

$producto = new Producto($pdo);

$consultaSucursales = $pdo->query(
    "SELECT id_sucursal, nombre
     FROM sucursales
     WHERE activo = 1
     ORDER BY nombre"
);

$sucursales = $consultaSucursales->fetchAll();

$consultaCategorias = $pdo->query(
    "SELECT id_categoria, nombre
     FROM categorias
     WHERE activo = 1
     ORDER BY nombre"
);

$categorias = $consultaCategorias->fetchAll();


$sucursalSeleccionada = null;

if (isset($_GET['sucursal']) && ctype_digit($_GET['sucursal'])) {
    $sucursalSeleccionada = (int) $_GET['sucursal'];
}

$inventario = $producto->obtenerInventario($sucursalSeleccionada);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de inventario - Don Pancho</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/inventario.css">
</head>

<body class="bg-light">

    <header class="bg-dark text-white shadow-sm py-3 mb-4">
        <div class="container">
            <h1 class="h4 fw-bold mb-1">Control de inventario</h1>
            <p class="text-muted small mb-0">Administración de productos y existencias</p>
        </div>
    </header>

    <main class="container mb-5">

        <!-- TARJETAS DE ACCIONES -->
        <section class="mb-5">
            <h2 class="h4 fw-bold mb-3">Administrar productos</h2>

            <div class="row g-3 contenedor-tarjetas">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100 py-3 text-center tarjeta" role="button" onclick="abrirModal('modalCrear')">
                        <div class="card-body">
                            <i class="bi bi-plus-circle fs-1 text-primary mb-2"></i>
                            <h3 class="h5 fw-bold">Crear producto</h3>
                            <p class="text-muted small mb-0">Registrar un nuevo producto en el inventario.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100 py-3 text-center tarjeta" role="button" onclick="abrirModal('modalActualizar')">
                        <div class="card-body">
                            <i class="bi bi-pencil-square fs-1 text-success mb-2"></i>
                            <h3 class="h5 fw-bold">Actualizar producto</h3>
                            <p class="text-muted small mb-0">Modificar la información de un producto existente.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100 py-3 text-center tarjeta" role="button" onclick="abrirModal('modalEliminar')">
                        <div class="card-body">
                            <i class="bi bi-trash fs-1 text-danger mb-2"></i>
                            <h3 class="h5 fw-bold">Eliminar producto</h3>
                            <p class="text-muted small mb-0">Dar de baja un producto del inventario.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- INVENTARIO DE PRODUCTOS -->
        <section>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h2 class="h4 fw-bold mb-0">Inventario de productos</h2>

                <form method="GET" action="inventario.php" class="d-flex align-items-center gap-2">
                    <label for="sucursal" class="form-label text-nowrap mb-0">Filtrar por sucursal:</label>
                    <select name="sucursal" id="sucursal" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todas las sucursales</option>
                        <?php foreach ($sucursales as $s): ?>
                            <option
                                value="<?= $s['id_sucursal'] ?>"
                                <?= ($sucursalSeleccionada === (int) $s['id_sucursal']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($s['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>


            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Sucursal</th>
                                <th>Existencias</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($inventario) > 0): ?>
                                <?php foreach ($inventario as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) $item['id_producto']) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($item['nombre']) ?></td>
                                        <td><?= htmlspecialchars($item['categoria'] ?? 'Sin categoría') ?></td>
                                        <td class="text-success fw-bold">L. <?= number_format((float) $item['precio'], 2) ?></td>
                                        <td><?= htmlspecialchars($item['sucursal'] ?? 'Sin sucursal') ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars((string) ($item['cantidad'] ?? 0)) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No hay productos registrados en esta sucursal.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>


    <!-- Modal de crear producto -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="procesar_repuesto.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalCrearLabel">Crear producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="crear">

                        <div class="mb-3">
                            <label class="form-label">Nombre del producto:</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categoría:</label>
                            <select name="id_categoria" class="form-select" required>
                                <option value="">Seleccione una categoría</option>
                                <?php foreach ($categorias as $c): ?>
                                    <option value="<?= $c['id_categoria'] ?>">
                                        <?= htmlspecialchars($c['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción:</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Especificaciones:</label>
                            <textarea name="especificaciones" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio:</label>
                            <input type="number" name="precio" class="form-control" step="0.01" min="0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fotografía:</label>
                            <input type="file" name="fotografia" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marca del vehículo:</label>
                                <input type="text" name="marca_vehiculo" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de vehículo:</label>
                                <select name="tipo_vehiculo" class="form-select">
                                    <option value="sedan">Sedán</option>
                                    <option value="suv">SUV</option>
                                    <option value="pickup_4x4">Pickup 4x4</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Viscosidad:</label>
                            <input type="text" name="viscosidad" class="form-control">
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Inventario inicial</h6>

                        <div class="mb-3">
                            <label class="form-label">Sucursal:</label>
                            <select name="id_sucursal" class="form-select" required>
                                <option value="">Seleccione una sucursal</option>
                                <?php foreach ($sucursales as $s): ?>
                                    <option value="<?= $s['id_sucursal'] ?>">
                                        <?= htmlspecialchars($s['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cantidad inicial:</label>
                            <input type="number" name="cantidad" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal de actualizar producto -->
    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-labelledby="modalActualizarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="procesar_repuesto.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalActualizarLabel">Actualizar producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="id_producto" id="editarId">

                        <div class="mb-3">
                            <label class="form-label">Buscar producto por nombre o ID:</label>
                            <input type="text" id="buscarActualizar" class="form-control" placeholder="Escriba el nombre o ID">
                        </div>

                        <div id="resultadosActualizar" class="mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label">Nombre:</label>
                            <input type="text" name="nombre" id="editarNombre" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio:</label>
                            <input type="number" name="precio" id="editarPrecio" class="form-control" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva fotografía:</label>
                            <input type="file" name="fotografia" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal de eliminar producto -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="procesar_repuesto.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger" id="modalEliminarLabel">Eliminar producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_producto" id="eliminarId">

                        <div class="mb-3">
                            <label class="form-label">Buscar producto por nombre o ID:</label>
                            <input type="text" id="buscarEliminar" class="form-control" placeholder="Escriba el nombre o ID">
                        </div>

                        <div id="resultadosEliminar" class="mb-3"></div>

                        <p id="nombreEliminar" class="fw-bold fs-5"></p>
                        <p class="text-muted">¿Está seguro de que desea eliminar este producto?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar eliminación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/inventario.js"></script>

</body>
</html>
<?php require_once '../includes/footer.php'; ?>