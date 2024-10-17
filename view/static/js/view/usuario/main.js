import { manejarInputConEnter } from '../../utils.js';
import { deleteUsuario } from './crud.js';
import * as pagination from './pagination.js';
import * as gui from './gui.js';

// Función para limpiar las referencias globales
function limpiarGlobales() {
    delete window.deleteUsuario;
    delete window.pagination;
    delete window.gui;
}

// Función para cargar la vista de usuarios
export async function cargarUsuarios() {
    // Limpia funciones globales anteriores
    limpiarGlobales();

    // Asignar nuevas funciones a la ventana global
    window.deleteUsuario = deleteUsuario;
    window.pagination = pagination;
    window.gui = gui;

    // Agregar evento de cambio al selector de tamaño de página
    document.getElementById('usuario-page-size-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSize con el nuevo tamaño de página seleccionado
        pagination.changePageSize(event.target.value);
    });

    // Agregar evento de cambio al selector de orden
    document.getElementById('usuario-sort-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSort con el nuevo orden seleccionado
        pagination.changePageSort(event.target.value);
    });

    // Inicializar el campo de búsqueda
    manejarInputConEnter('usuario-search-input', 'usuario-search-button');

    // Llamada inicial para cargar la primera página
    pagination.fetchUsuarios(1, 5, 'email');
}