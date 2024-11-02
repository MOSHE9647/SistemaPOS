// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { checkEmptyTable, manejarInputNumeroTelefono } from "../../utils.js";
import { hideLoader, showLoader } from "../../gui/loader.js";
import { obtenerListaImpuestos } from "../impuesto/crud.js";
import { obtenerListaProductos } from "../producto/crud.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { initializeSelects } from "../telefono/selects.js";
import { obtenerListaClientes } from "../cliente/crud.js";
import * as crud from "./crud.js";

// Variables globales
let productos = { [tab1]: [] };

function getActiveTable() {
    // Obtener la tabla activa
    const activeTable = document.querySelector('.tab-content.active');
    if (!activeTable) {
        mostrarMensaje('No se encontró la tabla activa.', 'error', 'Error de tabla');
        return null;
    }
    return activeTable;
}

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
    const activeTable = getActiveTable();
    if (!activeTable) return;

    // Obtener el ID de la tabla activa y guardar sus respectivos productos
    const activeTableID = activeTable.id;
    productos[activeTableID] = listaProductos;

    // Obtener el cuerpo de la tabla
    const tableBodyID = 'table-sales-body';
    const tableBody = activeTable.querySelector(`#${tableBodyID}`);
    tableBody.innerHTML = '';

    // Recorrer cada producto en el arreglo
    listaProductos.forEach(data => {
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
            <td data-field="existencia">${producto.Cantidad}</
        `;

        const actionsCell = document.createElement('td');
        actionsCell.classList.add('actions');
        actionsCell.innerHTML = `
            <button class="btn-delete las la-trash"></button>
        `;
        row.appendChild(actionsCell);

        // Agregar el evento de eliminación al botón
        row.querySelector('.btn-delete').addEventListener('click', () => {
            crud.deleteProducto(producto.ID, productos[activeTableID]);
            renderTable(productos[activeTableID]);
        });

        // Agregar el evento de cambio de cantidad al input
        row.querySelector('.cantidad').addEventListener('change', () => {
            const cantidad = parseInt(row.querySelector('.cantidad').value, 10);
            if (cantidad < 1) {
                mostrarMensaje('La cantidad no puede ser menor a 1.', 'error', 'Error de cantidad');
                row.querySelector('.cantidad').value = 1;
            } else if (cantidad > producto.Cantidad) {
                mostrarMensaje('La cantidad del producto no puede ser mayor a la existencia.', 'error', 'Error de cantidad');
                row.querySelector('.cantidad').value = producto.Cantidad;
            } else {
                data.cantidad = cantidad ? cantidad : 1;
                renderTable(productos[activeTableID]);
            }
        });
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.appendChild(row);
    });

    // Verificar si la tabla está vacía
    checkEmptyTable(tableBody, 'las la-box', true);

    // Actualizar el subtotal
    const subtotal = activeTable.querySelector('#sales-subtotal');
    if (subtotal) subtotal.innerHTML = `&#162;${getSubtotal(activeTableID)}`;

    // Actualizar el impuesto
    const impuesto = activeTable.querySelector('#sales-impuesto');
    if (impuesto) impuesto.innerHTML = `&#162;${getImpuesto(activeTableID)}`;

    // Actualizar el total
    const total = activeTable.querySelector('#sales-total');
    if (total) total.innerHTML = `&#162;${getTotal(activeTableID)}`;

    // const barcodeInput = document.getElementById('sales-search-input');
    // if (barcodeInput) barcodeInput.focus();

    // Obtener el último input de cantidad y darle foco
    const lastInput = obtenerUltimoInputCantidad();
    if (lastInput) lastInput.focus();
}

export function agregarProducto(codigoBarras) {
    showLoader();

    let cantidad = 1;
    let codigo = codigoBarras;

    // Verificar si 'codigoBarras' contiene un signo de multiplicación
    const multiplicacionRegex = /(\d+)\s*[*x]\s*(\d+)/i;
    const match = codigoBarras.match(multiplicacionRegex);

    if (match) {
        cantidad = parseInt(match[1], 10);
        codigo = match[2];
    }

    // Obtener la tabla activa
    const activeTable = getActiveTable();
    if (!activeTable) {
        mostrarMensaje('No se encontró la tabla activa.', 'error', 'Error de tabla');
        hideLoader();
        return;
    }

    const activeTableID = activeTable.id;
    // Inicializar la lista de productos si no existe
    if (!productos[activeTableID]) {
        productos[activeTableID] = [];
    }

    crud.obtenerProductoPorCodigoBarras(codigo).then(producto => {
        // Verificar si el producto ya existe en la lista
        agregarOActualizarProductoEnLista(activeTableID, producto, cantidad);
        renderTable(productos[activeTableID]);
    })
    .catch(error => {
        mostrarMensaje(error.message, 'error', 'Error de búsqueda');
    })
    .finally(() => {
        hideLoader();
    });
}

export async function mostrarListaSeleccionableDeProductos() {
    try {
        const products = await obtenerListaProductos();
        if (!products) throw new Error('No se encontraron productos');

        if (!Array.isArray(products)) {
            throw new Error('La lista de productos no es un arreglo.');
        }

        let html = `<div class="sales-product-select">`;
        products.forEach(producto => {
            const imagenURL = window.baseURL + producto.Imagen;
            html += `
                <div class="product-card">
                    <input type="radio" name="product-radio" class="product-radio" data-id="${producto.ID}">
                    <div class="card-header">
                        <div class="product-img" style="background-image: url(${imagenURL});"></div>
                    </div>
                    <div class="card-body">
                        <div class="card-title">
                            <h2>${producto.Nombre}</h2>
                            <small>Código: <span>${producto.CodigoBarras.Numero}</span>, </small>
                            <small>Marca: <span>${producto.Marca?.Nombre}</span></small>
                        </div>
                        <p>${producto.Descripcion || 'El producto no tiene descripci&oacute;n'}</p>
                        <h3>Precio: <span>&#162;${producto.PrecioCompra.toFixed(2)}</span></h3>
                    </div>
                </div>
            `;
        });
        html += `</div>`;

        Swal.fire({
            title: "Seleccione un producto",
            html: html,
            showCancelButton: true,
            confirmButtonText: "Seleccionar",
            cancelButtonText: "Cancelar",
            customClass: {
                popup: 'modal-container',
                header: 'modal-header',
                title: 'modal-title',
                htmlContainer: 'modal-body',
                cancelButton: 'modal-close',
                confirmButton: 'modal-confirm',
                actions: 'modal-actions',
            },
            preConfirm: async () => {
                const product = getSelectedProduct(products);
                if (!product) {
                    mostrarMensaje('Seleccione un producto para agregar a la lista.', 'error', 'Error de selección');
                    return false;
                }
                return product;
            }
        }).then(result => {
            if (result.isConfirmed) {
                // Obtener la tabla activa
                const activeTable = getActiveTable();
                if (!activeTable) throw new Error('No se encontró la tabla activa.');

                // Inicializar la lista de productos si no existe
                const activeTableID = activeTable.id;
                if (!productos[activeTableID]) {
                    productos[activeTableID] = [];
                }

                agregarOActualizarProductoEnLista(activeTableID, result.value, 1);
                renderTable(productos[activeTableID]);
            }
        }).catch(error => {
            mostrarMensaje(error.message, 'error', 'Error al seleccionar producto');
        });

        addSelectFunctionToProduct();
    } catch (error) {
        mostrarMensaje(error.message, 'error', 'Error al listar productos');
    }
}

export function mostrarOpcionesDeCobro() {
    // Obtenemos la tabla activa
    const activeTable = getActiveTable();
    if (!activeTable) {
        mostrarMensaje('No se encontró la tabla activa.', 'error', 'Error de tabla');
        return;
    }

    if (!productos[activeTable.id]) {
        mostrarMensaje('Debe seleccionar, al menos, un producto para cobrar.', 'warning');
        return;
    }

    // Obtenemos el ID de la tabla y los datos de la venta
    // const activeTableID = activeTable.id;
    // const listaProductos = productos[activeTableID];
    // const total = getTotal(activeTableID);
    // const usuario = usuarioActual;
    // console.log(listaProductos, total, usuario);

    let html = `
        <div class="modal-form-container">
            <h2>Informaci&oacute;n del Cliente</h2>
            <div class="cliente-info">
                <div class="cliente-info info">
                    <div class="cliente-info input-select basic">
                        <label>Cliente atendido (*):</label>
                        <div class="select">
                            <select id="cliente-select">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <button type="button" id="cliente-add-button">
                                <span class="las la-plus"></span>
                                <span>Crear Nuevo</span>
                            </button>
                        </div>
                        <form id="cliente-form" class="cliente-form" style="display: none;">
                            <div class="cliente-form-group">
                                <div class="cliente-info input-select form">
                                    <label for="cliente-nombre">Nombre:</label>
                                    <input type="text" id="cliente-nombre" name="nombre">
                                </div>
                                <div class="cliente-info input-select form">
                                    <label for="cliente-alias">Alias:</label>
                                    <input type="text" id="cliente-alias" name="alias">
                                </div>
                            </div>
                            <div class="cliente-form-group">
                                <div class="cliente-info input-select form">
                                    <label>Tipo de Tel&eacute;fono (*):</label>
                                    <select id="tipo-select" required>
                                        <option value="">--Seleccionar--</option>
                                    </select>
                                </div>
                                <div class="cliente-info input-select form">
                                    <label>Código de Pa&iacute;s (*):</label>
                                    <select id="codigo-select" required>
                                        <option value="">--Seleccionar--</option>
                                    </select>
                                </div>
                                <div class="cliente-info input-select form">
                                    <label for="numero">Número de Tel&eacute;fono (*):</label>
                                    <input type="text" id="numero" name="numero" required>
                                </div>
                            </div>
                            <div class="cliente-form-group form-buttons">
                                <button type="submit" class="modal-confirm" id="cliente-save-button">Guardar</button>
                                <button type="button" class="modal-close" id="cliente-cancel-button">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;
    Swal.fire({
        title: "Venta de Productos: Cobrar",
        html: html,
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: '<i class="las la-print"></i> <span>Cobrar e imprimir ticket</span>',
        denyButtonText: '<i class="las la-check"></i> <span>Cobrar sin imprimir ticket</span>',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'modal-container modal-cobro',
            header: 'modal-header',
            title: 'modal-title',
            htmlContainer: 'modal-body',
            denyButton: 'modal-confirm',
            cancelButton: 'modal-close',
            confirmButton: 'modal-confirm',
            actions: 'modal-actions',
        },
        preConfirm: () => {
            mostrarMensaje('Venta realizada e impresa.', 'success', 'Venta realizada');
        },
        preDeny: () => {
            mostrarMensaje('Venta realizada sin imprimir ticket.', 'success', 'Venta realizada');
        }
    });

    // Inicializar el select de clientes
    initializeSelectCliente();

    // Agregar evento al botón de agregar cliente
    const addClienteButton = document.getElementById('cliente-add-button');
    if (addClienteButton) {
        addClienteButton.addEventListener('click', () => {
            const clienteForm = document.getElementById('cliente-form');
            if (clienteForm) clienteForm.style.display = 'block';

            const clienteSelect = document.getElementById('cliente-select');
            if (clienteSelect) clienteSelect.disabled = true;
            
            initializeClienteForm();
        });
    }
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

export function getSubtotal(tabId) {
    let total = 0.00;
    productos[tabId].forEach(p => {
        total += p.producto.PrecioCompra * p.cantidad;
    });
    return total.toFixed(2);
}

export function getImpuesto(tabId) {
    return (getSubtotal(tabId) * obtenerValorImpuesto()).toFixed(2);
}

export function getTotal(tabId) {
    return (parseFloat(getSubtotal(tabId)) + parseFloat(getImpuesto(tabId))).toFixed(2);
}

export function clearTable() {
    const activeTable = getActiveTable();
    if (!activeTable) return;

    const activeTableID = activeTable.id;
    productos[activeTableID] = [];
    renderTable(productos[activeTableID]);
}

// Darle funcionalidad de click al producto seleccionado
function addSelectFunctionToProduct() {
    document.body.addEventListener('click', event => {
        const productCard = event.target.closest('.product-card');
        if (productCard) {
            document.querySelectorAll('.product-card').forEach(card => card.classList.remove('selected'));
            productCard.classList.add('selected');
            productCard.querySelector('.product-radio').checked = true;
        }
    });
}

function getSelectedProduct(productos) {
    const selectedProduct = document.querySelector('.product-card.selected');
    if (!selectedProduct) return null;

    const radio = selectedProduct.querySelector('.product-radio');
    if (!radio) return null;

    const productID = radio.dataset.id;
    const producto = productos.find(producto => producto.ID === parseInt(productID, 10));

    if (!producto) return null;
    if (producto.Cantidad < 1) {
        mostrarMensaje('El producto seleccionado no tiene existencias.', 'error', 'Error de existencia');
        return null;
    }

    return producto;
}

function agregarOActualizarProductoEnLista(tablaID, producto, cantidad) {
    // Verificar que la cantidad ingresada no supere la existencia
    if (cantidad > producto.Cantidad) {
        mostrarMensaje('La cantidad del producto no puede ser mayor a la existencia.', 'error', 'Error de cantidad');
        return;
    }

    // Verificar si el producto ya existe en la lista
    const existingProductIndex = productos[tablaID].findIndex(p => p.producto.ID === producto.ID);
    if (existingProductIndex !== -1) {
        // Si el producto ya existe, actualizar la cantidad
        productos[tablaID][existingProductIndex].cantidad += cantidad;
    } else {
        // Si el producto no existe, agregarlo a la lista
        productos[tablaID].push({ producto, cantidad });
    }
}

function obtenerUltimoInputCantidad() {
    const activeTable = getActiveTable();
    if (!activeTable) return null;

    const tableBody = activeTable.querySelector('#table-sales-body');
    if (!tableBody) return null;

    const cantidadInputs = tableBody.querySelectorAll('.cantidad');
    return cantidadInputs[cantidadInputs.length - 1];
}

function initializeSelectCliente(selectedID = -1) {
    try {
        // Obtener la lista de clientes
        const clientes = obtenerListaClientes();
        const clienteSelect = document.getElementById('cliente-select');
        if (!clienteSelect) throw new Error('No se encontró el select de clientes.');

        // Limpiar el select de clientes
        clienteSelect.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.text = '-- Seleccionar --';
        clienteSelect.add(option);

        // Llenar el select de clientes
        clientes.forEach(cliente => {
            // Crear el texto del Select
            const clienteTelefono = cliente.Telefono ? ` (${cliente.Telefono.CodigoPais} ${cliente.Telefono.Numero})` : '';
            const selectText = cliente.Nombre + ' - ' + cliente.Alias + clienteTelefono;

            // Crear una opción para el select
            const option = document.createElement('option');
            option.value = cliente.ID;
            option.text = selectText;
            option.selected = cliente.ID === selectedID;
            clienteSelect.add(option);
        });
    } catch (error) {
        console.error(error);
        mostrarMensaje(`Error al cargar la lista de clientes: ${error.message}`, 'error');
    }
}

function initializeClienteForm() {
    // Obtener el formulario de cliente
    const clienteForm = document.getElementById('cliente-form');
    if (!clienteForm) throw new Error('No se encontró el formulario de cliente.');

    if (clienteForm.style.display !== 'none') {
        initializeSelects(); // Inicializar los selects de tipo y código de país

        // Evitar que el formulario se envíe al presionar Enter
        if (clienteForm) {
            clienteForm.addEventListener('submit', event => {
                event.preventDefault();
            });
        }

        // Agregar evento al botón de cancelar cliente
        const cancelClienteButton = document.getElementById('cliente-cancel-button');
        if (cancelClienteButton) {
            cancelClienteButton.addEventListener('click', () => {
                const clienteForm = document.getElementById('cliente-form');
                if (clienteForm) clienteForm.style.display = 'none';
                
                const clienteSelect = document.getElementById('cliente-select');
                if (clienteSelect) clienteSelect.disabled = false;
            });
        }

        // Formatear el número de teléfono ingresado
        const numeroInput = document.getElementById('numero');
        if (numeroInput) numeroInput.addEventListener('input', manejarInputNumeroTelefono);

        // Dar formato al número al cambiar el país
        const codigoSelect = document.getElementById('codigo-select');
        if (codigoSelect) codigoSelect.addEventListener('change', manejarInputNumeroTelefono);

        // Agregar evento al botón de guardar cliente
        const saveClienteButton = document.getElementById('cliente-save-button');
        if (saveClienteButton) {
            saveClienteButton.addEventListener('click', () => {
                try {
                    const clienteForm = document.getElementById('cliente-form');
                    if (!clienteForm) return;

                    if (!clienteForm.checkValidity()) {
                        clienteForm.reportValidity();
                        return;
                    }

                    // Obtener datos del formulario si es válido
                    const nombre = document.getElementById('cliente-nombre').value || 'No Definido';
                    const alias = document.getElementById('cliente-alias').value || 'No Definido';
                    const tipo = document.getElementById('tipo-select').value;

                    // Crear el objeto FormData
                    const formData = new FormData();
                    formData.append('accion', 'insertar');
                    formData.append('nombre', nombre);
                    formData.append('alias', alias);
                    formData.append('tipo', tipo);
                    formData.append('codigo', codigoSelect.value);
                    formData.append('numero', numeroInput.value);

                    // Crear el cliente
                    crud.insertCliente(formData).then((clienteID) => {
                        initializeSelectCliente(clienteID);

                        // Limpiar los campos del formulario y ocultarlo
                        clienteForm.reset();
                        clienteForm.style.display = 'none';

                        // Habilitar el select de clientes
                        const clienteSelect = document.getElementById('cliente-select');
                        if (clienteSelect) clienteSelect.disabled = false;
                    }).catch(error => { throw error; });
                } catch (error) {
                    console.error(error);
                    mostrarMensaje(`Error al guardar el cliente: ${error.message}`, 'error');
                }
            });
        }
    }
}