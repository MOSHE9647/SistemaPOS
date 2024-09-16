<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Teléfonos de Proveedores | POSFusion</title>
        <link rel="stylesheet" href="./css/styles.css">
        <!-- Toastr -->
        

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
        <h2>Lista de Teléfonos según Proveedor</h2>

        <div id="message"></div>

        <div class="proveedores-container">
            <label for="proveedor-select">Seleccione un Proveedor:</label>
            <select id="proveedor-select" onchange="loadTelefonos()">
                <option value="">-- Seleccionar --</option>
                <!-- Las opciones se llenan dinámicamente con JavaScript -->
            </select>
        </div>

        <hr>

        <div class="table-container" id="telefonos-table" style="display: none;">
            <div class="table-header">
                <div id="paginationSort">
                    <label for="sortSelector">Ordenar por:</label>
                    <select id="sortSelector">
                        <option value="tipo">Tipo</option>
                        <option value="extension">Extension</option>
                        <option value="codigopais">Código de País</option>
                        <option value="numero">Número</option>
                    </select>
                </div>

                <!-- Botón para crear nuevo Teléfono -->
                <button id="createButton" onclick="showCreateRow()">Crear</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th data-field="tipo">Tipo de Número</th>
                        <th data-field="codigo">Código de País</th>
                        <th data-field="numero">Número</th>
                        <th data-field="extension">Extensión</th>
                        <th data-field="creacion">Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Las filas se llenan dinámicamente con JavaScript -->
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

        <!-- Toastr Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        <script>
            function loadTelefonos() {
                const proveedor = document.getElementById('proveedor-select').value;
                if (proveedor === '') {
                    showMessage('Debe seleccionar un Proveedor', 'error');
                    return;
                }
                
                const table = document.getElementById('telefonos-table');
                if (table) {
                    table.style.display = 'inline';
                    cancelCreate();
                    cancelEdit();
                    fetchTelefonos(proveedor, currentPage, pageSize, sort);
                } else {
                    showMessage('No se encontró la tabla de teléfonos', 'error');
                }
            }
        </script>
        
        <!-- Scripts del Archivo -->
        <script src="./js/utils.js"></script>
        <script src="./js/telefono/selects.js"></script>
        <script src="./js/proveedorTelefono/gui.js"></script>
        <script src="./js/proveedorTelefono/selects.js"></script>
        <script src="./js/proveedorTelefono/pagination.js"></script>
        <script src="./js/proveedorTelefono/crud.js"></script>
    </body>
</html>
