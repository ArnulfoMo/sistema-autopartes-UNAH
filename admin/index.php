<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/upload_images.php';

// Verificar que el usuario sea administrador
$rol = strtolower($_SESSION['rol'] ?? '');

if (!in_array($rol, ['admin', 'administrador'], true)) {
    header("Location: ../login.php");
    exit;
}

$tituloPagina = "Administrar productos | Don Pancho";

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

$mensaje = "";
$tipoMensaje = "danger";

// Token para proteger los formularios
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo !== null) {

    try {

        if (!hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token'] ?? ''
        )) {
            throw new RuntimeException("Solicitud no válida.");
        }

        $accion = $_POST['accion'] ?? '';

        // ---------------- REGISTRAR PRODUCTO ----------------

        if ($accion === 'crear') {

            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $especificaciones = trim($_POST['especificaciones'] ?? '');
            $idCategoria = filter_var(
                $_POST['id_categoria'] ?? null,
                FILTER_VALIDATE_INT
            );

            $precio = filter_var(
                $_POST['precio'] ?? null,
                FILTER_VALIDATE_FLOAT
            );

            $marcaVehiculo = trim($_POST['marca_vehiculo'] ?? '');
            $tipoVehiculo = $_POST['tipo_vehiculo'] ?? '';
            $viscosidad = trim($_POST['viscosidad'] ?? '');

            if (
                $nombre === '' ||
                $idCategoria === false ||
                $idCategoria === null ||
                $precio === false ||
                $precio < 0
            ) {
                throw new RuntimeException(
                    "Completa correctamente los campos obligatorios."
                );
            }

            if (strlen($nombre) > 150) {
                throw new RuntimeException("El nombre es demasiado largo.");
            }

            if (strlen($descripcion) > 255 ||
                strlen($especificaciones) > 255) {
                throw new RuntimeException(
                    "La descripción o especificaciones superan los 255 caracteres."
                );
            }

            if (!in_array($tipoVehiculo, [
                '',
                'sedan',
                'suv',
                'pickup_4x4'
            ], true)) {
                throw new RuntimeException("Tipo de vehículo no válido.");
            }

            // Comprobar que la categoría exista
            $consultaCategoria = $pdo->prepare(
                "SELECT id_categoria FROM categorias
                 WHERE id_categoria = ?"
            );

            $consultaCategoria->execute([$idCategoria]);

            if (!$consultaCategoria->fetch()) {
                throw new RuntimeException("La categoría seleccionada no existe.");
            }

            // Subir fotografía de manera segura
            $fotografia = null;

            if (
                isset($_FILES['fotografia']) &&
                $_FILES['fotografia']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                $fotografia = subirFotografia(
                    $_FILES['fotografia'],
                    false
                );
            }

            try {

                $sql = "INSERT INTO productos (
                            id_categoria,
                            nombre,
                            descripcion,
                            especificaciones,
                            precio,
                            fotografia,
                            marca_vehiculo,
                            tipo_vehiculo,
                            viscosidad,
                            activo
                        ) VALUES (
                            :categoria,
                            :nombre,
                            :descripcion,
                            :especificaciones,
                            :precio,
                            :fotografia,
                            :marca,
                            :tipo,
                            :viscosidad,
                            1
                        )";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':categoria' => $idCategoria,
                    ':nombre' => $nombre,
                    ':descripcion' => $descripcion,
                    ':especificaciones' => $especificaciones,
                    ':precio' => $precio,
                    ':fotografia' => $fotografia,
                    ':marca' => $marcaVehiculo ?: null,
                    ':tipo' => $tipoVehiculo ?: null,
                    ':viscosidad' => $viscosidad ?: null
                ]);

                $mensaje = "Producto agregado correctamente.";
                $tipoMensaje = "success";

            } catch (PDOException $e) {

                // Eliminar imagen si falla el registro
                if ($fotografia !== null) {
                    $ruta = dirname(__DIR__) . '/uploads/' . $fotografia;

                    if (is_file($ruta)) {
                        unlink($ruta);
                    }
                }

                throw $e;
            }
        }

        // ---------------- DESACTIVAR PRODUCTO ----------------

        elseif ($accion === 'eliminar') {

            $idProducto = filter_var(
                $_POST['id_producto'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$idProducto || $idProducto <= 0) {
                throw new RuntimeException("Producto no válido.");
            }

            $sql = "UPDATE productos
                    SET activo = 0
                    WHERE id_producto = :id
                    AND activo = 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $idProducto]);

            if ($stmt->rowCount() > 0) {
                $mensaje = "Producto retirado del catálogo correctamente.";
                $tipoMensaje = "success";
            } else {
                $mensaje = "El producto no existe o ya está desactivado.";
            }

        } else {
            throw new RuntimeException("Acción no reconocida.");
        }

    } catch (RuntimeException $e) {

        $mensaje = $e->getMessage();

    } catch (PDOException $e) {

        error_log("Error en administración de productos: " . $e->getMessage());

        $mensaje = "Error al registrar o actualizar el producto.";

    }
}

// ---------------- OBTENER CATEGORÍAS ----------------

$categorias = [];
$productos = [];

if ($pdo !== null) {

    try {

        $sqlCategorias = "SELECT id_categoria, nombre
                          FROM categorias
                          ORDER BY nombre";

        $categorias = $pdo->query($sqlCategorias)->fetchAll();

        // Productos activos
        $sqlProductos = "SELECT
                            p.id_producto,
                            p.nombre,
                            p.descripcion,
                            p.precio,
                            p.fotografia,
                            p.marca_vehiculo,
                            p.tipo_vehiculo,
                            c.nombre AS categoria
                         FROM productos p
                         INNER JOIN categorias c
                            ON p.id_categoria = c.id_categoria
                         WHERE p.activo = 1
                         ORDER BY p.id_producto DESC";

        $productos = $pdo->query($sqlProductos)->fetchAll();

    } catch (PDOException $e) {

        error_log("Error al consultar catálogo: " . $e->getMessage());

        $mensaje = "No se pudieron cargar los productos o las categorías.";
        $tipoMensaje = "danger";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>

    <!-- ENCABEZADO -->

    <section class="fondo-gris py-5">
        <div class="container">

            <div class="d-flex flex-column flex-md-row
                        justify-content-between align-items-md-center gap-3">

                <div>
                    <span class="text-warning fw-bold">
                        PANEL DE ADMINISTRACIÓN
                    </span>

                    <h1 class="fw-bold mb-2">
                        Gestión del catálogo
                    </h1>

                    <p class="text-muted mb-0">
                        Administra los repuestos disponibles en Don Pancho.
                    </p>
                </div>

                <div>
                    <a href="../catalogo.php"
                       class="btn btn-dark">
                        <i class="bi bi-eye me-2"></i>
                        Ver catálogo público
                    </a>
                </div>

            </div>

        </div>
    </section>

    <section class="fondo-blanco py-5">
        <div class="container">

            <?php if ($pdo === null): ?>

                <div class="alert alert-danger">
                    No se pudo conectar con la base de datos.
                </div>

            <?php else: ?>

                <?php if ($mensaje !== ''): ?>

                    <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?> alert-dismissible fade show">

                        <?= htmlspecialchars($mensaje) ?>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                        </button>

                    </div>

                <?php endif; ?>

                <div class="row g-4">

                    <!-- FORMULARIO -->

                    <div class="col-12 col-lg-5">

                        <div class="card shadow-sm border-0">

                            <div class="card-header bg-dark text-white p-3">

                                <h2 class="h5 fw-bold mb-0">
                                    <i class="bi bi-plus-circle me-2 text-warning"></i>
                                    Agregar nuevo repuesto
                                </h2>

                            </div>

                            <div class="card-body p-4">

                                <form method="POST"
                                      enctype="multipart/form-data">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                    <input type="hidden"
                                           name="accion"
                                           value="crear">

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Nombre del repuesto *
                                        </label>

                                        <input type="text"
                                               name="nombre"
                                               class="form-control"
                                               maxlength="150"
                                               placeholder="Ej. Filtro de aceite"
                                               required>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Categoría *
                                        </label>

                                        <select name="id_categoria"
                                                class="form-select"
                                                required>

                                            <option value="">
                                                Selecciona una categoría
                                            </option>

                                            <?php foreach ($categorias as $categoria): ?>

                                                <option value="<?= (int)$categoria['id_categoria'] ?>">
                                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Descripción
                                        </label>

                                        <textarea name="descripcion"
                                                  class="form-control"
                                                  rows="3"
                                                  maxlength="255"
                                                  placeholder="Descripción del repuesto..."></textarea>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Especificaciones
                                        </label>

                                        <textarea name="especificaciones"
                                                  class="form-control"
                                                  rows="2"
                                                  maxlength="255"
                                                  placeholder="Características técnicas..."></textarea>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Precio (L.) *
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                L.
                                            </span>

                                            <input type="number"
                                                   name="precio"
                                                   class="form-control"
                                                   min="0"
                                                   step="0.01"
                                                   required>

                                        </div>

                                    </div>

                                    <hr>

                                    <h3 class="h6 fw-bold mb-3">
                                        Compatibilidad del vehículo
                                    </h3>

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Marca del vehículo
                                        </label>

                                        <input type="text"
                                               name="marca_vehiculo"
                                               class="form-control"
                                               maxlength="50"
                                               placeholder="Toyota, Honda, Hyundai...">

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Tipo de vehículo
                                        </label>

                                        <select name="tipo_vehiculo"
                                                class="form-select">

                                            <option value="">No especificado</option>
                                            <option value="sedan">Sedán</option>
                                            <option value="suv">SUV</option>
                                            <option value="pickup_4x4">Pickup / 4x4</option>

                                        </select>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Viscosidad
                                        </label>

                                        <input type="text"
                                               name="viscosidad"
                                               class="form-control"
                                               maxlength="20"
                                               placeholder="Ej. 10W-30">

                                    </div>

                                    <hr>

                                    <!-- FOTOGRAFÍA -->

                                    <div class="mb-4">

                                        <label class="form-label fw-bold">
                                            Fotografía del repuesto
                                        </label>

                                        <input type="file"
                                               name="fotografia"
                                               id="fotografia"
                                               class="form-control"
                                               accept=".jpg,.jpeg,.png,.webp">

                                        <div class="form-text">
                                            JPG, PNG o WEBP. Máximo 4 MB.
                                        </div>

                                        <div class="mt-3 text-center">

                                            <img id="vistaPrevia"
                                                 src=""
                                                 class="img-thumbnail d-none"
                                                 style="max-height: 180px;"
                                                 alt="Vista previa">

                                        </div>

                                    </div>

                                    <button type="submit"
                                            class="btn btn-warning fw-bold w-100">

                                        <i class="bi bi-plus-lg me-2"></i>
                                        Registrar repuesto

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                    <!-- LISTADO DE PRODUCTOS -->

                    <div class="col-12 col-lg-7">

                        <div class="card shadow-sm border-0">

                            <div class="card-header bg-dark text-white p-3">

                                <div class="d-flex justify-content-between
                                            align-items-center">

                                    <h2 class="h5 fw-bold mb-0">
                                        <i class="bi bi-box-seam me-2 text-warning"></i>
                                        Productos registrados
                                    </h2>

                                    <span class="badge bg-warning text-dark">
                                        <?= count($productos) ?> productos
                                    </span>

                                </div>

                            </div>

                            <div class="card-body p-3">

                                <?php if (empty($productos)): ?>

                                    <div class="text-center py-5">

                                        <i class="bi bi-inbox display-3 text-muted"></i>

                                        <h3 class="h5 mt-3">
                                            No hay productos registrados
                                        </h3>

                                        <p class="text-muted">
                                            Agrega tu primer repuesto utilizando el formulario.
                                        </p>

                                    </div>

                                <?php else: ?>

                                    <div class="table-responsive">

                                        <table class="table align-middle">

                                            <thead class="table-light">

                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Precio</th>
                                                    <th>Marca</th>
                                                    <th>Acciones</th>
                                                </tr>

                                            </thead>

                                            <tbody>

                                                <?php foreach ($productos as $producto): ?>

                                                    <tr>

                                                        <td>

                                                            <div class="d-flex align-items-center gap-3">

                                                                <?php if (!empty($producto['fotografia'])): ?>

                                                                    <img
                                                                        src="../uploads/<?= rawurlencode($producto['fotografia']) ?>"
                                                                        alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                                                        class="rounded"
                                                                        style="width:65px;height:65px;object-fit:cover;"
                                                                    >

                                                                <?php else: ?>

                                                                    <div class="bg-light rounded d-flex
                                                                                align-items-center justify-content-center"
                                                                         style="width:65px;height:65px;">

                                                                        <i class="bi bi-image text-muted fs-3"></i>

                                                                    </div>

                                                                <?php endif; ?>

                                                                <div>

                                                                    <div class="fw-bold">
                                                                        <?= htmlspecialchars($producto['nombre']) ?>
                                                                    </div>

                                                                    <small class="text-muted">
                                                                        <?= htmlspecialchars($producto['categoria']) ?>
                                                                    </small>

                                                                </div>

                                                            </div>

                                                        </td>

                                                        <td class="fw-bold text-success">
                                                            L. <?= number_format((float)$producto['precio'], 2) ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($producto['marca_vehiculo'] ?? 'N/A') ?>
                                                        </td>

                                                        <td>

                                                            <form method="POST"
                                                                  onsubmit="return confirm('¿Deseas retirar este producto del catálogo?');">

                                                                <input type="hidden"
                                                                       name="csrf_token"
                                                                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                                                <input type="hidden"
                                                                       name="accion"
                                                                       value="eliminar">

                                                                <input type="hidden"
                                                                       name="id_producto"
                                                                       value="<?= (int)$producto['id_producto'] ?>">

                                                                <button type="submit"
                                                                        class="btn btn-outline-danger btn-sm">

                                                                    <i class="bi bi-trash"></i>

                                                                </button>

                                                            </form>

                                                        </td>

                                                    </tr>

                                                <?php endforeach; ?>

                                            </tbody>

                                        </table>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<script>
document.getElementById('fotografia')?.addEventListener('change', function () {

    const archivo = this.files[0];
    const imagen = document.getElementById('vistaPrevia');

    if (!archivo) {
        imagen.src = '';
        imagen.classList.add('d-none');
        return;
    }

    imagen.src = URL.createObjectURL(archivo);
    imagen.classList.remove('d-none');

});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
