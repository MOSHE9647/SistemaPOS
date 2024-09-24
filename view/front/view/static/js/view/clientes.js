import * as pagination from './cliente/pagination.js';
import { deleteCliente } from '../view/cliente/crud.js';
import * as gui from '../view/cliente/gui.js';
 
// Asignar funciones a la ventana global
window.deleteCliente = deleteCliente;
window.gui = gui;

// Función para cargar la vista de clientes
export async function cargarClientes() {
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

    // Llamada inicial para cargar la primera página
    pagination.fetchClientes(1, 5, 'nombre');
}