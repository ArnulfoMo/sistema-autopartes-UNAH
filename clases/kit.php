<?php

declare(strict_types=1);

class KitMantenimiento
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    //marcas y modelos

    public static function catalogoVehiculos(): array
    {
        return [
            'sedan' => [
                'Toyota' => ['Corolla', 'Camry'],
                'Honda' => ['Civic', 'Accord'],
                'Nissan' => ['Sentra', 'Versa'],
                'Hyundai' => ['Accent', 'Elantra'],
                'Kia' => ['Rio', 'Forte'],
                'Mazda' => ['Mazda 3', 'Mazda 6'],
                'Chevrolet' => ['Aveo', 'Cruze']
            ],

            'suv' => [
                'Toyota' => ['RAV4', 'Highlander', 'Fortuner'],
                'Honda' => ['CR-V', 'Pilot'],
                'Nissan' => ['X-Trail', 'Pathfinder'],
                'Hyundai' => ['Tucson', 'Santa Fe'],
                'Kia' => ['Sportage', 'Sorento'],
                'Mazda' => ['CX-5', 'CX-9'],
                'Ford' => ['Escape', 'Explorer'],
                'Chevrolet' => ['Tracker', 'Equinox']
            ],

            'pickup_4x4' => [
                'Toyota' => ['Hilux', 'Tacoma'],
                'Nissan' => ['Frontier', 'NP300'],
                'Mitsubishi' => ['L200'],
                'Ford' => ['Ranger'],
                'Chevrolet' => ['Colorado'],
                'Isuzu' => ['D-Max'],
                'Mazda' => ['BT-50']
            ]
        ];
    }

    //modelos

    public function obtenerProductos(
        string $tipo,
        string $marca,
        string $grupo
    ): array {

        $gruposPermitidos = ['aceites', 'filtros', 'repuestos'];

        if (!in_array($grupo, $gruposPermitidos, true)) {
            return [];
        }

        $sql = "
            SELECT
                p.id_producto,
                p.nombre,
                p.descripcion,
                p.especificaciones,
                p.precio,
                p.fotografia,
                p.marca_vehiculo,
                p.tipo_vehiculo,
                p.viscosidad,

                COALESCE(
                    (
                        SELECT SUM(i.cantidad)
                        FROM inventario i
                        WHERE i.id_producto = p.id_producto
                        AND i.activo = 1
                    ),
                    0
                ) AS stock

            FROM productos p

            INNER JOIN categorias c
                ON p.id_categoria = c.id_categoria

            WHERE p.activo = 1

            AND (
                p.tipo_vehiculo IS NULL
                OR p.tipo_vehiculo = :tipo
            )

            AND (
                p.marca_vehiculo IS NULL
                OR p.marca_vehiculo = ''
                OR LOWER(p.marca_vehiculo) = LOWER(:marca)
            )

            AND (
                SELECT COALESCE(SUM(i.cantidad), 0)
                FROM inventario i
                WHERE i.id_producto = p.id_producto
                AND i.activo = 1
            ) > 0
        ";

        /*
            grupos
        */

        if ($grupo === 'aceites') {

            $sql .= "
                AND p.id_categoria = 4
                AND (
                    p.viscosidad IS NOT NULL
                    OR LOWER(p.nombre) LIKE '%aceite%'
                )
            ";

        } elseif ($grupo === 'filtros') {

            $sql .= "
                AND (
                    LOWER(p.nombre) LIKE '%filtro%'
                    OR LOWER(COALESCE(p.descripcion, '')) LIKE '%filtro%'
                    OR LOWER(COALESCE(p.especificaciones, '')) LIKE '%filtro%'
                )
            ";

        } elseif ($grupo === 'repuestos') {

            $sql .= "
                AND p.id_categoria IN (1, 2, 3, 5)

                AND LOWER(p.nombre) NOT LIKE '%filtro%'

                AND LOWER(COALESCE(p.descripcion, '')) NOT LIKE '%filtro%'

                AND LOWER(COALESCE(p.especificaciones, '')) NOT LIKE '%filtro%'
            ";
        }

        $sql .= " ORDER BY p.nombre ASC";

        $consulta = $this->pdo->prepare($sql);

        $consulta->execute([
            ':tipo' => $tipo,
            ':marca' => $marca
        ]);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}

