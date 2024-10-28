import { mostrarMensaje } from "../../gui/notification.js";
import { manejarInputConEnter } from "../../utils.js";
import { agregarProducto } from "./crud.js";

export function cargarHome() {
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

}