// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { checkEmptyTable, manejarInputConEnter } from "../../utils.js";
import { insertProveedor, updateProveedor } from "./crud.js";
import { showLoader, hideLoader } from "../../gui/loader.js";
import { initializeSelects } from "./selects.js";
import * as dir from "../direccion/gui.js";
import * as tel from "../telefono/gui.js";

// Variables globales
let proveedores = [];

/**
 * Renderiza la tabla de proveedores con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada proveedor en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el proveedor.
 * 
 * @param {Array} listaProveedores - El arreglo de proveedores a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
export function renderTable(listaProveedores) {
    showLoader();

    // Asignar la lista de proveedores a la variable global
    proveedores = listaProveedores;

    // Obtener el cuerpo de la tabla
    let tableBodyID = 'table-proveedores-body';
    let tableBody = document.getElementById(tableBodyID);
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada proveedor en el arreglo
    proveedores.forEach(proveedor => {
        // Crear una fila para el proveedor
        let row = `
            <tr data-id="${proveedor.ID}">
            <td data-field="email">${proveedor.Email}</td>
            <td data-field="nombre">${proveedor.Nombre}</td>
            <td data-field="categoria">${proveedor.Categoria.Nombre}</td>
                ${isAdmin ? `
                    <td data-field="creacion" data-iso="${proveedor.CreacionISO}">${proveedor.Creacion}</td>
                    <td data-field="modificacion" data-iso="${proveedor.ModificacionISO}">${proveedor.Modificacion}</td>
                ` : ''}
            <td class="actions">
                <button class="btn-info las la-info-circle" onclick="gui.infoProveedor(${proveedor.ID})"></button>
                <button class="btn-edit las la-edit" onclick="gui.editProveedor(${proveedor.ID})"></button>
                <button class="btn-delete las la-trash" onclick="deleteProveedor(${proveedor.ID})"></button>
            </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    // Verificar si la tabla está vacía
    checkEmptyTable(tableBodyID, 'las la-exclamation-circle');
    hideLoader();
}

/**
 * Muestra información detallada sobre un proveedor en un cuadro de diálogo modal.
 *
 * @param {number} proveedorID - El ID del proveedor para mostrar información.
 * @returns {Promise<void>} - Una promesa que se resuelve cuando se ha mostrado la información del proveedor.
 *
 * @throws {Error} Si no se encuentra el proveedor con el ID proporcionado.
 *
 * @example
 * // Muestra información para el proveedor con ID 123
 * infoProveedor(123);
 */
export function infoProveedor(proveedorID) {
    // Obtener el proveedor seleccionado
    const proveedor = proveedores.find(proveedor => proveedor.ID == proveedorID);
    if (!proveedor) {
        mostrarMensaje('No se encontró el proveedor solicitado.', 'error', 'Proveedor no encontrado');
        return;
    }

    let html = `
        <div class="modal-form-container">
            <h2>Información Básica</h2>
            <div class="proveedor-info">
                <div class="proveedor-info basic-info">
                    <div class="proveedor-info input-select basic">
                        <label>Nombre del Proveedor:</label>
                        <input type="text" value="${proveedor.Nombre}" disabled>
                    </div>
                    <div class="proveedor-info input-select basic">
                        <label>Categoría:</label>
                        <input type="text" value="${proveedor.Categoria.Nombre}" disabled>
                    </div>
                    <div class="proveedor-info input-select basic">
                        <label>Email:</label>
                        <input type="text" value="${proveedor.Email}" disabled>
                    </div>
                </div>
            </div>
            
            <h2>Direcciones del Proveedor</h2>
            <div class="proveedor-info address-table">
                <!-- Tabla 'Direcciones' -->
                <div class="records table-responsive">
                    <div class="table-container">
                        <div class="table-header">
                            <!-- Opciones de ordenamiento -->
                            <div class="paginationSort proveedor">
                                <span>Ordenar por:</span>
                                <select id="direccion-sort-selector">
                                    <option value="Provincia">Provincia</option>
                                    <option value="Canton">Cant&oacute;n</option>
                                    <option value="Distrito">Distrito</option>
                                    <option value="Barrio">Barrio</option>
                                    <option value="Sennas">Se&ntilde;as</option>
                                    <option value="Distancia">Distancia</option>
                                </select>
                            </div>

                            <!-- Barra de busqueda -->
                            <div class="search-bar proveedor">
                                <input type="text" id="direccion-search-input" placeholder="Buscar por provincia, cant&oacute;n, distrito, etc...">
                                <button class="search-button" id="direccion-search-button">
                                    <span class="las la-search"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de direcciones -->
                        <table class="tables-proveedor" id="table-direcciones" width="100%">
                            <thead>
                                <tr>
                                    <th data-field="provincia">Provincia</th>
                                    <th data-field="canton">Cant&oacute;n</th>
                                    <th data-field="distrito">Distrito</th>
                                    <th data-field="barrio">Barrio</th>
                                    <th data-field="sennas">Se&ntilde;as</th>
                                    <th data-field="distancia">Distancia (Km)</th>
                                    <!-- <th>Acciones</th> -->
                                </tr>
                            </thead>
                            <tbody id="table-direcciones-body">
                                <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                                <tr>
                                    <td colspan="7" class="nodata">
                                        <i class="las la-exclamation-circle"></i>
                                        <p>Este proveedor no poesee ninguna direcci&oacute;n</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <h2>Tel&eacute;fonos del Proveedor</h2>
            <div class="proveedor-info address-table">
                <!-- Tabla 'Teléfonos' -->
                <div class="records table-responsive">
                    <div class="table-container">
                        <div class="table-header">
                            <!-- Opciones de ordenamiento -->
                            <div class="paginationSort proveedor">
                                <span>Ordenar por:</span>
                                <select id="telefono-sort-selector">
                                    <option value="Tipo">Tipo</option>
                                    <option value="CodigoPais">C&oacute;digo de Pa&iacute;s</option>
                                    <option value="Numero">N&uacute;mero de Tel&eacute;fono</option>
                                </select>
                            </div>

                            <!-- Barra de busqueda -->
                            <div class="search-bar proveedor">
                                <input type="text" id="telefono-search-input" placeholder="Buscar por tipo, c&oacute;digo de pa&iacute;s o n&uacute;mero">
                                <button class="search-button" id="telefono-search-button">
                                    <span class="las la-search"></span>
                                </button>
                            </div>

                            <!-- Botón para crear nueva Telefono -->
                        </div>

                        <!-- Tabla de telefonos -->
                        <table class="tables-proveedor" id="table-telefonos" width="100%">
                            <thead>
                                <tr>
                                    <th data-field="tipo">Tipo</th>
                                    <th data-field="codigopais">C&oacute;digo de Pa&iacute;s</th>
                                    <th data-field="numero">N&uacute;mero</th>
                                    <th data-field="extension">Extensi&oacute;n</th>
                                    <!-- <th>Acciones</th> -->
                                </tr>
                            </thead>
                            <tbody id="table-telefonos-body">
                                <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                                <tr>
                                    <td colspan="5" class="nodata">
                                        <i class="las la-exclamation-circle"></i>
                                        <p>Este proveedor no poesee ning&uacute;n tel&eacute;fono</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;

    Swal.fire({
        title: "Información del proveedor",
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

    // Cargar las listas de direcciones y teléfonos
    initializeDireccionTable(proveedor.Direcciones || [], 'table-direcciones-body', true);
    initializeTelefonoTable(proveedor.Telefonos || [], 'table-telefonos-body', true);
}

/**
 * Abre un modal para editar un proveedor.
 * 
 * @description Esta función abre un modal con un formulario para editar los datos del proveedor.
 *              Los campos del formulario se llenan con los datos actuales del proveedor.
 *              Al confirmar, se validan los datos y se envían para actualizar el proveedor.
 * 
 * @param {number} proveedorID - El ID del proveedor a editar
 * 
 * @example
 * editProveedor(1);
 * 
 * @returns {void}
 */
export function editProveedor(proveedorID) {
    const proveedor = proveedores.find(proveedor => proveedor.ID == proveedorID);
    if (!proveedor) {
        mostrarMensaje('No se encontró el proveedor solicitado.', 'error', 'Proveedor no encontrado');
        return;
    }

    // Obtiene las listas de direcciones y de teléfonos
    let direcciones = proveedor.Direcciones || [];
    let telefonos = proveedor.Telefonos || [];

    // Generar el HTML para el formulario editable
    let html = `
        <div class="modal-form-container">
            <form id="proveedor-edit-form">
                <h2>Información Básica</h2>
                <div class="proveedor-info">
                    <!-- Información básica del proveedor -->
                    <div class="proveedor-info basic-info">
                        <div class="proveedor-info input-select basic">
                            <label>Nombre del Proveedor:</label>
                            <input type="text" id="nombre" value="${proveedor.Nombre}" required>
                        </div>
                        <div class="proveedor-info input-select basic">
                            <label>Email:</label>
                            <input type="email" id="email" value="${proveedor.Email}" required>
                        </div>
                        <div class="proveedor-info input-select basic">
                            <label>Categoría:</label>
                            <select id="categoria-select" required>
                                <option value="${proveedor.Categoria.ID}">${proveedor.Categoria.Nombre}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <h2>Direcciones del Proveedor</h2>
                <div class="proveedor-info address-table">
                    <!-- Tabla 'Direcciones' -->
                    <div class="records table-responsive">
                        <div class="table-container">
                            <div class="table-header">
                                <!-- Opciones de ordenamiento -->
                                <div class="paginationSort proveedor">
                                    <span>Ordenar por:</span>
                                    <select id="direccion-sort-selector">
                                        <option value="Provincia">Provincia</option>
                                        <option value="Canton">Cant&oacute;n</option>
                                        <option value="Distrito">Distrito</option>
                                        <option value="Barrio">Barrio</option>
                                        <option value="Sennas">Se&ntilde;as</option>
                                        <option value="Distancia">Distancia</option>
                                    </select>
                                </div>

                                <!-- Barra de busqueda -->
                                <div class="search-bar proveedor">
                                    <input type="text" id="direccion-search-input" placeholder="Buscar por provincia, cant&oacute;n, distrito, etc...">
                                    <button class="search-button" id="direccion-search-button">
                                        <span class="las la-search"></span>
                                    </button>
                                </div>

                                <!-- Botón para crear nueva Direccion -->
                                <button id="btn-create-dir" type="button" class="createButton">A&ntilde;adir</button>
                            </div>

                            <!-- Tabla de direcciones -->
                            <table class="tables-proveedor" id="table-direcciones" width="100%">
                                <thead>
                                    <tr>
                                        <th data-field="provincia">Provincia</th>
                                        <th data-field="canton">Cant&oacute;n</th>
                                        <th data-field="distrito">Distrito</th>
                                        <th data-field="barrio">Barrio</th>
                                        <th data-field="sennas">Se&ntilde;as</th>
                                        <th data-field="distancia">Distancia (Km)</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table-direcciones-body">
                                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                                    <tr>
                                        <td colspan="7" class="nodata">
                                            <i class="las la-exclamation-circle"></i>
                                            <p>Este proveedor no poesee ninguna direcci&oacute;n</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <h2>Tel&eacute;fonos del Proveedor</h2>
                <div class="proveedor-info address-table">
                    <!-- Tabla 'Teléfonos' -->
                    <div class="records table-responsive">
                        <div class="table-container">
                            <div class="table-header">
                                <!-- Opciones de ordenamiento -->
                                <div class="paginationSort proveedor">
                                    <span>Ordenar por:</span>
                                    <select id="telefono-sort-selector">
                                        <option value="Tipo">Tipo</option>
                                        <option value="CodigoPais">C&oacute;digo de Pa&iacute;s</option>
                                        <option value="Numero">N&uacute;mero de Tel&eacute;fono</option>
                                    </select>
                                </div>

                                <!-- Barra de busqueda -->
                                <div class="search-bar proveedor">
                                    <input type="text" id="telefono-search-input" placeholder="Buscar por tipo, c&oacute;digo de pa&iacute;s o n&uacute;mero">
                                    <button class="search-button" id="telefono-search-button">
                                        <span class="las la-search"></span>
                                    </button>
                                </div>

                                <!-- Botón para crear nueva Telefono -->
                                <button id="btn-create-tel" type="button" class="createButton">A&ntilde;adir</button>
                            </div>

                            <!-- Tabla de telefonos -->
                            <table class="tables-proveedor" id="table-telefonos" width="100%">
                                <thead>
                                    <tr>
                                        <th data-field="tipo">Tipo</th>
                                        <th data-field="codigopais">C&oacute;digo de Pa&iacute;s</th>
                                        <th data-field="numero">N&uacute;mero</th>
                                        <th data-field="extension">Extensi&oacute;n</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table-telefonos-body">
                                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                                    <tr>
                                        <td colspan="5" class="nodata">
                                            <i class="las la-exclamation-circle"></i>
                                            <p>Este proveedor no poesee ning&uacute;n tel&eacute;fono</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Editar proveedor",
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
            const form = document.getElementById('proveedor-edit-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }
    
            // Obtener datos del formulario si es válido
            const formData = new FormData();
            formData.append("accion"      , "actualizar");
            formData.append("id"          , proveedor.ID);
            formData.append("nombre"      , document.getElementById('nombre').value);
            formData.append("email"       , document.getElementById('email').value);
            formData.append("categoria"   , document.getElementById('categoria-select').value);
            formData.append("direcciones" , JSON.stringify(direcciones));
            formData.append("telefonos"   , JSON.stringify(telefonos));
    
            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            updateProveedor(formData);
        }
    });

    // Evitar que el formulario se envíe al presionar Enter o al hacer click en cualquier botón
    const form = document.getElementById('proveedor-edit-form');
    form.addEventListener('submit', (e) => e.preventDefault());

    // Inicializar las tablas de direcciones y teléfonos
    initializeDireccionTable(direcciones, 'table-direcciones-body');
    initializeTelefonoTable(telefonos, 'table-telefonos-body');

    // Cargar las listas de categorías
    initializeSelects();
}

/**
 * Abre un modal para crear un proveedor.
 * 
 * @description Esta función abre un modal con un formulario para obtener los datos del proveedor.
 *              Al confirmar, se validan los datos y se envían para crear el proveedor.
 * 
 * @param {number} proveedorID - El ID del proveedor a crear
 * 
 * @example
 * createProveedor();
 * 
 * @returns {void}
 */
export function createProveedor() {
    // Genera las listas de direcciones y de teléfonos
    let direcciones = [];
    let telefonos = [];

    // Generar el HTML para el formulario
    let html = `
        <div class="modal-form-container">
            <form id="proveedor-create-form">
                <h2>Información Básica</h2>
                <div class="proveedor-info">
                    <!-- Información básica del proveedor -->
                    <div class="proveedor-info basic-info">
                        <div class="proveedor-info input-select basic">
                            <label>Nombre del Proveedor:</label>
                            <input type="text" id="nombre" required>
                        </div>
                        <div class="proveedor-info input-select basic">
                            <label>Email:</label>
                            <input type="email" id="email" required>
                        </div>
                        <div class="proveedor-info input-select basic">
                            <label>Categoría:</label>
                            <select id="categoria-select" required>
                                <option value="">--Seleccionar--</option>
                            </select>
                        </div>
                    </div>
                </div>
                <h2>Direcciones del Proveedor</h2>
                <div class="proveedor-info address-table">
                    <!-- Tabla 'Direcciones' -->
                    <div class="records table-responsive">
                        <div class="table-container">
                            <div class="table-header">
                                <!-- Opciones de ordenamiento -->
                                <div class="paginationSort proveedor">
                                    <span>Ordenar por:</span>
                                    <select id="direccion-sort-selector">
                                        <option value="Provincia">Provincia</option>
                                        <option value="Canton">Cant&oacute;n</option>
                                        <option value="Distrito">Distrito</option>
                                        <option value="Barrio">Barrio</option>
                                        <option value="Sennas">Se&ntilde;as</option>
                                        <option value="Distancia">Distancia</option>
                                    </select>
                                </div>

                                <!-- Barra de busqueda -->
                                <div class="search-bar proveedor">
                                    <input type="text" id="direccion-search-input" placeholder="Buscar por provincia, cant&oacute;n, distrito, etc...">
                                    <button class="search-button" id="direccion-search-button">
                                        <span class="las la-search"></span>
                                    </button>
                                </div>

                                <!-- Botón para crear nueva Direccion -->
                                <button id="btn-create-dir" type="button" class="createButton">A&ntilde;adir</button>
                            </div>

                            <!-- Tabla de direcciones -->
                            <table class="tables-proveedor" id="table-direcciones" width="100%">
                                <thead>
                                    <tr>
                                        <th data-field="provincia">Provincia</th>
                                        <th data-field="canton">Cant&oacute;n</th>
                                        <th data-field="distrito">Distrito</th>
                                        <th data-field="barrio">Barrio</th>
                                        <th data-field="sennas">Se&ntilde;as</th>
                                        <th data-field="distancia">Distancia (Km)</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table-direcciones-body">
                                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                                    <tr>
                                        <td colspan="7" class="nodata">
                                            <i class="las la-exclamation-circle"></i>
                                            <p>Este proveedor no poesee ninguna direcci&oacute;n</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <h2>Tel&eacute;fonos del Proveedor</h2>
                <div class="proveedor-info address-table">
                    <!-- Tabla 'Teléfonos' -->
                    <div class="records table-responsive">
                        <div class="table-container">
                            <div class="table-header">
                                <!-- Opciones de ordenamiento -->
                                <div class="paginationSort proveedor">
                                    <span>Ordenar por:</span>
                                    <select id="telefono-sort-selector">
                                        <option value="Tipo">Tipo</option>
                                        <option value="CodigoPais">C&oacute;digo de Pa&iacute;s</option>
                                        <option value="Numero">N&uacute;mero de Tel&eacute;fono</option>
                                    </select>
                                </div>

                                <!-- Barra de busqueda -->
                                <div class="search-bar proveedor">
                                    <input type="text" id="telefono-search-input" placeholder="Buscar por tipo, c&oacute;digo de pa&iacute;s o n&uacute;mero">
                                    <button class="search-button" id="telefono-search-button">
                                        <span class="las la-search"></span>
                                    </button>
                                </div>

                                <!-- Botón para crear nueva Telefono -->
                                <button id="btn-create-tel" type="button" class="createButton">A&ntilde;adir</button>
                            </div>

                            <!-- Tabla de telefonos -->
                            <table class="tables-proveedor" id="table-telefonos" width="100%">
                                <thead>
                                    <tr>
                                        <th data-field="tipo">Tipo</th>
                                        <th data-field="codigopais">C&oacute;digo de Pa&iacute;s</th>
                                        <th data-field="numero">N&uacute;mero</th>
                                        <th data-field="extension">Extensi&oacute;n</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table-telefonos-body">
                                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                                    <tr>
                                        <td colspan="5" class="nodata">
                                            <i class="las la-exclamation-circle"></i>
                                            <p>Este proveedor no poesee ning&uacute;n tel&eacute;fono</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Crear proveedor",
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
        preConfirm: async () => {
            const form = document.getElementById('proveedor-create-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }
    
            // Obtener datos del formulario si es válido
            const formData = new FormData();
            formData.append("accion"      , "insertar");
            formData.append("nombre"      , document.getElementById('nombre').value);
            formData.append("email"       , document.getElementById('email').value);
            formData.append("categoria"   , document.getElementById('categoria-select').value);
            formData.append("direcciones" , JSON.stringify(direcciones));
            formData.append("telefonos"   , JSON.stringify(telefonos));
    
            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            insertProveedor(formData);
        }
    });

    // Evitar que el formulario se envíe al presionar Enter o al hacer click en cualquier botón
    const form = document.getElementById('proveedor-create-form');
    form.addEventListener('submit', (e) => e.preventDefault());

    // Inicializar las tablas de direcciones y teléfonos
    initializeDireccionTable(direcciones, 'table-direcciones-body');
    initializeTelefonoTable(telefonos, 'table-telefonos-body');

    // Cargar las listas de categorías
    initializeSelects();
}

/**
 * Inicializa la tabla de direcciones con los datos proporcionados y asigna eventos de búsqueda y ordenamiento.
 * 
 * @param {Array} direcciones - El arreglo de direcciones a renderizar.
 * @param {string} tableBodyID - El ID del cuerpo de la tabla donde se renderizarán las direcciones.
 * @param {boolean} [isInfo=false] - Indica si la tabla es solo para visualización (sin acciones).
 * 
 * @returns {void}
 */
function initializeDireccionTable(direcciones, tableBodyID, isInfo = false) {
    // Renderizar la tabla de direcciones
    dir.renderTable(direcciones, tableBodyID, isInfo);
    dir.sortDirecciones('Provincia', tableBodyID, isInfo);

    // Asignar eventos de búsqueda y ordenamiento
    const assignEvent = (elementId, eventType, handler) => {
        const element = document.getElementById(elementId);
        if (element) element.addEventListener(eventType, handler);
    };

    assignEvent('direccion-search-button', 'click', () => dir.searchDirecciones('direccion-search-input', tableBodyID, isInfo));
    manejarInputConEnter('direccion-search-input', 'direccion-search-button');
    assignEvent('direccion-sort-selector', 'change', () => dir.sortDirecciones(document.getElementById('direccion-sort-selector').value, tableBodyID, isInfo));
}

/**
 * Inicializa la tabla de teléfonos con los datos proporcionados y asigna eventos de búsqueda y ordenamiento.
 * 
 * @param {Array} telefonos - El arreglo de teléfonos a renderizar.
 * @param {string} tableBodyID - El ID del cuerpo de la tabla donde se renderizarán los teléfonos.
 * @param {boolean} [isInfo=false] - Indica si la tabla es solo para visualización (sin acciones).
 * 
 * @returns {void}
 */
function initializeTelefonoTable(telefonos, tableBodyID, isInfo = false) {
    // Renderizar la tabla de teléfonos
    tel.renderTable(telefonos, tableBodyID, isInfo);
    tel.sortTelefonos('Tipo', tableBodyID, isInfo);

    // Asignar eventos de búsqueda y ordenamiento
    const assignEvent = (elementId, eventType, handler) => {
        const element = document.getElementById(elementId);
        if (element) element.addEventListener(eventType, handler);
    };

    assignEvent('telefono-search-button', 'click', () => tel.searchTelefonos('telefono-search-input', tableBodyID, isInfo));
    manejarInputConEnter('telefono-search-input', 'telefono-search-button');
    assignEvent('telefono-sort-selector', 'change', () => tel.sortTelefonos(document.getElementById('telefono-sort-selector').value, tableBodyID, isInfo));
}