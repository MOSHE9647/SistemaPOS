// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { obtenerListaImpuestos } from "../impuesto/crud.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { checkEmptyTable } from "../../utils.js";
import * as crud from "./crud.js";

// Variables globales
let productos = [];

/**
 * Renderiza la tabla de productos con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada producto en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para eliminar el producto.
 * 
 * @param {Array} listaProductos - El arreglo de productos a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
export function renderTable(listaProductos) {
    productos = listaProductos;

    // Obtener el cuerpo de la tabla
    const tableBodyID = 'table-sales-body';
    const tableBody = document.getElementById(tableBodyID);
    tableBody.innerHTML = '';

    // Recorrer cada producto en el arreglo
    productos.forEach(data => {
        // Obtener la cantidad y el producto
        const cantidad = data.cantidad || 1;
        const producto = data.producto;

        // Crear el nombre del producto y el subtotal
        const nombreProducto = (producto.Nombre || '') + ' ' + (producto.Presentacion?.Nombre || '') + ', ' + (producto.Marca?.Nombre || '');
        const imagenURL = window.baseURL + producto.Imagen;
        const subtotal = producto.PrecioCompra * cantidad;
        const impuesto = subtotal * obtenerValorImpuesto();

        // Crear una fila para el producto
        const row = document.createElement('tr');
        row.dataset.id = producto.ID;

        // Agregar los datos del producto a la fila
        row.innerHTML = `
            <td data-field="codigobarras">${producto.CodigoBarras.Numero}</td>
            <td data-field="imagen"><img src="${imagenURL}" alt="${producto.Nombre}" style="width: 50px; height: 50px;"></td>
            <td data-field="nombre">${nombreProducto}</td>
            <td data-field="preciounitario">&#162;${(producto.PrecioCompra).toFixed(2)}</td>
            <td data-field="cantidad"><input type="number" class="cantidad" value="${cantidad}" min="0" style="width: 80px;"></td>
            <td data-field="subtotal">&#162;${subtotal.toFixed(2)}</td>
            <td data-field="impuesto">&#162;${impuesto.toFixed(2)}</td>
        `;

        const actionsCell = document.createElement('td');
        actionsCell.classList.add('actions');
        actionsCell.innerHTML = `
            <button class="btn-delete las la-trash"></button>
        `;
        row.appendChild(actionsCell);

        // Agregar el evento de eliminación al botón
        row.querySelector('.btn-delete').addEventListener('click', () => {
            crud.deleteProducto(producto.ID, productos);
            renderTable(productos);
        });

        // Agregar el evento de cambio de cantidad al input
        row.querySelector('.cantidad').addEventListener('change', () => {
            const cantidad = parseInt(row.querySelector('.cantidad').value, 10);
            if (cantidad < 1) {
                mostrarMensaje('La cantidad no puede ser menor a 1.', 'error', 'Error de cantidad');
                row.querySelector('.cantidad').value = 1;
            } else {
                data.cantidad = cantidad ? cantidad : 1;
                renderTable(productos);
            }
        });
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.appendChild(row);
    });

    // Verificar si la tabla está vacía
    checkEmptyTable(tableBodyID, 'las la-box');

    // Actualizar el subtotal
    const subtotal = document.getElementById('sales-subtotal');
    if (subtotal) subtotal.innerHTML = `&#162;${getSubtotal()}`;

    // Actualizar el impuesto
    const impuesto = document.getElementById('sales-impuesto');
    if (impuesto) impuesto.innerHTML = `&#162;${getImpuesto()}`;

    // Actualizar el total
    const total = document.getElementById('sales-total');
    if (total) total.innerHTML = `&#162;${getTotal()}`;

    const barcodeInput = document.getElementById('sales-search-input');
    if (barcodeInput) barcodeInput.focus();
}

export function agregarProducto(codigoBarras) {
    let cantidad = 1;
    let codigo = codigoBarras;

    // Verificar si 'codigoBarras' contiene un signo de multiplicación
    const multiplicacionRegex = /(\d+)\s*[*x]\s*(\d+)/i;
    const match = codigoBarras.match(multiplicacionRegex);

    if (match) {
        cantidad = parseInt(match[1], 10);
        codigo = match[2];
    }

    crud.obtenerProductoPorCodigoBarras(codigo).then(producto => {
        // Verificar si el producto ya existe en la lista
        const existingProductIndex = productos.findIndex(p => p.producto.ID === producto.ID);
        if (existingProductIndex !== -1) {
            // Si el producto ya existe, actualizar la cantidad
            productos[existingProductIndex].cantidad += cantidad;
        } else {
            // Si el producto no existe, agregarlo a la lista
            productos.push({ producto, cantidad });
        }
        renderTable(productos);
    })
    .catch(error => {
        mostrarMensaje(error.message, 'error', 'Error de búsqueda');
    });
}

export function obtenerValorImpuesto() {
    try {
        const impuestos = obtenerListaImpuestos();
        const impuesto = impuestos ? impuestos.find(i => i.Nombre === 'IVA') : null;
        return impuesto ? impuesto.Valor / 100 : 0;
    } catch (error) {
        mostrarMensaje(error.message, 'error', 'Error de impuesto');
        return 0;
    }
}

export function getSubtotal() {
    let total = 0.00;
    productos.forEach(p => {
        total += p.producto.PrecioCompra * p.cantidad;
    });
    return total.toFixed(2);
}

export function getImpuesto() {
    return (getSubtotal() * obtenerValorImpuesto()).toFixed(2);
}

export function getTotal() {
    return (parseFloat(getSubtotal()) + parseFloat(getImpuesto())).toFixed(2);
}

export function clearTable() {
    productos = [];
    renderTable(productos);
}