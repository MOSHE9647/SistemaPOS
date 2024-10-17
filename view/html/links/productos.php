<!-- Titulo de Bienvenida -->
<div class="page-header">
    <div class="page-title">
        <h1>Administraci&oacute;n de <span>Productos</span>:</h1>
        <small>Inicio <span>/</span> Productos</small>
    </div>
</div>
<hr>

<!-- Contenido de la pagina -->
<div class="page-content table-card">
    <!-- Tabla 'Productos' -->
    <div class="records table-responsive">
        <div class="table-container">
            <div class="table-header">
                <!-- Opciones de ordenamiento -->
                <div class="paginationSort">
                    <span>Ordenar por:</span>
                    <select id="producto-sort-selector">
                        <option value="nombre">Nombre</option>
                        <option value="codigo">C&oacute;digo de Barras</option>
                        <option value="preciocompra">Precio</option>
                    </select>
                </div>

                <!-- Barra de busqueda -->
                <div class="search-bar">
                    <input type="text" id="producto-search-input" placeholder="Buscar por código de barras o nombre del producto">
                    <button class="search-button" id="producto-search-button" onclick="pagination.searchProductos()">
                        <span class="las la-search"></span>
                    </button>
                </div>

                <!-- Botón para crear nuevo Producto -->
                <button type="button" onclick="gui.createProducto()" class="createButton">Crear</button>
            </div>

            <!-- Tabla de productos -->
            <table id="table-productos" width="100%">
                <thead>
                    <tr>
                        <th>C&oacute;digo de Barras</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Categor&iacute;a</th>
                        <th>Subcategor&iacute;a</th>
                        <th>Marca</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="table-productos-body">
                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                    <tr>
                        <td colspan="9" class="nodata">
                            <i class="la la-box"></i>
                            <p>No hay registros disponibles</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginación de la tabla -->
            <div class="pagination-container">
                <!-- Selector de tamaño de página -->
                <div class="pagination-size">
                    <span>Mostrando:</span>
                    <select id="producto-page-size-selector">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    de <span id="totalRecords">0</span> registros
                </div>

                <!-- Controles de paginación -->
                <div class="pagination-controls">
                    <button id="prevPage" onclick="pagination.changePage(currentPage - 1)">
                        <span class="las la-arrow-left"></span>
                    </button>
                    <span class="pageInfo">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
                    <button id="nextPage" onclick="pagination.changePage(currentPage + 1)">
                        <span class="las la-arrow-right"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
