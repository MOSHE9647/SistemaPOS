// *********************************************************************************************** //
// ************* Métodos para obtener las listas de Provincias, Cantones y Distritos ************* //
// *********************************************************************************************** //

/**
 * Carga la lista de proveedores desde el servidor.
 * 
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de proveedores.
 * 
 * @example
 * loadProveedores().then(data => {
 *   console.log(data);
 * });
 */
async function loadProveedores() {
    try {
        // Enviar la solicitud GET al servidor para obtener los proveedores con los datos en la URL
        const response = await fetch('../controller/proveedorAction.php?accion=listarProveedores');
        return await response.json();
    } catch (error) {
        // Muestra el mensaje de error detallado
        showMessage(`Ocurrió un error al obtener la lista de proveedores.<br>${error}`, 'error');
        return {};
    }
}

loadProveedores().then(data => {
    // Verificar si la respuesta fue exitosa
    if (data.success) {
        // Asignar los datos a una variable global
        window.dataP = data;
    } else {
        // Muestra el mensaje de error específico enviado desde el servidor
        showMessage(data.message, 'error');
        window.dataP = {};
    }
    loadSelectProveedores();
});

/**
 * Carga las opciones del select de proveedores.
 */
function loadSelectProveedores() {
    const proveedoresSelect = document.getElementById('proveedor-select');
    let value = proveedoresSelect.value;
    proveedoresSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    // Asegura que `window.dataP` esté disponible antes de usarlo
    if (window.dataP.listaProveedores) {
        window.dataP.listaProveedores.forEach(proveedor => {
            const option = document.createElement('option');
            option.value = proveedor.ID;
            option.textContent = proveedor.Nombre;
            option.selected = option.value === value;
            proveedoresSelect.appendChild(option);
        });
    }
}