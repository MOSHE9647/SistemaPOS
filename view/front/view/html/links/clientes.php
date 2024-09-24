<!-- Titulo de Bienvenida -->
<div class="page-header">
    <div class="page-title">
        <h1>Administración de <span>Clientes</span>:</h1>
        <small>Inicio <span>/</span> Clientes</small>
    </div>
</div>
<hr>

<!-- Contenido de la pagina -->
<div class="page-content table-card">
    <!-- Tabla 'Citas' -->
    <div class="records table-responsive">
        <div class="table-container">
            <div class="table-header">
                <!-- Opciones de ordenamiento -->
                <div class="paginationSort">
                    <span>Ordenar por:</span>
                    <select id="cliente-sort-selector">
                        <option value="nombre">Nombre</option>
                        <option value="telefono">N&deg; Tel&eacute;fono</option>
                        <?php if ($isAdmin): ?>
                        <option value="fechacreacion">Creaci&oacute;n</option>
                        <option value="fechamodificacion">Modificaci&oacute;n</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Barra de busqueda -->
                <div class="search-bar">
                    <input type="text" id="cliente-search-input" placeholder="Buscar por nombre o teléfono">
                    <button class="search-button" onclick="searchRecords()">
                        <span class="las la-search"></span>
                    </button>
                </div>

                <!-- Botón para crear nuevo Teléfono -->
                <button type="button" onclick="gui.createCliente()" class="createButton">Crear</button>
            </div>

            <!-- Tabla de clientes -->
            <table id="table-clientes" width="100%">
                <thead>
                    <tr>
                        <th data-field="nombre">Nombre</th>
                        <th data-field="telefono">Tel&eacute;fono</th>
                        <th data-field="telefonotipo">Tipo Tel&eacute;fono</th>
                        <?php if ($isAdmin): ?>
                        <th data-field="creacion">Fecha Creaci&oacute;n</th>
                        <th data-field="modificacion">&Uacute;ltima Modificaci&oacute;n</th>
                        <?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="table-clientes-body">
                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                    <tr>
                        <?php $colspan = $isAdmin ? 6 : 4; ?>
                        <td colspan="<?= $colspan ?>" class="nodata">
                            <i class="la la-user-times"></i>
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
                    <select id="cliente-page-size-selector">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    de <span id="totalRecords">0</span> registros
                </div>

                <!-- Controles de paginación -->
                <div class="pagination-controls">
                    <button id="prevPage" onclick="changePage(currentPage - 1)">
                        <span class="las la-arrow-left"></span>
                    </button>
                    <span class="pageInfo">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
                    <button id="nextPage" onclick="changePage(currentPage + 1)">
                        <span class="las la-arrow-right"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>