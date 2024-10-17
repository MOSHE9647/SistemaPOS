import { manejarInputConEnter } from '../../utils.js';
import { deleteProducto } from './crud.js';
import * as pagination from './pagination.js';
import * as gui from './gui.js';

// Función para limpiar las referencias globales
function limpiarGlobales() {
    delete window.deleteProducto;
    delete window.pagination;
    delete window.gui;
}

// Función para cargar la vista de productos
export async function cargarProductos() {
    // Limpia funciones globales anteriores
    limpiarGlobales();

    // Asignar nuevas funciones a la ventana global
    window.deleteProducto = deleteProducto; // Función para eliminar un producto
    window.pagination = pagination; // Objeto con funciones de paginación
    window.gui = gui; // Objeto con funciones de interfaz de usuario

    // Agregar evento de cambio al selector de tamaño de página
    document.getElementById('producto-page-size-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSize con el nuevo tamaño de página seleccionado
        pagination.changePageSize(event.target.value);
    });

    // Agregar evento de cambio al selector de orden
    document.getElementById('producto-sort-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSort con el nuevo orden seleccionado
        pagination.changePageSort(event.target.value);
    });

    // Inicializar el campo de búsqueda
    manejarInputConEnter('producto-search-input', 'producto-search-button');

    // Llamada inicial para cargar la primera página
    pagination.fetchProductos(1, 5, 'nombre');
}