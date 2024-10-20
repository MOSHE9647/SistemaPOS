// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { checkEmptyTable, getCurrentDate, manejarInputConEnter } from '../../utils.js';
import { fetchBarcode, generateBarcode } from '../../barcode.js';
import { showLoader, hideLoader } from '../../gui/loader.js';
import { mostrarMensaje } from '../../gui/notification.js';
import { insertProducto, updateProducto } from './crud.js';
import { initializeSelects } from './selects.js';

// Variables globales
let productos = [];

/**
 * Renderiza la tabla de productos con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada producto en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el producto.
 * 
 * @param {Array} listaProductos - El arreglo de productos a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
export function renderTable(listaProductos) {
    showLoader();
    productos = listaProductos;

    // Obtener el cuerpo de la tabla
    let tableBodyID = 'table-productos-body';
    let tableBody = document.getElementById(tableBodyID);
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada producto en el arreglo
    productos.forEach(producto => {
        // Crear una fila para el producto
        let row = `
            <tr data-id="${producto.ID}">
            <td data-field="codigobarras">${producto.CodigoBarras.Numero}</td>
            <td data-field="imagen">
                <img src="${window.baseURL}${producto.Imagen}" alt="${producto.Nombre}" style="width: 50px; height: 50px;">
            </td>
            <td data-field="nombre">${producto.Nombre}</td>
            <td data-field="preciocompra">&#162;${producto.PrecioCompra}</td>
            <td data-field="categoria">${producto.Categoria.Nombre}</td>
            <td data-field="subcategoria">${producto.Subcategoria.Nombre}</td>
            <td data-field="marca">${producto.Marca.Nombre}</td>
            <td class="actions">
                <button class="btn-info las la-info-circle" onclick="gui.infoProducto(${producto.ID})"></button>
                <button class="btn-edit las la-edit" onclick="gui.editProducto(${producto.ID})"></button>
                <button class="btn-delete las la-trash" onclick="deleteProducto(${producto.ID})"></button>
            </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    checkEmptyTable(tableBodyID, 'las la-box');
    hideLoader();
}

/**
 * Inicializa el input de imagen y configura los eventos necesarios para
 * seleccionar y mostrar una imagen en la interfaz de usuario.
 *
 * Este método agrega un evento de clic al botón de imagen para abrir el
 * selector de archivos y un evento de cambio al input de imagen para
 * actualizar el nombre del archivo seleccionado y mostrar la imagen
 * seleccionada en un elemento img.
 *
 * @function
 */
function initializeImageInput() {
    const input = document.getElementById('imagen');
    const btnImagen = document.getElementById('btn-imagen');
    const inputNombre = document.getElementById('imagen-nombre');
    const imgProducto = document.getElementById('img-producto');

    btnImagen.addEventListener('click', () => input.click());
    input.addEventListener('change', () => {
        const file = input.files[0];
        inputNombre.value = file.name;

        // Cambiar el src de 'img-producto' por el de la imagen seleccionada
        const reader = new FileReader();
        reader.onload = (e) => {
            imgProducto.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

/**
 * Muestra información detallada sobre un producto en un cuadro de diálogo modal.
 *
 * @param {number} productoID - El ID del producto para mostrar información.
 * @returns {Promise<void>} - Una promesa que se resuelve cuando se ha mostrado la información del producto.
 *
 * @throws {Error} Si no se encuentra el producto con el ID proporcionado.
 *
 * @example
 * // Muestra información para el producto con ID 123
 * infoProducto(123);
 */
export async function infoProducto(productoID) {
    // Obtener el producto seleccionado
    const producto = productos.find(producto => producto.ID == productoID);
    if (!producto) {
        mostrarMensaje('No se encontró el producto solicitado.', 'error', 'Producto no encontrado');
        return;
    }

    // Obtener la imagen del código de barras
    const barcodeImage = await fetchBarcode(producto.CodigoBarras.Numero).then(data => data.image);

    // Obtener la imagen del producto
    const productoImagen = 
        producto.Imagen && producto.Imagen !== "" ? 
        `${window.baseURL}${producto.Imagen}` : 
        `${window.baseURL}${defaultProductImage}`
    ;

    let html = `
        <div class="modal-form-container">
            <div class="producto-info">
                <div class="producto-info basic-info">
                    <div class="producto-info input-select">
                        <label>Nombre del Producto:</label>
                        <input type="text" value="${producto.Nombre}" disabled>
                    </div>
                    <div class="producto-info input-select group">
                        <div class="producto-info input-select">
                            <label>Cantidad:</label>
                            <input type="text" value="${producto.Cantidad}" disabled>
                        </div>
                        <div class="producto-info input-select">
                            <label>Fecha de Vencimiento:</label>
                            <input type="text" value="${producto.Vencimiento}" disabled>
                        </div>
                    </div>
                    <div class="producto-info input-select group">
                        <div class="producto-info input-select">
                            <label>Precio de Compra (&#162):</label>
                            <input type="text" value="${producto.PrecioCompra}" disabled>
                        </div>
                        <div class="producto-info input-select">
                            <label>Ganancia (%):</label>
                            <input type="text" value="${producto.PorcentajeGanancia}" disabled>
                        </div>
                    </div>
                </div>
                <div class="producto-info barcode">
                    <div class="producto-info input-select">
                        <label>C&oacute;digo de Barras:</label>
                        <input type="text" value="${producto.CodigoBarras.Numero}" disabled>
                    </div>
                    <div class="barcode-image">
                        <img src="${barcodeImage}" alt="Código de Barras">
                    </div>
                </div>
            </div>
            <div class="producto-info">
                <div class="producto-info descripcion">
                    <div class="producto-info input-select">
                        <label>Descripci&oacute;n:</label>
                        <textarea wrap="soft" placeholder="Sin descripci&oacute;n..." disabled>${producto.Descripcion}</textarea>
                    </div>
                </div>
            </div>
            <hr>
            <div class="producto-info">
                <div class="producto-info categoria">
                    <div class="producto-info input-select group">
                        <div class="producto-info input-select">
                            <label>Categor&iacute;a:</label>
                            <input type="text" value="${producto.Categoria.Nombre}" disabled>
                        </div>
                        <div class="producto-info input-select">
                            <label>Subcategor&iacute;a:</label>
                            <input type="text" value="${producto.Subcategoria.Nombre}" disabled>
                        </div>
                    </div>
                    <div class="producto-info input-select group">
                        <div class="producto-info input-select">
                            <label>Marca:</label>
                            <input type="text" value="${producto.Marca.Nombre}" disabled>
                        </div>
                        <div class="producto-info input-select">
                            <label>Presentaci&oacute;n:</label>
                            <input type="text" value="${producto.Presentacion.Nombre}" disabled>
                        </div>
                    </div>
                </div>
                <div class="producto-info imagen">
                    <label>Imagen del Producto:</label>
                    <img src="${productoImagen}" alt="${producto.Nombre}">
                </div>
            </div>
        </div>
    `;

    Swal.fire({
        title: "Informaci&oacute;n del producto",
        html: html,
        showCancelButton: false,
        confirmButtonText: "Cerrar",
        customClass: {
            popup: 'modal-container',
            header: 'modal-header',
            title: 'modal-title',
            htmlContainer: 'modal-body',
            cancelButton: 'modal-close',
            confirmButton: 'modal-confirm',
            actions: 'modal-actions',
        },
    });
}

/**
 * Abre un modal para editar un producto.
 * 
 * @description Esta función abre un modal con un formulario para editar los datos del producto.
 *              Los campos del formulario se llenan con los datos actuales del producto.
 *              Al confirmar, se validan los datos y se envían para actualizar el producto.
 * 
 * @param {number} productoID - El ID del producto a editar
 * 
 * @example
 * editProducto(1);
 * 
 * @returns {void}
 */
export async function editProducto(productoID) {
    const producto = productos.find(producto => producto.ID == productoID);
    if (!producto) {
        mostrarMensaje('No se encontró el producto solicitado.', 'error', 'Producto no encontrado');
        return;
    }

    // Obtener la imagen del código de barras
    const barcodeImage = await fetchBarcode(producto.CodigoBarras.Numero).then(data => data.image);

    // Obtener la imagen del producto
    const productoImagen = 
        producto.Imagen && producto.Imagen !== "" ? 
        `${window.baseURL}${producto.Imagen}` : 
        `${window.baseURL}${defaultProductImage}`
    ;

    // Generar el HTML para el formulario editable
    let html = `
        <div class="modal-form-container">
            <form id="producto-edit-form">
                <div class="producto-info">
                    <!-- Información básica del producto -->
                    <div class="producto-info basic-info">
                        <div class="producto-info input-select">
                            <label>Nombre del Producto:</label>
                            <input type="text" id="nombre" value="${producto.Nombre}" required>
                        </div>
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Cantidad:</label>
                                <input type="number" id="cantidad" value="${producto.Cantidad}" min="0" step="1" max="99999999" required>
                            </div>
                            <div class="producto-info input-select">
                                <label>Fecha de Vencimiento:</label>
                                <input type="date" id="vencimiento" value="${producto.VencimientoISO}" min="${getCurrentDate()}" required>
                            </div>
                        </div>
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Precio de Compra (&#162):</label>
                                <input type="number" id="precio" value="${producto.PrecioCompra}" min="0" step="0.1" max="99999999.99" required>
                            </div>
                            <div class="producto-info input-select">
                                <label>Ganancia (%):</label>
                                <input type="number" id="ganancia" value="${producto.PorcentajeGanancia}" min="0" step="0.1" max="100" required>
                            </div>
                        </div>
                    </div>
                    <!-- Código de barras y su imagen -->
                    <div class="producto-info barcode">
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Código de Barras:</label>
                                <input type="text" id="codigoBarras" value="${producto.CodigoBarras.Numero}" disabled>
                            </div>
                        </div>
                        <div class="barcode-image">
                            <img src="${barcodeImage}" alt="Código de Barras">
                        </div>
                    </div>
                </div>
                <div class="producto-info">
                    <div class="producto-info descripcion">
                        <div class="producto-info input-select">
                            <label>Descripción:</label>
                            <textarea 
                                id="descripcion" 
                                wrap="soft" 
                                placeholder="Escribe aquí la descripción del producto...">${producto.Descripcion}</textarea>
                        </div>
                    </div>
                </div>
                <hr>
                <!-- Categoría, Subcategoría, Marca, y Presentación -->
                <div class="producto-info">
                    <div class="producto-info categoria">
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Categoría:</label>
                                <select id="categoria-select" required>
                                    <option value="${producto.Categoria.ID}">${producto.Categoria.Nombre}</option>
                                </select>
                            </div>
                            <div class="producto-info input-select">
                                <label>Subcategoría:</label>
                                <select id="subcategoria-select" required>
                                    <option value="${producto.Subcategoria.ID}">${producto.Subcategoria.Nombre}</option>
                                </select>
                            </div>
                        </div>
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Marca:</label>
                                <select id="marca-select" required>
                                    <option value="${producto.Marca.ID}">${producto.Marca.Nombre}</option>
                                </select>
                            </div>
                            <div class="producto-info input-select">
                                <label>Presentación:</label>
                                <select id="presentacion-select" required>
                                    <option value="${producto.Presentacion.ID}">${producto.Presentacion.Nombre}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Imagen del producto -->
                    <div class="producto-info imagen">
                        <label>Imagen del Producto:</label>
                        <img id="img-producto" src="${productoImagen}" alt="${producto.Nombre}">
                    </div>
                </div>
                <hr>
                <!-- Cambiar la imagen del producto -->
                <div class="producto-info">
                    <div class="producto-info input-select">
                        <label>Cambiar la imagen del producto:</label>
                        <input type="hidden" id="imagen-actual" value="${producto.Imagen}">
                        <input type="file" id="imagen" accept="image/*" style="display: none;">
                        <div class="producto-info input-select group imagen-group">
                            <button type="button" id="btn-imagen" class="btn-imagen">Elegir archivo</button>
                            <input type="text" id="imagen-nombre" placeholder="No se eligió ningún archivo" disabled>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Editar producto",
        html: html,
        showCancelButton: true,
        confirmButtonText: "Actualizar",
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
            const form = document.getElementById('producto-edit-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }
    
            // Obtener datos del formulario si es válido
            const formData = new FormData();
            formData.append("accion"            , "actualizar");
            formData.append("id"                , producto.ID);
            formData.append("codigobarras"      , producto.CodigoBarras.ID);
            formData.append("codigobarrasnumero", producto.CodigoBarras.Numero);
            formData.append("nombre"            , document.getElementById('nombre').value);
            formData.append("cantidad"          , document.getElementById('cantidad').value);
            formData.append("vencimiento"       , document.getElementById('vencimiento').value);
            formData.append("preciocompra"      , document.getElementById('precio').value);
            formData.append("ganancia"          , document.getElementById('ganancia').value);
            formData.append("descripcion"       , document.getElementById('descripcion').value);
            formData.append("categoria"         , document.getElementById('categoria-select').value);
            formData.append("subcategoria"      , document.getElementById('subcategoria-select').value);
            formData.append("marca"             , document.getElementById('marca-select').value);
            formData.append("presentacion"      , document.getElementById('presentacion-select').value);
    
            // Obtener la imagen del producto
            let imagen = document.getElementById('imagen').files[0];
            if (!imagen) {
                const imagenActual = document.getElementById('imagen-actual').value;
                const response = await fetch(imagenActual);
                const blob = await response.blob();
                imagen = new File([blob], imagenActual.split('/').pop(), { type: blob.type });
            }
            formData.append("imagen", imagen);

            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            updateProducto(formData);
        }
    });

    // Cargar las listas de categorías, subcategorías, marcas y presentaciones
    initializeSelects();

    // Inicializar el campo de imagen
    initializeImageInput();
}

/**
 * Abre un modal para crear un nuevo producto.
 * 
 * @description Esta función abre un modal con un formulario para ingresar los datos de un nuevo producto.
 *              Al confirmar, se validan los datos y se envían para crear el nuevo producto.
 * 
 * @example
 * createProducto();
 * 
 * @returns {void}
 */
export async function createProducto() {
    // Generar una imagen para el código de barras
    const barcodeImage = await fetchBarcode("", false).then(data => data.image);

    // Crear campos editables para los datos del producto
    let html = `
        <div class="modal-form-container">
            <form id="producto-create-form">
                <div class="producto-info">
                    <div class="producto-info basic-info">
                        <div class="producto-info input-select">
                            <label>Nombre del Producto:</label>
                            <input type="text" id="nombre" required>
                        </div>
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Cantidad:</label>
                                <input type="number" id="cantidad" min="0" step="1" max="99999999" required>
                            </div>
                            <div class="producto-info input-select">
                                <label>Fecha de Vencimiento:</label>
                                <input type="date" id="vencimiento" min="${getCurrentDate()}" required>
                            </div>
                        </div>
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Precio de Compra (&#162):</label>
                                <input type="number" id="precio" min="0" step="0.1" max="99999999.99" required>
                            </div>
                            <div class="producto-info input-select">
                                <label>Ganancia (%):</label>
                                <input type="number" id="ganancia" min="0" step="0.1" max="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="producto-info barcode">
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>C&oacute;digo de Barras:</label>
                                <input type="number" id="codigoBarras" min="12" required>
                            </div>
                            <button type="button" id="btn-barcode" class="btn-barcode">Generar</button>
                        </div>
                        <div class="barcode-image">
                            <img src="${barcodeImage}" alt="Código de Barras">
                        </div>
                    </div>
                </div>
                <div class="producto-info">
                    <div class="producto-info descripcion">
                        <div class="producto-info input-select">
                            <label>Descripci&oacute;n:</label>
                            <textarea 
                                id="descripcion" 
                                wrap="soft" 
                                placeholder="Escribe aquí la descripción del producto..."></textarea>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="producto-info">
                    <div class="producto-info categoria">
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Categor&iacute;a:</label>
                                <select id="categoria-select" required></select>
                            </div>
                            <div class="producto-info input-select">
                                <label>Subcategor&iacute;a:</label>
                                <select id="subcategoria-select" required></select>
                            </div>
                        </div>
                        <div class="producto-info input-select group">
                            <div class="producto-info input-select">
                                <label>Marca:</label>
                                <select id="marca-select" required></select>
                            </div>
                            <div class="producto-info input-select">
                                <label>Presentaci&oacute;n:</label>
                                <select id="presentacion-select" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="producto-info imagen">
                        <label>Imagen del Producto:</label>
                        <img id="img-producto" src="${window.baseURL}${defaultProductImage}" alt="Imagen del Producto">
                    </div>
                </div>
                <hr>
                <div class="producto-info">
                    <div class="producto-info input-select">
                        <label>Cambiar la imagen del producto:</label>
                        <input type="hidden" id="imagen-actual">
                        <input type="file" id="imagen" accept="image/*" style="display: none;">
                        <div class="producto-info input-select group imagen-group">
                            <button type="button" id="btn-imagen" class="btn-imagen">Elegir archivo</button>
                            <input type="text" id="imagen-nombre" placeholder="No se eligió ningún archivo" disabled>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Crear producto",
        html: html,
        showCancelButton: true,
        confirmButtonText: "Crear",
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
        preConfirm: () => {
            const form = document.getElementById('producto-create-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }

            // Crear el objeto FormData
            const formData = new FormData();

            // Agregar los datos del producto al FormData
            formData.append('accion'            , 'insertar');
            formData.append('codigobarrasnumero', document.getElementById('codigoBarras').value);
            formData.append('nombre'            , document.getElementById('nombre').value);
            formData.append('cantidad'          , document.getElementById('cantidad').value);
            formData.append('vencimiento'       , document.getElementById('vencimiento').value);
            formData.append('preciocompra'      , document.getElementById('precio').value);
            formData.append('ganancia'          , document.getElementById('ganancia').value);
            formData.append('descripcion'       , document.getElementById('descripcion').value);
            formData.append('categoria'         , document.getElementById('categoria-select').value);
            formData.append('subcategoria'      , document.getElementById('subcategoria-select').value);
            formData.append('marca'             , document.getElementById('marca-select').value);
            formData.append('presentacion'      , document.getElementById('presentacion-select').value);

            const imagen = document.getElementById('imagen').files[0];
            if (imagen) {
                formData.append('imagen', imagen);
            }

            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            insertProducto(formData);
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al crear el nuevo producto.<br>${error}`, 'error', 'Error al crear');
    });

    // Inicializar el botón para generar el código de barras
    const codigoBarrasInput = document.getElementById('codigoBarras');
    const btnBarcode = document.getElementById('btn-barcode');
    btnBarcode.addEventListener('click', () => generateBarcode(codigoBarrasInput, '.barcode-image img'));

    // Configurar el input del código de barras para generar el código al presionar 'Enter'
    manejarInputConEnter('codigoBarras', 'btn-barcode');

    // Cargar las listas de categorías, subcategorías, marcas y presentaciones
    initializeSelects();

    // Inicializar el campo de imagen
    initializeImageInput();
}