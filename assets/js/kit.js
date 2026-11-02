
document.addEventListener('DOMContentLoaded', () => {

    const tipo = document.getElementById('tipoVehiculo');
    const marca = document.getElementById('marcaVehiculo');
    const modelo = document.getElementById('modeloVehiculo');
    const viscosidad = document.getElementById('viscosidad');

    const contenedorAceites = document.getElementById('contenedorAceites');
    const contenedorFiltros = document.getElementById('contenedorFiltros');
    const contenedorRepuestos = document.getElementById('contenedorRepuestos');

    const resumen = document.getElementById('resumenKit');
    const totalKit = document.getElementById('totalKit');

    const productosCarrito = document.getElementById('productosCarrito');
    const btnAgregar = document.getElementById('btnAgregarCarrito');

    let productos = {
        aceites: [],
        filtros: [],
        repuestos: []
    };

    /*
    |--------------------------------------------------------------------------
    | Formatear precios
    |--------------------------------------------------------------------------
    */

    function formatoPrecio(precio) {

        return Number(precio).toLocaleString('es-HN', {
            style: 'currency',
            currency: 'HNL'
        });

    }

    /*
    |--------------------------------------------------------------------------
    | Escapar HTML
    |--------------------------------------------------------------------------
    */

    function escaparHTML(valor) {

        return String(valor ?? '').replace(/[&<>"']/g, caracter => {

            const entidades = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return entidades[caracter];

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Cargar marcas
    |--------------------------------------------------------------------------
    */

    tipo.addEventListener('change', () => {

        marca.innerHTML = '<option value="">Selecciona una marca</option>';

        modelo.innerHTML = '<option value="">Selecciona un modelo</option>';

        marca.disabled = true;
        modelo.disabled = true;

        limpiarProductos();

        const tipoSeleccionado = tipo.value;

        if (!tipoSeleccionado) {
            return;
        }

        const marcas = window.KIT_VEHICULOS[tipoSeleccionado];

        Object.keys(marcas).forEach(nombreMarca => {

            const opcion = document.createElement('option');

            opcion.value = nombreMarca;
            opcion.textContent = nombreMarca;

            marca.appendChild(opcion);

        });

        marca.disabled = false;

    });

    /*
    |--------------------------------------------------------------------------
    | Cargar modelos
    |--------------------------------------------------------------------------
    */

    marca.addEventListener('change', () => {

        modelo.innerHTML = '<option value="">Selecciona un modelo</option>';

        modelo.disabled = true;

        limpiarProductos();

        if (!marca.value) {
            return;
        }

        const modelos = window.KIT_VEHICULOS[tipo.value][marca.value];

        modelos.forEach(nombreModelo => {

            const opcion = document.createElement('option');

            opcion.value = nombreModelo;
            opcion.textContent = nombreModelo;

            modelo.appendChild(opcion);

        });

        modelo.disabled = false;

    });

    /*
    |--------------------------------------------------------------------------
    | Consultar productos
    |--------------------------------------------------------------------------
    */

    modelo.addEventListener('change', cargarProductos);

    async function cargarProductos() {

        limpiarProductos();

        if (!tipo.value || !marca.value || !modelo.value) {
            return;
        }

        contenedorAceites.innerHTML = '<p>Consultando productos...</p>';
        contenedorFiltros.innerHTML = '<p>Consultando productos...</p>';
        contenedorRepuestos.innerHTML = '<p>Consultando productos...</p>';

        try {

            const parametros = new URLSearchParams({
                accion: 'productos',
                tipo: tipo.value,
                marca: marca.value
            });

            const respuesta = await fetch(`kit.php?${parametros.toString()}`);

            if (!respuesta.ok) {
                throw new Error('No se pudieron consultar los productos.');
            }

            const datos = await respuesta.json();

            if (datos.error) {
                throw new Error(datos.error);
            }

            productos = datos;

            cargarViscosidades();

            mostrarProductos('filtros', contenedorFiltros, 'checkbox');

            mostrarProductos('repuestos', contenedorRepuestos, 'checkbox');

            actualizarResumen();

        } catch (error) {

            contenedorAceites.innerHTML = `<p class="text-danger">${escaparHTML(error.message)}</p>`;

            contenedorFiltros.innerHTML = '';
            contenedorRepuestos.innerHTML = '';

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Cargar viscosidades disponibles
    |--------------------------------------------------------------------------
    */

    function cargarViscosidades() {

        viscosidad.innerHTML = '';

        const disponibles = [
            ...new Set(
                productos.aceites.map(producto =>
                    producto.viscosidad || 'Sin especificar'
                )
            )
        ];

        if (disponibles.length === 0) {

            viscosidad.innerHTML = '<option value="">No hay aceites disponibles</option>';

            viscosidad.disabled = true;

            contenedorAceites.innerHTML =
                '<p class="text-muted">No hay aceites disponibles para este vehículo.</p>';

            return;
        }

        viscosidad.innerHTML = '<option value="">Selecciona una viscosidad</option>';

        disponibles.forEach(valor => {

            const opcion = document.createElement('option');

            opcion.value = valor;
            opcion.textContent = valor;

            viscosidad.appendChild(opcion);

        });

        viscosidad.disabled = false;

    }

    viscosidad.addEventListener('change', () => {

        mostrarProductos('aceites', contenedorAceites, 'radio');

        actualizarResumen();

    });

    /*
    |--------------------------------------------------------------------------
    | Mostrar productos
    |--------------------------------------------------------------------------
    */

    function mostrarProductos(grupo, contenedor, tipoInput) {

        contenedor.innerHTML = '';

        let lista = productos[grupo] || [];

        if (grupo === 'aceites') {

            if (!viscosidad.value) {

                contenedor.innerHTML =
                    '<p class="text-muted">Selecciona una viscosidad para ver los aceites.</p>';

                return;
            }

            lista = lista.filter(producto => {

                const valor = producto.viscosidad || 'Sin especificar';

                return valor === viscosidad.value;

            });

        }

        if (lista.length === 0) {

            contenedor.innerHTML =
                '<p class="text-muted">No hay productos disponibles en esta categoría.</p>';

            return;
        }

        lista.forEach(producto => {

            const columna = document.createElement('div');

            columna.className = 'col-md-6 col-lg-4';

            const identificador = `${grupo}_${producto.id_producto}`;

            const descripcion = producto.descripcion || 'Sin descripción disponible';

            columna.innerHTML = `

                <div class="kit-producto">

                    <div class="kit-producto-cabecera">

                        <input
                            type="${tipoInput}"
                            name="${grupo === 'aceites' ? 'aceite' : grupo}"
                            value="${Number(producto.id_producto)}"
                            id="${identificador}"
                            class="form-check-input producto-seleccion"
                            data-id="${Number(producto.id_producto)}"
                            data-grupo="${grupo}"
                            data-precio="${Number(producto.precio)}"
                        >

                        <label for="${identificador}">
                            ${escaparHTML(producto.nombre)}
                        </label>

                    </div>

                    <p class="kit-descripcion">
                        ${escaparHTML(descripcion)}
                    </p>

                    <div class="kit-producto-precio">

                        ${formatoPrecio(producto.precio)}

                    </div>

                    <div class="kit-cantidad">

                        <label>Cantidad:</label>

                        <input
                            type="number"
                            class="form-control cantidad-producto"
                            min="1"
                            max="99"
                            value="1"
                            disabled
                        >

                    </div>

                    <small class="text-muted">
                        Existencias: ${Number(producto.stock)}
                    </small>

                </div>

            `;

            contenedor.appendChild(columna);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Selección de productos y cantidades
    |--------------------------------------------------------------------------
    */

    document.addEventListener('change', evento => {

        if (evento.target.classList.contains('producto-seleccion')) {

            const seleccion = evento.target;

            const cantidad = seleccion
                .closest('.kit-producto')
                .querySelector('.cantidad-producto');

            cantidad.disabled = !seleccion.checked;

            if (seleccion.checked) {

                const stock = obtenerStock(
                    Number(seleccion.dataset.id),
                    seleccion.dataset.grupo
                );

                cantidad.max = Math.min(stock, 99);

                if (Number(cantidad.value) > stock) {
                    cantidad.value = Math.min(stock, 99);
                }

            }

            actualizarResumen();

        }

        if (evento.target.classList.contains('cantidad-producto')) {

            validarCantidad(evento.target);

            actualizarResumen();

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Obtener stock del producto
    |--------------------------------------------------------------------------
    */

    function obtenerStock(id, grupo) {

        const producto = productos[grupo].find(p =>
            Number(p.id_producto) === id
        );

        return producto ? Number(producto.stock) : 0;

    }

    /*
    |--------------------------------------------------------------------------
    | Validar cantidades
    |--------------------------------------------------------------------------
    */

    function validarCantidad(input) {

        const minimo = Number(input.min);
        const maximo = Number(input.max);

        let cantidad = parseInt(input.value, 10);

        if (isNaN(cantidad)) {
            cantidad = minimo;
        }

        cantidad = Math.max(minimo, Math.min(maximo, cantidad));

        input.value = cantidad;

    }

    /*
    |--------------------------------------------------------------------------
    | Obtener selección actual
    |--------------------------------------------------------------------------
    */

    function obtenerSeleccionados() {

        const seleccionados = [];

        document.querySelectorAll('.producto-seleccion:checked').forEach(input => {

            const tarjeta = input.closest('.kit-producto');

            const cantidadInput = tarjeta.querySelector('.cantidad-producto');

            const cantidad = Number(cantidadInput.value);

            const precio = Number(input.dataset.precio);

            seleccionados.push({

                id: Number(input.dataset.id),

                nombre: tarjeta.querySelector('label').textContent.trim(),

                cantidad: cantidad,

                precio: precio,

                subtotal: precio * cantidad

            });

        });

        return seleccionados;

    }

    /*
    |--------------------------------------------------------------------------
    | Calcular total y actualizar resumen
    |--------------------------------------------------------------------------
    */

    function actualizarResumen() {

        const seleccionados = obtenerSeleccionados();

        resumen.innerHTML = '';

        let total = 0;

        if (seleccionados.length === 0) {

            resumen.innerHTML =
                '<p class="text-muted">Todavía no has seleccionado productos.</p>';

        }

        seleccionados.forEach(producto => {

            total += producto.subtotal;

            const fila = document.createElement('div');

            fila.className = 'kit-resumen-producto';

            fila.innerHTML = `

                <div>

                    <span>${escaparHTML(producto.nombre)}</span>

                    <small>
                        ${producto.cantidad} × ${formatoPrecio(producto.precio)}
                    </small>

                </div>

                <span>
                    ${formatoPrecio(producto.subtotal)}
                </span>

            `;

            resumen.appendChild(fila);

        });

        totalKit.textContent = formatoPrecio(total);

        actualizarCarrito(seleccionados);

    }

    /*
    |--------------------------------------------------------------------------
    | Preparar productos para el carrito
    |--------------------------------------------------------------------------
    */

    function actualizarCarrito(seleccionados) {

        productosCarrito.innerHTML = '';

        seleccionados.forEach(producto => {

            const input = document.createElement('input');

            input.type = 'hidden';

            input.name = `items[${producto.id}]`;

            input.value = producto.cantidad;

            productosCarrito.appendChild(input);

        });

        const tieneAceite = seleccionados.some(producto => {

            return productos.aceites.some(aceite =>
                Number(aceite.id_producto) === producto.id
            );

        });

        btnAgregar.disabled = !tieneAceite;

    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar cotizador
    |--------------------------------------------------------------------------
    */

    function limpiarProductos() {

        productos = {
            aceites: [],
            filtros: [],
            repuestos: []
        };

        viscosidad.innerHTML =
            '<option value="">Primero selecciona tu vehículo</option>';

        viscosidad.disabled = true;

        contenedorAceites.innerHTML =
            '<p class="text-muted">Selecciona un vehículo para consultar los aceites.</p>';

        contenedorFiltros.innerHTML =
            '<p class="text-muted">Primero selecciona tu vehículo.</p>';

        contenedorRepuestos.innerHTML =
            '<p class="text-muted">Primero selecciona tu vehículo.</p>';

        actualizarResumen();

    }

});
