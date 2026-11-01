
<?php
$tituloPagina = "Sucursales | Autopartes Don Pancho";


$sucursales = [
    [
        'ciudad' => 'Tegucigalpa',
        'departamento' => 'Francisco Morazán',
        'direccion' => 'Boulevard Centroamérica, zona comercial, Tegucigalpa, M.D.C.',
        'telefono' => '+504 2234-5678',
        'horario' => 'Lunes a viernes: 8:00 a.m. - 5:00 p.m. | Sábados: 8:00 a.m. - 12:00 p.m.',
        'icono' => 'bi-building',
        'mapa' => 'https://www.google.com/maps/search/?api=1&query=Boulevard+Centroamerica+Tegucigalpa'
    ],
    [
        'ciudad' => 'San Pedro Sula',
        'departamento' => 'Cortés',
        'direccion' => 'Boulevard del Norte, sector comercial, San Pedro Sula, Cortés.',
        'telefono' => '+504 2556-7890',
        'horario' => 'Lunes a viernes: 8:00 a.m. - 5:30 p.m. | Sábados: 8:00 a.m. - 1:00 p.m.',
        'icono' => 'bi-building',
        'mapa' => 'https://www.google.com/maps/search/?api=1&query=Boulevard+del+Norte+San+Pedro+Sula'
    ],
    [
        'ciudad' => 'La Ceiba',
        'departamento' => 'Atlántida',
        'direccion' => 'Avenida San Isidro, sector comercial, La Ceiba, Atlántida.',
        'telefono' => '+504 2443-5678',
        'horario' => 'Lunes a viernes: 8:00 a.m. - 5:00 p.m. | Sábados: 8:00 a.m. - 12:00 p.m.',
        'icono' => 'bi-building',
        'mapa' => 'https://www.google.com/maps/search/?api=1&query=Avenida+San+Isidro+La+Ceiba'
    ]
];


require_once __DIR__ . '/includes/header.php';
?>

<main>

    <!-- encabezado -->
    <section class="fondo-gris py-5 text-center">
        <div class="container">

            <h1 class="fw-bold mb-3">
                Nuestras sucursales
            </h1>

            <p class="text-muted lead mb-0">
                Estamos cerca de ti para ofrecerte
                los mejores repuestos automotrices.
            </p>

        </div>
    </section>

    <!-- todas las sucursales -->
    <section class="fondo-blanco py-5">
        <div class="container">

            <h2 class="text-center fw-bold mb-4">
                Encuentra tu sucursal
            </h2>

            <div class="row g-4">

                <?php foreach ($sucursales as $sucursal): ?>

                    <div class="col-12 col-md-6 col-lg-4">

                        <div class="card sucursal-card h-100 shadow-sm">

                            <div class="card-body p-4">

                                <div class="text-center mb-4">

                                    <div class="sucursal-icono mx-auto mb-3">
                                        <i class="bi <?= htmlspecialchars($sucursal['icono']) ?>"></i>
                                    </div>

                                    <h3 class="fw-bold h4">
                                        <?= htmlspecialchars($sucursal['ciudad']) ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        <?= htmlspecialchars($sucursal['departamento']) ?>
                                    </p>

                                </div>

                                <hr>

                                <div class="mb-3">
                                    <h4 class="h6 fw-bold">
                                        <i class="bi bi-geo-alt-fill text-warning me-2"></i>
                                        Dirección
                                    </h4>

                                    <p class="text-muted ms-4">
                                        <?= htmlspecialchars($sucursal['direccion']) ?>
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <h4 class="h6 fw-bold">
                                        <i class="bi bi-telephone-fill text-warning me-2"></i>
                                        Teléfono
                                    </h4>

                                    <p class="text-muted ms-4">
                                        <?= htmlspecialchars($sucursal['telefono']) ?>
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <h4 class="h6 fw-bold">
                                        <i class="bi bi-clock-fill text-warning me-2"></i>
                                        Horario
                                    </h4>

                                    <p class="text-muted ms-4">
                                        <?= htmlspecialchars($sucursal['horario']) ?>
                                    </p>
                                </div>

                                <a
                                    href="<?= htmlspecialchars($sucursal['mapa'], ENT_QUOTES, 'UTF-8') ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-warning fw-bold w-100"
                                >
                                    <i class="bi bi-map me-2"></i>
                                    Ver ciudad en Google Maps
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>
    </section>

    <!-- para mas info -->
    <section class="fondo-gris py-5 text-center">
        <div class="container">

            <h2 class="fw-bold mb-3">
                ¿Buscas un repuesto específico?
            </h2>

            <p class="text-muted mb-4">
                Explora nuestro catálogo de repuestos
                para vehículos japoneses, coreanos y americanos.
            </p>

            <a href="<?= $baseUrl ?>catalogo.php"
               class="btn btn-warning fw-bold px-4">
                <i class="bi bi-search me-2"></i>
                Explorar catálogo
            </a>

        </div>
    </section>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
