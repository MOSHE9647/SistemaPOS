<?php

    require_once __DIR__ . '/../service/productoCategoriaBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : "";

        // Datos recibidos en la solicitud (Form)
        $productoID = isset($_POST['producto']) ? intval($_POST['producto']) : null;
        $categoriaID = isset($_POST['categoria']) ? intval($_POST['categoria']) : null;
        
        // Se crea el Service para las operaciones
        $productoCategoriaBusiness = new ProductoCategoriaBusiness();

        // Crea y verifica que los ID de la categoria y del producto sean correctos
        $check = $productoCategoriaBusiness->validarProductoCategoria($productoID, $categoriaID);

        // Si los datos son válidos se realiza acción correspondiente
        if ($check['is_valid']) {
            switch ($accion) {
                case 'agregar':
                    // Agrega una subcategoría a un producto en la base de datos.
                    $response = $productoCategoriaBusiness->addCategoriaToProducto($productoID, $categoriaID);
                    break;
                case 'eliminar':
                    // Elimina una subcategoría de un producto en la base de datos.
                    $response = $productoCategoriaBusiness->removeCategoriaFromProducto($productoID, $categoriaID);
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
        $productoCategoriaBusiness = new ProductoCategoriaBusiness();

        // Crea y verifica que el ID del producto sea correcto
        $check = $productoCategoriaBusiness->validarProductoCategoria($productoID, null, false);

        if ($check['is_valid']) {
            switch ($accion) {
                case 'todo':
                    // Obtiene las subcategorías de un producto en la base de datos.
                    $response = $productoCategoriaBusiness->getCategoriasByProducto($productoID, true);
                    break;

                    case 'listarProductoCategorias':
                        // Este es el bloque que quieres insertar
                        $result = $productoCategoriaBusiness->getAllTBProductoCategoria();
                        header('Content-Type: application/json');
                        echo json_encode($result);
                        exit();

                default:
                    // Obtener parámetros de la solicitud GET
                    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                    // Validar los parámetros
                    if ($page < 1) $page = 1;
                    if ($size < 1) $size = 5;

                    // Obtiene las subcategorías de un producto en la base de datos.
                    $response = $productoCategoriaBusiness->getPaginatedCategoriasByProducto($productoID, $page, $size, $sort, $onlyActiveOrInactive, $deleted);
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