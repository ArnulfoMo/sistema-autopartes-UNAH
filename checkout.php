<?php
declare(strict_types=1);
require_once __DIR__ . '/clases/Carrito.php';
require_once __DIR__ . '/clases/Factura.php';

try {
    $carrito = new Carrito();
    $factura = new Factura();
} catch (RuntimeException $e) {
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}

$items = $carrito->items();
if (!$items) {
    $_SESSION['flash'] = ['tipo' => 'warning', 'texto' => 'Tu carrito está vacío.'];
    header('Location: carrito.php');
    exit;
}
if (Carrito::hayProblemas($items)) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'texto' => 'Revisa las existencias de tu carrito antes de pagar.'];
    header('Location: carrito.php');
    exit;
}

// Token CSRF
if (empty($_SESSION['csrf_checkout'])) {
    $_SESSION['csrf_checkout'] = bin2hex(random_bytes(32));
}

$errores     = [];
$errorGlobal = '';
$valores     = [
    'nombre' => '', 'rtn' => '', 'telefono' => '', 'correo' => '',
    'direccion' => '', 'modalidad_flete' => 'mostrador', 'id_sucursal' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $_) {
        $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    if (!hash_equals($_SESSION['csrf_checkout'], (string) ($_POST['csrf'] ?? ''))) {
        $errorGlobal = 'La sesión del formulario expiró. Intenta de nuevo.';
    } else {
        $v = $factura->validarCheckout($_POST);
        $errores = $v['errores'];

        if (!$errores) {
            try {
                $res = $factura->crear($v['datos'], $carrito);

                $carrito->vaciar();
                $_SESSION['mis_facturas'][] = $res['id']; // permite ver su factura
                $_SESSION['csrf_checkout']  = bin2hex(random_bytes(32));

                header('Location: factura.php?id=' . $res['id']);
                exit;
            } catch (DomainException $ex) {
                $errorGlobal = $ex->getMessage();
            } catch (Throwable $ex) {
                error_log('Error al facturar: ' . $ex->getMessage());
                $errorGlobal = 'No se pudo generar la factura. Intenta de nuevo en un momento.';
            }
        }
    }
}

$modalidadActual = isset(Carrito::FLETES[$valores['modalidad_flete']]) ? $valores['modalidad_flete'] : 'mostrador';
$totales    = $carrito->totales($items, $modalidadActual);
$sucursales = $factura->sucursalesActivas();
$e          = fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$invalido   = fn(string $c): string => isset($errores[$c]) ? ' is-invalid' : '';

$tituloPagina = 'Facturación | Autopartes Don Pancho';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-receipt text-warning"></i> Finalizar compra</h1>

    <?php if ($errorGlobal !== ''): ?>
        <div class="alert alert-danger"><?= $e($errorGlobal) ?></div>
    <?php endif; ?>
    <?php if ($errores): ?>
        <div class="alert alert-danger">Revisa los campos marcados en rojo.</div>
    <?php endif; ?>

    <form method="post" action="checkout.php" id="formCheckout" novalidate>
        <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf_checkout']) ?>">

        <div class="row g-4">
            <div class="col-lg-7">
                <!-- Datos del comprador -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-semibold">1. Datos del comprador</div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <label for="nombre" class="form-label">Nombre completo o razón social *</label>
                            <input type="text" id="nombre" name="nombre" maxlength="150"
                                   class="form-control<?= $invalido('nombre') ?>" value="<?= $e($valores['nombre']) ?>" required>
                            <div class="invalid-feedback"><?= $e($errores['nombre'] ?? 'Escribe el nombre del comprador.') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label for="rtn" class="form-label">RTN (14 dígitos) *</label>
                            <input type="text" id="rtn" name="rtn" inputmode="numeric" maxlength="17"
                                   placeholder="08011990123456"
                                   class="form-control<?= $invalido('rtn') ?>" value="<?= $e($valores['rtn']) ?>" required>
                            <div class="invalid-feedback"><?= $e($errores['rtn'] ?? 'El RTN debe tener exactamente 14 dígitos.') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="tel" id="telefono" name="telefono" inputmode="tel" maxlength="14"
                                   placeholder="9999-0000"
                                   class="form-control<?= $invalido('telefono') ?>" value="<?= $e($valores['telefono']) ?>" required>
                            <div class="invalid-feedback"><?= $e($errores['telefono'] ?? 'Usa 8 dígitos que empiecen con 2, 3, 8 o 9.') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo (opcional)</label>
                            <input type="email" id="correo" name="correo" maxlength="150"
                                   class="form-control<?= $invalido('correo') ?>" value="<?= $e($valores['correo']) ?>">
                            <div class="invalid-feedback"><?= $e($errores['correo'] ?? 'Correo no válido.') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label for="id_sucursal" class="form-label">Sucursal que atiende *</label>
                            <select id="id_sucursal" name="id_sucursal" class="form-select<?= $invalido('id_sucursal') ?>" required>
                                <option value="">Selecciona...</option>
                                <?php foreach ($sucursales as $s): ?>
                                    <option value="<?= (int) $s['id_sucursal'] ?>"
                                        <?= (string) $s['id_sucursal'] === $valores['id_sucursal'] ? 'selected' : '' ?>>
                                        <?= $e($s['nombre']) ?> (<?= $e($s['ciudad']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= $e($errores['id_sucursal'] ?? 'Elige una sucursal.') ?></div>
                        </div>
                        <div class="col-12">
                            <label for="direccion" class="form-label">
                                Dirección de entrega <span class="text-muted" id="avisoDireccion">(solo si pides envío)</span>
                            </label>
                            <input type="text" id="direccion" name="direccion" maxlength="255"
                                   class="form-control<?= $invalido('direccion') ?>" value="<?= $e($valores['direccion']) ?>">
                            <div class="invalid-feedback"><?= $e($errores['direccion'] ?? 'Indica la dirección de entrega.') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Flete -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-semibold">2. Modalidad de entrega</div>
                    <div class="card-body">
                        <?php foreach (Carrito::FLETES as $clave => $f): ?>
                            <label class="opcion-flete d-flex align-items-center gap-3 p-3 border rounded mb-2">
                                <input class="form-check-input mt-0" type="radio" name="modalidad_flete"
                                       value="<?= $e($clave) ?>" data-monto="<?= $f['monto'] ?>"
                                       <?= $clave === $modalidadActual ? 'checked' : '' ?>>
                                <span class="flex-grow-1">
                                    <span class="fw-semibold d-block"><?= $e($f['etiqueta']) ?></span>
                                    <small class="text-muted"><?= $e($f['detalle']) ?></small>
                                </span>
                                <span class="fw-bold"><?= Carrito::dinero($f['monto']) ?></span>
                            </label>
                        <?php endforeach; ?>
                        <?php if (isset($errores['modalidad_flete'])): ?>
                            <div class="text-danger small"><?= $e($errores['modalidad_flete']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="col-lg-5">
                <div class="card shadow-sm resumen-card" id="resumenCheckout"
                     data-subtotal="<?= $totales['subtotal'] ?>"
                     data-descuento="<?= $totales['descuento'] ?>"
                     data-tasa-isv="<?= $totales['tasa_isv'] ?>">
                    <div class="card-header bg-dark text-white fw-semibold">Tu pedido</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($items as $it): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?= $it['cantidad'] ?> × <?= $e($it['nombre']) ?></span>
                                <span><?= Carrito::dinero($it['subtotal']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="card-body">
                        <dl class="resumen-lista mb-0">
                            <dt>Subtotal</dt><dd id="resSubtotal"><?= Carrito::dinero($totales['subtotal']) ?></dd>
                            <?php if ($totales['porcentaje_descuento'] > 0): ?>
                                <dt class="text-success">Descuento (<?= $totales['porcentaje_descuento'] ?>%)</dt>
                                <dd class="text-success" id="resDescuento">- <?= Carrito::dinero($totales['descuento']) ?></dd>
                            <?php endif; ?>
                            <dt>ISV (<?= $totales['tasa_isv'] ?>%)</dt><dd id="resIsv"><?= Carrito::dinero($totales['isv']) ?></dd>
                            <dt>Flete</dt><dd id="resFlete"><?= Carrito::dinero($totales['flete']) ?></dd>
                            <dt class="fs-5 fw-bold border-top pt-2">Total</dt>
                            <dd class="fs-5 fw-bold border-top pt-2" id="resTotal"><?= Carrito::dinero($totales['total']) ?></dd>
                        </dl>
                    </div>
                    <div class="card-footer bg-white">
                        <button type="submit" class="btn btn-warning w-100 fw-semibold">
                            <i class="bi bi-check2-circle"></i> Confirmar y generar factura
                        </button>
                        <a href="carrito.php" class="btn btn-link w-100 mt-1">Volver al carrito</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="<?= $baseUrl ?>assets/js/carrito.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
