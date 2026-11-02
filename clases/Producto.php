<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class Producto {

    private PDO $conexion;

    public function __construct(PDO $db) {
        $this->conexion = $db;
    }

    // valida y suibe imagen
    public function validarYSubirImagen(array $archivo): array {

        // CANDADO 1: Verificar errores
        if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return [
                "exito" => false,
                "mensaje" => "Error al recibir la imagen."
            ];
        }

        // CANDADO 2: Limitar tamaño a 4 MB
        $maxBytes = 4 * 1024 * 1024;

        if ($archivo['size'] > $maxBytes) {
            return [
                "exito" => false,
                "mensaje" => "La imagen no puede superar los 4 MB."
            ];
        }

        // CANDADO 3: Validar extensión y MIME
        $extension = strtolower(
            pathinfo($archivo['name'], PATHINFO_EXTENSION)
        );

        $extensionesPermitidas = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp'
        ];

        if (!isset($extensionesPermitidas[$extension])) {
            return [
                "exito" => false,
                "mensaje" => "Formato de imagen no permitido."
            ];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if ($mimeReal !== $extensionesPermitidas[$extension]) {
            return [
                "exito" => false,
                "mensaje" => "El tipo de imagen no es válido."
            ];
        }

        // CANDADO 4: Nombre criptográfico único
        try {
            $nuevoNombre = bin2hex(random_bytes(16)) . '.' . $extension;
        } catch (Exception $e) {
            return [
                "exito" => false,
                "mensaje" => "No se pudo generar el nombre de la imagen."
            ];
        }

        $carpetaDestino = __DIR__ . '/../uploads/productos/';

        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        $rutaFinal = $carpetaDestino . $nuevoNombre;

        if (!is_uploaded_file($archivo['tmp_name'])) {
            return [
                "exito" => false,
                "mensaje" => "Archivo de subida no válido."
            ];
        }

        if (!move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            return [
                "exito" => false,
                "mensaje" => "No se pudo guardar la imagen."
            ];
        }

        return [
            "exito" => true,
            "nombre_archivo" => $nuevoNombre
        ];
    }


    public function registrarProducto(array $datos, ?string $nombreImagen): array {

        try {

            $query = "INSERT INTO productos
            (id_categoria, nombre, descripcion, especificaciones,
            precio, fotografia, marca_vehiculo, tipo_vehiculo, viscosidad)

            VALUES
            (:id_categoria, :nombre, :descripcion, :especificaciones,
            :precio, :fotografia, :marca_vehiculo, :tipo_vehiculo, :viscosidad)";

            $stmt = $this->conexion->prepare($query);

            $stmt->execute([
                ':id_categoria' => $datos['id_categoria'],
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion'],
                ':especificaciones' => $datos['especificaciones'],
                ':precio' => $datos['precio'],
                ':fotografia' => $nombreImagen,
                ':marca_vehiculo' => $datos['marca_vehiculo'],
                ':tipo_vehiculo' => $datos['tipo_vehiculo'],
                ':viscosidad' => $datos['viscosidad']
            ]);

            return [
                "exito" => true,
                "mensaje" => "Producto registrado correctamente."
            ];

        } catch (PDOException $e) {

            error_log("ERROR REGISTRANDO PRODUCTO: " . $e->getMessage());

            return [
                "exito" => false,
                "mensaje" => $e->getMessage()
            ];
        }
    }


    public function obtenerInventario(?int $sucursal_id = null): array {

        $query = "SELECT
                    p.id_producto,
                    p.id_categoria,
                    p.nombre,
                    p.descripcion,
                    p.especificaciones,
                    p.precio,
                    p.fotografia,
                    p.marca_vehiculo,
                    p.tipo_vehiculo,
                    p.viscosidad,
                    p.activo,
                    i.id_inventario,
                    i.id_sucursal,
                    s.nombre AS sucursal,
                    c.nombre AS categoria,
                    COALESCE(i.cantidad, 0) AS cantidad

                  FROM productos p

                  LEFT JOIN inventario i
                    ON p.id_producto = i.id_producto
                    AND i.activo = 1

                  LEFT JOIN sucursales s
                    ON i.id_sucursal = s.id_sucursal

                  LEFT JOIN categorias c
                  ON p.id_categoria = c.id_categoria

                  WHERE p.activo = 1";

        if ($sucursal_id !== null) {
            $query .= " AND i.id_sucursal = :sucursal_id";
        }

        $query .= " ORDER BY p.nombre ASC";

        $stmt = $this->conexion->prepare($query);

        if ($sucursal_id !== null) {
            $stmt->bindValue(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }


    // BUSCAR PRODUCTO POR ID
    public function obtenerPorId(int $id): ?array {

        $query = "SELECT *
                  FROM productos
                  WHERE id_producto = :id
                  AND activo = 1
                  LIMIT 1";

        $stmt = $this->conexion->prepare($query);

        $stmt->execute([
            ':id' => $id
        ]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }


    public function buscarProducto(string $busqueda): array {

        $busqueda = trim($busqueda);

        if ($busqueda === '') {
            return [];
        }

        $query = "SELECT id_producto, nombre, precio, fotografia
                FROM productos
                WHERE activo = 1
                AND nombre LIKE :nombre";

        if (ctype_digit($busqueda)) {
            $query .= " OR (activo = 1 AND id_producto = :id)";
        }

        $query .= " ORDER BY nombre ASC LIMIT 20";

        $stmt = $this->conexion->prepare($query);

        $stmt->bindValue(
            ':nombre',
            '%' . $busqueda . '%',
            PDO::PARAM_STR
        );

        if (ctype_digit($busqueda)) {
            $stmt->bindValue(
                ':id',
                (int) $busqueda,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function actualizar(
        int $id,
        array $datos,
        ?string $nombreImagen = null
    ): array {

        try {

            if ($nombreImagen !== null) {

                $query = "UPDATE productos SET
                            id_categoria = :id_categoria,
                            nombre = :nombre,
                            descripcion = :descripcion,
                            especificaciones = :especificaciones,
                            precio = :precio,
                            fotografia = :fotografia,
                            marca_vehiculo = :marca_vehiculo,
                            tipo_vehiculo = :tipo_vehiculo,
                            viscosidad = :viscosidad
                          WHERE id_producto = :id_producto";

            } else {

                $query = "UPDATE productos SET
                            id_categoria = :id_categoria,
                            nombre = :nombre,
                            descripcion = :descripcion,
                            especificaciones = :especificaciones,
                            precio = :precio,
                            marca_vehiculo = :marca_vehiculo,
                            tipo_vehiculo = :tipo_vehiculo,
                            viscosidad = :viscosidad
                          WHERE id_producto = :id_producto";
            }

            $stmt = $this->conexion->prepare($query);

            $parametros = [
                ':id_producto' => $id,
                ':id_categoria' => $datos['id_categoria'],
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion'],
                ':especificaciones' => $datos['especificaciones'],
                ':precio' => $datos['precio'],
                ':marca_vehiculo' => $datos['marca_vehiculo'],
                ':tipo_vehiculo' => $datos['tipo_vehiculo'],
                ':viscosidad' => $datos['viscosidad']
            ];

            if ($nombreImagen !== null) {
                $parametros[':fotografia'] = $nombreImagen;
            }

            $stmt->execute($parametros);

            return [
                "exito" => true,
                "mensaje" => "Producto actualizado correctamente."
            ];

        } catch (PDOException $e) {

            error_log($e->getMessage());

            return [
                "exito" => false,
                "mensaje" => "Error al actualizar el producto."
            ];
        }
    }


    public function eliminar(int $id): array {

        try {

            $query = "UPDATE productos
                      SET activo = 0
                      WHERE id_producto = :id";

            $stmt = $this->conexion->prepare($query);

            $stmt->execute([
                ':id' => $id
            ]);

            return [
                "exito" => true,
                "mensaje" => "Producto desactivado correctamente."
            ];

        } catch (PDOException $e) {

            error_log($e->getMessage());

            return [
                "exito" => false,
                "mensaje" => "No se pudo desactivar el producto."
            ];
        }
    }

}
?>