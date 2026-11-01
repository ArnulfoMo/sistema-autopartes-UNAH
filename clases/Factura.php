<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/Carrito.php';

class Factura
{
    public const PREFIJO = '001-001-01-';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db === null) {
            $db = (new ConexionBD())->conectar();
            if ($db === null) {
                throw new RuntimeException('No se pudo conectar con la base de datos');
            }
        }
        $this->db = $db;
    }

    /* ---------- Validaciones ---------- */

    private static function largo(string $texto): int
    {
        return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : (int) preg_match_all('/./su', $texto);
    }

    /** Deja solo los dígitos (acepta 0801-1990-123456). */
    public static function normalizarRTN(string $rtn): string
    {
        return preg_replace('/\D+/', '', $rtn) ?? '';
    }

    public static function rtnValido(string $rtn): bool
    {
        return preg_match('/^\d{14}$/', $rtn) === 1;
    }

    /** Acepta 9999-0001, 9999 0001 o +504 9999-0001; devuelve 8 dígitos. */
    public static function normalizarTelefono(string $tel): string
    {
        $d = preg_replace('/\D+/', '', $tel) ?? '';
        if (strlen($d) === 11 && str_starts_with($d, '504')) {
            $d = substr($d, 3);
        }
        return $d;
    }

    public static function telefonoValido(string $tel): bool
    {
        return preg_match('/^[2389]\d{7}$/', $tel) === 1;
    }

    /**
     * Valida el formulario de checkout.
     * @return array{errores: array<string,string>, datos: array}
     */
    public function validarCheckout(array $in): array
    {
        $errores = [];

        $nombre    = trim((string) ($in['nombre'] ?? ''));
        $rtn       = self::normalizarRTN((string) ($in['rtn'] ?? ''));
        $telefono  = self::normalizarTelefono((string) ($in['telefono'] ?? ''));
        $correo    = trim((string) ($in['correo'] ?? ''));
        $direccion = trim((string) ($in['direccion'] ?? ''));
        $modalidad = (string) ($in['modalidad_flete'] ?? '');
        $sucursal  = filter_var($in['id_sucursal'] ?? null, FILTER_VALIDATE_INT);

        if (self::largo($nombre) < 3 || self::largo($nombre) > 150) {
            $errores['nombre'] = 'Escribe el nombre completo o razón social (3 a 150 caracteres).';
        }
        if (!self::rtnValido($rtn)) {
            $errores['rtn'] = 'El RTN debe tener exactamente 14 dígitos.';
        }
        if (!self::telefonoValido($telefono)) {
            $errores['telefono'] = 'Teléfono no válido. Usa 8 dígitos que empiecen con 2, 3, 8 o 9.';
        }
        if ($correo !== '' && (!filter_var($correo, FILTER_VALIDATE_EMAIL) || self::largo($correo) > 150)) {
            $errores['correo'] = 'El correo no tiene un formato válido.';
        }
        if (self::largo($direccion) > 255) {
            $errores['direccion'] = 'La dirección no puede pasar de 255 caracteres.';
        }
        if (!isset(Carrito::FLETES[$modalidad])) {
            $errores['modalidad_flete'] = 'Elige cómo quieres recibir tu pedido.';
        } elseif ($modalidad !== 'mostrador' && self::largo($direccion) < 5) {
            $errores['direccion'] = 'Indica la dirección de entrega para el envío.';
        }
        if ($sucursal === false || $sucursal === null || !$this->sucursalExiste((int) $sucursal)) {
            $errores['id_sucursal'] = 'Elige la sucursal que atiende tu pedido.';
        }

        return [
            'errores' => $errores,
            'datos'   => [
                'nombre'          => $nombre,
                'rtn'             => $rtn,
                'telefono'        => $telefono,
                'correo'          => $correo,
                'direccion'       => $direccion,
                'modalidad_flete' => $modalidad,
                'id_sucursal'     => (int) $sucursal,
            ],
        ];
    }

    /* ---------- Creación de la factura ---------- */

    /**
     * Guarda cliente, factura y detalle en UNA transacción y descuenta el
     * inventario. Si algo falla, no se guarda nada.
     *
     * @return array{id:int, numero:string}
     */
    public function crear(array $datos, Carrito $carrito): array
    {
        $items = $carrito->items();
        if (!$items) {
            throw new DomainException('Tu carrito está vacío.');
        }
        if (Carrito::hayProblemas($items)) {
            throw new DomainException('Hay repuestos sin existencias suficientes. Revisa tu carrito.');
        }

        $t = $carrito->totales($items, $datos['modalidad_flete']);

        $this->db->beginTransaction();
        try {
            foreach ($items as $it) {
                $this->descontarStock($it['id'], $it['nombre'], $it['cantidad'], $datos['id_sucursal']);
            }

            $idCliente = $this->guardarCliente($datos);
            $numero    = $this->siguienteNumero();

            $st = $this->db->prepare(
                "INSERT INTO facturas
                    (numero_factura, id_cliente, id_sucursal, subtotal, descuento,
                     tasa_isv, isv, flete, total, modalidad_flete)
                 VALUES
                    (:numero, :cliente, :sucursal, :subtotal, :descuento,
                     :tasa, :isv, :flete, :total, :modalidad)"
            );
            $st->execute([
                'numero'    => $numero,
                'cliente'   => $idCliente,
                'sucursal'  => $datos['id_sucursal'],
                'subtotal'  => $t['subtotal'],
                'descuento' => $t['descuento'],
                'tasa'      => $t['tasa_isv'],
                'isv'       => $t['isv'],
                'flete'     => $t['flete'],
                'total'     => $t['total'],
                'modalidad' => $datos['modalidad_flete'],
            ]);
            $idFactura = (int) $this->db->lastInsertId();

            $det = $this->db->prepare(
                "INSERT INTO detalle_factura
                    (id_factura, id_producto, cantidad, precio_unitario, subtotal)
                 VALUES (:f, :p, :c, :pu, :s)"
            );
            foreach ($items as $it) {
                $det->execute([
                    'f'  => $idFactura,
                    'p'  => $it['id'],
                    'c'  => $it['cantidad'],
                    'pu' => $it['precio'],
                    's'  => $it['subtotal'],
                ]);
            }

            $this->db->commit();
            return ['id' => $idFactura, 'numero' => $numero];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Toma primero de la sucursal elegida y completa con las demás. */
    private function descontarStock(int $idProducto, string $nombre, int $cantidad, int $idSucursal): void
    {
        $st = $this->db->prepare(
            "SELECT id_inventario, cantidad
             FROM inventario
             WHERE id_producto = :p AND activo = 1 AND cantidad > 0
             ORDER BY (id_sucursal = :s) DESC, cantidad DESC
             FOR UPDATE"
        );
        $st->execute(['p' => $idProducto, 's' => $idSucursal]);

        $upd   = $this->db->prepare("UPDATE inventario SET cantidad = cantidad - :t WHERE id_inventario = :i");
        $falta = $cantidad;
        foreach ($st->fetchAll() as $fila) {
            if ($falta <= 0) {
                break;
            }
            $toma = min($falta, (int) $fila['cantidad']);
            $upd->execute(['t' => $toma, 'i' => $fila['id_inventario']]);
            $falta -= $toma;
        }

        if ($falta > 0) {
            throw new DomainException('No hay existencias suficientes de «' . $nombre . '».');
        }
    }

    /** Crea el cliente por RTN o actualiza sus datos si ya existe. */
    private function guardarCliente(array $d): int
    {
        $st = $this->db->prepare("SELECT id_cliente FROM clientes WHERE rtn = :rtn LIMIT 1 FOR UPDATE");
        $st->execute(['rtn' => $d['rtn']]);
        $id = $st->fetchColumn();

        $params = [
            'nombre'    => $d['nombre'],
            'telefono'  => $d['telefono'],
            'correo'    => $d['correo'] !== '' ? $d['correo'] : null,
            'direccion' => $d['direccion'] !== '' ? $d['direccion'] : null,
        ];

        if ($id !== false) {
            $upd = $this->db->prepare(
                "UPDATE clientes
                 SET nombre = :nombre, telefono = :telefono, correo = :correo,
                     direccion = :direccion, activo = 1
                 WHERE id_cliente = :id"
            );
            $upd->execute($params + ['id' => $id]);
            return (int) $id;
        }

        $ins = $this->db->prepare(
            "INSERT INTO clientes (nombre, rtn, telefono, correo, direccion)
             VALUES (:nombre, :rtn, :telefono, :correo, :direccion)"
        );
        $ins->execute($params + ['rtn' => $d['rtn']]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Correlativo 001-001-01-00000001, 00000002, ...
     * Se calcula dentro de la transacción; numero_factura es UNIQUE, así que
     * un duplicado por concurrencia haría fallar (y revertir) la compra.
     */
    private function siguienteNumero(): string
    {
        $st = $this->db->prepare(
            "SELECT numero_factura FROM facturas
             WHERE numero_factura LIKE :pref
             ORDER BY numero_factura DESC
             LIMIT 1 FOR UPDATE"
        );
        $st->execute(['pref' => self::PREFIJO . '%']);
        $ultimo = $st->fetchColumn();

        $sec = 1;
        if ($ultimo !== false) {
            $sec = (int) substr((string) $ultimo, strlen(self::PREFIJO)) + 1;
        }
        return self::PREFIJO . str_pad((string) $sec, 8, '0', STR_PAD_LEFT);
    }

    /* ---------- Consultas ---------- */

    public function obtener(int $idFactura): ?array
    {
        $st = $this->db->prepare(
            "SELECT f.*, c.nombre AS cliente, c.rtn, c.telefono, c.correo,
                    c.direccion AS direccion_cliente,
                    s.nombre AS sucursal, s.ciudad AS ciudad_sucursal
             FROM facturas f
             JOIN clientes c   ON c.id_cliente = f.id_cliente
             JOIN sucursales s ON s.id_sucursal = f.id_sucursal
             WHERE f.id_factura = :id
             LIMIT 1"
        );
        $st->execute(['id' => $idFactura]);
        $fila = $st->fetch();
        return $fila ?: null;
    }

    public function detalle(int $idFactura): array
    {
        $st = $this->db->prepare(
            "SELECT d.cantidad, d.precio_unitario, d.subtotal, p.nombre
             FROM detalle_factura d
             JOIN productos p ON p.id_producto = d.id_producto
             WHERE d.id_factura = :id
             ORDER BY d.id_detalle"
        );
        $st->execute(['id' => $idFactura]);
        return $st->fetchAll();
    }

    /** Historial de un cliente: requiere RTN y teléfono que coincidan. */
    public function historial(string $rtn, string $telefono): array
    {
        $st = $this->db->prepare(
            "SELECT f.id_factura, f.numero_factura, f.fecha_emision, f.total,
                    f.modalidad_flete, f.anulada, s.nombre AS sucursal,
                    (SELECT COALESCE(SUM(d.cantidad), 0)
                     FROM detalle_factura d WHERE d.id_factura = f.id_factura) AS unidades
             FROM facturas f
             JOIN clientes c   ON c.id_cliente = f.id_cliente
             JOIN sucursales s ON s.id_sucursal = f.id_sucursal
             WHERE c.rtn = :rtn AND c.telefono = :tel
             ORDER BY f.fecha_emision DESC, f.id_factura DESC"
        );
        $st->execute(['rtn' => $rtn, 'tel' => $telefono]);
        return $st->fetchAll();
    }

    /** Vista de personal (admin / bodega): últimas facturas de todos. */
    public function recientes(int $limite = 50): array
    {
        $limite = max(1, min($limite, 200));
        $st = $this->db->query(
            "SELECT f.id_factura, f.numero_factura, f.fecha_emision, f.total,
                    f.modalidad_flete, f.anulada, c.nombre AS cliente, c.rtn,
                    s.nombre AS sucursal
             FROM facturas f
             JOIN clientes c   ON c.id_cliente = f.id_cliente
             JOIN sucursales s ON s.id_sucursal = f.id_sucursal
             ORDER BY f.fecha_emision DESC, f.id_factura DESC
             LIMIT $limite"
        );
        return $st->fetchAll();
    }

    public function sucursalesActivas(): array
    {
        return $this->db->query(
            "SELECT id_sucursal, nombre, ciudad FROM sucursales WHERE activo = 1 ORDER BY ciudad, nombre"
        )->fetchAll();
    }

    private function sucursalExiste(int $id): bool
    {
        $st = $this->db->prepare("SELECT 1 FROM sucursales WHERE id_sucursal = :id AND activo = 1");
        $st->execute(['id' => $id]);
        return $st->fetchColumn() !== false;
    }
}