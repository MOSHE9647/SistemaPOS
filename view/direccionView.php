<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Direcciones | POSFusion</title>
        <link rel="stylesheet" href="./css/styles.css">
        <!-- Toastr -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    </head>
    <body>
        <h2>Lista de Direcciones</h2>

        <div id="message"></div>

        <div class="table-container">
            <div class="table-header">
                <div id="paginationSort">
                    Ordenar por:
                    <select id="sortSelector">
                        <option value="provincia">Provincia</option>
                        <option value="canton">Canton</option>
                        <option value="distrito">Distrito</option>
                        <option value="barrio">Barrio</option>
                        <option value="distancia">Distancia</option>
                    </select>
                </div>

                <!-- Botón para crear nueva dirección -->
                <button id="createButton" onclick="showCreateRow()">Crear</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th data-field="provincia">Provincia</th>
                        <th data-field="canton">Cantón</th>
                        <th data-field="distrito">Distrito</th>
                        <th data-field="barrio">Barrio</th>
                        <th data-field="senas">Señas</th>
                        <th data-field="distancia">Distancia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan = "7" style = "textalign: center; height: 50px;">
                            No hay registros disponibles
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="pagination-container">
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
        <script src="./js/utils.js"></script>                   <!-- Utiles para mostrar notificaciones y demás           -->
        <script src="./js/direccion/gui.js"></script>           <!-- Manejo dinámico de la página                         -->
        <script src="./js/direccion/selects.js"></script>       <!-- Carga de Provincias, Cantones y Distritos            -->
        <script src="./js/direccion/pagination.js"></script>    <!-- Métodos para Paginación                              -->
        <script src="./js/direccion/crud.js"></script>          <!-- Creación, Actualización y Eliminación de Direcciones -->
    </body>
</html>
