// *************************************************************************************************************** //
// ************* Métodos para obtener las listas de Proveedores, Categorias de Teléfono y Códigos de País ************* //
// *************************************************************************************************************** //

/**
 * Carga la lista de categorias de teléfono desde un archivo JSON.
 * 
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de categorias de teléfono.
 * 
 * @example
 * loadCategorias().then(data => {
 *   console.log(data);
 * });
 */
async function loadCategorias() {
    try {
        const response = await fetch('../controller/categoriaAction.php');
        return await response.json();
    } catch (error) {
        // Muestra el mensaje de error detallado
        showMessage(`Ocurrió un error al obtener las categorias.<br>${error}`, 'error');
        return {};
    }
}

/**
 * Carga la lista de códigos de país desde un archivo JSON.
 * 
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de códigos de país.
 * 
 * @example
 * loadSubcategorias().then(data => {
 *   console.log(data);
 * });
 */
async function loadSubcategorias(categoriaID) {
    try {
        const response = await fetch(`../controller/subcategoriaAction.php?accion=subcategoria-categoria&categoria=${categoriaID}`);
        return await response.json();
    } catch (error) {
        // Muestra el mensaje de error detallado
        showMessage(`Ocurrió un error al obtener la lista de subcategorias.<br>${error}`, 'error');
        return {};
    }
}

/**
 * Inicializa los select de proveedores, categorias de teléfono y códigos de país.
 */
function initializeSelects() {
    loadCategorias().then(data => {
        // Asignar los datos a una variable global
        window.dataT = data;
        loadSelectCategorias();
    });

    // Agregar un evento de cambio para cargar subcategorias
    const categoriasSelect = document.getElementById('categoria-select');
    categoriasSelect.addEventListener('change', async function() {
        const categoriaID = categoriasSelect.value;
        if (categoriaID) {
            window.dataC = await loadSubcategorias(categoriaID);
            console.log(window.dataC);
            loadSelectSubcategorias();
        }
    });
}

/**
 * Carga las opciones del select de categorias de teléfono.
 */
function loadSelectCategorias() {
    const categoriasSelect = document.getElementById('categoria-select');
    if (!categoriasSelect) console.error('No se encontró el select de categorias');
    let value = categoriasSelect.value;
    categoriasSelect.innerHTML = ''; // Limpiar opciones anteriores

    // Asegura que `window.dataT` esté disponible antes de usarlo
    if (window.dataT.listaCategorias) {
        window.dataT.listaCategorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.ID;
            option.textContent = categoria.Nombre;
            option.selected = option.value === value;
            categoriasSelect.appendChild(option);
        });
    }
}

/**
 * Carga las opciones del select de códigos de país.
 */
function loadSelectSubcategorias() {
    const subcategoriasSelect = document.getElementById('subcategoria-select');
    let value = subcategoriasSelect.value;
    subcategoriasSelect.innerHTML = ''; // Limpiar opciones anteriores

    // Asegura que `window.dataC` esté disponible antes de usarlo
    if (window.dataC.subcategorias) {
        window.dataC.subcategorias.forEach(subcategoria => {
            const option = document.createElement('option');
            option.value = subcategoria.ID;
            option.textContent = subcategoria.Nombre;
            option.selected = option.value === value;
            subcategoriasSelect.appendChild(option);
        });
    }
}