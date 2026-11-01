<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/conexion.php';

class Carrito
{
    // Reglas de negocio 
    public const TASA_ISV = 15; // % obligatorio SAR

    public const FLETES = [
        'mostrador'    => ['etiqueta' => 'Retiro en mostrador', 'monto' => 0.00,   'detalle' => 'Recoges en la sucursal, sin costo'],
        'moto_express' => ['etiqueta' => 'Moto express',        'monto' => 50.00,  'detalle' => 'Entrega rápida de piezas pequeñas'],
        'camioncito'   => ['etiqueta' => 'Flete en camioncito', 'monto' => 180.00, 'detalle' => 'Para piezas grandes o pedidos pesados'],
    ];

    // Descuentos automáticos por cantidad total de repuestos (unidades)
    public const DESCUENTOS = [
        ['min_unidades' => 10, 'porcentaje' => 10],
    ];

    public const MAX_POR_PRODUCTO = 99;

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($db === null) {
            $db = (new ConexionBD())->conectar();
            if ($db === null) {
                throw new RuntimeException('No se pudo conectar con la base de datos');
            }
        }
        $this->db = $db;

        if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
    }

    /* ---------- Operaciones sobre la sesión ---------- */

    /** @return array{nombre:string,cantidad:int,ajustada:bool,limite:int} */
    public function agregar(int $idProducto, int $cantidad = 1): array
    {
        if ($cantidad < 1) {
            throw new DomainException('La cantidad debe ser al menos 1.');
        }
        $actual = (int) ($_SESSION['carrito'][$idProducto] ?? 0);
        return $this->fijar($idProducto, $actual + $cantidad);
    }

    /** Cambia la cantidad de un producto */
    public function actualizar(int $idProducto, int $cantidad): ?array
    {
        if ($cantidad < 1) {
            $this->quitar($idProducto);
            return null;
        }
        if (!isset($_SESSION['carrito'][$idProducto])) {
            throw new DomainException('Ese repuesto no está en tu carrito.');
        }
        return $this->fijar($idProducto, $cantidad);
    }

    public function quitar(int $idProducto): void
    {
        unset($_SESSION['carrito'][$idProducto]);
    }

    public function vaciar(): void
    {
        $_SESSION['carrito'] = [];
    }

    public function estaVacio(): bool
    {
        return count($_SESSION['carrito']) === 0;
    }

    public function unidades(): int
    {
        return (int) array_sum($_SESSION['carrito']);
    }

    public static function contarUnidades(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $c = $_SESSION['carrito'] ?? [];
        return is_array($c) ? (int) array_sum($c) : 0;
    }

    private function fijar(int $idProducto, int $nueva): array
    {
        $p = $this->infoProducto($idProducto);
        if ($p === null) {
            $this->quitar($idProducto);
            throw new DomainException('El repuesto no existe o ya no está disponible.');
        }

        $stock = (int) $p['stock'];
        if ($stock < 1) {
            throw new DomainException('«' . $p['nombre'] . '» está agotado.');
        }

        $limite   = min($stock, self::MAX_POR_PRODUCTO);
        $ajustada = false;
        if ($nueva > $limite) {
            $nueva    = $limite;
            $ajustada = true;
        }

        $_SESSION['carrito'][$idProducto] = $nueva;

        return [
            'nombre'   => (string) $p['nombre'],
            'cantidad' => $nueva,
            'ajustada' => $ajustada,
            'limite'   => $limite,
        ];
    }

    private function infoProducto(int $idProducto): ?array
    {
        $sql = "SELECT p.id_producto, p.nombre,
                       COALESCE((SELECT SUM(i.cantidad)
                                 FROM inventario i
                                 WHERE i.id_producto = p.id_producto AND i.activo = 1), 0) AS stock
                FROM productos p
                WHERE p.id_producto = :id AND p.activo = 1
                LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute(['id' => $idProducto]);
        $fila = $st->fetch();
        return $fila ?: null;
    }

    public function items(): array
    {
        $ids = array_map('intval', array_keys($_SESSION['carrito']));
        if (!$ids) {
            return [];
        }

        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.fotografia,
                       p.marca_vehiculo,
                       COALESCE((SELECT SUM(i.cantidad)
                                 FROM inventario i
                                 WHERE i.id_producto = p.id_producto AND i.activo = 1), 0) AS stock
                FROM productos p
                WHERE p.activo = 1 AND p.id_producto IN ($marcas)";
        $st = $this->db->prepare($sql);
        $st->execute($ids);

        $porId = [];
        foreach ($st->fetchAll() as $f) {
            $porId[(int) $f['id_producto']] = $f;
        }

        $items = [];
        foreach ($ids as $id) {
            if (!isset($porId[$id])) {
                unset($_SESSION['carrito'][$id]);
                continue;
            }
            $f        = $porId[$id];
            $cantidad = (int) $_SESSION['carrito'][$id];
            $stock    = (int) $f['stock'];
            $precioC  = (int) round(((float) $f['precio']) * 100);

            $problema = null;
            if ($stock < 1) {
                $problema = 'Agotado';
            } elseif ($cantidad > $stock) {
                $problema = "Solo hay $stock en existencia";
            }

            $items[] = [
                'id'          => $id,
                'nombre'      => (string) $f['nombre'],
                'descripcion' => (string) ($f['descripcion'] ?? ''),
                'marca'       => (string) ($f['marca_vehiculo'] ?? ''),
                'foto'        => $f['fotografia'] !== null ? (string) $f['fotografia'] : '',
                'precio'      => $precioC / 100,
                'precio_c'    => $precioC,
                'cantidad'    => $cantidad,
                'subtotal_c'  => $precioC * $cantidad,
                'subtotal'    => ($precioC * $cantidad) / 100,
                'stock'       => $stock,
                'problema'    => $problema,
            ];
        }
        return $items;
    }

    public static function hayProblemas(array $items): bool
    {
        foreach ($items as $it) {
            if ($it['problema'] !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * Calcula todos los montos (en centavos internamente para no arrastrar
     * errores de decimales).
     *
     * Orden: subtotal -> descuento -> ISV sobre (subtotal - descuento) -> + flete.
     */
    public function totales(array $items, string $modalidad = 'mostrador'): array
    {
        if (!isset(self::FLETES[$modalidad])) {
            throw new DomainException('Modalidad de flete no válida.');
        }

        $subC = 0;
        $uni  = 0;
        foreach ($items as $it) {
            $subC += $it['subtotal_c'];
            $uni  += $it['cantidad'];
        }

        $pct = 0;
        foreach (self::DESCUENTOS as $d) {
            if ($uni >= $d['min_unidades'] && $d['porcentaje'] > $pct) {
                $pct = $d['porcentaje'];
            }
        }

        $descC  = (int) round($subC * $pct / 100);
        $baseC  = $subC - $descC;
        $isvC   = (int) round($baseC * self::TASA_ISV / 100);
        $fleteC = (int) round(self::FLETES[$modalidad]['monto'] * 100);
        $totalC = $baseC + $isvC + $fleteC;

        // Siguiente nivel de descuento (para avisar al cliente)
        $falta = null;
        foreach (self::DESCUENTOS as $d) {
            if ($uni < $d['min_unidades']) {
                $falta = ['unidades' => $d['min_unidades'] - $uni, 'porcentaje' => $d['porcentaje']];
                break;
            }
        }

        return [
            'unidades'             => $uni,
            'subtotal'             => $subC / 100,
            'porcentaje_descuento' => $pct,
            'descuento'            => $descC / 100,
            'base'                 => $baseC / 100,
            'tasa_isv'             => self::TASA_ISV,
            'isv'                  => $isvC / 100,
            'flete'                => $fleteC / 100,
            'total'                => $totalC / 100,
            'falta_descuento'      => $falta,
        ];
    }

    public static function dinero(float|int|string $monto): string
    {
        return 'L ' . number_format((float) $monto, 2, '.', ',');
    }
}