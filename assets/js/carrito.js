(function () {
    'use strict';

    var RTN_RE = /^\d{14}$/;
    var TEL_RE = /^[2389]\d{7}$/;

    function soloDigitos(v) { return (v || '').replace(/\D+/g, ''); }

    function dinero(n) {
        return 'L ' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function normalizarTel(v) {
        var d = soloDigitos(v);
        if (d.length === 11 && d.indexOf('504') === 0) { d = d.slice(3); }
        return d;
    }

    function avisar(texto, tipo) {
        var t = document.createElement('div');
        t.className = 'toast-carrito alert alert-' + (tipo || 'success') + ' shadow';
        t.setAttribute('role', 'status');
        t.textContent = texto;
        document.body.appendChild(t);
        setTimeout(function () { t.remove(); }, 2800);
    }

    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest('[data-agregar-carrito]');
        if (!btn) { return; }
        ev.preventDefault();

        var datos = new FormData();
        datos.append('accion', 'agregar');
        datos.append('id_producto', btn.getAttribute('data-id'));
        datos.append('cantidad', btn.getAttribute('data-cantidad') || '1');

        btn.disabled = true;
        fetch('carrito.php', {
            method: 'POST',
            body: datos,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                avisar(r.mensaje, r.ok ? 'success' : 'danger');
                document.querySelectorAll('.js-carrito-contador').forEach(function (el) {
                    el.textContent = r.unidades;
                });
            })
            .catch(function () { avisar('No se pudo agregar al carrito.', 'danger'); })
            .then(function () { btn.disabled = false; });
    });

    /* ---------- Página del carrito: enviar al cambiar la cantidad ---------- */
    document.querySelectorAll('.form-cantidad .input-cantidad').forEach(function (input) {
        var temporizador;
        input.addEventListener('change', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(function () { input.form.submit(); }, 350);
        });
    });

    /* ---------- Checkout: total en vivo según el flete ---------- */
    var resumen = document.getElementById('resumenCheckout');
    var formCheckout = document.getElementById('formCheckout');

    if (resumen && formCheckout) {
        var subtotal = parseFloat(resumen.dataset.subtotal);
        var descuento = parseFloat(resumen.dataset.descuento);
        var tasa = parseFloat(resumen.dataset.tasaIsv);

        var aviso = document.getElementById('avisoDireccion');

        var recalcular = function () {
            var radio = formCheckout.querySelector('input[name="modalidad_flete"]:checked');
            var flete = radio ? parseFloat(radio.dataset.monto) : 0;

            // Mismo cálculo que Carrito::totales (en centavos, redondeando)
            var subC = Math.round(subtotal * 100);
            var base = subC - Math.round(descuento * 100);
            var isv = Math.round(base * tasa / 100);
            var fleteC = Math.round(flete * 100);
            var total = base + isv + fleteC;

            document.getElementById('resIsv').textContent = dinero(isv / 100);
            document.getElementById('resFlete').textContent = dinero(fleteC / 100);
            document.getElementById('resTotal').textContent = dinero(total / 100);

            if (aviso) {
                aviso.textContent = (radio && radio.value !== 'mostrador')
                    ? '(obligatoria para el envío)' : '(solo si pides envío)';
            }
        };

        formCheckout.querySelectorAll('input[name="modalidad_flete"]').forEach(function (r) {
            r.addEventListener('change', recalcular);
        });
        recalcular();
    }

    /* ---------- Validación en el navegador (el servidor valida igual) ---------- */
    function marcar(input, valido) {
        input.classList.toggle('is-invalid', !valido);
    }

    function validarCampos(form) {
        var ok = true;
        var rtn = form.querySelector('#rtn');
        var tel = form.querySelector('#telefono');
        var nombre = form.querySelector('#nombre');
        var sucursal = form.querySelector('#id_sucursal');

        if (rtn) { var vr = RTN_RE.test(soloDigitos(rtn.value)); marcar(rtn, vr); ok = ok && vr; }
        if (tel) { var vt = TEL_RE.test(normalizarTel(tel.value)); marcar(tel, vt); ok = ok && vt; }
        if (nombre) { var vn = nombre.value.trim().length >= 3; marcar(nombre, vn); ok = ok && vn; }
        if (sucursal) { var vs = sucursal.value !== ''; marcar(sucursal, vs); ok = ok && vs; }
        return ok;
    }

    [formCheckout, document.getElementById('formHistorial')].forEach(function (form) {
        if (!form) { return; }
        form.addEventListener('submit', function (ev) {
            if (!validarCampos(form)) {
                ev.preventDefault();
                var primero = form.querySelector('.is-invalid');
                if (primero) { primero.focus(); }
            }
        });
    });

    // RTN y teléfono: solo números y guiones/espacios/+
    document.querySelectorAll('#rtn').forEach(function (el) {
        el.addEventListener('input', function () { el.value = el.value.replace(/[^\d-]/g, ''); });
    });
    document.querySelectorAll('#telefono').forEach(function (el) {
        el.addEventListener('input', function () { el.value = el.value.replace(/[^\d+\s-]/g, ''); });
    });

    /* ---------- Factura: imprimir ---------- */
    var btnImprimir = document.getElementById('btnImprimir');
    if (btnImprimir) {
        btnImprimir.addEventListener('click', function () { window.print(); });
    }
})();
