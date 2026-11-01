<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class Producto{
    private PDO $conexion;

    public function __construct ( PDO $db){
        $this->conexion = $db;
    }

    //metodo que valida la imagen 
    public function validarYSubirImagen (array $archivo): ?string {
        
            //Verificar que no haya errores de transmisión (CANDADO1)
            if(!isset($archivo ['error']) || $archivo['error'] !== UPLOAD_ERR_OK){
                return ["exito" => false, "mensaje"=> "Error al recibir el archivo desde el navegador Código: " . ($archivo['error'] ?? 'desconocido')];
            }

            //validar el tamañp de la archivo (CANDADO2)
            $maxBytes = 4 * 1024 * 1024;
            if($archivo['size'] > $maxBytes){
                die("Error: El archivo excede el tamaño máximo permitido de 4 MB.");
            }

            //validar extensiones y el tipo de MIME real (CANDADO3)
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $extensionesPermitidas, true)) {
            die("Error: Extensión no permitida. Solo se aceptan imágenes JPG, PNG o WEBP.");
            }

            //Renombrar con identificador criptográfico único (CANDADO4)
            $nuevoNombre = uniqid('prod_', true) . '.' . $extension;
            $carpetaDestino = __DIR__ . '/../uploads/';

            // Crear la carpeta si no existe
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }
            $rutaFinal = $carpetaDestino . $nuevoNombre;

            // TRASLADO SEGURO: Mover desde la bodega temporal al directorio final
            if (!move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
                return ["exito" => false, "mensaje" => "Error al mover el archivo al directorio final."];
            }

            return ["exito" => true, "nombre_archivo" => $nuevoNombre];
        }

        public function registrarProducto (array $datos, ?string $nombreImagen): bool {
            try{
                $query = "INSERT INTO productos 
                (id_categoria, nombre, descripcion, especificaciones, precio, fotografia, marca_vehiculo, tipo_vehiculo, viscosidad)
                VALUES (:id_categoria, :nombre, :descripcion, :especificaciones, :precio, :fotografia, :marca_vehiculo, :tipo_vehiculo, :viscosidad)";
                $stmt = $this->conexion->prepare($query);
            
                // Vincular cada parámetro con su valor del formulario o de la imagen
                $stmt->bindParam(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);
                $stmt->bindParam(':nombre', $datos['nombre']);
                $stmt->bindParam(':descripcion', $datos['descripcion']);
                $stmt->bindParam(':especificaciones', $datos['especificaciones']);
                $stmt->bindParam(':precio', $datos['precio']);
                $stmt->bindParam(':fotografia', $nombreImagen);
                $stmt->bindParam(':marca_vehiculo', $datos['marca_vehiculo']);
                $stmt->bindParam(':tipo_vehiculo', $datos['tipo_vehiculo']);
                $stmt->bindParam(':viscosidad', $datos['viscosidad']);

                if ($stmt->execute()) {
                return ["exito" => true, "mensaje" => "Repuesto registrado correctamente en la base de datos."];
                } else {
                return ["exito" => false, "mensaje" => "Error al registrar el producto en la base de datos."];
                }

            } catch (PDOException $e) {
            return ["exito" => false, "mensaje" => "Error de base de datos: " . $e->getMessage()];
            }
        }

        //metodo para  listar el inventario o sucursal 
        public function obtenerInventario (?int $sucursal_id = null): array {
            $query = "SELECT p.id_producto, p.id_categoria, p.nombre, p.descripcion, p.especificaciones, p.precio, p.fotografia, p.marca_vehiculo, p.tipo_vehiculo, 
                    p.viscosidad, p.activo, i.id_inventario, i.id_sucursal, s.nombre AS sucursal,
                    COALESCE(i.cantidad, 0) AS cantidad
                    FROM productos p
                    LEFT JOIN inventario i
                        ON p.id_producto = i.id_producto
                        AND i.activo = 1
                    LEFT JOIN sucursales s
                        ON i.id_sucursal = s.id_sucursal
                    WHERE p.activo = 1";

            if ($sucursal_id !== null) {
                $sql .= " AND i.id_sucursal = :sucursal_id";
            }

            $sql .= " ORDER BY p.nombre ASC";

            $consulta = $this->conexion->prepare($sql);

            if ($sucursal_id !== null) {
                $consulta->bindValue(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
            }

            $consulta->execute();

            return $consulta->fetchAll();
    }

        //metodo para obener productor segun su id 
        public function obtenerPorId($id){
            $query = "SELECT * FROM productos WHERE id = :id LIMIT 1";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        //metodo para actualizar un producto 
        public function actualizar($id, $datos) {
            try {
            // Si se subió una nueva imagen, actualizamos también el campo fotografia
            if ($nombreImagen) {
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
                // Si no se cambió la imagen, mantenemos la que ya estaba registrada
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
            
            $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
            $stmt->bindParam(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':especificaciones', $datos['especificaciones']);
            $stmt->bindParam(':precio', $datos['precio']);
            $stmt->bindParam(':marca_vehiculo', $datos['marca_vehiculo']);
            $stmt->bindParam(':tipo_vehiculo', $datos['tipo_vehiculo']);
            $stmt->bindParam(':viscosidad', $datos['viscosidad']);

            if ($nombreImagen) {
                $stmt->bindParam(':fotografia', $nombreImagen);
            }

            return $stmt->execute() 
                ? ["exito" => true, "mensaje" => "Repuesto actualizado exitosamente."] 
                : ["exito" => false, "mensaje" => "No se pudo actualizar el repuesto."];

            } catch (PDOException $e) {
                return ["exito" => false, "mensaje" => "Error de base de datos: " . $e->getMessage()];
            }
        }   

        // Método para eliminar un producto
        public function eliminar(int $id): bool {
            $query = "UPDATE productos
                    SET activo = 0
                    WHERE id_producto = :id";

            $stmt = $this->conexion->prepare($sql);

            return $consulta->execute([
                ':id' => $id
            ]);
    }
    }

?>