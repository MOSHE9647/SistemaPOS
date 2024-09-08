<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Direcciones de Proveedores | POSFusion</title>
        <link rel="stylesheet" href="./css/styles.css">

        <style>
            .proveedores-container {
                margin-bottom: 20px;
            }

            .proveedores-container label {
                margin-right: 10px;
            }

            #proveedor-select {
                width: 320px;
                height: 30px;
                margin: 5px 10px;
                background-color: #ffffd6;
                border: 1px solid #545454;
                border-radius: 5px;
                padding: 5px;
            }

            hr {
                width: 80%;
                border: 1px solid #7f848e;
                border-radius: 5px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <h2>Lista de Direcciones según Proveedor</h2>

        <div id="message"></div>

        <div class="proveedores-container">
            <label for="proveedor-select">Seleccione un Proveedor:</label>
            <select id="proveedor-select" onchange="loadDirecciones()">
                <option value="">-- Seleccionar --</option>
                <!-- Las opciones se llenan dinámicamente con JavaScript -->
            </select>
        </div>

        <hr>

        <div class="table-container" id="direcciones-table" style="display: none;">
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
                    <!-- Las filas se llenan dinámicamente con JavaScript -->
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

        <script>
            function loadDirecciones() {
                const proveedor = document.getElementById('proveedor-select').value;
                const proveedorID = parseInt(proveedor);
                if (proveedorID === '') {
                    showMessage('Debe seleccionar un Proveedor', 'error');
                    return;
                }
                
                const table = document.getElementById('direcciones-table');
                if (table) {
                    table.style.display = 'inline';
                    cancelCreate();
                    cancelEdit();
                    fetchDirecciones(proveedorID, currentPage, pageSize, sort);
                } else {
                    showMessage('No se encontró la tabla de teléfonos', 'error');
                }
            }
        </script>

        <script src="./js/utils.js"></script>                   <!-- Utiles para mostrar notificaciones y demás           -->
        <script src="./js/direccion/selects.js"></script>       <!-- Carga de Provincias, Cantones y Distritos            -->
        <script src="./js/proveedorDireccion/gui.js"></script>
        <script src="./js/proveedorDireccion/selects.js"></script>
        <script src="./js/proveedorDireccion/pagination.js"></script>
        <script src="./js/proveedorDireccion/crud.js"></script>

    </body>
</html>
