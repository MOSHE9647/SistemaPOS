<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Teléfonos de Proveedores | SistemaPOS</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>

    <h2>Lista de Teléfonos de Proveedores</h2>

    <div id="message"></div>

    <!-- Botón para crear nuevo teléfono -->
    <button id="createButton" onclick="showCreateRow()">Crear</button>

    <table>
        <thead>
            <tr>
                <th data-field="proveedorid">ID Proveedor</th>
                <th data-field="telefono">Teléfono</th>
                <th data-field="activo">Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php
                $proveedorTelefonoBusiness = new ProveedorTelefonoBusiness();
                $result = $proveedorTelefonoBusiness->getAllProveedorTelefono();

                if ($result["success"]) {
                    $listaTelefonos = $result["listaTelefonos"];

                    foreach ($listaTelefonos as $current) {
                        echo '<tr data-id="' . $current->getProveedorTelefonoID() . '">';
                        echo '<td data-field="proveedorid">' . $current->getProveedorID() . '</td>';
                        echo '<td data-field="telefono">' . $current->getTelefono() . '</td>';
                        echo '<td data-field="activo">' . ($current->getActivo() ? 'Sí' : 'No') . '</td>';
                        echo '<td>';
                        echo '<button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>';
                        echo '<button onclick="deleteRow(' . $current->getProveedorTelefonoID() . ')">Eliminar</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr> <td colspan="4"> <p style="color: red; text-align: center;">' . $result["message"] . '</p> </td> </tr>';
                }
            ?>
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

    <a href="../index.php" class="menu-button">Regresar al Menú</a>
    <script src="./js/proveedorTelefono.js"></script>
</body>
</html>
