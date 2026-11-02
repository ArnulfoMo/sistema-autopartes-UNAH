<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/header.php'; 
require_once __DIR__ . '/../includes/auth.php';
protegerPagina(['admin','bodeguero']);



$conexionBD = new ConexionBD();
$pdo = $conexionBD->conectar();

if ($pdo === null) {
    die("Error al conectar con la base de datos.");
}

$query = $pdo->query(
    " SELECT
        COUNT(*) AS total_productos
    FROM productos
    WHERE activo = 1"
);

$totalProductos = $query->fetch()['total_productos'];

$query = $pdo->query(
    "SELECT
        COUNT(*) AS total_categorias
    FROM categorias
    WHERE activo = 1"
);

$totalCategorias = $query->fetch()['total_categorias'];

$query = $pdo->query(
"   SELECT
        COUNT(*) AS total_sucursales
    FROM sucursales
    WHERE activo = 1"
);

$totalSucursales = $query->fetch()['total_sucursales'];

$nombreUsuario = $_SESSION['nombre'];
$rolUsuario = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">

<!-- Header -->
<section class="bg-dark text-white py-4 mb-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold mb-1">Panel Administrativo</h1>
            <p class="text-muted mb-0">Autopartes y Repuestos Don Pancho</p>
        </div>
        <div class="text-md-end mt-3 mt-md-0">
            <p class="mb-0">Bienvenido, <span class="fw-bold text-warning"><?php echo htmlspecialchars($nombreUsuario); ?></span></p>
            <small class="text-muted text-uppercase">Rol: <?php echo htmlspecialchars($rolUsuario); ?></small>
        </div>
    </div>
</section>

<main class="container mb-5">

    <!-- resumen genersl -->
    <section class="mb-5">
        <h2 class="fw-bold mb-4">Resumen general</h2>

        <div class="row g-4">
            
            <!-- Tarjeta Productos -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted fs-6 mb-2">Productos registrados</h5>
                                <p class="card-text fs-3 fw-bold mb-0"><?php echo $totalProductos; ?></p>
                            </div>
                            <div class="text-primary fs-1">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Categorías -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted fs-6 mb-2">Categorías activas</h5>
                                <p class="card-text fs-3 fw-bold mb-0"><?php echo $totalCategorias; ?></p>
                            </div>
                            <div class="text-success fs-1">
                                <i class="bi bi-tags"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Sucursales -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted fs-6 mb-2">Sucursales activas</h5>
                                <p class="card-text fs-3 fw-bold mb-0"><?php echo $totalSucursales; ?></p>
                            </div>
                            <div class="text-warning fs-1">
                                <i class="bi bi-shop"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- accesos rapidos -->
    <section>
        <h2 class="fw-bold mb-3">Accesos rápidos</h2>
        
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex gap-3">
                <a href="inventario.php" class="btn btn-warning fw-bold">
                    <i class="bi bi-tools me-2"></i> Administrar inventario
                </a>
            </div>
        </div>
    </section>

</main>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>