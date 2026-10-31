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
            $error = "Ocurrió un error al iniciar sesión.";
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

    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <main class="login-contenedor">

        <div class="login-encabezado">

        <div class="login-logo">
            <img src="assets/img/logo.png" alt="Logo Autopartes Don Pancho">
        </div>

            <h1>Autopartes Don Pancho</h1>

            <p>Expertos en Repuestos Genuinos y Compatibles</p>

        </div>

        <div class="login-formulario">

            <h2>Iniciar sesión</h2>

            <p class="login-subtitulo">
                Ingresa tus credenciales para continuar
            </p>

            <?php if ($error !== ""): ?>

                <div class="login-error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="">

                <div class="campo">
                    <label for="usuario">Usuario</label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Ingresa tu usuario"
                        value="<?= htmlspecialchars($usuarioIngresado, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="clave">Contraseña</label>

                    <input
                        type="password"
                        id="clave"
                        name="clave"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-ingresar">
                    Ingresar
                </button>

            </form>

            <div class="login-volver">
                <a href="index.php">
                    ← Volver al inicio
                </a>
            </div>

        </div>

        <div class="login-pie">
            © <?= date('Y') ?> Autopartes Don Pancho
        </div>

    </main>

</body>
</html>
