<!-- Titulo de Bienvenida -->
<div class="page-header">
    <div class="page-title">
        <h1>Administraci&oacute;n de <span>Usuarios</span>:</h1>
        <small>Inicio <span>/</span> Usuarios</small>
    </div>
</div>
<hr>

<!-- Contenido de la pagina -->
<div class="page-content table-card">
    <!-- Tabla 'Usuarios' -->
    <div class="records table-responsive">
        <div class="table-container">
            <div class="table-header">
                <!-- Opciones de ordenamiento -->
                <div class="paginationSort">
                    <span>Ordenar por:</span>
                    <select id="usuario-sort-selector">
                        <option value="email">Correo</option>
                        <option value="rol">Rol</option>
                        <option value="nombre">Nombre</option>
                        <option value="apellido1">Prim. Apellido</option>
                        <option value="apellido2">Seg. Apellido</option>
                        <?php if ($isAdmin): ?>
                        <option value="fechacreacion">Creaci&oacute;n</option>
                        <option value="fechamodificacion">Modificaci&oacute;n</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Barra de busqueda -->
                <div class="search-bar">
                    <input type="text" id="usuario-search-input" placeholder="Buscar por nombre, apellidos, correo o rol">
                    <button class="search-button" id="usuario-search-button" onclick="pagination.searchUsuarios()">
                        <span class="las la-search"></span>
                    </button>
                </div>

                <!-- Botón para crear nuevo Usuario -->
                <button type="button" onclick="gui.createUsuario()" class="createButton">Crear</button>
            </div>

            <!-- Tabla de usuarios -->
            <table id="table-usuarios" width="100%">
                <thead>
                    <tr>
                        <th data-field="correo">Correo</th>
                        <th data-field="nombre">Nombre</th>
                        <th data-field="apellido1">Prim. Apellido</th>
                        <th data-field="apellido2">Seg. Apellido</th>
                        <th data-field="rol">Rol</th>
                        <?php if ($isAdmin): ?>
                        <th data-field="creacion">Fecha Creaci&oacute;n</th>
                        <th data-field="modificacion">&Uacute;ltima Modificaci&oacute;n</th>
                        <?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="table-usuarios-body">
                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                    <tr>
                        <?php $colspan = $isAdmin ? 8 : 6; ?>
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
                    <select id="usuario-page-size-selector">
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