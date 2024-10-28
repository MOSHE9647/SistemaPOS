// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

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
    let tableBodyID = 'table-sales-body';
    let tableBody = document.getElementById(tableBodyID);
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada producto en el arreglo
    productos.forEach(producto => {
        const nombreProducto = (producto.Nombre || '') + (producto.Presentacion?.Nombre || '') + ', ' + (producto.Marca?.Nombre || '');

        // Crear una fila para el producto
        let row = `
            <tr data-id="${producto.ID}">
                <td data-field="codigobarras">${producto.CodigoBarras.Numero}</td>
                <td data-field="imagen">
                    <img src="${window.baseURL}${producto.Imagen}" alt="${producto.Nombre}" style="width: 50px; height: 50px;">
                </td>
                <td data-field="nombre">${nombreProducto}</td>
                <td data-field="preciounitario">${producto.PrecioCompra}</td>
                <td data-field="cantidad">
                    <input type="number" class="cantidad" value="${producto.Cantidad}" style="width: 80px;">
                </td>
                <td data-field="subtotal">${producto.Subtotal}</td>
                <td class="actions">
                    <button class="btn-delete las la-trash" onclick="deleteProducto(${producto.ID})"></button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    checkEmptyTable(tableBodyID, 'las la-box');
}

