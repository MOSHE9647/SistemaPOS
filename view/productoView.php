<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos | SistemaPOS</title>
    <?php 
        include __DIR__ . '/../service/productoBusiness.php'; 
        require_once __DIR__ . '/../utils/Utils.php';
    ?>
    <link rel="stylesheet" href="./css/producto.css">
</head>
<body>

    <h2>Lista de Productos</h2>

    <div id="message"></div>

    <!-- Botón para crear nuevo producto -->
    <button id="createButton" onclick="showCreateRow()">Crear</button>

    <table>
        <thead>
            <tr>
                <th data-field="nombreproducto">Nombre</th>
                <th data-field="preciounitarioproducto">Precio Unitario</th>
                <th data-field="cantidadproducto">Cantidad</th>
                <th data-field="fechaadquisicionproducto">Fecha Adquisición</th>
                <th data-field="descripcionproducto">Descripción</th>
                <th data-field="estadoproducto">Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php
                $productoBusiness = new ProductoBusiness();
                $result = $productoBusiness->getAllTBProducto();

                if ($result["success"]) {
                    $listaProductos = $result["listaProductos"];

                    foreach ($listaProductos as $current) {
                        $fechaFormateada = Utils::formatearFecha($current->getFechaAdquisicionProducto());
                        $fechaISO = Utils::formatearFecha($current->getFechaAdquisicionProducto(), 'Y-MM-dd');

                        echo '<tr data-id="' . $current->getIdProducto() . '">';
                        echo '<td data-field="nombreproducto">' . $current->getNombreProducto() . '</td>';
                        echo '<td data-field="preciounitarioproducto">₡' . number_format($current->getPrecioUnitarioProducto(), 2) . '</td>';
                        echo '<td data-field="cantidadproducto">' . $current->getCantidadProducto() . '</td>';
                        echo '<td data-field="fechaadquisicionproducto" data-iso="' . $fechaISO . '">' . $fechaFormateada . '</td>';
                        echo '<td data-field="descripcionproducto">' . $current->getDescripcionProducto() . '</td>';
                        echo '<td data-field="estadoproducto">' . $current->getEstadoProducto() . '</td>';
                        echo '<td>';
                        echo '<button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>';
                        echo '<button onclick="deleteRow(' . $current->getIdProducto() . ')">Eliminar</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr> <td colspan="7"> <p style="color: red; text-align: center;">' . $result["message"] . '</p> </td> </tr>';
                }
            ?>
        </tbody>
    </table>
    <a href="../index.php" class="menu-button">Regresar al Menú</a>
    <script src="./js/producto.js"></script>
</body>
</html>
