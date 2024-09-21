// *********************************************************************************************** //
// ************* Métodos para obtener las listas de Categorías y Subcategorías ***************** //
// *********************************************************************************************** //

/**
 * Carga datos de categorías y subcategorías desde el backend.
 * 
 * @returns {Promise<object>} Promesa que resuelve a un objeto con categorías y subcategorías.
 * 
 * @example
 * const datos = await loadCategoriasFromBackend();
 * console.log(datos); // Salida: { categorias: [...] }
 */
async function loadCategoriasFromBackend() {
    // Suponiendo que tienes un método getAll que devuelve las categorías
    const categorias = await getAllCategorias(); // Método que trae todas las categorías
    const subcategorias = await getAllSubcategorias(); // Método que trae todas las subcategorías

    // Asumimos que las subcategorías tienen un campo que indica a qué categoría pertenecen
    categorias.forEach(categoria => {
        categoria.subcategorias = subcategorias.filter(subcategoria => subcategoria.categoriaId === categoria.id);
    });

    return { categorias };
}

/**
 * Inicializa los selects de categoría y subcategoría.
 * 
 * Carga los datos de las categorías y subcategorías desde el backend
 * y asigna los datos a una variable global. Luego, añade event listeners para
 * actualizar los selects dependientes cuando se selecciona una categoría.
 * 
 * @example
 * initializeSelects();
 */
async function initializeSelects() {
    const data = await loadCategoriasFromBackend();
    window.data = data; // Asigna los datos a una variable global
    loadCategorias(); // Carga las categorías en el select

    // Añadir event listener para actualizar el select de subcategorías
    const categoriaSelect = document.getElementById('categoria-select');
    if (!categoriaSelect) {
        showMessage('No se encontró el select de categorías.', 'error');
        return;
    }
    categoriaSelect.addEventListener('change', loadSubcategorias);
}

/**
 * Carga las categorías en el select de categorías.
 * 
 * Limpia las opciones anteriores y carga las categorías desde la variable global `window.data`.
 * 
 * @example
 * loadCategorias();
 */
function loadCategorias() {
    const categoriaSelect = document.getElementById('categoria-select');
    let value = categoriaSelect.value;
    categoriaSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    if (window.data.categorias) {
        window.data.categorias.forEach(categoria => {
            const option = document.createElement('option');
            option.dataset.field = categoria.id;
            option.value = categoria.nombre;
            option.textContent = categoria.nombre;
            option.selected = option.value === value;
            categoriaSelect.appendChild(option);
        });
    }

    loadSubcategorias(); // Cargar subcategorías si hay una selección inicial
}

/**
 * Carga las subcategorías en el select de subcategorías según la categoría seleccionada.
 * 
 * Limpia las opciones anteriores y carga las subcategorías desde la variable global `window.data` según la categoría seleccionada.
 * 
 * @example
 * loadSubcategorias();
 */
function loadSubcategorias() {
    const subcategoriaSelect = document.getElementById('subcategoria-select');
    let value = subcategoriaSelect.value;
    subcategoriaSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    const categoriaSelect = document.getElementById('categoria-select');
    const categoriaIndex = (categoriaSelect.options[categoriaSelect.selectedIndex].dataset.field) - 1;

    if (categoriaIndex >= 0 && window.data.categorias[categoriaIndex]) {
        window.data.categorias[categoriaIndex].subcategorias.forEach(subcategoria => {
            const option = document.createElement('option');
            option.value = subcategoria.nombre;
            option.textContent = subcategoria.nombre;
            option.selected = option.value === value;
            subcategoriaSelect.appendChild(option);
        });
    }
}
