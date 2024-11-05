// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { checkEmptyTable, getCurrentDate, manejarInputNumeroTelefono } from "../../utils.js";
import { hideLoader, showLoader } from "../../gui/loader.js";
import { obtenerListaImpuestos } from "../impuesto/crud.js";
import { obtenerListaProductos } from "../producto/crud.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { initializeSelects } from "../telefono/selects.js";
import { obtenerListaClientes } from "../cliente/crud.js";
import * as crud from "./crud.js";

// Variables globales
var productos = { [tab1]: [] };
var listaClientes = [];
var totales = { 
    [tab1]: { 
        moneda: 'CRC',
        tipoCambio: 0.00,
        subtotal: 0.00, 
        impuesto: 0.00, 
        total: 0.00 
    } 
};

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
    totales[activeTableID] = { subtotal: 0.00, impuesto: 0.00, total: 0.00 };

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

    // Actualizar el subtotal, impuesto y total
    totales[activeTableID]['subtotal'] = getSubtotal(activeTableID);
    totales[activeTableID]['impuesto'] = getImpuesto(activeTableID);
    totales[activeTableID]['total'] = getTotal(activeTableID);
    totales[activeTableID]['tipoCambio'] = 0.00;
    totales[activeTableID]['moneda'] = 'CRC';

    ['subtotal', 'impuesto', 'total'].forEach(field => {
        const span = activeTable.querySelector(`#sales-${field}`);
        if (span) span.innerHTML = `&#162;${totales[activeTableID][field]}`;
    });

    // const barcodeInput = document.getElementById('sales-search-input');
    // if (barcodeInput) barcodeInput.focus();

    // Obtener el último input de cantidad y darle foco
    const lastInput = obtenerUltimoInputCantidad();
    if (lastInput) lastInput.focus();
}

/**
 * Agrega un producto a la tabla activa utilizando su código de barras.
 * Si el código de barras contiene un signo de multiplicación, se interpreta como cantidad * código.
 *
 * @param {string} codigoBarras - El código de barras del producto, puede incluir una cantidad (ej. "3*x12345").
 */
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

    // Verificar si la lista de productos está vacía
    if (!productos[activeTable.id] || productos[activeTable.id].length < 1) {
        mostrarMensaje('Debe seleccionar, al menos, un producto para cobrar.', 'warning');
        return;
    }

    // Obtenemos el ID de la tabla activa
    const activeTableID = activeTable.id;
    // const usuario = usuarioActual;

    let html = `
        <div class="modal-form-container">
            <h2>Informaci&oacute;n de la Venta</h2>
            <div class="sale-info">
                <div class="sale-info container">
                    <div class="sale-info details">
                        <div class="sale-info info subtotal">
                            <span>Subtotal:</span>
                            <span id="sales-subtotal-info">¢${totales[activeTableID]['subtotal']}</span>
                        </div>
                        <div class="sale-info info impuesto">
                            <span>Impuesto:</span>
                            <span id="sales-impuesto-info">¢${totales[activeTableID]['impuesto']}</span>
                        </div>
                        <div class="sale-info info total">
                            <span>Total:</span>
                            <span id="sales-total-info">¢${totales[activeTableID]['total']}</span>
                        </div>
                    </div>
                    <div class="sale-info details">
                        <div class="sale-info info currency">
                            <span>Moneda:</span>
                            <select id="currency-select" required>
                                <option value="CRC">Colones</option>
                                <option value="USD">Dólares</option>
                                <option value="EUR">Euros</option>
                            </select>
                        </div>
                        <div id="currency-input-container" class="sale-info info currency change">
                            <span>Tipo de Cambio:</span>
                            <span id="currency-change-info">¢0.00</span>
                            <!-- <input type="number" id="currency-input" value="0.00" min="0" disabled> -->
                        </div>
                    </div>
                </div>
            </div>

            <h2>M&eacute;todo de Pago</h2>
            <div class="payment-info">
                <form class="payment-info methods" id="payment-methods">
                    <div class="payment-info methods buttons">
                        <button id="btn-method-efectivo" class="payment-method active" data-method="efectivo">
                            <span class="las la-money-bill"></span>
                            <span>Efectivo</span>
                        </button>
                        <button id="btn-method-tarjeta" class="payment-method" data-method="tarjeta">
                            <span class="las la-credit-card"></span>
                            <span>Tarjeta</span>
                        </button>
                        <button id="btn-method-sinpe" class="payment-method" data-method="sinpe">
                            <span class="las la-phone"></span>
                            <span>SINPE Móvil</span>
                        </button>
                        <button id="btn-method-credito" class="payment-method" data-method="credito">
                            <span class="las la-handshake"></span>
                            <span>Cr&eacute;dito</span>
                        </button>
                        <button id="btn-method-multiple" class="payment-method" data-method="multiple">
                            <span class="las la-wallet"></span>
                            <span>Combinado</span>
                        </button>
                    </div>
                    
                    <!-- Efectivo -->
                    <div class="payment-info methods container active" id="payment-method-efectivo">
                        <div class="payment-info input-select container">
                            <div class="payment-info methods input-select item">
                                <label for="pago-efectivo">Pag&oacute; con:</label>
                                <input 
                                    type="number" id="pago-efectivo" name="pago" 
                                    value="${totales[activeTableID]['total']}" min="${totales[activeTableID]['total']}" 
                                    step="0.10" required
                                >
                            </div>
                            <div class="payment-info methods input-select item">
                                <label for="vuelto-efectivo">Su cambio:</label>
                                <input type="number" id="vuelto-efectivo" name="vuelto-efectivo" value="0.00" disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta -->
                    <div class="payment-info methods container" id="payment-method-tarjeta">
                        <div class="payment-info input-select container">
                            <div class="payment-info methods input-select item">
                                <label for="referencia-tarjeta">N&deg; de Referencia:</label>
                                <input type="text" id="referencia-tarjeta" name="referencia">
                            </div>
                        </div>
                    </div>

                    <!-- SINPE Movil -->
                    <div class="payment-info methods container" id="payment-method-sinpe">
                        <div class="payment-info input-select container">
                            <div class="payment-info methods input-select item">
                                <label for="comprobante-sinpe">N&deg; de Comprobante:</label>
                                <input type="number" id="comprobante-sinpe" name="comprobante" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Credito -->
                    <div class="payment-info methods container" id="payment-method-credito">
                        <div class="payment-info input-select container">
                            <div class="payment-info methods input-select item">
                                <label for="vencimiento-credito">Plazo hasta:</label>
                                <input type="date" id="vencimiento-credito" name="vencimiento" min="${getCurrentDate(1)}">
                            </div>
                            <div class="payment-info methods input-select item">
                                <label for="notas">Notas Adicionales:</label>
                                <input type="text" id="notas" name="notas">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <h2>Informaci&oacute;n del Cliente</h2>
            <div class="cliente-info">
                <div class="cliente-info info">
                    <div class="cliente-info input-select basic">
                        <label>Cliente atendido:</label>
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
                                    <label>Tipo de Tel&eacute;fono:</label>
                                    <select id="tipo-select" required>
                                        <option value="">--Seleccionar--</option>
                                    </select>
                                </div>
                                <div class="cliente-info input-select form">
                                    <label>Código de Pa&iacute;s:</label>
                                    <select id="codigo-select" required>
                                        <option value="">--Seleccionar--</option>
                                    </select>
                                </div>
                                <div class="cliente-info input-select form">
                                    <label for="numero">Número de Tel&eacute;fono:</label>
                                    <input type="text" id="numero" name="numero" required>
                                </div>
                            </div>
                            <div class="cliente-form-group form-buttons">
                                <button type="submit" class="modal-confirm" id="cliente-save-button">Guardar</button>
                                <button type="button" class="modal-close" id="cliente-cancel-button">Cancelar</button>
                            </div>
                        </form>
                        <form id="cliente-deuda-form" class="cliente-form" style="display: none;">
                            <div class="cliente-form-group">
                                <div class="cliente-info input-select form">
                                    <label for="cliente-deuda">Deuda:</label>
                                    <select id="cliente-deuda" name="deuda">
                                        <option value="">-- Seleccionar --</option>
                                    </select>
                                </div>
                                <div class="cliente-info input-select form">
                                    <label for="cliente-fecha-limite">Fecha Limite:</label>
                                    <input type="date" id="cliente-fecha-limite" name="fecha-limite" disabled>
                                </div>
                            </div>
                            <div class="cliente-form-group">
                                <div class="cliente-info input-select form">
                                    <label for="cliente-monto-abono">Monto a abonar:</label>
                                    <input type="number" id="cliente-abono" name="abono" value="0.00" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="cliente-form-group form-buttons">
                                <button type="submit" id="deuda-abonar-button" class="modal-confirm">Abonar</button>
                                <button type="button" id="deuda-cancel-button" class="modal-close">Cancelar</button>
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
            const venta = obtenerDatosDeVenta();
            if (!venta) {
                mostrarMensaje('Error al obtener los datos de la venta.', 'error', 'Error de venta');
                return false;
            }

            const cobroForm = document.getElementById('payment-methods');
            if (!cobroForm) {
                mostrarMensaje('No se encontró el formulario de cobro.', 'error', 'Error de formulario');
                return false;
            }

            if (!cobroForm.checkValidity()) {
                cobroForm.reportValidity();
                return false;
            }

            const data = {
                impuesto: obtenerValorImpuesto(),
                cliente: listaClientes.find(c => c.ID === parseInt(venta[0].Venta.Cliente, 10)),
                usuario: usuarioActual
            };
            return { venta, data };
        },
        preDeny: () => {
            const venta = obtenerDatosDeVenta();
            if (!venta) {
                mostrarMensaje('Error al obtener los datos de la venta.', 'error', 'Error de venta');
                return false;
            }
            
            const cobroForm = document.getElementById('payment-methods');
            if (!cobroForm) {
                mostrarMensaje('No se encontró el formulario de cobro.', 'error', 'Error de formulario');
                return false;
            }
            
            if (!cobroForm.checkValidity()) {
                cobroForm.reportValidity();
                return false;
            }

            return { venta };
        }
    })
    .then(result => {
        if (result.isConfirmed || result.isDenied) {
            const { venta, data } = result.value;
            if (result.isConfirmed) {
                // Imprimir ticket
                crud.generarFactura(venta, data);
            }

            // Intenta guardar la venta en la BD
            crud.insertVentaDetalle(venta).then((success) => {
                if (!success) {
                    mostrarMensaje('Venta realizada con éxito.', 'success', 'Venta realizada');

                    const lastSaleInfo = {
                        total: parseFloat(totales[activeTableID].total ?? 0.00),
                        pay: venta[0].Venta.MontoPago,
                        change: venta[0].Venta.MontoVuelto
                    };

                    Object.entries(lastSaleInfo).forEach(([field, value]) => {
                        const span = document.getElementById(`last-sale-${field}`);
                        if (span) span.innerHTML = `&#162;${value.toFixed(2)}`;
                    });
                } else {
                    mostrarMensaje('Error al realizar la venta.', 'error', 'Error de venta');
                }
            });

            // Limpiar la lista de productos y renderizar la tabla
            // clearProductList(getActiveTable().id);
            // renderTable(productos[getActiveTable().id]);
        }
    })
    .catch(() => {
        mostrarMensaje('Error al realizar la operación.', 'error');
    });

    // Evitar que el formulario se envíe al presionar Enter
    addEventListenerToElement('payment-methods', 'submit', event => event.preventDefault());
    addEventListenerToElement('cliente-form', 'submit', event => event.preventDefault());
    addEventListenerToElement('cliente-deuda-form', 'submit', event => event.preventDefault());

    // Inicializar el select de clientes
    initializeSelectCliente();

    // Agregar evento al botón de agregar cliente
    addEventListenerToElement('cliente-add-button', 'click', () => {
        const clienteForm = document.getElementById('cliente-form');
        if (clienteForm) {
            clienteForm.style.display = 'block'
            clienteForm.scrollIntoView({ behavior: 'smooth', block: 'end' });
        };

        const clienteSelect = document.getElementById('cliente-select');
        if (clienteSelect) clienteSelect.disabled = true;

        const clienteDeudaForm = document.getElementById('cliente-deuda-form');
        if (clienteDeudaForm) {
            clienteDeudaForm.style.display = 'none';
            clienteDeudaForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };
        
        initializeClienteForm();
    });

    // Agregar evento al input de cambio (efectivo)
    addEventListenerToElement('pago-efectivo', 'input', () => {
        const vuelto = document.getElementById('vuelto-efectivo');
        if (vuelto) vuelto.value = getCambio('pago-efectivo');
    });

    // Agregar evento a los botones de métodos de pago
    const paymentMethods = document.getElementById('payment-methods');
    if (paymentMethods) {
        const methodButtons = paymentMethods.querySelectorAll('.payment-method');
        methodButtons.forEach(button => {
            addEventListenerToElement(button.id, 'click', event => handlePaymentMethodChange(event));
        });
    }

    // Agregar evento al select de clientes
    addEventListenerToElement('cliente-select', 'change', handleClienteSelectChange);

    // Agregar evento al select de tipo de moneda
    addEventListenerToElement('currency-select', 'change', event => handleCurrencySelect(event));
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

export function getCambio(pagoInputID) {
    const pago = parseFloat(document.getElementById(pagoInputID).value) || 0;
    const activeTableID = getActiveTable().id;
    const { moneda, tipoCambio } = totales[activeTableID];
    const totalCRC = parseFloat(getTotal(activeTableID));
    
    // Convertir el pago a colones si la moneda no es CRC
    const pagoEnColones = moneda === 'CRC' ? pago : pago * tipoCambio;
    const cambio = pagoEnColones - totalCRC;
    return cambio > 0 ? cambio.toFixed(2) : '0.00';
}

function obtenerDatosDeVenta() {
    const activeTable = getActiveTable();
    if (!activeTable) return null;

    const activeTableID = activeTable.id;
    const listaProductos = productos[activeTableID];

    const clienteSelect = document.getElementById('cliente-select');
    const clienteID = clienteSelect.value;
    if (!clienteID) {
        mostrarMensaje('No se seleccionó ningún cliente.', 'error', 'Error de cliente');
        return null;
    }

    const paymentMethod = document.querySelector('.payment-method.active').dataset.method;
    const paymentInfo = document.getElementById(`payment-method-${paymentMethod}`);
    const paymentData = {};

    switch (paymentMethod) {
        case 'efectivo':
            paymentData['pago'] = parseFloat(paymentInfo.querySelector('#pago-efectivo').value);
            paymentData['vuelto'] = parseFloat(paymentInfo.querySelector('#vuelto-efectivo').value);
            break;
        case 'tarjeta':
            paymentData['referencia'] = paymentInfo.querySelector('#referencia-tarjeta').value;
            break;
        case 'sinpe':
            paymentData['comprobante'] = paymentInfo.querySelector('#comprobante-sinpe').value;
            break;
        case 'credito':
            paymentData['vencimiento'] = paymentInfo.querySelector('#vencimiento-credito').value;
            paymentData['notas'] = paymentInfo.querySelector('#notas').value;
            break;
        case 'multiple':
            break;
        default:
            break;
    }

    const venta = {
        Cliente: clienteID,
        Moneda: totales[activeTableID].moneda,
        MontoBruto: parseFloat(totales[activeTableID].subtotal),
        MontoNeto: parseFloat(totales[activeTableID].total),
        MontoImpuesto: parseFloat(totales[activeTableID].impuesto),
        Condicion: paymentMethod === 'credito' ? 'CREDITO' : 'CONTADO',
        TipoPago: paymentMethod.toUpperCase(),
        TipoCambio: parseFloat(totales[activeTableID].tipoCambio),
        MontoPago: paymentData.pago || 0.00,
        MontoVuelto: paymentData.vuelto || 0.00,
        ReferenciaTarjeta: paymentData.referencia || '',
        ComprobanteSINPE: paymentData.comprobante || '',
    };

    const listaVentaDetalle = listaProductos.map(data => {
        return {
            Precio: data.producto.PrecioCompra,
            Cantidad: data.cantidad,
            Venta: venta,
            Producto: data.producto,
        }
    });

    return listaVentaDetalle;
}

export function clearProductList(tabID = null) {
    const activeTableID = tabID || (getActiveTable() && getActiveTable().id);
    if (!activeTableID) return;
    productos[activeTableID] = [];
    totales[activeTableID] = { subtotal: 0.00, impuesto: 0.00, total: 0.00 };
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
        listaClientes = clientes;
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
    const clienteForm = document.getElementById('cliente-form');
    if (!clienteForm) throw new Error('No se encontró el formulario de cliente.');

    if (clienteForm.style.display !== 'none') {
        initializeSelects();

        addEventListenerToElement('numero', 'input', manejarInputNumeroTelefono);
        addEventListenerToElement('codigo-select', 'change', manejarInputNumeroTelefono);
        addEventListenerToElement('cliente-cancel-button', 'click', handleClienteCancelClick);
        addEventListenerToElement('cliente-save-button', 'click', handleClienteSaveClick);
    }
}

function addEventListenerToElement(elementId, event, handler) {
    const element = document.getElementById(elementId);
    if (element) {
        element.removeEventListener(event, handler);
        element.addEventListener(event, handler);
    }
}

function handleClienteCancelClick() {
    const clienteForm = document.getElementById('cliente-form');
    if (clienteForm) {
        clienteForm.style.display = 'none';
        clienteForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
    
    const clienteSelect = document.getElementById('cliente-select');
    if (clienteSelect) clienteSelect.disabled = false;
}

async function handleClienteSaveClick() {
    try {
        const clienteForm = document.getElementById('cliente-form');
        if (!clienteForm) throw new Error('No se encontró el formulario de cliente.');

        const codigoSelect = document.getElementById('codigo-select');
        if (!codigoSelect) throw new Error('No se encontró el select de código de país.');

        if (!clienteForm.checkValidity()) {
            clienteForm.reportValidity();
            return;
        }

        const nombre = document.getElementById('cliente-nombre').value || 'No Definido';
        const alias = document.getElementById('cliente-alias').value || 'No Definido';
        const tipo = document.getElementById('tipo-select').value;
        const numeroInput = document.getElementById('numero').value;

        const formData = new FormData();
        formData.append('accion', 'insertar');
        formData.append('nombre', nombre);
        formData.append('alias', alias);
        formData.append('tipo', tipo);
        formData.append('codigo', codigoSelect.value);
        formData.append('numero', numeroInput);

        const clienteID = await Promise.resolve(crud.insertCliente(formData));
        if (clienteID !== -1) {
            initializeSelectCliente(clienteID);
            clienteForm.reset();
            clienteForm.style.display = 'none';

            const clienteSelect = document.getElementById('cliente-select');
            if (clienteSelect) clienteSelect.disabled = false;
        }
    } catch (error) {
        console.error(error);
        mostrarMensaje(`Error al guardar el cliente: ${error.message}`, 'error');
    }
}

function handlePaymentMethodChange(event) {
    const button = event.target.closest('.payment-method');
    if (!button) return;

    if (button.textContent.includes('Combinado')) {
        mostrarMensaje('El método de pago combinado aún no está disponible.', 'info', 'Método de pago');
        return;
    }

    const method = button.dataset.method;
    const paymentMethods = document.getElementById('payment-methods');
    if (!paymentMethods) return;

    // Toggle active class on method containers
    paymentMethods.querySelectorAll('.payment-info.methods.container').forEach(container => {
        const isActive = container.id === `payment-method-${method}`;
        container.classList.toggle('active', isActive);

        // Set required attribute for inputs in the active container if they are not disabled
        container.querySelectorAll('input, select').forEach(input => {
            if (input.id === 'notas') return;
            input.required = isActive && !input.disabled;
        });
    });

    // Toggle active class on method buttons
    paymentMethods.querySelectorAll('.payment-method').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.method === method);
    });

    // Scroll to the selected method container
    if (paymentMethods) {
        paymentMethods.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
}

function handleClienteSelectChange(event) {
    const clienteSelect = event.target.closest('#cliente-select');
    if (!clienteSelect) return;

    const clienteID = clienteSelect.value;
    if (!clienteID) return;

    // Obtener el formulario de deuda
    const clienteDeudaForm = document.getElementById('cliente-deuda-form');
    if (!clienteDeudaForm) {
        mostrarMensaje('No se encontró el formulario de deudas.', 'error');
        return;
    };

    // Obtener las deudas del cliente seleccionado
    crud.obtenerDeudasPorClienteID(clienteID).then(result => {
        // Verificar si el cliente tiene deudas
        const deudas = result.deudas;
        if (!deudas || deudas.length < 1) {
            // Si el cliente no tiene deudas, ocultar el formulario de deuda
            clienteDeudaForm.style.display !== 'none' && (clienteDeudaForm.style.display = 'none');
            return;
        }

        // Obtenemos el select de deudas del cliente y lo llenamos con las deudas obtenidas
        const deudaSelect = clienteDeudaForm.querySelector('#cliente-deuda');
        if (!deudaSelect) throw new Error('No se encontró el select de deudas.');

        // Limpiar el select de deudas
        deudaSelect.innerHTML = '';
        deudas.forEach(deuda => {
            const option = document.createElement('option');
            option.value = deuda.ID;
            option.text = `¢${deuda.Venta.MontoNeto.toFixed(2)} - Factura N° ${deuda.Venta.NumeroFactura}`;
            deudaSelect.add(option);
        });
        deudaSelect.selectedIndex = 0;

        // Manejar el cambio de selección de deuda
        addEventListenerToElement(deudaSelect.id, 'change', () => {
            if (deudas.length < 1) {
                clienteDeudaForm.style.display !== 'none' && (clienteDeudaForm.style.display = 'none');
                mostrarMensaje('El cliente seleccionado ya no tiene deudas pendientes.', 'info', 'Deuda del Cliente');
                clienteDeudaForm.reset();
                return;
            }

            const deuda = deudas.find(d => d.ID === parseInt(deudaSelect.value, 10));
            if (!deuda) return;

            // Obtener los datos de la deuda seleccionada
            const vencimiento = deuda.VencimientoISO;
            const monto = deuda.Venta.MontoNeto.toFixed(2);

            // Actualizar los campos del formulario de deuda
            clienteDeudaForm.querySelector('#cliente-fecha-limite').value = vencimiento;
            clienteDeudaForm.querySelector('#cliente-abono').value = monto;
            clienteDeudaForm.querySelector('#cliente-abono').max = monto;
            clienteDeudaForm.querySelector('#cliente-abono').focus();
        });

        // Disparar el evento de cambio para inicializar los campos
        deudaSelect.dispatchEvent(new Event('change'));

        // Agregar evento al botón de cancelar
        addEventListenerToElement('deuda-cancel-button', 'click', () => {
            clienteDeudaForm.style.display !== 'none' && (clienteDeudaForm.style.display = 'none');
            clienteDeudaForm.reset();
        });
        
        // Mostrar el formulario de deuda
        clienteDeudaForm.style.display = 'block';
        if (clienteDeudaForm) clienteDeudaForm.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }).catch(error => {
        console.error(error);
        mostrarMensaje(`Error al obtener las deudas del cliente: ${error.message}`, 'error', 'Error de deuda');
    });
}

function handleCurrencySelect(event) {
    const currencySelect = event.target.closest('#currency-select');
    if (!currencySelect) return;

    const currencyContainer = document.getElementById('currency-input-container');
    if (!currencyContainer) return;

    const currencyInfo = currencyContainer.querySelector('#currency-change-info');
    if (!currencyInfo) return;
    
    const currency = currencySelect.value;
    totales[getActiveTable().id].moneda = currency;

    fetch(`https://api.exchangerate-api.com/v4/latest/${currency}`)
        .then(response => response.json())
        .then(data => {
            const rate = data.rates['CRC'];
            totales[getActiveTable().id].tipoCambio = rate || 0.00;

            currencyInfo.innerHTML = `¢${rate ? rate.toFixed(2) : '0.00'}`;
            const currencySymbols = { 'USD': '$', 'EUR': '€', 'CRC': '¢' };

            if (currency !== 'CRC') {
                mostrarMensaje(`El tipo de cambio actual es de ¢${rate.toFixed(2)} por ${currency}.`, 'info', 'Tipo de Cambio');
            }

            const updateCurrencyValues = (field, rate, currencySymbol) => {
                const element = document.getElementById(`sales-${field}-info`);
                const activeTableID = getActiveTable().id;
                if (element) {
                    const value = parseFloat(totales[activeTableID][field]);
                    const newValue = currency === 'CRC' ? value * rate : value / rate;
                    element.innerHTML = `${currencySymbol}${newValue.toFixed(2)}`;
                }
            };

            const pagoEfectivo = document.getElementById('pago-efectivo');
            const pagoEfectivoContainer = document.getElementById('payment-method-efectivo');
            if (pagoEfectivo && pagoEfectivoContainer.classList.contains('active')) {
                const value = parseFloat(totales[getActiveTable().id]['total']);
                const newValue = currency === 'CRC' ? value * rate : value / rate;
                pagoEfectivo.min = newValue.toFixed(2);
                pagoEfectivo.value = newValue.toFixed(2);
            }

            ['subtotal', 'impuesto', 'total'].forEach(field => {
                updateCurrencyValues(field, rate, currencySymbols[currency]);
            });
        })
        .catch(error => {
            mostrarMensaje(`Error al obtener el tipo de cambio: ${error.message}`, 'error', 'Error de cambio');
        });
}