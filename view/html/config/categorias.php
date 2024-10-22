<!-- Titulo de Bienvenida -->
<div class="page-header">
    <div class="page-title">
        <h1>Gesti&oacute;n de <span>Categorias</span>:</h1>
        <small>Inicio <span>/</span> CRUD&apos;s <span>/</span> Categorias</small>
    </div>
</div>
<hr>

<!-- Contenido de la pagina -->
<div class="page-content table-card">
    <!-- Tabla 'Categorias' -->
    <div class="records table-responsive">
        <div class="table-container">
            <!-- Tabla 'Categorias' -->
            <div class="table-header">
                <!-- Opciones de ordenamiento -->
                <div class="paginationSort">
                    <span>Ordenar por:</span>
                    <select id="categoria-sort-selector">
                        <option value="nombre">Nombre</option>
                        <option value="descripcion">Descripcion</option>
                    </select>
                </div>
            
                <!-- Botón para crear nueva Categoria -->
                <button type="button" onclick="gui.createCategoria()" class="createButton">Crear</button>
            </div>
            
            <!-- Tabla de Categorias -->
            <table id="table-categorias" width="100%">
                <thead>
                    <tr>
                        <th data-field="nombre">Nombre</th>
                        <th data-field="descripcion">Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="table-categorias-body">
                    <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                    <tr>
                        <td colspan="3" class="nodata">
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
                    <select id="categoria-page-size-selector">
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