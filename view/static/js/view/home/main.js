import { agregarProducto, mostrarListaSeleccionableDeProductos } from "./gui.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { manejarInputConEnter } from "../../utils.js";
import * as tabs from "./tabs.js";

// Función para limpiar las referencias globales
function limpiarGlobales() {
    delete window.tabs;
}

export function cargarHome() {
    // Limpia funciones globales anteriores
    limpiarGlobales();

    // Asignar nuevas funciones a la ventana global
    window.tabs = tabs; //<- Asignamos el objeto tabs a la ventana global

    const addButton = document.getElementById('sales-add-button');
    if (addButton) {
        addButton.addEventListener('click', () => {
            const input = document.getElementById('sales-search-input');
            if (input && input.value) {
                agregarProducto(document.getElementById('sales-search-input').value);
                input.value = '';
            }
            else mostrarMensaje('Debe digitar un código de barras.', 'error', 'Error de búsqueda');
        });
    }
    // Darle el foco al campo de búsqueda al cargar la página
    manejarInputConEnter('sales-search-input', 'sales-add-button');

    // Darle funcionalidad al botón de búsqueda
    const searchButton = document.getElementById('sales-search-button');
    if (searchButton) {
        searchButton.addEventListener('click', () => {
            mostrarListaSeleccionableDeProductos();
        });
    } else {
        console.error('No se encontró el botón de búsqueda.');
    }

    // Darle funcionalidad a las pestañas
    const tabButton = document.querySelector('.tab-button');
    if (!tabButton) {
        console.error('No se encontró el botón de pestaña: ', tabButton);
        mostrarMensaje('No se encontró el botón de pestaña.', 'error');
    }

    // Añadir evento al botón de la pestaña
    tabButton.addEventListener('click', (evt) => {
        evt.stopPropagation();
        const tabContent = document.getElementById('tab1');
        if (!tabContent) {
            console.error('No se encontró el contenido de la pestaña: ', tabContent);
            mostrarMensaje('No se encontró el contenido de la pestaña.', 'error');
        }
        tabs.openTab(tabButton, tabContent);
    });

    tabButton.querySelector('.delete-tab').addEventListener('click', (evt) => {
        evt.stopPropagation();
        tabs.deleteTab(tabButton, document.getElementById('tab1'));
    });
}