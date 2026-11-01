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

$e = fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$esPersonal = in_array($_SESSION['rol'] ?? '', ['admin', 'bodeguero'], true);
$rtn        = '';
$telefono   = '';
$error      = '';
$facturas   = null; // null = aún no se ha consultado

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rtn      = Factura::normalizarRTN((string) ($_POST['rtn'] ?? ''));
    $telefono = Factura::normalizarTelefono((string) ($_POST['telefono'] ?? ''));

    if (!Factura::rtnValido($rtn) || !Factura::telefonoValido($telefono)) {
        $error = 'Escribe un RTN de 14 dígitos y un teléfono de 8 dígitos válidos.';
    } else {
        $facturas = $modelo->historial($rtn, $telefono);
        // Quien demuestra su RTN + teléfono puede abrir sus facturas
        foreach ($facturas as $f) {
            $_SESSION['mis_facturas'][] = (int) $f['id_factura'];
        }
    }
}

$recientes = $esPersonal ? $modelo->recientes(50) : [];

$modalidades = [
    'mostrador'    => 'Mostrador',
    'moto_express' => 'Moto express',
    'camioncito'   => 'Camioncito',
];

$tituloPagina = 'Historial de compras | Autopartes Don Pancho';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= $baseUrl ?>assets/css/checkout.css">

<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-clock-history text-warning"></i> Historial de compras</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="post" action="historial.php" class="row g-3 align-items-end" id="formHistorial" novalidate>
                <div class="col-md-5">
                    <label for="rtn" class="form-label">RTN</label>
                    <input type="text" id="rtn" name="rtn" inputmode="numeric" maxlength="17"
                           class="form-control" value="<?= $e($rtn) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" inputmode="tel" maxlength="14"
                           class="form-control" value="<?= $e($telefono) ?>" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-warning w-100 fw-semibold"><i class="bi bi-search"></i> Consultar</button>
                </div>
            </form>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger mt-3 mb-0"><?= $e($error) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($facturas !== null): ?>
        <?php if (!$facturas): ?>
            <div class="alert alert-info">No encontramos compras con ese RTN y teléfono.</div>
        <?php else: ?>
            <div class="card shadow-sm mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Factura</th><th>Fecha</th><th>Sucursal</th>
                                <th class="text-center">Unidades</th><th>Entrega</th>
                                <th class="text-end">Total</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($facturas as $f): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?= $e($f['numero_factura']) ?>
                                    <?php if ((int) $f['anulada'] === 1): ?><span class="badge text-bg-danger">Anulada</span><?php endif; ?>
                                </td>
                                <td><?= $e(date('d/m/Y H:i', strtotime((string) $f['fecha_emision']))) ?></td>
                                <td><?= $e($f['sucursal']) ?></td>
                                <td class="text-center"><?= (int) $f['unidades'] ?></td>
                                <td><?= $e($modalidades[$f['modalidad_flete']] ?? $f['modalidad_flete']) ?></td>
                                <td class="text-end fw-semibold"><?= Carrito::dinero($f['total']) ?></td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-dark" href="factura.php?id=<?= (int) $f['id_factura'] ?>">Ver</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($esPersonal): ?>
        <h2 class="h5 fw-bold mt-5 mb-3">Últimas facturas (vista de personal)</h2>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Factura</th><th>Fecha</th><th>Cliente</th><th>RTN</th><th>Sucursal</th><th class="text-end">Total</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recientes as $f): ?>
                        <tr>
                            <td><?= $e($f['numero_factura']) ?>
                                <?php if ((int) $f['anulada'] === 1): ?><span class="badge text-bg-danger">Anulada</span><?php endif; ?></td>
                            <td><?= $e(date('d/m/Y H:i', strtotime((string) $f['fecha_emision']))) ?></td>
                            <td><?= $e($f['cliente']) ?></td>
                            <td><?= $e($f['rtn']) ?></td>
                            <td><?= $e($f['sucursal']) ?></td>
                            <td class="text-end"><?= Carrito::dinero($f['total']) ?></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-dark" href="factura.php?id=<?= (int) $f['id_factura'] ?>">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?= $baseUrl ?>assets/js/carrito.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>