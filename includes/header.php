
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['nombre'] ?? null;
$rol = $_SESSION['rol'] ?? null;


$baseUrl = '/don_pancho/';

$tituloPagina = $tituloPagina ?? 'Autopartes Don Pancho';

$pagina = basename($_SERVER['PHP_SELF'], '.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($tituloPagina) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- CSS general -->
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/estilos.css">

    <!-- CSS pagina actual -->
    <?php if (file_exists(__DIR__ . '/../assets/css/' . $pagina . '.css')): ?>
        <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/<?= $pagina ?>.css">
    <?php endif; ?>

</head>

<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center"
        href="<?= $baseUrl ?>index.php">

            <img src="<?= $baseUrl ?>assets/img/logo.png" alt="" class="logo-navbar">

        </a>

        <!-- Celular -->
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal"
                aria-expanded="false"
                aria-label="Abrir menú">

            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- menuu -->
        <div class="collapse navbar-collapse" id="menuPrincipal">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= $baseUrl ?>index.php">
                        Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= $baseUrl ?>catalogo.php">
                        Catálogo
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= $baseUrl ?>kit.php">
                        Arma tu Kit
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= $baseUrl ?>sucursales.php">
                        Sucursales
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= $baseUrl ?>carrito.php">
                        <i class="bi bi-cart3"></i>
                        Carrito
                    </a>
                </li>

            </ul>

            <!-- inicio sesion -->
            <div class="d-flex align-items-center gap-2">

                <?php if ($usuario !== null): ?>

                    <span class="text-white small">
                        <i class="bi bi-person-circle"></i>

                        <?= htmlspecialchars(
                            is_array($usuario)
                                ? ($usuario['nombre'] ?? 'Usuario')
                                : (string) $usuario
                        ) ?>

                        (<?= htmlspecialchars(
                            $rol === 'admin' ? 'Admin' :
                            ($rol === 'bodeguero' ? 'Bodega' : 'Usuario')
                        ) ?>)
                    </span>

                    <?php if (in_array($rol, ['admin', 'bodeguero'], true)): ?>
                        <a href="<?= $baseUrl ?>admin/index.php"
                           class="btn btn-outline-warning btn-sm">
                            Panel
                        </a>
                    <?php endif; ?>

                    <a href="<?= $baseUrl ?>logout.php"
                       class="btn btn-danger btn-sm">
                        Cerrar sesión
                    </a>

                <?php else: ?>

                    <a href="<?= $baseUrl ?>login.php"
                       class="btn btn-warning btn-sm fw-semibold">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Iniciar sesión
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </div>
</nav>

<main class="flex-grow-1">
