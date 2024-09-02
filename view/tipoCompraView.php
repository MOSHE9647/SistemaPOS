<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tipos de Compra | POSFusion</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>

    <h2>Lista de Tipos de Compra</h2>

    <div id="message"></div>

    <div class="table-container">
        <div class="table-header">
            <div id="paginationSort">
                Ordenar por:
                <select id="sortSelector">
                    <option value="tipoCompraNombre">Nombre</option>
                    <option value="tipoCompraTasaInteres">Tasa de Interés</option>
                    <option value="tipoCompraPlazos">Plazos</option>
                    <option value="tipoCompraMeses">Meses</option>
                    <option value="tipoCompraFechaCreacion">Fecha de Creación</option>
                    <option value="tipoCompraFechaModificacion">Fecha de Modificación</option>
                </select>
            </div>

            <!-- Botón para crear un nuevo tipo de compra -->
            <button id="createButton" onclick="showCreateRow()">Crear</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th data-field="tipoCompraNombre">Nombre</th>
                    <th data-field="tipoCompraDescripcion">Descripción</th>
                    <th data-field="tipoCompraTasaInteres">Tasa de Interés</th>
                    <th data-field="tipoCompraPlazos">Plazos</th>
                    <th data-field="tipoCompraMeses">Meses</th>
                    <th data-field="tipoCompraEstado">Estado</th>
                    <th data-field="tipoCompraFechaCreacion">Fecha de Creación</th>
                    <th data-field="tipoCompraFechaModificacion">Fecha de Modificación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Las filas se llenarán con JavaScript -->
            </tbody>
        </table>

        <div class="pagination-container">
            <!-- Selector de tamaño de página -->
            <div id="paginationSize">
                Mostrando:
                <select id="pageSizeSelector">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
                de <span id="totalRecords"></span> registros
            </div>

            <!-- Controles de paginación -->
            <div id="paginationControls">
                <button id="prevPage" onclick="changePage(currentPage - 1)">Anterior</button>
                <span id="pageInfo">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
                <button id="nextPage" onclick="changePage(currentPage + 1)">Siguiente</button>
            </div>
        </div>
    </div>

    <a href="../index.php" class="menu-button">Regresar al Menú</a>
    <script src="./js/utils.js"></script>                 <!-- Utiles para mostrar notificaciones y demás         -->
    <script src="./js/tipoCompra/gui.js"></script>        <!-- Manejo dinámico de la página                       -->
    <script src="./js/tipoCompra/pagination.js"></script> <!-- Métodos para Paginación                            -->
    <script src="./js/tipoCompra/crud.js"></script>       <!-- Creación, Actualización y Eliminación de Tipos de Compra -->
</body>
</html>
