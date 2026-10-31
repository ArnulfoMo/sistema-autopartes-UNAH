<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/clases/Usuario.php';

$error = "";
$usuarioIngresado = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuarioIngresado = trim($_POST['usuario'] ?? '');
    $clave = $_POST['clave'] ?? '';

    if ($usuarioIngresado === "" || $clave === "") {

        $error = "Debes completar todos los campos.";

    } else {

        try {

            $usuarioBD = new Usuario();

            $datosUsuario = $usuarioBD->autenticacion(
                $usuarioIngresado,
                $clave
            );

            if ($datosUsuario !== null) {

                session_regenerate_id(true);

                $_SESSION['id_usuario'] = $datosUsuario['id_usuario'];
                $_SESSION['nombre'] = $datosUsuario['nombre'];
                $_SESSION['rol'] = $datosUsuario['rol'];

                header("Location: index.php");
                exit();

            } else {

                $error = "Usuario o contraseña incorrectos.";
            }

        } catch (Throwable $e) {

            error_log("Error en login: " . $e->getMessage());
              $error = "Error técnico: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Don Pancho</title>
</head>

<body>

    <h1>Autopartes y Repuestos Don Pancho</h1>

    <h2>Iniciar Sesión</h2>

    <?php if ($error !== ""): ?>
        <p style="color: red;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="usuario">Usuario:</label>
        <br>

        <input
            type="text"
            id="usuario"
            name="usuario"
            value="<?= htmlspecialchars($usuarioIngresado, ENT_QUOTES, 'UTF-8') ?>"
            autocomplete="username"
            required
        >

        <br><br>

        <label for="clave">Contraseña:</label>
        <br>

        <input
            type="password"
            id="clave"
            name="clave"
            autocomplete="current-password"
            required
        >

        <br><br>

        <button type="submit">Ingresar</button>

    </form>

</body>
</html>
