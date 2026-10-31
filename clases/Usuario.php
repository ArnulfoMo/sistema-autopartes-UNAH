<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/conexion.php';


class usuario {
    private PDO $conexion;
    public function __construct(){
        $conexionBD = new ConexionBD();
        $pdo = $conexionBD -> conectar();

        if ($pdo === null){
            throw new RuntimeException("No se pudo conectar con la base de datos");
        }

    $this->conexion =$pdo ;

    }

    public function autenticacion(string $usuario,string $contra): ?array {
        $sql = "SELECT id_usuario, nombre, usuario,`contraseña`,rol
                FROM usuarios
                WHERE usuario = :usuario
                LIMIT 1";    

        $consulta =$this->conexion->prepare($sql);
        $consulta ->execute(['usuario'=>$usuario]);

        $datos=$consulta->fetch();

        if (!$datos){
            return null;
        }
        
        if (!password_verify($contra, $datos['contraseña'])) {
        return null;
        }

        
        if(!in_array($datos['rol'],['admin', 'bodeguero'],true)){
            return null;
        }

        unset($datos['contraseña']);

        return $datos;
    }


}
?>
