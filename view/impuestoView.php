<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Impuestos | POSFusion</title>
        <link rel="stylesheet" href="./css/styles.css">
        <!-- Toastr -->
        
    </head>
    <body>

        <h2>Lista de Impuestos</h2>

        <div id="message"></div>

        <div class="table-container">
            <div class="table-header">
                <div id="paginationSort">
                    Ordenar por:
                    <select id="sortSelector">
                        <option value="nombre">Nombre</option>
                        <option value="valor">Valor</option>
                        <option value="fechainiciovigencia">Inicio Vigencia</option>
                        <option value="fechafinvigencia">Fin Vigencia</option>
                    </select>
                </div>

                <!-- Botón para crear nuevo impuesto -->
                <button id="createButton" onclick="showCreateRow()">Crear</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th data-field="nombre">Nombre</th>
                        <th data-field="valor">Valor</th>
                        <th data-field="descripcion">Descripción</th>
                        <th data-field="fechaInicio">Inicio Vigencia</th>
                        <th data-field="fechaFin">Fin Vigencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan = "6" style = "textalign: center; height: 50px;">
                            No hay registros disponibles
                        </td>
                    </tr>
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
                    de <span id="totalRecords">0</span> registros
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

        <!-- Toastr Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        <!-- Scripts del Archivo -->
        <script src="./js/utils.js"></script>                  <!-- Utiles para mostrar notificaciones y demás         -->
        <script src="./js/impuesto/gui.js"></script>           <!-- Manejo dinámico de la página                       -->
        <script src="./js/impuesto/pagination.js"></script>    <!-- Métodos para Paginación                            -->
        <script src="./js/impuesto/crud.js"></script>          <!-- Creación, Actualización y Eliminación de Impuestos -->
    </body>
</html>
