<?php
    require_once __DIR__ . '/../service/productoBusiness.php';
    require_once __DIR__ . '/../service/codigoBarrasBusiness.php';

    $response = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Acción que se va a realizar
        $accion = $_POST['accion'] ?? "";
        if (empty($accion)) {
            $response = [
                'success' => false,
                'message' => "No se ha especificado una acción."
            ];
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Datos del Producto recibidos en la solicitud
        $id                 = isset($_POST['id'])                   ? intval($_POST['id'])              : -1;
        $codigoBarrasID     = isset($_POST['codigobarras'])         ? intval($_POST['codigobarras'])    : -1;
        $codigoBarrasNumero = isset($_POST['codigobarrasnumero'])   ? $_POST['codigobarrasnumero']      : "";
        $nombre             = isset($_POST['nombre'])               ? $_POST['nombre']                  : "";
        $precioCompra       = isset($_POST['preciocompra'])         ? floatval($_POST['preciocompra'])  :  0;
        $ganancia           = isset($_POST['ganancia'])             ? floatval($_POST['ganancia'])      :  0;
        $descripcion        = isset($_POST['descripcion'])          ? $_POST['descripcion']             : "";
        $categoriaID        = isset($_POST['categoria'])            ? intval($_POST['categoria'])       : -1;
        $subcategoriaID     = isset($_POST['subcategoria'])         ? intval($_POST['subcategoria'])    : -1;
        $marcaID            = isset($_POST['marca'])                ? intval($_POST['marca'])           : -1;
        $presentacionID     = isset($_POST['presentacion'])         ? intval($_POST['presentacion'])    : -1;
        $imagen             = isset($_FILES['imagen'])              ? $_FILES['imagen']                 : null;

        // Se crea el Service para las operaciones
        $productoBusiness = new ProductoBusiness();
        $codigoBarrasBusiness = new CodigoBarrasBusiness();

        // Si no se ha especificado un código de barras, se genera uno
        if (empty($codigoBarrasNumero) && $accion != 'eliminar') {
            $barcode = $codigoBarrasBusiness->generarCodigoDeBarras();
            if (!$barcode['success']) {
                $response = $barcode;
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }
            $codigoBarrasNumero = $barcode['code'];
        }
        
        // Crea y verifica que los datos del producto sean correctos
        $codigoBarras = new CodigoBarras($codigoBarrasID, $codigoBarrasNumero);
        $producto = new Producto(
            $id, $codigoBarras, $nombre, $precioCompra, $ganancia, new Categoria($categoriaID), new Subcategoria($subcategoriaID),
            new Marca($marcaID), new Presentacion($presentacionID), $descripcion, $imagen
        );

        $check = $productoBusiness->validarProducto($producto, $accion != 'eliminar', $accion == 'insertar'); //<- Indica si se validan (o no) los campos además del ID
        if ($check['is_valid']) {
            // Si los datos son válidos se realiza la acción correspondiente
            switch ($accion) {
                case 'insertar':
                    // Inserta el producto en la base de datos
                    $response = $productoBusiness->insertTBProducto($producto);
                    break;
                case 'actualizar':
                    // Actualiza la info del producto en la base de datos
                    $response = $productoBusiness->updateTBProducto($producto);
                    break;
                case 'eliminar':
                    $response = $productoBusiness->deleteTBProducto($id);
                    // Elimina el producto de la base de datos
                    break;
                default:
                    // Error en caso de que la acción no sea válida
                    $response['success'] = false;
                    $response['message'] = "Acción no válida.";
                    break;
            }
        } else {
            // Si los datos no son válidos, se devuelve un mensaje de error
            $response['success'] = $check['is_valid'];
            $response['message'] = $check['message'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    else if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $accion = isset($_GET['accion']) ? $_GET['accion'] : "";
        $deleted = isset($_GET['deleted']) ? boolval($_GET['deleted']) : false;
        $onlyActive = isset($_GET['filter']) ? boolval($_GET['filter']) : true;

        $productoBusiness = new ProductoBusiness();
        switch ($accion) {
            case 'all':
                $response = $productoBusiness->getAllTBProducto($onlyActive, $deleted);
                break;
            case 'id':
                $productoID = isset($_GET['id']) ? intval($_GET['id']) : -1;
                $response = $productoBusiness->getProductoByID($productoID, $onlyActive, $deleted);
                break;
            default:
                // Obtener parámetros de la solicitud GET
                $search = isset($_GET['search']) ? $_GET['search'] : null;
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

                // Validar los parámetros
                if ($page < 1) $page = 1;
                if ($size < 1) $size = 5;

                $response = $productoBusiness->getPaginatedProductos($search, $page, $size, $sort, $onlyActive, $deleted);
                break;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    else {
        $response['success'] = false;
        $response['message'] = "Método no permitido (" . $_SERVER["REQUEST_METHOD"] . ").";

        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

?>