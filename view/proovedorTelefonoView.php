<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Teléfonos de Proveedores | SistemaPOS</title>
    <?php 
        include __DIR__ . '/../service/proveedorTelefonoBusiness.php'; 
        require_once __DIR__ . '/../utils/Utils.php';
    ?>
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
    <a href="../index.php" class="menu-button">Regresar al Menú</a>
    <script src="./js/proveedorTelefono.js"></script>
</body>
</html>
