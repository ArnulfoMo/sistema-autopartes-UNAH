<?php
declare(strict_types=1);
require_once __DIR__ . '/clases/Carrito.php';

try {
    $carrito = new Carrito(); // también inicia la sesión
} catch (RuntimeException $e) {
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}

$esAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

/* ---------- Acciones (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion  = (string) ($_POST['accion'] ?? '');
    $ok      = true;
    $tipo    = 'success';
    $mensaje = '';

    try {
        switch ($accion) {
            case 'agregar':
                // Un producto (id_producto + cantidad) o varios (items[id] = cantidad, útil para el kit)
                $lista = [];
                if (isset($_POST['items']) && is_array($_POST['items'])) {
                    foreach ($_POST['items'] as $id => $cant) {
                        $lista[(int) $id] = (int) $cant;
                    }
                } else {
                    $id = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT);
                    if ($id === false || $id === null) {
                        throw new DomainException('Repuesto no válido.');
                    }
                    $lista[$id] = (int) ($_POST['cantidad'] ?? 1);
                }

                $ajustado = false;
                $nombres  = [];
                foreach ($lista as $id => $cant) {
                    $r = $carrito->agregar($id, $cant);
                    $nombres[] = $r['nombre'];
                    $ajustado = $ajustado || $r['ajustada'];
                }
                $mensaje = count($nombres) === 1
                    ? '«' . $nombres[0] . '» agregado al carrito.'
                    : count($nombres) . ' repuestos agregados al carrito.';
                if ($ajustado) {
                    $tipo    = 'warning';
                    $mensaje .= ' Se ajustó la cantidad al máximo disponible.';
                }
                break;

            case 'actualizar':
                $id   = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT);
                $cant = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);
                if ($id === false || $id === null || $cant === false || $cant === null) {
                    throw new DomainException('Datos no válidos.');
                }
                $r = $carrito->actualizar($id, $cant);
                $mensaje = 'Carrito actualizado.';
                if ($r !== null && $r['ajustada']) {
                    $tipo    = 'warning';
                    $mensaje = 'Solo hay ' . $r['limite'] . ' en existencia de «' . $r['nombre'] . '».';
                }
                break;

            case 'quitar':
                $id = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT);
                if ($id === false || $id === null) {
                    throw new DomainException('Repuesto no válido.');
                }
                $carrito->quitar($id);
                $mensaje = 'Repuesto eliminado del carrito.';
                break;

            case 'vaciar':
                $carrito->vaciar();
                $mensaje = 'Carrito vaciado.';
                break;

            default:
                throw new DomainException('Acción no válida.');
        }
    } catch (DomainException $e) {
        $ok      = false;
        $tipo    = 'danger';
        $mensaje = $e->getMessage();
    }

    if ($esAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'mensaje' => $mensaje, 'unidades' => $carrito->unidades()]);
        exit;
    }

    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $mensaje];
    header('Location: carrito.php');
    exit;
}

/* ---------- Vista ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$items     = $carrito->items();
$totales   = $carrito->totales($items);
$problemas = Carrito::hayProblemas($items);
$e         = fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$tituloPagina = 'Mi carrito | Autopartes Don Pancho';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= $baseUrl ?>assets/css/checkout.css">

<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-cart3 text-warning"></i> Mi carrito</h1>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $e($flash['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= $e($flash['texto']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!$items): ?>
        <div class="card shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-cart-x display-3 text-secondary"></i>
                <h2 class="h5 mt-3">Tu carrito está vacío</h2>
                <p class="text-muted">Explora el catálogo o arma tu kit de mantenimiento.</p>
                <a href="<?= $baseUrl ?>catalogo.php" class="btn btn-warning fw-semibold">Ver catálogo</a>
                <a href="<?= $baseUrl ?>kit.php" class="btn btn-outline-dark">Arma tu Kit</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 tabla-carrito">
                            <thead class="table-dark">
                                <tr>
                                    <th>Repuesto</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img class="carrito-foto"
                                                 src="<?= $baseUrl ?>uploads/<?= rawurlencode($it['foto']) ?>"
                                                 alt="<?= $e($it['nombre']) ?>"
                                                 onerror="this.onerror=null;this.src='<?= $baseUrl ?>assets/img/logo.png'">
                                            <div>
                                                <div class="fw-semibold"><?= $e($it['nombre']) ?></div>
                                                <?php if ($it['marca'] !== ''): ?>
                                                    <small class="text-muted"><?= $e($it['marca']) ?></small>
                                                <?php endif; ?>
                                                <?php if ($it['problema'] !== null): ?>
                                                    <div><span class="badge text-bg-danger"><?= $e($it['problema']) ?></span></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end"><?= Carrito::dinero($it['precio']) ?></td>
                                    <td class="text-center">
                                        <form method="post" action="carrito.php" class="form-cantidad">
                                            <input type="hidden" name="accion" value="actualizar">
                                            <input type="hidden" name="id_producto" value="<?= $it['id'] ?>">
                                            <input type="number" name="cantidad" class="form-control form-control-sm mx-auto input-cantidad"
                                                   value="<?= $it['cantidad'] ?>" min="0"
                                                   max="<?= max(1, min($it['stock'], Carrito::MAX_POR_PRODUCTO)) ?>"
                                                   aria-label="Cantidad de <?= $e($it['nombre']) ?>">
                                            <noscript><button class="btn btn-sm btn-outline-dark mt-1">Actualizar</button></noscript>
                                        </form>
                                    </td>
                                    <td class="text-end fw-semibold"><?= Carrito::dinero($it['subtotal']) ?></td>
                                    <td class="text-end">
                                        <form method="post" action="carrito.php">
                                            <input type="hidden" name="accion" value="quitar">
                                            <input type="hidden" name="id_producto" value="<?= $it['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" title="Quitar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="<?= $baseUrl ?>catalogo.php" class="btn btn-outline-dark">
                        <i class="bi bi-arrow-left"></i> Seguir comprando
                    </a>
                    <form method="post" action="carrito.php"
                          onsubmit="return confirm('¿Vaciar todo el carrito?');">
                        <input type="hidden" name="accion" value="vaciar">
                        <button class="btn btn-outline-danger">Vaciar carrito</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm resumen-card">
                    <div class="card-header bg-dark text-white fw-semibold">Resumen</div>
                    <div class="card-body">
                        <dl class="resumen-lista mb-0">
                            <dt>Repuestos (unidades)</dt><dd><?= $totales['unidades'] ?></dd>
                            <dt>Subtotal</dt><dd><?= Carrito::dinero($totales['subtotal']) ?></dd>
                            <?php if ($totales['porcentaje_descuento'] > 0): ?>
                                <dt class="text-success">Descuento (<?= $totales['porcentaje_descuento'] ?>%)</dt>
                                <dd class="text-success">- <?= Carrito::dinero($totales['descuento']) ?></dd>
                            <?php endif; ?>
                            <dt>ISV (<?= $totales['tasa_isv'] ?>%)</dt><dd><?= Carrito::dinero($totales['isv']) ?></dd>
                            <dt class="fw-bold border-top pt-2">Total sin flete</dt>
                            <dd class="fw-bold border-top pt-2"><?= Carrito::dinero($totales['total']) ?></dd>
                        </dl>

                        <?php if ($totales['falta_descuento'] !== null): ?>
                            <div class="alert alert-info small mt-3 mb-0">
                                <i class="bi bi-tag"></i>
                                Agrega <strong><?= $totales['falta_descuento']['unidades'] ?></strong> repuesto(s) más
                                y obtén <strong><?= $totales['falta_descuento']['porcentaje'] ?>% de descuento</strong>.
                            </div>
                        <?php endif; ?>
                        <p class="small text-muted mt-3 mb-0">El flete se calcula en el siguiente paso.</p>
                    </div>
                    <div class="card-footer bg-white">
                        <?php if ($problemas): ?>
                            <div class="alert alert-danger small">Ajusta o quita los repuestos marcados para continuar.</div>
                            <button class="btn btn-warning w-100 fw-semibold" disabled>Continuar con la compra</button>
                        <?php else: ?>
                            <a href="checkout.php" class="btn btn-warning w-100 fw-semibold">
                                Continuar con la compra <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?= $baseUrl ?>assets/js/carrito.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
