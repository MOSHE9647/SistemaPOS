import { mostrarMensaje } from "../../gui/notification.js";

let tabCount = 1;

export function openTab(button, tab) {
    // Obtenemos todos los elementos con la clase "tab-content"
    const tabs = document.getElementsByClassName("tab-content");
    if (tabs.length === 0) { //<- Si no se encontró ninguna pestaña
        mostrarMensaje("No se encontró la lista de pestañas", "error");
        return; //<- Salimos de la función
    }
    
    // Ocultamos el contenido de todas las pestañas
    for (const tab of tabs) {
        tab.classList.remove("active");
        tab.style.display = "none";
    }

    // Obtenemos todos los elementos con la clase "tab-button"
    const tabButtons = document.getElementsByClassName("tab-button");
    if (tabButtons.length === 0) { //<- Si no se encontró ninguna pestaña
        mostrarMensaje("No se encontraron los botones de las pestañas", "error");
        return; //<- Salimos de la función
    }

    // Desactivamos todos los botones de las pestañas
    for (const button of tabButtons) {
        button.classList.remove("active");
    };

    // Obtenemos la pestaña seleccionada
    if (!tab) { //<- Si no se encontró la pestaña
        mostrarMensaje("No se encontró la pestaña seleccionada", "error");
        return; //<- Salimos de la función
    }

    // Mostramos el contenido de la pestaña seleccionada
    tab.classList.add("active");
    tab.style.display = "block";

    // Activamos el botón de la pestaña seleccionada
    button.classList.add("active");
}

export function addTab() {
    tabCount++; //<- Incrementamos el contador de pestañas

    // Mostramos el botón de cerrar en la primera pestaña
    const buttons = document.getElementsByClassName("tab-button");
    if (buttons.length === 1) { //<- Si solo hay una pestaña
        buttons[0].lastElementChild.style.display = "flex"; //<- Mostramos el botón de cerrar
    }

    // Obtenemos el contenedor de las pestañas y el de los botones
    const tabButtons = document.getElementById("tab-buttons");
    const tabContainer = document.querySelector(".tab-container");
    if (!tabButtons || !tabContainer) { //<- Si no se encontró alguno de los contenedores
        mostrarMensaje("No se encontró el contenedor de las pestañas ni sus botones", "error");
        return; //<- Salimos de la función
    }

    // Creamos el contenido de la nueva pestaña
    const newTabContent = document.createElement("div");
    newTabContent.id = `tab${tabCount}`;
    newTabContent.className = "tab-content";
    newTabContent.innerHTML = `
        <table class="table-sales" width="100%">
            <thead>
                <tr>
                    <th data-field="codigo">C&oacute;digo de Barras</th>
                    <th data-field="imagen">Imagen</th>
                    <th data-field="nombre">Nombre del Producto</th>
                    <th data-field="precio">Precio Unitario</th>
                    <th data-field="cantidad">Cantidad</th>
                    <th data-field="preciobruto">Subtotal (sin IVA)</th>
                    <th data-field="impuesto">Importe</th>
                    <th data-field="cantidad">Existencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="table-sales-body" class="table-sales-body active">
                <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                <tr>
                    <td colspan="9" class="nodata">
                        <i class="la la-box"></i>
                        <p>No se han agregado productos</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Información de la venta (Total, Subtotal, Impuestos) -->
        <div class="sales-info">
            <div class="sales-price-info">
                <div class="sales-price subtotal">
                    <span>Subtotal:</span>
                    <span id="sales-subtotal">&#162;0.00</span>
                </div>
                <div class="sales-price total">
                    <span>Total:</span>
                    <span id="sales-total">&#162;0.00</span>
                </div>
            </div>
            <div class="sales-buttons">
                <button class="sales-button" id="sales-reprint-button">
                    <span class="las la-print icon"></span>
                    <span>Reimprimir Ticket</span>
                </button>
                <button class="sales-button" id="sales-return-button">
                    <span class="las la-undo icon"></span>
                    <span>Devoluciones</span>
                </button>
                <button class="sales-button" id="sales-charge-button" onclick="gui.mostrarOpcionesDeCobro()">
                    <span class="las la-credit-card icon"></span>
                    <span>Cobrar</span>
                </button>
            </div>
        </div>
    `;
    tabContainer.insertBefore(newTabContent, tabContainer.lastElementChild);

    // Creamos el botón de la nueva pestaña
    const newTabButton = document.createElement("button");
    newTabButton.className = "tab-button";
    newTabButton.onclick = () => openTab(newTabButton, newTabContent);

    // Le añadimos el texto al botón
    const newTabText = document.createElement("span");
    newTabText.innerText = `Ticket ${tabCount}`;
    newTabButton.appendChild(newTabText);
    
    // Le añadimos botón de cerrar al botón de la pestaña
    const deleteButton = document.createElement("span");
    deleteButton.className = "las la-times";
    deleteButton.classList.add("delete-tab");
    deleteButton.onclick = (evt) => {
        evt.stopPropagation();
        deleteTab(newTabButton, newTabContent);
    }
    newTabButton.appendChild(deleteButton);

    // Añadimos el botón al contenedor de botones de pestañas
    tabButtons.insertBefore(newTabButton, tabButtons.lastElementChild);

    // Mostramos la nueva pestaña
    openTab(newTabButton, newTabContent); //<- Simulamos un evento para abrir la pestaña
}

export function deleteTab(tabButton, tabContent) {
    // Obtenemos el contenedor de las pestañas y el de los botones
    const tabButtons = document.getElementById("tab-buttons");
    const tabContainer = document.querySelector(".tab-container");
    if (!tabButtons || !tabContainer) { //<- Si no se encontró alguno de los contenedores
        mostrarMensaje("No se encontró el contenedor de las pestañas ni sus botones", "error");
        return; //<- Salimos de la función
    }

    // Eliminamos el botón de la pestaña
    tabButtons.removeChild(tabButton);

    // Eliminamos el contenido de la pestaña
    if (!tabContent) { //<- Si no se encontró el contenido de la pestaña
        mostrarMensaje("No se encontró el contenido de la pestaña", "error");
        return; //<- Salimos de la función
    }
    tabContainer.removeChild(tabContent);

    // Obtenemos el primer botón de las pestañas
    const firstTabButton = document.querySelector(".tab-button");
    if (firstTabButton) { //<- Si se encontró un botón
        if (!firstTabButton.classList.contains("active")) {
            // Mostramos la primera pestaña si no está activa
            firstTabButton.click(); //<- Simulamos un evento para abrir
        }

        // Ocultamos el botón de cerrar si solo queda una pestaña
        const buttons = document.getElementsByClassName("tab-button");
        if (buttons.length === 1) { //<- Si solo queda una pestaña
            buttons[0].lastElementChild.style.display = "none"; //<- Ocultamos el botón de cerrar
        }
    }
}