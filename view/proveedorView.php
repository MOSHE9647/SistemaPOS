<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Proveedores | SistemaPOS</title>
        <?php 
            include __DIR__ . '/../service/proveedorBusiness.php'; 
            require_once __DIR__ . '/../utils/Utils.php';
        ?>
        <link rel="stylesheet" href="./css/styles.css">
    </head>
    <body>

        <h2>Lista de Proveedores</h2>

        <div id="message"></div>

        <!-- Botón para crear nuevo proveedor -->
        <button id="createButton" onclick="showCreateRow()">Crear</button>

        <table>
            <thead>
                <tr>
                    <th data-field="nombre">Nombre</th> 
                    <th data-field="email">Email</th>
                    <th data-field="tipo">Tipo</th>                  
                    <th data-field="fecha_registro">Fecha de Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                    $proveedorBusiness = new ProveedorBusiness();
                    $result = $proveedorBusiness->getAllTBProveedor();

                    if ($result["success"]) {
                        $listaProveedores = $result["listaProveedores"];

                        foreach ($listaProveedores as $current) {
                            $fechaFormateada = Utils::formatearFecha($current->getProveedorFechaRegistro());
                            $fechaISO = Utils::formatearFecha($current->getProveedorFechaRegistro(), 'Y-MM-dd');

                            echo '<tr data-id="' . $current->getProveedorID() . '">';
                            echo '<td data-field="nombre">' . $current->getProveedorNombre() . '</td>'; 
                            echo '<td data-field="email">' . $current->getProveedorEmail() . '</td>';
                            echo '<td data-field="tipo">' . $current->getProveedorTipo() . '</td>';
                            echo '<td data-field="fecha_registro" data-iso="' . $fechaISO . '">' . $fechaFormateada . '</td>';
                            echo '<td>';
                            echo '<button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>';
                            echo '<button onclick="deleteRow(' . $current->getProveedorID() . ')">Eliminar</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr> <td colspan="5"> <p style="color: red; text-align: center;">' . $result["message"] . '</p> </td> </tr>';
                    }
                ?>
            </tbody>
        </table>
        <a href="../index.php" class="menu-button">Regresar al Menú</a>
        <script src="./js/proveedor.js"></script>
    </body>
</html>
