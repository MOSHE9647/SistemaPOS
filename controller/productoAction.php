<?php

    require_once dirname(__DIR__, 1) . '/service/productoBusiness.php';
    require_once dirname(__DIR__, 1) . '/service/codigoBarrasBusiness.php';
    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    $response = [];                                         //<- Respuesta a enviar al cliente
    $method = $_SERVER["REQUEST_METHOD"];                   //<- Método de la solicitud
    $productoBusiness = new ProductoBusiness();             //<- Lógica de negocio de Producto
    $codigoBarrasBusiness = new CodigoBarrasBusiness();     //<- Lógica de negocio de Código de Barras

    if ($method == "POST") {
        // Acción a realizar en el controlador
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            Utils::enviarRespuesta(400, false, "No se ha especificado una acción.");
        }

        // Datos del Producto recibidos en la solicitud
        $id                 = isset($_POST['id'])                   ? intval($_POST['id'])              : -1;
        $codigoBarrasID     = isset($_POST['codigobarras'])         ? intval($_POST['codigobarras'])    : -1;
        $codigoBarrasNumero = isset($_POST['codigobarrasnumero'])   ? $_POST['codigobarrasnumero']      : "";
        $nombre             = isset($_POST['nombre'])               ? $_POST['nombre']                  : "";
        $cantidad           = isset($_POST['cantidad'])             ? intval($_POST['cantidad'])        :  0;
        $precioCompra       = isset($_POST['preciocompra'])         ? floatval($_POST['preciocompra'])  :  0;
        $ganancia           = isset($_POST['ganancia'])             ? floatval($_POST['ganancia'])      :  0;
        $descripcion        = isset($_POST['descripcion'])          ? $_POST['descripcion']             : "";
        $categoriaID        = isset($_POST['categoria'])            ? intval($_POST['categoria'])       : -1;
        $subcategoriaID     = isset($_POST['subcategoria'])         ? intval($_POST['subcategoria'])    : -1;
        $marcaID            = isset($_POST['marca'])                ? intval($_POST['marca'])           : -1;
        $presentacionID     = isset($_POST['presentacion'])         ? intval($_POST['presentacion'])    : -1;
        $vencimiento        = isset($_POST['vencimiento'])          ? $_POST['vencimiento']             : "";
        $imagen             = isset($_FILES['imagen'])              ? $_FILES['imagen']                 : null;

        // Validar si se ha enviado un código de barras
        if (empty($codigoBarrasNumero) && $accion != 'eliminar') {
            $barcode = $codigoBarrasBusiness->generarCodigoDeBarras();
            if (!$barcode['success']) {
                Utils::enviarRespuesta(500, false, $barcode['message']);
            }
            $codigoBarrasNumero = $barcode['code'];
        }

        // Crear un objeto Producto con los datos recibidos
        $codigoBarras = new CodigoBarras($codigoBarrasID, $codigoBarrasNumero);
        $producto = new Producto(
            $id, $codigoBarras, $nombre, $cantidad, $precioCompra, $ganancia, $descripcion, 
            new Categoria($categoriaID), new Subcategoria($subcategoriaID), new Marca($marcaID), 
            new Presentacion($presentacionID), $imagen, $vencimiento
        );

        // Realizar la acción solicitada si los datos son válidos
        $check = $productoBusiness->validarProducto($producto, $accion != 'eliminar', $accion == 'insertar');
        if ($check['is_valid']) {
            switch ($accion) {
                case 'insertar':
                    $response = $productoBusiness->insertTBProducto($producto);
                    break;
                case 'actualizar':
                    $response = $productoBusiness->updateTBProducto($producto);
                    break;
                case 'eliminar':
                    $response = $productoBusiness->deleteTBProducto($id);
                    break;
                default:
                    Utils::enviarRespuesta(400, false, "Acción no válida.");
            }
        } else {
            Utils::enviarRespuesta(400, false, $check['message']);
        }

        // Enviar respuesta al cliente
        http_response_code($response['success'] ? 200 : 400);
        header("Content-Type: application/json");
        echo json_encode($response);
        exit();
    } 
    
    else if ($method == "GET") {
        // Parámetros de la solicitud
        $accion     = isset($_GET['accion'])    ? $_GET['accion']           : "";
        $deleted    = isset($_GET['deleted'])   ? boolval($_GET['deleted']) : false;
        $onlyActive = isset($_GET['filter'])    ? boolval($_GET['filter'])  : true;

        // Realizar la acción solicitada
        switch ($accion) {
            case 'codigo':
                // Obtener un producto por su código de barras
                $codigoBarras = $_GET['codigo'] ?? "";
                $response = $productoBusiness->getProductoByCodigoBarras($codigoBarras);
                break;
            case 'all':
                // Obtener todos los productos
                $response = $productoBusiness->getAllTBProducto($onlyActive, $deleted);
                break;
            case 'id':
                // Obtener un producto por su ID
                $productoID = intval($_GET['id'] ?? -1);
                $response = $productoBusiness->getProductoByID($productoID, $onlyActive, $deleted);
                break;
            default:
                // Parámetros de paginación
                $search = isset($_GET['search']) ? $_GET['search']          : null;
                $page   = isset($_GET['page'])   ? intval($_GET['page'])    : 1;
                $size   = isset($_GET['size'])   ? intval($_GET['size'])    : 5;
                $sort   = isset($_GET['sort'])   ? $_GET['sort']            : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                // Obtener los productos paginados
                $response = $productoBusiness->getPaginatedProductos($search, $page, $size, $sort, $onlyActive, $deleted);
                break;
        }

        // Enviar respuesta al cliente
        http_response_code($response['success'] ? 200 : 400);
        header("Content-Type: application/json");
        echo json_encode($response);
        exit();
    } 
    
    else {
        // Enviar respuesta de método no permitido
        Utils::enviarRespuesta(405, false, "Método no permitido ($method).");
    }

?>