<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Impuestos | SistemaPOS</title>
        <?php 
            include __DIR__ . '/../service/impuestoBusiness.php'; 
            require_once __DIR__ . '/../utils/Utils.php';
        ?>
        <link rel="stylesheet" href="./css/styles.css">
    </head>
    <body>

        <h2>Lista de Impuestos</h2>

        <div id="message"></div>

        <!-- Botón para crear nuevo impuesto -->
        <button id="createButton" onclick="showCreateRow()">Crear</button>

        <table>
            <thead>
                <tr>
                    <th data-field="nombre">Nombre</th>
                    <th data-field="valor">Valor</th>
                    <th data-field="descripcion">Descripción</th>
                    <th data-field="fecha_vigencia">Fecha Vigencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                    $impuestoBusiness = new ImpuestoBusiness();
                    $result = $impuestoBusiness->getAllTBImpuesto();

                    if ($result["success"]) {
                        $listaImpuestos = $result["listaImpuestos"];

                        foreach ($listaImpuestos as $current) {
                            $fechaFormateada = Utils::formatearFecha($current->getImpuestoFechaVigencia());
                            $fechaISO = Utils::formatearFecha($current->getImpuestoFechaVigencia(), 'Y-MM-dd');

                            echo '<tr data-id="' . $current->getImpuestoID() . '">';
                            echo '<td data-field="nombre">' . $current->getImpuestoNombre() . '</td>';
                            echo '<td data-field="valor">' . $current->getImpuestoValor() . '%</td>';
                            echo '<td data-field="descripcion">' . $current->getImpuestoDescripcion() . '</td>';
                            echo '<td data-field="fecha_vigencia" data-iso="' . $fechaISO . '">' . $fechaFormateada . '</td>';
                            echo '<td>';
                            echo '<button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>';
                            echo '<button onclick="deleteRow(' . $current->getImpuestoID() . ')">Eliminar</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr> <td colspan="6"> <p style="color: red; text-align: center;">' . $result["message"] . '</p> </td> </tr>';
                    }
                ?>
            </tbody>
        </table>
        <a href="../index.php" class="menu-button">Regresar al Menú</a>
        <script src="./js/impuesto.js"></script>
    </body>
</html>