<!-- Titulo de Bienvenida -->
<div class="page-header">
    <div class="page-title">
        <h1>Administraci&oacute;n de <span>Proveedores</span>:</h1>
        <small>Inicio <span>/</span> Proveedores</small>
    </div>
</div>
<hr>

<!-- Contenido de la pagina -->
<div class="page-content table-card">
    <!-- Tabla 'Proveedores' -->
    <div class="records table-responsive">
        <div class="table-container">
            <div class="table-header">
                <!-- Opciones de ordenamiento -->
                <div class="paginationSort">
                    <span>Ordenar por:</span>
                    <select id="proveedor-sort-selector">
                        <option value="nombre">Nombre</option>
                        <option value="email">Correo</option>
                        <option value="categoria">Categor&iacute;a</option>
                        <?php if ($isAdmin): ?>
                        <option value="fechacreacion">Creaci&oacute;n</option>
                        <option value="fechamodificacion">Modificaci&oacute;n</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Barra de busqueda -->
                <div class="search-bar">
                    <input type="text" id="proveedor-search-input" placeholder="Buscar por nombre, correo o categor&iacute;a">
                    <button class="search-button" id="proveedor-search-button" onclick="pagination.searchProveedores()">
                        <span class="las la-search"></span>
                    </button>
                </div>

                <!-- Botón para crear nuevo Proveedor -->
                <button type="button" onclick="gui.createProveedor()" class="createButton">Crear</button>
            </div>

            <!-- Tabla de proveedores -->
            <table id="table-proveedores" width="100%">
                <thead>
                    <tr>
                        <th data-field="email">Correo</th>
                        <th data-field="nombre">Nombre</th>
                        <th data-field="categoria">Categor&iacute;a</th>
                        <?php if ($isAdmin): ?>
                        <th data-field="creacion">Fecha Creaci&oacute;n</th>
                        <th data-field="modificacion">&Uacute;ltima Modificaci&oacute;n</th>
                        <?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="table-proveedores-body">
                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                    <tr>
                        <?php $colspan = $isAdmin ? 5 : 3; ?>
                        <td colspan="<?= $colspan ?>" class="nodata">
                            <i class="las la-exclamation-circle"></i>
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
                    <select id="proveedor-page-size-selector">
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