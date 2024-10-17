import { deleteRol } from './crud.js';
import * as pagination from './pagination.js';
import * as gui from './gui.js';

// Función para limpiar las referencias globales
function limpiarGlobales() {
    delete window.deleteRol;
    delete window.pagination;
    delete window.gui;
}

// Función para cargar la vista de roles
export function cargarRoles() {
    // Limpia funciones globales anteriores
    limpiarGlobales();

    // Asignar nuevas funciones a la ventana global
    window.deleteRol = deleteRol;
    window.pagination = pagination;
    window.gui = gui;

    // Agregar evento de cambio al selector de tamaño de página
    document.getElementById('rol-page-size-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSize con el nuevo tamaño de página seleccionado
        pagination.changePageSize(event.target.value);
    });

    // Agregar evento de cambio al selector de orden
    document.getElementById('rol-sort-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSort con el nuevo orden seleccionado
        pagination.changePageSort(event.target.value);
    });

    // Llamada inicial para cargar la primera página
    pagination.fetchRoles(1, 5, 'nombre');
}