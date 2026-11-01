<?php
declare(strict_types=1);
require_once __DIR__ . '/clases/Factura.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $modelo = new Factura();
} catch (RuntimeException $e) {
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}

$e  = fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

$esPersonal = in_array($_SESSION['rol'] ?? '', ['admin', 'bodeguero'], true);
$esSuya     = $id !== false && $id !== null
              && in_array($id, $_SESSION['mis_facturas'] ?? [], true);

$factura = null;
$detalle = [];
$estado  = 'ok';

if ($id === false || $id === null || $id < 1) {
    $estado = 'invalida';
} elseif (!$esPersonal && !$esSuya) {
    $estado = 'sin_permiso';
} else {
    $factura = $modelo->obtener($id);
    if ($factura === null) {
        $estado = 'no_existe';
    } else {
        $detalle = $modelo->detalle($id);
    }
}

$modalidades = [
    'mostrador'    => 'Retiro en mostrador',
    'moto_express' => 'Moto express',
    'camioncito'   => 'Flete en camioncito',
];

$tituloPagina = 'Factura | Autopartes Don Pancho';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= $baseUrl ?>assets/css/checkout.css">

<div class="container py-4">
<?php if ($estado !== 'ok'): ?>
    <div class="alert alert-warning">
        <?php if ($estado === 'sin_permiso'): ?>
            No tienes acceso a esta factura. Consulta tus compras en el
            <a href="historial.php">historial</a> con tu RTN y teléfono.
        <?php elseif ($estado === 'no_existe'): ?>
            La factura no existe.
        <?php else: ?>
            Factura no válida.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3 no-imprimir">
        <div class="alert alert-success mb-0 py-2">
            <i class="bi bi-check-circle-fill"></i> Compra registrada correctamente.
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-dark" id="btnImprimir"><i class="bi bi-printer"></i> Imprimir</button>
            <a href="historial.php" class="btn btn-outline-dark">Historial</a>
        </div>
    </div>

    <div class="card shadow-sm factura-papel">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between flex-wrap gap-3 border-bottom pb-3 mb-3">
                <div>
                    <h1 class="h4 fw-bold mb-0">Autopartes &amp; Repuestos «Don Pancho»</h1>
                    <div class="text-muted small">Factura digital</div>
                </div>
                <div class="text-md-end">
                    <div class="text-muted small">Factura No.</div>
                    <div class="h5 fw-bold mb-0"><?= $e($factura['numero_factura']) ?></div>
                    <div class="small"><?= $e(date('d/m/Y H:i', strtotime((string) $factura['fecha_emision']))) ?></div>
                    <?php if ((int) $factura['anulada'] === 1): ?>
                        <span class="badge text-bg-danger mt-1">ANULADA</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="text-muted small">Cliente</div>
                    <div class="fw-semibold"><?= $e($factura['cliente']) ?></div>
                    <div>RTN: <?= $e($factura['rtn']) ?></div>
                    <div>Tel.: <?= $e($factura['telefono']) ?></div>
                    <?php if (!empty($factura['correo'])): ?><div><?= $e($factura['correo']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Atendido en</div>
                    <div class="fw-semibold"><?= $e($factura['sucursal']) ?> (<?= $e($factura['ciudad_sucursal']) ?>)</div>
                    <div>Entrega: <?= $e($modalidades[$factura['modalidad_flete']] ?? $factura['modalidad_flete']) ?></div>
                    <?php if (!empty($factura['direccion_cliente']) && $factura['modalidad_flete'] !== 'mostrador'): ?>
                        <div>Dirección: <?= $e($factura['direccion_cliente']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Descripción</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">Precio unit.</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($detalle as $d): ?>
                        <tr>
                            <td><?= $e($d['nombre']) ?></td>
                            <td class="text-center"><?= (int) $d['cantidad'] ?></td>
                            <td class="text-end"><?= Carrito::dinero($d['precio_unitario']) ?></td>
                            <td class="text-end"><?= Carrito::dinero($d['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-5">
                    <dl class="resumen-lista mb-0">
                        <dt>Subtotal</dt><dd><?= Carrito::dinero($factura['subtotal']) ?></dd>
                        <?php if ((float) $factura['descuento'] > 0): ?>
                            <dt class="text-success">Descuento</dt>
                            <dd class="text-success">- <?= Carrito::dinero($factura['descuento']) ?></dd>
                        <?php endif; ?>
                        <dt>ISV (<?= $e(rtrim(rtrim(number_format((float) $factura['tasa_isv'], 2), '0'), '.')) ?>%)</dt>
                        <dd><?= Carrito::dinero($factura['isv']) ?></dd>
                        <dt>Flete</dt><dd><?= Carrito::dinero($factura['flete']) ?></dd>
                        <dt class="fs-5 fw-bold border-top pt-2">Total</dt>
                        <dd class="fs-5 fw-bold border-top pt-2"><?= Carrito::dinero($factura['total']) ?></dd>
                    </dl>
                </div>
            </div>

            <p class="text-muted small mt-4 mb-0">Gracias por su compra. Conserve esta factura para cualquier reclamo.</p>
        </div>
    </div>
<?php endif; ?>
</div>

<script src="<?= $baseUrl ?>assets/js/carrito.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
