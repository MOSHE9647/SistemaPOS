import { manejarInputConEnter } from '../../utils.js';
import { deleteCliente } from './crud.js';
import * as pagination from './pagination.js';
import * as gui from './gui.js';

// Función para limpiar las referencias globales
function limpiarGlobales() {
    delete window.deleteCliente;
    delete window.pagination;
    delete window.gui;
}

// Función para cargar la vista de clientes
export async function cargarClientes() {
    // Limpia funciones globales anteriores
    limpiarGlobales();

    // Asignar nuevas funciones a la ventana global
    window.deleteCliente = deleteCliente;
    window.pagination = pagination;
    window.gui = gui;

    // Agregar evento de cambio al selector de tamaño de página
    document.getElementById('cliente-page-size-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSize con el nuevo tamaño de página seleccionado
        pagination.changePageSize(event.target.value);
    });

    // Agregar evento de cambio al selector de orden
    document.getElementById('cliente-sort-selector').addEventListener('change', (event) => {
        // Llamar a la función changePageSort con el nuevo orden seleccionado
        pagination.changePageSort(event.target.value);
    });

    // Inicializar el campo de búsqueda
    manejarInputConEnter('cliente-search-input', 'cliente-search-button');

    // Llamada inicial para cargar la primera página
    pagination.fetchClientes(1, 5, 'nombre');
}