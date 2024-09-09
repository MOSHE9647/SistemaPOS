// ********************************************************************** //
// ************* Métodos para obtener la lista de Productos ************* //
// ********************************************************************** //

/**
 * Carga la lista de productos desde el servidor.
 * 
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de productos.
 * 
 * @example
 * loadProductos().then(data => {
 *   console.log(data);
 * });
 */
async function loadProductos() {
    try {
        // Enviar la solicitud GET al servidor para obtener los productos con los datos en la URL
        const response = await fetch('../controller/productoAction.php?accion=listarProductos');
        return await response.json();
    } catch (error) {
        // Muestra el mensaje de error detallado
        showMessage(`Ocurrió un error al obtener la lista de productos.<br>${error}`, 'error');
        return {};
    }
}

loadProductos().then(data => {
    // Verificar si la respuesta fue exitosa
    if (data.success) {
        // Asignar los datos a una variable global
        window.dataP = data;
    } else {
        // Muestra el mensaje de error específico enviado desde el servidor
        showMessage(data.message, 'error');
        window.dataP = {};
    }
    loadSelectProductos();
});

/**
 * Carga las opciones del select de productos.
 */
function loadSelectProductos() {
    const productosSelect = document.getElementById('producto-select');
    let value = productosSelect.value;
    productosSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    // Asegura que `window.dataP` esté disponible antes de usarlo
    if (window.dataP.listaProductos) {
        window.dataP.listaProductos.forEach(producto => {
            const option = document.createElement('option');
            option.value = producto.ID;
            option.textContent = producto.Nombre;
            option.selected = option.value === value;
            productosSelect.appendChild(option);
        });
    }
}