// ********************************************************************************** //
// ************* Métodos para obtener la lista de categorias para un usuario ************* //
// ********************************************************************************** //

async function loadCategorias() {
    try {
        // Enviar la solicitud GET al servidor para obtener los categorias con los datos en la URL
        const response = await fetch('../controller/categoriaAction.php?accion=listarCategorias');
        return await response.json();
    } catch (error) {
        showMessage(`Ocurrió un error al obtener la lista de categorias.<br>${error}`, 'error');
        return {};
    }
}

function initializeSelects() {
    loadCategorias().then(data => {
        // Asignar los datos a una variable global
        window.dataR = data;
        loadSelectCategorias();
    });
}

function loadSelectCategorias() {
    const categoriaSelect = document.getElementById('categoria-select');
    let value = categoriaSelect.value;
    categoriaSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    if (window.dataR.listaCategorias) {
        window.dataR.listaCategorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.ID;
            option.textContent = categoria.Nombre;
            option.selected = option.value === value;
            categoriaSelect.appendChild(option);
        });
    }
}