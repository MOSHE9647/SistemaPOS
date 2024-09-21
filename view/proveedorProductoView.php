<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedor-Producto | POSFusion</title>
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
    <h2>Proveedor-Producto</h2>

    <div id="message"></div>
    <div class="proveedor-conteiner">
        <label for="proveedor-select">Seleccione un proveedor: </label>
        <select id="proveedor-select" onchange="cargarProductos()">
            <option value = "">...Selecionar...</option>
        </select>
    <div>
    <hr>
    <!-- Tablas -->
    <div class="table-container" id="producto-table" style="display: none">
        <div class="table-header">
            <div id="paginatioSort">
                Ordenar por:
                <select id="sortSelector">
                    <option value="nombre">Nombre</option>
                    <option value="preciocompra">Precio</option>
                    <option value="descripcion">Descripción</option>
                </select>
            </div>
            <button id="createButton" onclick="showCreateRow()">Crear</button>
        </div>

        <table>
            <thead>
                <th data-field="nombre">Nombre</th>
                <th data-field="precioCompra">Precio de Compra</th>
                <th data-field="ganancia">Porcentaje de Ganancia</th>
                <th data-field="descripcion">Descripción</th>
                <th data-field="codigoBarrasID">Código de Barras</th>
                <th data-field="foto">Imagen</th>
                <th>Acciones</th>
            </thead>
            <tbody id="tableBody">
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
                <button id="prevPage" onclick="cambiarTamanoPagina(currentPage - 1)">Anterior</button>
                <span id="pageInfo">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
                <button id="nextPage" onclick="cambiarTamanoPagina(currentPage + 1)">Siguiente</button>
            </div>
        </div>
    </div>
            <script>
                function cargarProductos(){
                   
                    const proveedor = document.getElementById('proveedor-select').value;
                    const ID = parseInt(proveedor);
                    if( isNaN(ID)) {
                        showMessage('Debe seleccionar un proveedor', 'error');
                        return;
                    }

                    const table = document.getElementById('producto-table');
                    if(table){
                        table.style.display = 'inline';
                        fetchProductos(ID, currentPage, pageSize, sort);
                        // cancelCreate();
                        // cancelEditProducto();
                    }else{
                        showMessage('No se encontró la tabla productos', 'error');
                    }
                }
            </script>


            <a href="../index.php" class="menu-button">Regresar al Menú</a>
            
            <!-- Scripts -->
            <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>



            <script src="./js/utils.js"></script> 
            <script src="./js/proveedorProducto/selects.js"></script>   
            <script src="./js/proveedorProducto/pagination.js"></script>
            <script src="./js/proveedorProducto/crud.js"></script>
            <script src="./js/proveedorProducto/gui.js"></script>

    </body>
</html>
