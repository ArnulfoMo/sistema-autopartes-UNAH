//bloque de codigo con la funcionalidad total de los modales que se encuentran en la parte de inventario.php

function abrirModal(id) {

    const elemento = document.getElementById(id);

    const modal = bootstrap.Modal.getOrCreateInstance(elemento);

    modal.show();
}


function cerrarModal(id) {

    const elemento = document.getElementById(id);

    const modal = bootstrap.Modal.getInstance(elemento);

    if (modal) {
        modal.hide();
    }
}

async function buscarProductos(termino, contenedor, tipo) {

    contenedor.innerHTML = "";

    if (termino.trim() === "") {
        return;
    }

    try {

        const respuesta = await fetch(
            "procesar_producto.php?accion=buscar&termino=" +
            encodeURIComponent(termino)
        );

        if (!respuesta.ok) {
            throw new Error("Error en la solicitud.");
        }

        const productos = await respuesta.json();

        if (!Array.isArray(productos) || productos.length === 0) {

            const mensaje = document.createElement("p");

            mensaje.textContent = "No se encontraron productos.";

            contenedor.appendChild(mensaje);

            return;
        }

        productos.forEach(producto => {

            const resultado = document.createElement("div");

            const texto = document.createElement("p");

            texto.textContent =
                "ID: " + producto.id_producto +
                " - " + producto.nombre;

            const boton = document.createElement("button");

            boton.type = "button";
            boton.textContent = "Seleccionar";
            boton.className = "btn btn-primary btn-sm";

            boton.addEventListener("click", () => {

                if (tipo === "actualizar") {
                    cargarProducto(producto);
                }

                if (tipo === "eliminar") {
                    seleccionarEliminar(producto);
                }

            });

            resultado.appendChild(texto);
            resultado.appendChild(boton);

            contenedor.appendChild(resultado);

        });

    } catch (error) {

        console.error("Error al buscar productos:", error);

        contenedor.textContent = "Error al consultar los productos.";
    }
}

function cargarProducto(producto) {

    document.getElementById("editarId").value =
        producto.id_producto;

    document.getElementById("editarNombre").value =
        producto.nombre;

    document.getElementById("editarPrecio").value =
        producto.precio;

    document.getElementById("resultadosActualizar").textContent =
        "Producto seleccionado: " + producto.nombre;
}

function seleccionarEliminar(producto) {

    document.getElementById("eliminarId").value =
        producto.id_producto;

    document.getElementById("nombreEliminar").textContent =
        "Producto seleccionado: " + producto.nombre;

    document.getElementById("resultadosEliminar").textContent =
        "Producto seleccionado correctamente.";
}

document.addEventListener("DOMContentLoaded", () => {

    const buscarActualizar = document.getElementById("buscarActualizar");
    const resultadosActualizar = document.getElementById("resultadosActualizar");

    const buscarEliminar = document.getElementById("buscarEliminar");
    const resultadosEliminar = document.getElementById("resultadosEliminar");

    if (buscarActualizar) {
        buscarActualizar.addEventListener("input", () => {
            buscarProductos(buscarActualizar.value, resultadosActualizar, "actualizar");
        });
    }

    if (buscarEliminar) {
        buscarEliminar.addEventListener("input", () => {
            buscarProductos(buscarEliminar.value, resultadosEliminar, "eliminar");
        });
    }

    const formularioActualizar = document.querySelector("#modalActualizar form");

    if (formularioActualizar) {
        formularioActualizar.addEventListener("submit", function(event) {

            if (document.getElementById("editarId").value === "") {
                event.preventDefault();
                alert("Primero debes buscar y seleccionar un producto.");
            }

        });
    }

    const formularioEliminar = document.querySelector("#modalEliminar form");

    if (formularioEliminar) {
        formularioEliminar.addEventListener("submit", function(event) {

            if (document.getElementById("eliminarId").value === "") {
                event.preventDefault();
                alert("Primero debes seleccionar un producto.");
            }

        });
    }

});