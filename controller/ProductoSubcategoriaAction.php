<?php

    require_once __DIR__ . '/../service/productoSubcategoriaBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $productoID = isset($_POST['producto']) ? intval($_POST['producto']) : null;
        $subcategoriaID = isset($_POST['subcategoria']) ? intval($_POST['subcategoria']) : null;
        
        // Se crea el Service para las operaciones
        $productoSubcategoriaBusiness = new ProductoSubcategoriaBusiness();

        // Crea y verifica que los ID de la subcategoria y del producto sean correctos
        $check = $productoSubcategoriaBusiness->validarProductoSubcategoria($productoID, $subcategoriaID);

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'agregar':
                    // Agrega una subcategoría a un producto en la base de datos.
                    $response = $productoSubcategoriaBusiness->addSubcategoriaToProducto($productoID, $subcategoriaID);
                    break;
                case 'eliminar':
                    // Elimina una subcategoría de un producto en la base de datos.
                    $response = $productoSubcategoriaBusiness->removeSubcategoriaFromProducto($productoID, $subcategoriaID);
                    break;
                default:
                    // Error en caso de que la accion no sea válida
                    $response['success'] = false;
                    $response['message'] = "Acción no válida.";
                    break;
            }
        } else {
            // Si los datos no son validos, se devuelve un mensaje de error
            $response['success'] = $check['is_valid'];
            $response['message'] = $check['message'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $accion = isset($_GET['accion']) ? $_GET['accion'] : "";
        $deleted = isset($_GET['deleted']) ? boolval($_GET['deleted']) : false;
        $onlyActiveOrInactive = isset($_GET['filter']) ? boolval($_GET['filter']) : true;
        $productoID = isset($_GET['producto']) ? intval($_GET['producto']) : null;

        // Se crea el Service para las operaciones
        $productoSubcategoriaBusiness = new ProductoSubcategoriaBusiness();

        // Crea y verifica que el ID del producto sea correcto
        $check = $productoSubcategoriaBusiness->validarProductoSubcategoria($productoID, null, false);

        if ($check['is_valid']) {
            switch ($accion) {
                case 'todo':
                    // Obtiene las subcategorías de un producto en la base de datos.
                    $response = $productoSubcategoriaBusiness->getSubcategoriasByProducto($productoID, true);
                    break;
                default:
                    // Obtener parámetros de la solicitud GET
                    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                    // Validar los parámetros
                    if ($page < 1) $page = 1;
                    if ($size < 1) $size = 5;

                    // Obtiene las subcategorías de un producto en la base de datos.
                    $response = $productoSubcategoriaBusiness->getPaginatedSubcategoriasByProducto($productoID, $page, $size, $sort, $onlyActiveOrInactive, $deleted);
                    break;
            }
        } else {
            // Si los datos no son validos, se devuelve un mensaje de error
            $response['success'] = $check['is_valid'];
            $response['message'] = $check['message'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>