<?php
declare(strict_types=1);

function protegerPagina(array $rolesPermitidos = ['admin', 'bodeguero']): void {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (
        !isset($_SESSION['id_usuario']) ||
        !isset($_SESSION['nombre']) ||
        !isset($_SESSION['rol'])
    ) {

        header("Location: /sistema-autopartes-UNAH/login.php");
        exit();
    }

    if (!in_array($_SESSION['rol'], $rolesPermitidos, true)) {

        http_response_code(403);

        echo "No tienes permisos para acceder a esta pagina.";
        exit();
    }
}
