<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../clases/Producto.php';

$conexionBD = new ConexionBD();
$pdo = $conexionBD->conectar();

if (!$pdo) {
    die("Error al conectar con la base de datos.");
}

$producto = new Producto($pdo);


// Funciom que sirve para regresar a inventario con un mensaje
function regresar(string $mensaje): void
{
    header("Location: inventario.php?mensaje=" . urlencode($mensaje));
    exit;
}


// funciones que buscan productos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion'])) {

    if ($_GET['accion'] === 'buscar') {

        header('Content-Type: application/json; charset=utf-8');

        $termino = trim($_GET['termino'] ?? '');

        if ($termino === '') {
            echo json_encode([]);
            exit;
        }

        $resultados = $producto->buscarProducto($termino);

        echo json_encode($resultados);
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    try {

        // REGISTRAR PRODUCTO
        if ($accion === 'crear') {

            $nombreImagen = null;
            $archivoNuevo = null;

            // Validar y subir fotografía
            $resultadoImagen = $producto->validarYSubirImagen($_FILES['fotografia']);

            if (!$resultadoImagen['exito']) {
                regresar($resultadoImagen['mensaje']);
            }

            $nombreImagen = $resultadoImagen['nombre_archivo'];
            $archivoNuevo = $nombreImagen;

            // Validar cantidad inicial
            $cantidad = filter_var(
                $_POST['cantidad'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($cantidad === false || $cantidad < 0) {
                regresar("La cantidad inicial no es válida.");
            }

            // Validar sucursal
            $idSucursal = filter_var(
                $_POST['id_sucursal'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$idSucursal || $idSucursal < 1) {
                regresar("Seleccione una sucursal válida.");
            }

            $consultaSucursal = $pdo->prepare(
                "SELECT id_sucursal
                 FROM sucursales
                 WHERE id_sucursal = :id
                 AND activo = 1"
            );

            $consultaSucursal->execute(['id' => $idSucursal]);

            if (!$consultaSucursal->fetch()) {
                regresar("La sucursal seleccionada no existe o está inactiva.");
            }

            // Datos del producto
            $datos = [
                'id_categoria' => (int) $_POST['id_categoria'],
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'especificaciones' => trim($_POST['especificaciones'] ?? ''),
                'precio' => (float) $_POST['precio'],
                'marca_vehiculo' => trim($_POST['marca_vehiculo'] ?? ''),
                'tipo_vehiculo' => $_POST['tipo_vehiculo'],
                'viscosidad' => trim($_POST['viscosidad'] ?? '')
            ];

            $pdo->beginTransaction();

            $resultado = $producto->registrarProducto(
                $datos,
                $nombreImagen
            );

            if (!$resultado['exito']) {
                throw new Exception($resultado['mensaje']);
            }

            $idProducto = (int) $pdo->lastInsertId();

            $consultaInventario = $pdo->prepare(
                "INSERT INTO inventario
                (id_producto, id_sucursal, cantidad, activo)
                VALUES
                (:producto, :sucursal, :cantidad, 1)"
            );

            $consultaInventario->execute([
                'producto' => $idProducto,
                'sucursal' => $idSucursal,
                'cantidad' => $cantidad
            ]);

            $pdo->commit();

            regresar("Producto registrado correctamente.");

        }


        // ACTUALIZAR PRODUCTO
        elseif ($accion === 'actualizar') {

            $id = filter_var(
                $_POST['id_producto'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                regresar("ID de producto no válido.");
            }

            $actual = $producto->obtenerPorId($id);

            if (!$actual) {
                regresar("No se encontró el producto.");
            }

            // aqui cononservar los datos que no se modificaron
            $datos = [
                'id_categoria' => $actual['id_categoria'],
                'nombre' => trim($_POST['nombre'] ?? $actual['nombre']),
                'descripcion' => $actual['descripcion'],
                'especificaciones' => $actual['especificaciones'],
                'precio' => (float) ($_POST['precio'] ?? $actual['precio']),
                'marca_vehiculo' => $actual['marca_vehiculo'],
                'tipo_vehiculo' => $actual['tipo_vehiculo'],
                'viscosidad' => $actual['viscosidad']
            ];

            $nombreImagen = null;
            $archivoNuevo = null;

            // Comprobar si se seleccionó una fotografía nueva
            if (
                isset($_FILES['fotografia']) &&
                $_FILES['fotografia']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                $resultadoImagen = $producto->validarYSubirImagen(
                    $_FILES['fotografia']
                );

                if (!$resultadoImagen['exito']) {
                    regresar($resultadoImagen['mensaje']);
                }

                $nombreImagen = $resultadoImagen['nombre_archivo'];
                $archivoNuevo = $nombreImagen;
            }

            $resultado = $producto->actualizar(
                $id,
                $datos,
                $nombreImagen
            );

            if (!$resultado['exito']) {
                throw new Exception($resultado['mensaje']);
            }

            regresar("Producto actualizado correctamente.");

        }


        // ELIMINAR PRODUCTO
        elseif ($accion === 'eliminar') {

            $id = filter_var(
                $_POST['id_producto'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                regresar("ID de producto no válido.");
            }

            $resultado = $producto->eliminar($id);

            if (!$resultado['exito']) {
                regresar($resultado['mensaje']);
            }

            regresar("Producto eliminado correctamente.");

        }


        else {
            regresar("Acción no válida.");
        }

    } catch (Exception $e) {

        // Deshacer cambios si existe una transacción activa
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Eliminar la imagen nueva si ocurrió un error
        if (isset($archivoNuevo) && $archivoNuevo !== null) {

            $rutaImagen = __DIR__ . '/../uploads/productos/' . $archivoNuevo;

            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
        }

        error_log("Error al procesar producto: " . $e->getMessage());

        regresar("Error: " . $e->getMessage());
    }

}

regresar("Solicitud no válida.");