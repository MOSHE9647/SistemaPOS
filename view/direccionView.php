<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Direcciones | SistemaPOS</title>
    <?php 
        include __DIR__ . '/../service/direccionBusiness.php'; 
        require_once __DIR__ . '/../utils/Utils.php';
    ?>
    <link rel="stylesheet" href="./css/direccion.css">
</head>
<body>

    <h2>Lista de Direcciones</h2>

    <div id="message"></div>

    <!-- Botón para crear nueva dirección -->
    <button id="createButton" onclick="showCreateRow()">Crear</button>

    <table>
        <thead>
            <tr>
                <th data-field="provincia">Provincia</th>
                <th data-field="canton">Cantón</th>
                <th data-field="distrito">Distrito</th>
                <th data-field="barrio">Barrio</th>
                <th data-field="senas">Señas</th>
                <th data-field="distancia">Distancia</th>
                <th data-field="estado">Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tableBody">ç
            ç
            <?php
                $direccionBusiness = new DireccionBusiness();
                $result = $direccionBusiness->getAllDirecciones();

                if ($result["success"]) {
                    $listaDirecciones = $result["listaDirecciones"];

                    foreach ($listaDirecciones as $current) {
                        echo '<tr data-id="' . $current->getDireccionID() . '">';
                        echo '<td data-field="provincia">' . htmlspecialchars($current->getDireccionProvincia()) . '</td>';
                        echo '<td data-field="canton">' . htmlspecialchars($current->getDireccionCanton()) . '</td>';
                        echo '<td data-field="distrito">' . htmlspecialchars($current->getDireccionDistrito()) . '</td>';
                        echo '<td data-field="barrio">' . htmlspecialchars($current->getDireccionBarrio()) . '</td>';
                        echo '<td data-field="senas">' . htmlspecialchars($current->getDireccionSennas()) . '</td>';
                        echo '<td data-field="distancia">' . htmlspecialchars($current->getDireccionDistancia()) . '</td>';
                        echo '<td data-field="estado">' . htmlspecialchars($current->getDireccionEstado()) . '</td>';
                        echo '<td>';
                        echo '<button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>';
                        echo '<button onclick="deleteRow(' . $current->getDireccionID() . ')">Eliminar</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8"><p style="color: red; text-align: center;">' . htmlspecialchars($result["message"]) . '</p></td></tr>';
                }
            ?>
        </tbody>
    </table>
    <div id="paginationControls">
    <button id="prevPage" onclick="changePage(currentPage - 1)">Anterior</button>
    <span id="pageInfo">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
    <button id="nextPage" onclick="changePage(currentPage + 1)">Siguiente</button>
</div>

    <a href="../index.php" class="menu-button">Regresar al Menú</a>
    <script src="./js/direccion.js"></script>
</body>
</html>
