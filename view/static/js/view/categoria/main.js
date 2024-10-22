import { deleteCategoria } from './crud.js';
import * as pagination from './pagination.js';
import * as gui from './gui.js';

// Función para limpiar las referencias globales
function limpiarGlobales() {
    delete window.deleteCategoria;
    delete window.pagination;
    delete window.gui;
}

// Función para cargar la vista de categorías
export function cargarCategorias() {
    // Limpia funciones globales anteriores
    limpiarGlobales();

    // Asignar nuevas funciones a la ventana global
    window.deleteCategoria = deleteCategoria;
    window.pagination = pagination;
    window.gui = gui;

    // Agregar evento de cambio al selector de tamaño de página
    document.getElementById('categoria-page-size-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSize con el nuevo tamaño de página seleccionado
        pagination.changePageSize(event.target.value);
    });

    // Agregar evento de cambio al selector de orden
    document.getElementById('categoria-sort-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSort con el nuevo orden seleccionado
        pagination.changePageSort(event.target.value);
    });

    // Llamada inicial para cargar la primera página
    pagination.fetchCategorias(1, 5, 'nombre');
}